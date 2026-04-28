<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\DailyVoucherSale;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\SinkronTransaksi;
// DEPRECATED: Transaksi model sudah diganti dengan SinkronTransaksi
// use App\Models\Transaksi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class LaporanController extends Controller
{
    // =========================================================
    // HELPER: Summary bulanan via DB aggregation
    // FIX: Tidak load rows ke PHP — semua dihitung di database
    // =========================================================

    private function getSummaryBulanan(int $bulan, int $tahun): array
    {
        // FIX: Menggunakan SinkronTransaksi sebagai sumber data transaksi
        $sinkronTransaksi = SinkronTransaksi::selectRaw("
                status,
                SUM(jumlah) as total,
                COUNT(*)   as jumlah
            ")
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        // DEPRECATED: Transaksi model sudah diganti dengan SinkronTransaksi
        // $member = Transaksi::selectRaw("...")->whereMonth('tanggal', $bulan)->...

        $voucher = DailyVoucherSale::selectRaw("
                SUM(total_amount)       as total,
                SUM(total_transactions) as transaksi
            ")
            ->whereMonth('sale_date', $bulan)
            ->whereYear('sale_date', $tahun)
            ->first();

        $other = OtherIncome::selectRaw("
                SUM(amount) as total,
                COUNT(*)    as jumlah
            ")
            ->whereMonth('income_date', $bulan)
            ->whereYear('income_date', $tahun)
            ->first();

        $expense = Expense::selectRaw("
                SUM(amount) as total,
                COUNT(*)    as jumlah
            ")
            ->whereMonth('expense_date', $bulan)
            ->whereYear('expense_date', $tahun)
            ->first();

        $v                = $voucher->total                     ?? 0;
        $o                = $other->total                       ?? 0;
        $b                = $sinkronTransaksi->sum('total')    ?? 0;
        $totalPengeluaran = $expense->total                     ?? 0;
        $totalPendapatan  = $b + $v + $o;

        return [
            'sinkronTransaksiTotal'   => $b,
            'sinkronTransaksiCount'   => $sinkronTransaksi->sum('jumlah')   ?? 0,
            'voucherTotal'            => $v,
            'voucherTransaksi'        => $voucher->transaksi               ?? 0,
            'otherTotal'              => $o,
            'otherCount'              => $other->jumlah                    ?? 0,
            'totalPendapatan'         => $totalPendapatan,
            'totalPengeluaran'        => $totalPengeluaran,
            'expenseCount'            => $expense->jumlah                  ?? 0,
            'labaKotor'               => $totalPendapatan - $totalPengeluaran,
        ];
    }

    // =========================================================
    // HELPER: Load data bulanan — select kolom minimal
    // FIX: Exclude kolom besar (deskripsi JSON)
    //      Expense pakai eager load relasi COA (hanya id,name,code)
    // =========================================================

    private function getBulananData(int $bulan, int $tahun): array
    {
        // FIX: Menggunakan SinkronTransaksi sebagai sumber data transaksi
        $transaksis = SinkronTransaksi::select(
                'kode_transaksi', 'nama_pelanggan', 'tanggal_bayar', 'jumlah', 'metode', 'area', 'paket'
            )
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->latest('tanggal_bayar')
            ->get();

        // DEPRECATED: Data Transaksi model sudah diganti dengan SinkronTransaksi
        // $transaksis = Transaksi::select(
        //     'kode_transaksi', 'nama_customer', 'tanggal', 'total', 'status', 'paid_at'
        // )->whereMonth('tanggal', $bulan)->...

        $vouchers = DailyVoucherSale::select(
                'sale_date', 'total_amount', 'total_transactions'
            )
            ->whereMonth('sale_date', $bulan)
            ->whereYear('sale_date', $tahun)
            ->orderBy('sale_date', 'desc')
            ->get();

        $otherIncomes = OtherIncome::select(
                'income_date', 'amount', 'description'
            )
            ->whereMonth('income_date', $bulan)
            ->whereYear('income_date', $tahun)
            ->orderBy('income_date', 'desc')
            ->get();

        $expenses = Expense::select(
                'id', 'expense_date', 'amount', 'description', 'expense_coa_id', 'cash_coa_id'
            )
          ->with([
                'expenseAccount:id,account_name,account_code',
                'cashAccount:id,account_name,account_code',
            ])
            ->whereMonth('expense_date', $bulan)
            ->whereYear('expense_date', $tahun)
            ->orderBy('expense_date', 'desc')
            ->get();

        return [$transaksis, $vouchers, $otherIncomes, $expenses];
    }

    // =========================================================
    // HELPER: Aggregasi tahunan per bulan via DB
    // =========================================================

    private function getTahunanAggregates(int $tahun): array
    {
        // FIX: Menggunakan SinkronTransaksi sebagai sumber data transaksi
        $sinkronTransaksi = SinkronTransaksi::selectRaw("
                MONTH(tanggal_bayar) as bulan,
                SUM(jumlah)          as total,
                COUNT(*)             as jumlah
            ")
            ->whereYear('tanggal_bayar', $tahun)
            ->groupByRaw('MONTH(tanggal_bayar)')
            ->get()
            ->keyBy('bulan');

        // DEPRECATED: Data Transaksi model sudah diganti dengan SinkronTransaksi
        // $memberPaid = Transaksi::selectRaw("...")->whereYear('tanggal', $tahun)->where('status', 'paid')->...
        // $memberUnpaid = Transaksi::selectRaw("...")->whereYear('tanggal', $tahun)->where('status', 'unpaid')->...

        $voucher = DailyVoucherSale::selectRaw("
                MONTH(sale_date)        as bulan,
                SUM(total_amount)       as total,
                SUM(total_transactions) as transaksi
            ")
            ->whereYear('sale_date', $tahun)
            ->groupByRaw('MONTH(sale_date)')
            ->get()
            ->keyBy('bulan');

        $other = OtherIncome::selectRaw("
                MONTH(income_date) as bulan,
                SUM(amount)        as total,
                COUNT(*)           as jumlah
            ")
            ->whereYear('income_date', $tahun)
            ->groupByRaw('MONTH(income_date)')
            ->get()
            ->keyBy('bulan');

        $expense = Expense::selectRaw("
                MONTH(expense_date) as bulan,
                SUM(amount)         as total,
                COUNT(*)            as jumlah
            ")
            ->whereYear('expense_date', $tahun)
            ->groupByRaw('MONTH(expense_date)')
            ->get()
            ->keyBy('bulan');

        return [$sinkronTransaksi, $voucher, $other, $expense];
    }

    // =========================================================
    // HELPER: Build array perBulan dari hasil aggregat
    // =========================================================

    private function buildPerBulan(int $tahun, $sinkronTransaksi, $voucher, $other, $expense): array
    {
        $perBulan = [];

        for ($i = 1; $i <= 12; $i++) {
            $b               = $sinkronTransaksi[$i]->total ?? 0;
            $v               = $voucher[$i]->total          ?? 0;
            $o               = $other[$i]->total            ?? 0;
            $e               = $expense[$i]->total          ?? 0;
            $totalPendapatan = $b + $v + $o;

            $perBulan[] = [
                'bulan'         => Carbon::create($tahun, $i)->translatedFormat('F'),
                'bulan_num'     => $i,
                'sinkron'       => $b,
                'voucher'       => $v,
                'other'         => $o,
                'total'         => $totalPendapatan,
                'pengeluaran'   => $e,
                'laba_kotor'    => $totalPendapatan - $e,
            ];
        }

        return $perBulan;
    }

    // =========================================================
    // HELPER: Build summary tahunan dari hasil aggregat
    // =========================================================

    private function buildSummaryTahunan($sinkronTransaksi, $voucher, $other, $expense): array
    {
        $totalPendapatan  = $sinkronTransaksi->sum('total') + $voucher->sum('total')
                         + $other->sum('total');
        $totalPengeluaran = $expense->sum('total');

        return [
            'sinkronTransaksiTotal' => $sinkronTransaksi->sum('total'),
            'sinkronTransaksiCount' => $sinkronTransaksi->sum('jumlah'),
            'voucherTotal'          => $voucher->sum('total'),
            'voucherTransaksi'      => $voucher->sum('transaksi'),
            'otherTotal'            => $other->sum('total'),
            'otherCount'            => $other->sum('jumlah'),
            'totalPendapatan'       => $totalPendapatan,
            'totalPengeluaran'      => $totalPengeluaran,
            'expenseCount'          => $expense->sum('jumlah'),
            'labaKotor'             => $totalPendapatan - $totalPengeluaran,
        ];
    }


    // =========================================================
    // INDEX
    // =========================================================

    public function index()
    {
        return view('finance.laporan.index');
    }


    // =========================================================
    // LAPORAN BULANAN
    // =========================================================

    public function bulanan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);

        $summary = $this->getSummaryBulanan($bulan, $tahun);

        [$transaksis, $vouchers, $otherIncomes, $expenses] = $this->getBulananData($bulan, $tahun);

        $label = Carbon::create($tahun, $bulan)->translatedFormat('F Y');

        return view('finance.laporan.bulanan', compact(
            'summary', 'transaksis', 'vouchers', 'otherIncomes', 'expenses',
            'bulan', 'tahun', 'label'
        ));
    }


    // =========================================================
    // LAPORAN TAHUNAN
    // =========================================================

    public function tahunan(Request $request)
    {
        $tahun = (int) ($request->tahun ?? now()->year);

        [$sinkronTransaksi, $voucher, $other, $expense] = $this->getTahunanAggregates($tahun);

        $perBulan = $this->buildPerBulan($tahun, $sinkronTransaksi, $voucher, $other, $expense);
        $summary  = $this->buildSummaryTahunan($sinkronTransaksi, $voucher, $other, $expense);

        return view('finance.laporan.tahunan', compact('summary', 'perBulan', 'tahun'));
    }


    // =========================================================
    // EXPORT EXCEL
    // =========================================================

    public function exportExcelBulanan(Request $request)
    {
        $bulan = (int) ($request->bulan ?? now()->month);
        $tahun = (int) ($request->tahun ?? now()->year);
        $label = Carbon::create($tahun, $bulan)->translatedFormat('F_Y');

        return Excel::download(
            new LaporanExport('bulanan', $bulan, $tahun),
            "laporan_bulanan_{$label}.xlsx"
        );
    }

    public function exportExcelTahunan(Request $request)
    {
        $tahun = (int) ($request->tahun ?? now()->year);

        return Excel::download(
            new LaporanExport('tahunan', null, $tahun),
            "laporan_tahunan_{$tahun}.xlsx"
        );
    }
}