<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Imports\TransaksiImport;
use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TransaksiController extends Controller
{
    /**
     * LIST DATA + SUMMARY
     */
    public function index(Request $request)
    {
        $query = Transaksi::query();

        // SEARCH
        if ($request->filled('search')) {
            $query->where('nama_customer', 'like', '%' . $request->search . '%');
        }

        // FILTER TANGGAL
        if ($request->filled('from')) {
            $query->whereDate('tanggal', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('tanggal', '<=', $request->to);
        }

        // CLONE QUERY UNTUK SUMMARY (BIAR IKUT FILTER)
        $summaryQuery = clone $query;

        $transaksis = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // ===== SUMMARY =====
        $totalTransaksi = $summaryQuery->count();
        $totalNominal = $summaryQuery->sum('total');

        $totalPaid = (clone $summaryQuery)
            ->where('status', 'paid')
            ->count();

        $totalUnpaid = (clone $summaryQuery)
            ->where('status', 'unpaid')
            ->count();

        $nominalPaid = (clone $summaryQuery)
            ->where('status', 'paid')
            ->sum('total');

        $nominalUnpaid = (clone $summaryQuery)
            ->where('status', 'unpaid')
            ->sum('total');

        return view('finance.index', compact(
            'transaksis',
            'totalTransaksi',
            'totalNominal',
            'totalPaid',
            'totalUnpaid',
            'nominalPaid',
            'nominalUnpaid'
        ));
    }


    /**
     * HALAMAN IMPORT
     */
    public function importForm()
    {
        return view('finance.import');
    }


    /**
     * PROSES IMPORT
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048',
        ]);

        try {

            Excel::import(new TransaksiImport, $request->file('file'));

            return redirect()
                ->route('finance.transaksi.index')
                ->with('success', 'Transaksi berhasil di-import 🔥');

        } catch (\Exception $e) {

            return redirect()
                ->route('finance.transaksi.index')
                ->with('error', 'Import gagal: ' . $e->getMessage());
        }
    }


    /**
     * DETAIL
     */
    public function show(Transaksi $transaksi)
    {
        return view('finance.show', compact('transaksi'));
    }


    /**
     * HALAMAN PEMBAYARAN
     */
    public function paymentForm(Transaksi $transaksi)
    {
        $tanggal = Carbon::parse($transaksi->tanggal);
        $nextAllowedPayment = $tanggal->copy()->addMonth()->day(10);

        if ($transaksi->status == 'paid' && now()->lessThan($nextAllowedPayment)) {
            return redirect()
                ->route('finance.transaksi.show', $transaksi->id)
                ->with('error', 'Transaksi sudah dibayar. Tidak bisa bayar lagi sebelum tanggal 10 berikutnya.');
        }

        return view('finance.payment', compact('transaksi'));
    }


    /**
     * PROSES PEMBAYARAN
     */public function processPayment(Request $request, Transaksi $transaksi)
{
    if ($transaksi->status === 'paid') {
        return redirect()
            ->route('finance.transaksi.show', $transaksi->id)
            ->with('error', 'Transaksi sudah dibayar sebelumnya.');
    }

    $transaksi->update([
        'status'  => 'paid',
        'paid_at' => now(),  // ← tambah ini
    ]);

    return redirect()
        ->route('finance.transaksi.show', $transaksi->id)
        ->with('success', 'Pembayaran Berhasil!');
}


    /**
     * HAPUS
     */
    public function destroy(Transaksi $transaksi)
    {
        $transaksi->delete();

        return redirect()
            ->route('finance.transaksi.index')
            ->with('success', 'Transaksi berhasil dihapus 🔥');
    }


    /**
     * STRUK / RECEIPT
     */
    public function receipt(Transaksi $transaksi)
    {
        if ($transaksi->status !== 'paid') {
            return redirect()
                ->route('finance.transaksi.show', $transaksi->id);
        }

        return view('finance.receipt', compact('transaksi'));
    }
}