<?php

namespace App\Http\Controllers;

use App\Exports\TransaksiExport;
use App\Models\SinkronTransaksi;
use App\Services\BillingApiService;
use App\Services\SinkronJournalizeService;
use App\Services\SinkronTransaksiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Maatwebsite\Excel\Facades\Excel;

class SinkronTransaksiController extends Controller
{
    private BillingApiService $billing;
    private SinkronTransaksiService $service;

    public function __construct()
    {
        $this->billing = new BillingApiService();
        $this->service = new SinkronTransaksiService();
    }

    // =========================================================
    // INDEX
    // =========================================================
    public function index(Request $request)
    {
        $query = SinkronTransaksi::orderBy('tanggal_bayar', 'desc');

        if ($request->filled('bulan_filter')) {
            $query->where('bulan_tagihan', 'like', $request->bulan_filter . '%');
        }
        if ($request->filled('area')) {
            $query->where('area', $request->area);
        }
        if ($request->filled('metode')) {
            $query->where('metode', $request->metode);
        }
        if ($request->filled('dibayar_oleh')) {
            $query->where('dibayar_oleh', $request->dibayar_oleh);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_transaksi', 'like', '%' . $request->search . '%');
            });
        }

        $transaksi = $query->paginate(20)->withQueryString();

        // 🔥 AUTO LOCK saat load
        $transaksi->getCollection()->each(function ($trx) {
            $this->service->autoLock($trx);
        });

        $totalNominal   = SinkronTransaksi::sum('jumlah');
        $totalTransaksi = SinkronTransaksi::count();

        $perAdmin = SinkronTransaksi::selectRaw(
            'dibayar_oleh, COUNT(*) as jumlah_transaksi, SUM(jumlah) as total_nominal'
        )->groupBy('dibayar_oleh')->get();

        $areaList   = SinkronTransaksi::distinct()->orderBy('area')->pluck('area');
        $metodeList = SinkronTransaksi::distinct()->orderBy('metode')->pluck('metode');
        $adminList  = SinkronTransaksi::distinct()->orderBy('dibayar_oleh')->pluck('dibayar_oleh');

        return view('pembukuan.sinkron', compact(
            'transaksi', 'totalNominal', 'totalTransaksi', 'perAdmin',
            'areaList', 'metodeList', 'adminList'
        ));
    }

    // =========================================================
    // IMPORT
    // =========================================================
    public function import(Request $request)
    {
        $key = 'sinkron-transaksi:' . Auth::id();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }

        RateLimiter::hit($key, 60);

        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'bulan.regex' => 'Format bulan tidak valid. Gunakan format YYYY-MM.',
        ]);

        $body = $this->billing->getTransaksiLunas($request->bulan);

        if (!$body['success']) {
            return back()->with('error', 'Gagal mengambil data dari billing.');
        }

        $transaksis = $body['data'];

        if (empty($transaksis)) {
            return back()->with('error', 'Tidak ada data.');
        }

        if (count($transaksis) > 1000) {
            return back()->with('error', 'Maksimal 1000 data per import.');
        }

        $imported = 0;
        $skipped  = 0;
        $warned   = 0;

        $allowedStatus = ['lunas'];
        $allowedMetode = ['cash', 'transfer', 'online', 'qris'];

        $journalizer = new SinkronJournalizeService();

        foreach ($transaksis as $trx) {

            if (!$this->service->validate($trx)) {
                $skipped++;
                continue;
            }

            $existing = SinkronTransaksi::where('id_transaksi_billing', $trx['id_transaksi'])->first();

            if ($existing && $this->service->shouldSkipUpdate($existing, $trx)) {
                $warned++;
                continue;
            }

            $result = SinkronTransaksi::updateOrCreate(
                ['id_transaksi_billing' => (int) $trx['id_transaksi']],
                $this->service->map($trx, $allowedMetode, $allowedStatus)
            );

            $result->wasRecentlyCreated ? $imported++ : $skipped++;

            $result->refresh();

            // 🔥 AUTO JOURNAL
            if (!$result->is_journalized) {
                $journalizer->journalize($result);
            }

            // 🔒 AUTO LOCK
            $this->service->autoLock($result);
        }

        $summary = $journalizer->getSummary();

        $message  = "Import: {$imported} baru, {$skipped} skip";
        $message .= " | Jurnal: {$summary['created']} dibuat";
        if ($warned > 0) {
            $message .= " | ⚠️ {$warned} berubah setelah jurnal";
        }

        return back()->with('success', $message);
    }

    // =========================================================
    // EXPORT
    // =========================================================
    public function export(Request $request)
    {
        $query = SinkronTransaksi::orderBy('tanggal_bayar', 'desc');

        if ($request->filled('bulan_filter')) {
            $query->where('bulan_tagihan', 'like', $request->bulan_filter . '%');
        }

        $data = $query->get();

        return Excel::download(
            new TransaksiExport($data),
            'transaksi_' . now()->format('Y_m_d') . '.xlsx'
        );
    }

    // =========================================================
    // DELETE BULK
    // =========================================================
    public function delete(Request $request)
    {
        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ]);

        $rows = SinkronTransaksi::where('bulan_tagihan', 'like', $request->bulan . '%')->get();

        $lockedCount = $rows->filter(fn($trx) => $this->service->isLocked($trx))->count();

        if ($lockedCount > 0) {
            return back()->with('error', "{$lockedCount} data sudah terkunci.");
        }

        SinkronTransaksi::where('bulan_tagihan', 'like', $request->bulan . '%')->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }

    // =========================================================
    // DELETE BY ID
    // =========================================================
    public function deleteById($id)
    {
        $trx = SinkronTransaksi::findOrFail($id);

        $this->service->autoLock($trx);

        if ($this->service->isLocked($trx)) {
            return back()->with('error', 'Data sudah terkunci.');
        }

        $trx->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }
}