<?php

namespace App\Http\Controllers;

use App\Models\SinkronBelumBayar;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class BelumBayarController extends Controller
{
    public function index(Request $request)
    {
        $query = SinkronBelumBayar::orderBy('bulan', 'desc');

        if ($request->filled('bulan')) {
            $query->where('bulan', $request->bulan);
        }
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->where('nama_pelanggan', 'like', '%' . $request->search . '%');
        }

        $data         = $query->paginate(20)->withQueryString();
        $totalTagihan = SinkronBelumBayar::sum('total_tagihan');
        $totalData    = SinkronBelumBayar::count();

        return view('pembukuan.belum-bayar', compact(
            'data', 'totalTagihan', 'totalData'
        ));
    }

    public function import(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'bulan.regex' => 'Format bulan tidak valid. Gunakan format YYYY-MM.',
        ]);

        try {
            $response = Http::withHeaders(['Accept' => 'application/json'])
                ->withOptions(['verify' => true, 'timeout' => 30])
                ->get(config('services.billing.url') . '/api/tagihan-belum-bayar', [
                    'bulan' => $request->bulan,
                ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return back()->with('error', 'Server billing tidak dapat dihubungi.');
        }

        if (!$response->ok()) {
            return back()->with('error', 'Gagal mengambil data dari billing (HTTP ' . $response->status() . ').');
        }

        $tagihans = $response->json('data');

        if (empty($tagihans)) {
            return back()->with('error', 'Tidak ada data tagihan belum bayar.');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($tagihans as $t) {

            $required = ['id_pelanggan', 'nama_pelanggan', 'bulan'];
            $valid = true;

            foreach ($required as $field) {
                if (!isset($t[$field])) {
                    Log::warning("SinkronBelumBayar: field '{$field}' tidak ada", ['data' => $t]);
                    $valid = false;
                    break;
                }
            }

            if (!$valid) {
                $skipped++;
                continue;
            }

            $result = SinkronBelumBayar::updateOrCreate(
                [
                    'id_pelanggan_billing' => (int) $t['id_pelanggan'],
                    'bulan'                => (string) $t['bulan'],
                ],
                [
                    'nama_pelanggan'   => (string) substr($t['nama_pelanggan'] ?? '', 0, 150),
                    'area'             => $t['area']  ?? null,
                    'paket'            => $t['paket'] ?? null,
                    'harga_paket'      => isset($t['harga_paket'])      ? (float) $t['harga_paket']      : 0,
                    'biaya_tambahan_1' => isset($t['biaya_tambahan_1']) ? (float) $t['biaya_tambahan_1'] : 0,
                    'biaya_tambahan_2' => isset($t['biaya_tambahan_2']) ? (float) $t['biaya_tambahan_2'] : 0,
                    'diskon'           => isset($t['diskon'])           ? (float) $t['diskon']           : 0,
                    'total_tagihan'    => isset($t['total_tagihan'])    ? (float) $t['total_tagihan']    : 0,
                    'status'           => $t['status'] ?? 'belum_lunas',
                ]
            );

            $result->wasRecentlyCreated ? $imported++ : $skipped++;
        }

        return back()->with(
            'success',
            "Sinkron belum bayar selesai: {$imported} data baru, {$skipped} diperbarui."
        );
    }

    public function delete(Request $request)
    {
        $key = 'delete-belum-bayar:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'bulan.regex' => 'Format bulan tidak valid. Gunakan format YYYY-MM.',
        ]);

        $deleted = SinkronBelumBayar::where('bulan', $request->bulan)->delete();

        Log::info('DeleteBelumBayar: selesai', [
            'user'    => Auth::user()->name ?? '-',
            'bulan'   => $request->bulan,
            'deleted' => $deleted,
        ]);

        return back()->with('success', "Hapus data belum bayar bulan {$request->bulan} selesai: {$deleted} data dihapus.");
    }

    public function deleteById($id)
    {
        $tagihan = SinkronBelumBayar::findOrFail($id);
        $nama  = $tagihan->nama_pelanggan;
        $bulan = $tagihan->bulan;

        $tagihan->delete();

        Log::info('DeleteBelumBayarById: selesai', [
            'user'  => Auth::user()->name ?? '-',
            'id'    => $id,
            'nama'  => $nama,
            'bulan' => $bulan,
        ]);

        return back()->with('success', "Data tagihan {$nama} bulan {$bulan} berhasil dihapus.");
    }
}