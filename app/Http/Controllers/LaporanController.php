<?php

namespace App\Http\Controllers;

use App\Exports\LaporanExport;
use App\Models\DailyVoucherSale;
use App\Models\Expense;
use App\Models\OtherIncome;
use App\Models\SinkronTransaksi;
use App\Models\Transaksi;
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
        $member = Transaksi::selectRaw("
                status,
                SUM(total) as total,
                COUNT(*)   as jumlah
            ")
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->groupBy('status')
            ->get()
            ->keyBy('status');

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

        // Pendapatan dari billing API
        $billing = SinkronTransaksi::selectRaw("
                SUM(jumlah) as total,
                COUNT(*) as jumlah
            ")
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->first();

        $paid             = $member['paid']->total   ?? 0;
        $unpaid           = $member['unpaid']->total ?? 0;
        $v                = $voucher->total          ?? 0;
        $o                = $other->total            ?? 0;
        $b                = $billing->total          ?? 0;
        $totalPengeluaran = $expense->total          ?? 0;
        $totalPendapatan  = $paid + $v + $o + $b;

        return [
            'memberPaid'        => $paid,
            'memberUnpaid'      => $unpaid,
            'memberPaidCount'   => $member['paid']->jumlah   ?? 0,
            'memberUnpaidCount' => $member['unpaid']->jumlah ?? 0,
            'voucherTotal'      => $v,
            'voucherTransaksi'  => $voucher->transaksi       ?? 0,
            'otherTotal'        => $o,
            'otherCount'        => $other->jumlah            ?? 0,
            'billingTotal'      => $b,
            'billingCount'      => $billing->jumlah          ?? 0,
            'totalPendapatan'   => $totalPendapatan,
            'totalPengeluaran'  => $totalPengeluaran,
            'expenseCount'      => $expense->jumlah          ?? 0,
            'labaKotor'         => $totalPendapatan - $totalPengeluaran,
        ];
    }

    // =========================================================
    // HELPER: Load data bulanan — select kolom minimal
    // FIX: Exclude kolom besar (deskripsi JSON)
    //      Expense pakai eager load relasi COA (hanya id,name,code)
    // =========================================================

    private function getBulananData(int $bulan, int $tahun): array
    {
        $transaksis = Transaksi::select(
                'kode_transaksi', 'nama_customer', 'tanggal', 'total', 'status', 'paid_at'
            )
            ->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->latest('tanggal')
            ->get();

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
        $memberPaid = Transaksi::selectRaw("
                MONTH(tanggal) as bulan,
                SUM(total)     as total,
                COUNT(*)       as jumlah
            ")
            ->whereYear('tanggal', $tahun)
            ->where('status', 'paid')
            ->groupByRaw('MONTH(tanggal)')
            ->get()
            ->keyBy('bulan');

        $memberUnpaid = Transaksi::selectRaw("
                MONTH(tanggal) as bulan,
                SUM(total)     as total,
                COUNT(*)       as jumlah
            ")
            ->whereYear('tanggal', $tahun)
            ->where('status', 'unpaid')
            ->groupByRaw('MONTH(tanggal)')
            ->get()
            ->keyBy('bulan');

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

        // Billing API per bulan
        $billing = SinkronTransaksi::selectRaw("
                MONTH(tanggal_bayar) as bulan,
                SUM(jumlah)          as total,
                COUNT(*)             as jumlah
            ")
            ->whereYear('tanggal_bayar', $tahun)
            ->groupByRaw('MONTH(tanggal_bayar)')
            ->get()
            ->keyBy('bulan');

        return [$memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing];
    }

    // =========================================================
    // HELPER: Build array perBulan dari hasil aggregat
    // =========================================================

    private function buildPerBulan(int $tahun, $memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing): array
    {
        $perBulan = [];

        for ($i = 1; $i <= 12; $i++) {
            $paid            = $memberPaid[$i]->total   ?? 0;
            $unpaid          = $memberUnpaid[$i]->total ?? 0;
            $v               = $voucher[$i]->total      ?? 0;
            $o               = $other[$i]->total        ?? 0;
            $e               = $expense[$i]->total      ?? 0;
            $b               = $billing[$i]->total      ?? 0;
            $totalPendapatan = $paid + $v + $o + $b;

            $perBulan[] = [
                'bulan'         => Carbon::create($tahun, $i)->translatedFormat('F'),
                'bulan_num'     => $i,
                'member_paid'   => $paid,
                'member_unpaid' => $unpaid,
                'voucher'       => $v,
                'other'         => $o,
                'billing'       => $b,
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

    private function buildSummaryTahunan($memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing): array
    {
        $totalPendapatan  = $memberPaid->sum('total') + $voucher->sum('total')
                         + $other->sum('total') + $billing->sum('total');
        $totalPengeluaran = $expense->sum('total');

        return [
            'memberPaid'        => $memberPaid->sum('total'),
            'memberUnpaid'      => $memberUnpaid->sum('total'),
            'memberPaidCount'   => $memberPaid->sum('jumlah'),
            'memberUnpaidCount' => $memberUnpaid->sum('jumlah'),
            'voucherTotal'      => $voucher->sum('total'),
            'voucherTransaksi'  => $voucher->sum('transaksi'),
            'otherTotal'        => $other->sum('total'),
            'otherCount'        => $other->sum('jumlah'),
            'billingTotal'      => $billing->sum('total'),
            'billingCount'      => $billing->sum('jumlah'),
            'totalPendapatan'   => $totalPendapatan,
            'totalPengeluaran'  => $totalPengeluaran,
            'expenseCount'      => $expense->sum('jumlah'),
            'labaKotor'         => $totalPendapatan - $totalPengeluaran,
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

        [$memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing] = $this->getTahunanAggregates($tahun);

        $perBulan = $this->buildPerBulan($tahun, $memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing);
        $summary  = $this->buildSummaryTahunan($memberPaid, $memberUnpaid, $voucher, $other, $expense, $billing);

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