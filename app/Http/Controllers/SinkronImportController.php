<?php

namespace App\Http\Controllers;
use App\Enums\JournalSourceType;
use App\Models\JournalEntry;
use App\Models\SinkronBelumBayar;
use App\Models\SinkronPelanggan;
use App\Models\SinkronTransaksi;
use App\Services\BillingApiService;
use App\Services\SinkronJournalizeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class SinkronImportController extends Controller
{
    private BillingApiService $billing;

    public function __construct()
    {
        $this->billing = new BillingApiService();
    }

    // ===================== TRANSAKSI =====================

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

        $transaksi      = $query->paginate(20)->withQueryString();
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

    public function import(Request $request)
    {
        // Rate limiting
        $key = 'sinkron-transaksi:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        // Validasi input
        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'bulan.regex' => 'Format bulan tidak valid. Gunakan format YYYY-MM (contoh: 2025-04).',
        ]);

        // Ambil data via service
        $body = $this->billing->getTransaksiLunas($request->bulan);

        if (!$body['success']) {
            $msg = isset($body['error']) && $body['error'] === 'connection'
                ? 'Server billing tidak dapat dihubungi. Coba beberapa saat lagi.'
                : 'Gagal mengambil data dari billing. Periksa log untuk detail.';
            return back()->with('error', $msg);
        }

        $transaksis = $body['data'];

        if (empty($transaksis)) {
            return back()->with('error', 'Tidak ada data untuk bulan tersebut.');
        }

        if (count($transaksis) > 1000) {
            return back()->with('error', 'Data terlalu banyak, maksimal 1000 per import.');
        }

        $imported      = 0;
        $skipped       = 0;
        $warned        = 0;
        $allowedStatus = ['lunas'];
        $allowedMetode = ['cash', 'transfer', 'online', 'qris'];

        $journalizer = new SinkronJournalizeService();

        foreach ($transaksis as $trx) {

            // Validasi field wajib
            $required = ['id_transaksi', 'kode_transaksi', 'nama_pelanggan', 'jumlah', 'tanggal_bayar'];
            $valid = true;
            foreach ($required as $field) {
                if (!isset($trx[$field])) {
                    Log::warning("SinkronImport: field '{$field}' tidak ada", ['trx' => $trx]);
                    $valid = false;
                    break;
                }
            }
            if (!$valid) { $skipped++; continue; }

            // Validasi jumlah
            if (!is_numeric($trx['jumlah']) || (float) $trx['jumlah'] <= 0) {
                Log::warning('SinkronImport: jumlah tidak valid', ['jumlah' => $trx['jumlah']]);
                $skipped++;
                continue;
            }

            // Validasi tanggal
            if (!strtotime($trx['tanggal_bayar'])) {
                Log::warning('SinkronImport: tanggal tidak valid', ['tanggal' => $trx['tanggal_bayar']]);
                $skipped++;
                continue;
            }

            // Guard jurnal
            $existing = SinkronTransaksi::where('id_transaksi_billing', $trx['id_transaksi'])->first();
            if ($existing) {
                $sudahDijurnal = JournalEntry::where('source_type', JournalSourceType::SinkronBilling->value)
                    ->where('source_id', $existing->id)
                    ->exists();

                if ($sudahDijurnal && (float) $existing->jumlah !== (float) $trx['jumlah']) {
                    Log::warning('SinkronImport: jumlah berubah setelah dijurnal', [
                        'id'   => $existing->id,
                        'lama' => $existing->jumlah,
                        'baru' => $trx['jumlah'],
                    ]);
                    $warned++;
                    continue;
                }
            }

            $result = SinkronTransaksi::updateOrCreate(
                ['id_transaksi_billing' => (int) $trx['id_transaksi']],
                [
                    'kode_transaksi' => (string) substr($trx['kode_transaksi'] ?? '', 0, 50),
                    'nama_pelanggan' => (string) substr($trx['nama_pelanggan'] ?? '', 0, 150),
                    'area'           => isset($trx['area'])         ? (string) substr($trx['area'], 0, 100)         : null,
                    'paket'          => isset($trx['paket'])         ? (string) substr($trx['paket'], 0, 100)        : null,
                    'jumlah'         => (float) $trx['jumlah'],
                    'metode'         => in_array($trx['metode'] ?? '', $allowedMetode) ? $trx['metode'] : 'cash',
                    'dibayar_oleh'   => isset($trx['dibayar_oleh'])  ? (string) substr($trx['dibayar_oleh'], 0, 100) : null,
                    'bulan_tagihan'  => isset($trx['bulan_tagihan']) ? (string) $trx['bulan_tagihan']                : null,
                    'tanggal_bayar'  => $trx['tanggal_bayar'],
                    'status'         => in_array($trx['status'] ?? '', $allowedStatus) ? $trx['status'] : 'lunas',
                ]
            );

            $result->wasRecentlyCreated ? $imported++ : $skipped++;

            if ($result->wasRecentlyCreated) {
                $journalizer->journalize($result);
            }
        }

        $journalSummary = $journalizer->getSummary();

        $message  = "Import selesai: {$imported} data baru, {$skipped} sudah ada.";
        $message .= " | Jurnal: {$journalSummary['created']} dibuat, {$journalSummary['skipped']} dilewati.";
        if ($warned > 0) {
            $message .= " | ⚠️ {$warned} transaksi dilewati karena jumlah berubah setelah dijurnal.";
        }
        if (!empty($journalSummary['errors'])) {
            $message .= ' | Error: ' . implode('; ', $journalSummary['errors']);
        }

        Log::info('SinkronImport: transaksi selesai', [
            'user'     => Auth::user()->name ?? '-',
            'bulan'    => $request->bulan,
            'imported' => $imported,
            'skipped'  => $skipped,
            'warned'   => $warned,
        ]);

        return back()->with('success', $message);
    }

    // ===================== PELANGGAN =====================

    public function pelanggan(Request $request)
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

    public function importPelanggan(Request $request)
    {
        // Rate limiting
        $key = 'sinkron-pelanggan:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        // Ambil data via service
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
                    'phone'            => isset($p['phone'])            ? (string) substr($p['phone'], 0, 20)   : null,
                    'paket'            => isset($p['paket'])            ? (string) substr($p['paket'], 0, 100)  : null,
                    'harga_paket'      => (float) ($p['harga_paket']   ?? 0),
                    'area'             => isset($p['area'])             ? (string) substr($p['area'], 0, 100)   : null,
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

    public function exportPelanggan(Request $request)
    {
        Log::info('Export pelanggan dilakukan', [
            'user_id' => Auth::id(),
            'user'    => Auth::user()->name ?? '-',
            'ip'      => $request->ip(),
            'at'      => now()->toDateTimeString(),
        ]);

        $pelanggan = SinkronPelanggan::orderBy('nama')->get();
        $filename  = 'pelanggan_' . now()->format('Y_m_d') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pelanggan) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'No', 'Nama', 'Phone', 'Paket', 'Harga Paket',
                'Area', 'Diskon (%)', 'Total Tagihan',
                'Tanggal Register', 'Status',
            ]);

            foreach ($pelanggan as $i => $p) {
                fputcsv($file, [
                    $i + 1,
                    $p->nama,
                    $p->phone,
                    $p->paket,
                    $p->harga_paket,
                    $p->area,
                    $p->diskon,
                    $p->total_tagihan,
                    $p->tanggal_register,
                    $p->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
    
    public function importBelumBayar(Request $request)
{
    // Validasi bulan
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

        // Validasi field wajib
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
                'area'             => $t['area'] ?? null,
                'paket'            => $t['paket'] ?? null,

                'harga_paket'      => isset($t['harga_paket']) ? (float) $t['harga_paket'] : 0,
                'biaya_tambahan_1' => isset($t['biaya_tambahan_1']) ? (float) $t['biaya_tambahan_1'] : 0,
                'biaya_tambahan_2' => isset($t['biaya_tambahan_2']) ? (float) $t['biaya_tambahan_2'] : 0,
                'diskon'           => isset($t['diskon']) ? (float) $t['diskon'] : 0,
                'total_tagihan'    => isset($t['total_tagihan']) ? (float) $t['total_tagihan'] : 0,

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


 public function belumBayar(Request $request)
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

    $data = $query->paginate(20)->withQueryString();

    $totalTagihan = SinkronBelumBayar::sum('total_tagihan');
    $totalData    = SinkronBelumBayar::count();

    return view('pembukuan.belum-bayar', compact(
        'data',
        'totalTagihan',
        'totalData'
    ));
}

    // ===================== DELETE METHODS =====================

    public function deleteTransaksi(Request $request)
    {
        // Rate limiting
        $key = 'delete-transaksi:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        // Validasi input
        $request->validate([
            'bulan' => ['required', 'regex:/^\d{4}-(0[1-9]|1[0-2])$/'],
        ], [
            'bulan.regex' => 'Format bulan tidak valid. Gunakan format YYYY-MM.',
        ]);

        // Cek apakah ada data yang sudah dijurnal
        $countJurnaled = SinkronTransaksi::where('bulan_tagihan', 'like', $request->bulan . '%')
            ->whereHas('journalEntries')
            ->count();

        if ($countJurnaled > 0) {
            return back()->with('error', "Tidak dapat menghapus data bulan {$request->bulan} karena {$countJurnaled} transaksi sudah dijurnal.");
        }

        $deleted = SinkronTransaksi::where('bulan_tagihan', 'like', $request->bulan . '%')->delete();

        Log::info('DeleteTransaksi: selesai', [
            'user'    => Auth::user()->name ?? '-',
            'bulan'   => $request->bulan,
            'deleted' => $deleted,
        ]);

        return back()->with('success', "Hapus data transaksi bulan {$request->bulan} selesai: {$deleted} data dihapus.");
    }

    public function deletePelanggan(Request $request)
    {
        // Rate limiting
        $key = 'delete-pelanggan:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        // Konfirmasi penghapusan semua data pelanggan
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

    public function deleteBelumBayar(Request $request)
    {
        // Rate limiting
        $key = 'delete-belum-bayar:' . Auth::id();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->with('error', "Terlalu banyak request. Coba lagi dalam {$seconds} detik.");
        }
        RateLimiter::hit($key, 60);

        // Validasi input
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

    // ===================== DELETE INDIVIDUAL METHODS =====================

    public function deleteTransaksiById($id)
    {
        $transaksi = SinkronTransaksi::findOrFail($id);

        // Cek apakah sudah dijurnal
        $sudahDijurnal = JournalEntry::where('source_type', JournalSourceType::SinkronBilling->value)
            ->where('source_id', $transaksi->id)
            ->exists();

        if ($sudahDijurnal) {
            return back()->with('error', "Tidak dapat menghapus transaksi yang sudah dijurnal. Hubungi admin untuk hapus jurnal terlebih dahulu.");
        }

        $nama = $transaksi->nama_pelanggan;
        $jumlah = number_format($transaksi->jumlah, 0, ',', '.');
        $transaksi->delete();

        Log::info('DeleteTransaksiById: selesai', [
            'user'    => Auth::user()->name ?? '-',
            'id'      => $id,
            'nama'    => $nama,
            'jumlah'  => $transaksi->jumlah,
        ]);

        return back()->with('success', "Data transaksi {$nama} (Rp {$jumlah}) berhasil dihapus.");
    }

    public function deletePelangganById($id)
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

    public function deleteBelumBayarById($id)
    {
        $tagihan = SinkronBelumBayar::findOrFail($id);
        $nama = $tagihan->nama_pelanggan;
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
