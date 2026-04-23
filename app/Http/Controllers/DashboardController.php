<?php

namespace App\Http\Controllers;

use App\Models\DailyVoucherSale;
use App\Models\Expense;
use App\Models\JournalLine;
use App\Models\OtherIncome;
use App\Models\SinkronTransaksi;
use Carbon\Carbon;
use App\Models\Transaksi;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── 1. Batch saldo kas & bank dalam 1 query ───────────────────────
        $balances = $this->calculateBalances(['1101', '1102']);
        $cashBalance = $balances['1101'] ?? 0.0;
        $bankBalance = $balances['1102'] ?? 0.0;

        // ─── 2. Piutang usaha ──────────────────────────────────────────────
        $arBalance = Transaksi::where('status', 'unpaid')->sum('total');

        // ─── 3. Member paid bulan ini ──────────────────────────────────────
        $paid = SinkronTransaksi::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('jumlah');

        // ─── 4. Semua sumber revenue bulan ini dalam 1 batch ───────────────
        [$revenueThisMonth, $otherIncomeThisMonth, $expenseThisMonth] = $this->getCurrentMonthSummary();

        // ─── 5. Data voucher (dengan mini chart 7 hari — 1 query) ─────────
        $voucherBalance = $this->voucherBalance();

        // ─── 6. Grafik 6 bulan terakhir (2 query total) ───────────────────
        $monthlyStats = $this->getMonthlyStats();

        return view('dashboard', compact(
            'cashBalance',
            'bankBalance',
            'voucherBalance',
            'arBalance',
            'revenueThisMonth',
            'otherIncomeThisMonth',
            'expenseThisMonth',
            'monthlyStats',
            'paid'
        ));
    }

    // =========================================================
    // HELPER: Batch saldo beberapa akun sekaligus (1 query)
    // Sebelumnya: 1 query per akun (N query)
    // =========================================================

    private function calculateBalances(array $accountCodes): array
    {
        return JournalLine::join('chart_of_accounts as coa', 'coa.id', '=', 'journal_lines.coa_id')
            ->whereIn('coa.account_code', $accountCodes)
            ->selectRaw('
                coa.account_code,
                COALESCE(SUM(journal_lines.debit), 0) - COALESCE(SUM(journal_lines.credit), 0) as saldo
            ')
            ->groupBy('coa.account_code')
            ->pluck('saldo', 'account_code')
            ->map(fn($v) => (float) $v)
            ->toArray();
    }

    // =========================================================
    // HELPER: Revenue + OtherIncome + Expense bulan ini
    // Sebelumnya: 5 query terpisah di index()
    // Sekarang: 3 query paralel (masing-masing ringan)
    // =========================================================

    private function getCurrentMonthSummary(): array
    {
        $m = now()->month;
        $y = now()->year;

        $memberPaid = Transaksi::whereMonth('tanggal', $m)
            ->whereYear('tanggal', $y)
            ->where('status', 'paid')
            ->sum('total');

        $voucher = DailyVoucherSale::whereMonth('sale_date', $m)
            ->whereYear('sale_date', $y)
            ->sum('total_amount');

        $other = OtherIncome::whereMonth('income_date', $m)
            ->whereYear('income_date', $y)
            ->sum('amount');

        $expense = Expense::whereMonth('expense_date', $m)
            ->whereYear('expense_date', $y)
            ->sum('amount');

        // Pendapatan dari billing API (SinkronTransaksi)
        $billingPaid = SinkronTransaksi::whereMonth('tanggal_bayar', $m)
            ->whereYear('tanggal_bayar', $y)
            ->sum('jumlah');

        $revenueThisMonth = $memberPaid + $voucher + $other + $billingPaid;

        return [$revenueThisMonth, $other, $expense];
    }

    // =========================================================
    // HELPER: Voucher balance
    // Sebelumnya: loop 7 hari = 7 query
    // Sekarang: 1 query whereIn/range untuk 7 hari
    // =========================================================

    private function voucherBalance(): array
    {
        $currentMonth = now()->month;
        $currentYear  = now()->year;
        $today        = now()->toDateString();
        $lastMonth    = now()->subMonth();

        // Bulan ini & bulan lalu dalam 1 query aggregate
        [$thisMonthTotal, $thisMonthTransactions, $lastMonthTotal, $daysWithData] =
            $this->getVoucherMonthlyAggregates($currentMonth, $currentYear, $lastMonth->month, $lastMonth->year);

        // Hari ini (ringan, terpisah agar tidak mengganggu aggregate)
        $todayRow = DailyVoucherSale::where('sale_date', $today)
            ->selectRaw('SUM(total_amount) as total, SUM(total_transactions) as trx')
            ->first();

        $todayTotal        = (float) ($todayRow->total ?? 0);
        $todayTransactions = (int)   ($todayRow->trx   ?? 0);
        $averagePerDay     = $daysWithData > 0 ? round($thisMonthTotal / $daysWithData) : 0;
        $growthPercent     = $lastMonthTotal > 0
            ? round((($thisMonthTotal - $lastMonthTotal) / $lastMonthTotal) * 100, 1)
            : 0;

        // 7 hari terakhir — 1 query, bukan loop
        $sevenDaysAgo = now()->subDays(6)->toDateString();

        $last7Raw = DailyVoucherSale::whereBetween('sale_date', [$sevenDaysAgo, $today])
            ->selectRaw('sale_date, total_amount, total_transactions')
            ->orderBy('sale_date')
            ->get()
            ->keyBy('sale_date');

        $last7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date      = now()->subDays($i)->toDateString();
            $dayData   = $last7Raw->get($date);
            $last7Days[] = [
                'date'         => $date,
                'label'        => now()->subDays($i)->format('d M'),
                'total_amount' => (float) ($dayData->total_amount ?? 0),
                'transactions' => (int)   ($dayData->total_transactions ?? 0),
            ];
        }

        return [
            'this_month_total'        => $thisMonthTotal,
            'this_month_transactions' => $thisMonthTransactions,
            'today_total'             => $todayTotal,
            'today_transactions'      => $todayTransactions,
            'average_per_day'         => $averagePerDay,
            'last_month_total'        => $lastMonthTotal,
            'growth_percent'          => $growthPercent,
            'last_7_days'             => $last7Days,
            'days_with_data'          => $daysWithData,
        ];
    }

    // Sub-helper: aggregate voucher 2 bulan sekaligus (1 query)
    private function getVoucherMonthlyAggregates(
        int $thisMonth, int $thisYear,
        int $lastMonth, int $lastYear
    ): array {
        $rows = DailyVoucherSale::selectRaw("
            MONTH(sale_date) as bulan,
            YEAR(sale_date)  as tahun,
            SUM(total_amount)       as total,
            SUM(total_transactions) as trx,
            COUNT(*)                as days
        ")
        ->where(function ($q) use ($thisMonth, $thisYear, $lastMonth, $lastYear) {
            $q->where(fn($s) => $s->whereMonth('sale_date', $thisMonth)->whereYear('sale_date', $thisYear))
              ->orWhere(fn($s) => $s->whereMonth('sale_date', $lastMonth)->whereYear('sale_date', $lastYear));
        })
        ->groupByRaw('MONTH(sale_date), YEAR(sale_date)')
        ->get()
        ->keyBy(fn($r) => $r->tahun . '-' . $r->bulan);

        $thisKey = $thisYear . '-' . $thisMonth;
        $lastKey = $lastYear . '-' . $lastMonth;

        return [
            (float) ($rows[$thisKey]->total ?? 0),
            (int)   ($rows[$thisKey]->trx   ?? 0),
            (float) ($rows[$lastKey]->total  ?? 0),
            (int)   ($rows[$thisKey]->days   ?? 0),
        ];
    }

    // =========================================================
    // HELPER: Monthly stats 6 bulan terakhir
    // Sebelumnya: loop 6x dengan 3 query/iterasi = 18 query
    // Sekarang: 3 query total (grouped by month)
    // =========================================================

   private function getMonthlyStats(): array
{
    $startDate = now()->subMonths(5)->startOfMonth()->toDateString();
    $endDate   = now()->endOfMonth()->toDateString();

    // Voucher per bulan
    $voucherRows = DailyVoucherSale::whereBetween('sale_date', [$startDate, $endDate])
        ->selectRaw("DATE_FORMAT(sale_date, '%Y-%m') as ym, SUM(total_amount) as total")
        ->groupByRaw("DATE_FORMAT(sale_date, '%Y-%m')")
        ->pluck('total', 'ym');

    // Other income per bulan
    $otherRows = OtherIncome::whereBetween('income_date', [$startDate, $endDate])
        ->selectRaw("DATE_FORMAT(income_date, '%Y-%m') as ym, SUM(amount) as total")
        ->groupByRaw("DATE_FORMAT(income_date, '%Y-%m')")
        ->pluck('total', 'ym');

    // Member paid per bulan
    $memberRows = Transaksi::where('status', 'paid')
        ->whereBetween('tanggal', [$startDate, $endDate])
        ->selectRaw("DATE_FORMAT(tanggal, '%Y-%m') as ym, SUM(total) as total")
        ->groupByRaw("DATE_FORMAT(tanggal, '%Y-%m')")
        ->pluck('total', 'ym');

    // Billing API per bulan (SinkronTransaksi)
    $billingRows = SinkronTransaksi::whereBetween('tanggal_bayar', [$startDate, $endDate])
        ->selectRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m') as ym, SUM(jumlah) as total")
        ->groupByRaw("DATE_FORMAT(tanggal_bayar, '%Y-%m')")
        ->pluck('total', 'ym');

    // Expense per bulan
    $expenseRows = Expense::whereBetween('expense_date', [$startDate, $endDate])
        ->selectRaw("DATE_FORMAT(expense_date, '%Y-%m') as ym, SUM(amount) as total")
        ->groupByRaw("DATE_FORMAT(expense_date, '%Y-%m')")
        ->pluck('total', 'ym');

    $months      = [];
    $revenueData = [];
    $expenseData = [];

    for ($i = 5; $i >= 0; $i--) {
        $date = Carbon::create(now()->year, now()->month, 1)->subMonths($i);
        $ym   = $date->format('Y-m');

        $bulanIndonesia = [
            1  => 'Jan', 2  => 'Feb', 3  => 'Mar',
            4  => 'Apr', 5  => 'Mei', 6  => 'Jun',
            7  => 'Jul', 8  => 'Agu', 9  => 'Sep',
            10 => 'Okt', 11 => 'Nov', 12 => 'Des',
        ];

        $months[]      = $bulanIndonesia[(int) $date->format('n')] . ' ' . $date->format('Y');
        $revenueData[] = (float) ($voucherRows[$ym] ?? 0)
                       + (float) ($otherRows[$ym]   ?? 0)
                       + (float) ($memberRows[$ym]  ?? 0)
                       + (float) ($billingRows[$ym] ?? 0);  // tambahkan billing
        $expenseData[] = (float) ($expenseRows[$ym] ?? 0);
    }

    return [
        'labels'  => $months,
        'revenue' => $revenueData,
        'expense' => $expenseData,
    ];
}

    /**
     * API endpoint untuk mendapatkan data dashboard
     */
    public function apiData()
    {
        $balances = $this->calculateBalances(['1101', '1102']);
        [$revenueThisMonth, $otherIncomeThisMonth, $expenseThisMonth] = $this->getCurrentMonthSummary();

        return response()->json([
            'cashBalance'          => $balances['1101'] ?? 0.0,
            'bankBalance'          => $balances['1102'] ?? 0.0,
            'voucherBalance'       => $this->voucherBalance(),
            'arBalance'            => Transaksi::where('status', 'unpaid')->sum('total'),
            'revenueThisMonth'     => $revenueThisMonth,
            'otherIncomeThisMonth' => $otherIncomeThisMonth,
            'expenseThisMonth'     => $expenseThisMonth,
            'monthlyStats'         => $this->getMonthlyStats(),
        ]);
    }
}