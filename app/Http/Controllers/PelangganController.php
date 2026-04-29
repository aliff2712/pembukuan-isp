<?php

namespace App\Http\Controllers;

use App\Exports\PelangganExport;
use App\Models\SinkronPelanggan;
use App\Services\BillingApiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Facades\Excel;

class PelangganController extends Controller
{
    private BillingApiService $billing;

    public function __construct()
    {
        $this->billing = new BillingApiService();
    }

    public function index(Request $request)
    {
        $query = SinkronPelanggan::orderBy('nama');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama',  'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $pelanggan      = $query->paginate(20)->withQueryString();
        $totalPelanggan = SinkronPelanggan::count();
        $totalTagihan   = SinkronPelanggan::sum('total_tagihan');

        $perArea = SinkronPelanggan::selectRaw('area, COUNT(*) as jumlah, SUM(total_tagihan) as total')
            ->groupBy('area')
            ->get();

        return view('pembukuan.pelanggan', compact(
            'pelanggan', 'totalPelanggan', 'totalTagihan', 'perArea'
        ));
    }

    public function import(Request $request)
    {
        $key = 'sinkron-pelanggan:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        $body = $this->billing->getPelanggan();

        if (!$body['success']) {
            $msg = isset($body['error']) && $body['error'] === 'connection'
                ? 'Server billing tidak dapat dihubungi. Coba beberapa saat lagi.'
                : 'Gagal mengambil data pelanggan dari billing. Periksa log untuk detail.';
            return back()->with('error', $msg);
        }

        $pelanggans = $body['data'];

        if (empty($pelanggans)) {
            return back()->with('error', 'Tidak ada data pelanggan.');
        }

        if (count($pelanggans) > 2000) {
            return back()->with('error', 'Data terlalu banyak, maksimal 2000 per import.');
        }

        $imported      = 0;
        $skipped       = 0;
        $allowedStatus = ['aktif', 'nonaktif'];

        foreach ($pelanggans as $p) {

            if (!isset($p['id'], $p['nama'])) {
                Log::warning('SinkronImportPelanggan: field wajib tidak ada', ['p' => $p]);
                $skipped++;
                continue;
            }

            if (!is_numeric($p['harga_paket'] ?? 0) || !is_numeric($p['total_tagihan'] ?? 0)) {
                $skipped++;
                continue;
            }

            $ipAddress = null;
            if (!empty($p['ip_address'])) {
                $ipAddress = filter_var($p['ip_address'], FILTER_VALIDATE_IP) ? $p['ip_address'] : null;
            }

            $result = SinkronPelanggan::updateOrCreate(
                ['id_pelanggan_billing' => (int) $p['id']],
                [
                    'nama'             => (string) substr($p['nama'] ?? '', 0, 150),
                    'phone'            => isset($p['phone'])       ? (string) substr($p['phone'], 0, 20)  : null,
                    'paket'            => isset($p['paket'])       ? (string) substr($p['paket'], 0, 100) : null,
                    'harga_paket'      => (float) ($p['harga_paket']  ?? 0),
                    'area'             => isset($p['area'])        ? (string) substr($p['area'], 0, 100)  : null,
                    'ip_address'       => $ipAddress,
                    'diskon'           => min(100, max(0, (float) ($p['diskon'] ?? 0))),
                    'total_tagihan'    => (float) ($p['total_tagihan'] ?? 0),
                    'tanggal_register' => $p['tanggal_register'] ?? null,
                    'status'           => in_array($p['status'] ?? '', $allowedStatus) ? $p['status'] : 'aktif',
                ]
            );

            $result->wasRecentlyCreated ? $imported++ : $skipped++;
        }

        Log::info('SinkronImportPelanggan: selesai', [
            'user'     => Auth::user()->name ?? '-',
            'imported' => $imported,
            'skipped'  => $skipped,
        ]);

        return back()->with('success', "Import selesai: {$imported} pelanggan baru, {$skipped} diperbarui.");
    }

    public function export(Request $request)
    {
        Log::info('Export pelanggan dilakukan', [
            'user_id' => Auth::id(),
            'user'    => Auth::user()->name ?? '-',
            'ip'      => $request->ip(),
            'at'      => now()->toDateTimeString(),
        ]);

        $pelanggan = SinkronPelanggan::orderBy('nama')->get();
        $filename  = 'pelangganPerTanggal_' . now()->format('Y_m_d') . '.xlsx';

        return Excel::download(new PelangganExport($pelanggan), $filename);
    }

    public function delete(Request $request)
    {
        $key = 'delete-pelanggan:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        if (!$request->filled('confirm') || $request->confirm !== 'DELETE_ALL') {
            return back()->with('error', 'Konfirmasi diperlukan. Masukkan "DELETE_ALL" untuk menghapus semua data pelanggan.');
        }

        $deleted = SinkronPelanggan::query()->delete();

        Log::info('DeletePelanggan: selesai', [
            'user'    => Auth::user()->name ?? '-',
            'deleted' => $deleted,
        ]);

        return back()->with('success', "Hapus semua data pelanggan selesai: {$deleted} data dihapus.");
    }

    public function deleteById($id)
    {
        $pelanggan = SinkronPelanggan::findOrFail($id);
        $nama = $pelanggan->nama;

        $pelanggan->delete();

        Log::info('DeletePelangganById: selesai', [
            'user' => Auth::user()->name ?? '-',
            'id'   => $id,
            'nama' => $nama,
        ]);

        return back()->with('success', "Data pelanggan {$nama} berhasil dihapus.");
    }
}