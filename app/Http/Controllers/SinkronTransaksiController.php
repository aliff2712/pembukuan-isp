<?php

namespace App\Http\Controllers;

use App\Exports\TransaksiExport;
use App\Models\SinkronTransaksi;
use App\Services\BillingApiService;
use App\Services\PaymentImportService;
use App\Services\SinkronTransaksiService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
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

        // Auto lock saat load
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
    // IMPORT — sekarang lewat staging dulu, bukan langsung jurnal
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

        // Alihkan ke PaymentImportService → masuk staging dulu
        $importService = new PaymentImportService();
        $summary       = $importService->process($transaksis);

        $message = "Import selesai: {$summary['total_approved']} approved";

        if ($summary['total_flagged'] > 0) {
            $message .= " | ⚠️ {$summary['total_flagged']} flagged — perlu review manual";
        }

        $flashType = $summary['total_flagged'] > 0 ? 'warning' : 'success';

        return back()->with($flashType, $message);
    }

    // =========================================================
    // EXPORT
    // =========================================================
   public function export(Request $request)
{
    $query = SinkronTransaksi::orderBy('tanggal_bayar', 'desc');

    // Jika export semua data, skip semua filter
    if (!$request->boolean('all')) {
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_pelanggan', 'like', '%' . $request->search . '%');
            });
        }

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
    }

    $data = $query->get();

    $suffix = $request->boolean('all') ? 'semua' : 'filter';

    return Excel::download(
        new TransaksiExport($data),
        'transaksi_' . $suffix . '_' . now()->format('Y_m_d') . '.xlsx'
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