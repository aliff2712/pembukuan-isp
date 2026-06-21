<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpenseController extends Controller
{
    protected $expenseService;

    public function __construct(ExpenseService $expenseService)
    {
        $this->expenseService = $expenseService;
    }

    /**
     * Display a listing of expenses
     */
    public function index(Request $request)
    {
        $query = Expense::with(['expenseAccount', 'cashAccount']);

        $this->applyFilters($query, $request);

        $expenses = $query->orderBy('expense_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20);

        // ─── Stats: 1 query aggregate + 3 query scalar (bukan 6 query) ────
        $stats = $this->getStatistics($request);

        // Get accounts for filter dropdowns — lazy via cache jika diperlukan
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->orderBy('account_code')
            ->get();

        $cashAccounts = ChartOfAccount::where('is_cash', true)
            ->orderBy('account_code')
            ->get();

        return view('expenses.index', compact('expenses', 'stats', 'expenseAccounts', 'cashAccounts'));
    }

    /**
     * Show the form for creating a new expense
     */
    public function create()
    {
        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->orderBy('account_code')
            ->get();

        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_cash', true)
            ->orderBy('account_code')
            ->get();

        if ($expenseAccounts->isEmpty()) {
            return redirect()
                ->route('chart-of-accounts.index')
                ->withErrors(['error' => 'Belum ada akun beban (expense). Silakan buat akun terlebih dahulu.']);
        }

        if ($cashAccounts->isEmpty()) {
            return redirect()
                ->route('chart-of-accounts.index')
                ->withErrors(['error' => 'Belum ada akun kas/bank. Silakan buat akun terlebih dahulu.']);
        }

        return view('expenses.create', compact('expenseAccounts', 'cashAccounts'));
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'expense_date'   => 'required|date',
            'expense_coa_id' => 'required|exists:chart_of_accounts,id',
            'cash_coa_id'    => 'required|exists:chart_of_accounts,id',
            'amount'         => 'required|numeric|min:0',
            'description'    => 'required|string|max:1000',
        ]);

        try {
            $expense = $this->expenseService->record($validated);

            return redirect()
                ->route('expenses.show', $expense->id)
                ->with('success', 'Expense berhasil dicatat sebesar Rp ' . number_format($expense->amount, 0, ',', '.'));

        } catch (\Exception $e) {
            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified expense
     */
    public function show($id)
    {
        $expense = Expense::with(['expenseAccount', 'cashAccount'])->findOrFail($id);

        // Eager load lines sekaligus agar tidak lazy-load di view
        $journalEntry = JournalEntry::where('source_type', 'expense')
            ->where('source_id', $expense->id)
            ->with('lines')
            ->first();

        return view('expenses.show', compact('expense', 'journalEntry'));
    }

    /**
     * Show the form for editing the specified expense
     */
    public function edit($id)
    {
        $expense = Expense::findOrFail($id);

        $expenseAccounts = ChartOfAccount::where('account_type', 'expense')
            ->orderBy('account_code')
            ->get();

        $cashAccounts = ChartOfAccount::where('is_cash', true)
            ->orderBy('account_code')
            ->get();

        return view('expenses.edit', compact('expense', 'expenseAccounts', 'cashAccounts'));
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, $id)
    {
        $expense = Expense::findOrFail($id);

        $validated = $request->validate([
            'expense_date'   => 'required|date',
            'expense_coa_id' => 'required|exists:chart_of_accounts,id',
            'cash_coa_id'    => 'required|exists:chart_of_accounts,id',
            'amount'         => 'required|numeric|min:0',
            'description'    => 'required|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $expense->update($validated);

            $journalEntry = JournalEntry::where('source_type', 'expense')
                ->where('source_id', $expense->id)
                ->first();

            if ($journalEntry) {
                $journalEntry->update([
                    'journal_date' => $validated['expense_date'],
                    'description'  => $validated['description'],
                    'total_debit'  => $validated['amount'],
                    'total_credit' => $validated['amount'],
                ]);

                // Batch delete + re-insert lines
                JournalLine::where('journal_entry_id', $journalEntry->id)->delete();

                // Fetch kedua akun sekaligus (1 query)
                $accounts = ChartOfAccount::findMany([
                    $validated['expense_coa_id'],
                    $validated['cash_coa_id'],
                ])->keyBy('id');

                $expenseAccount = $accounts[$validated['expense_coa_id']];
                $cashAccount    = $accounts[$validated['cash_coa_id']];

                JournalLine::insert([
                    [
                        'journal_entry_id' => $journalEntry->id,
                        'account_code'     => $expenseAccount->account_code,
                        'account_name'     => $expenseAccount->account_name,
                        'debit'            => $validated['amount'],
                        'credit'           => 0,
                    ],
                    [
                        'journal_entry_id' => $journalEntry->id,
                        'account_code'     => $cashAccount->account_code,
                        'account_name'     => $cashAccount->account_name,
                        'debit'            => 0,
                        'credit'           => $validated['amount'],
                    ],
                ]);
            }

            DB::commit();

            return redirect()->route('expenses.show', $expense->id)
                ->with('success', 'Expense berhasil diupdate.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()->withInput()
                ->withErrors(['error' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified expense
     */
    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $expense = Expense::findOrFail($id);

            $journalEntry = JournalEntry::where('source_type', 'expense')
                ->where('source_id', $expense->id)
                ->first();

            if ($journalEntry) {
                JournalLine::where('journal_entry_id', $journalEntry->id)->delete();
                $journalEntry->delete();
            }

            $amount      = $expense->amount;
            $expense->delete();

            DB::commit();

            return redirect()->route('expenses.index')
                ->with('success', 'Expense sebesar Rp ' . number_format($amount, 0, ',', '.') . ' berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->withErrors(['error' => 'Gagal menghapus expense: ' . $e->getMessage()]);
        }
    }

    // =========================================================
    // HELPER: Statistics
    // Sebelumnya: 6 query terpisah (total, count, avg, this_month, last_month, today)
    // Sekarang:   1 query aggregate + 3 query scalar yang berbeda konteks
    // =========================================================

    private function getStatistics(Request $request): array
    {
        // Query dengan filter (untuk total/count/avg sesuai filter aktif)
        $filteredQuery = Expense::query();
        $this->applyFilters($filteredQuery, $request);

        // 1 query untuk total, count, avg berdasarkan filter
        $filtered = $filteredQuery->selectRaw('
            COUNT(*)    as total_expenses,
            SUM(amount) as total_amount,
            AVG(amount) as average_amount
        ')->first();

        $today     = now()->toDateString();
        $thisMonth = now()->month;
        $thisYear  = now()->year;
        $lastMonth = now()->subMonth();

        // 3 scalar query — sudah tidak bisa digabung karena konteks beda
        [$thisMonthAmt, $lastMonthAmt, $todayAmt] = $this->getContextualAmounts(
            $thisMonth, $thisYear,
            $lastMonth->month, $lastMonth->year,
            $today
        );

        return [
            'total_expenses' => (int)   ($filtered->total_expenses  ?? 0),
            'total_amount'   => (float) ($filtered->total_amount     ?? 0),
            'average_amount' => (float) ($filtered->average_amount   ?? 0),
            'this_month'     => $thisMonthAmt,
            'last_month'     => $lastMonthAmt,
            'today'          => $todayAmt,
        ];
    }

    // Sub-helper: ambil this_month, last_month, today dalam 1 query CASE WHEN
    private function getContextualAmounts(
        int $thisMonth, int $thisYear,
        int $lastMonth, int $lastYear,
        string $today
    ): array {
        $row = Expense::selectRaw("
            SUM(CASE WHEN MONTH(expense_date) = ? AND YEAR(expense_date) = ? THEN amount ELSE 0 END) as this_month,
            SUM(CASE WHEN MONTH(expense_date) = ? AND YEAR(expense_date) = ? THEN amount ELSE 0 END) as last_month,
            SUM(CASE WHEN expense_date = ?                                    THEN amount ELSE 0 END) as today
        ", [$thisMonth, $thisYear, $lastMonth, $lastYear, $today])
        ->first();

        return [
            (float) ($row->this_month ?? 0),
            (float) ($row->last_month ?? 0),
            (float) ($row->today      ?? 0),
        ];
    }

    // =========================================================
    // HELPER: Centralized filter agar tidak duplikasi kode
    // =========================================================

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->where('expense_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('expense_date', '<=', $request->date_to);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('expense_date', $request->month)
                  ->whereYear('expense_date', $request->year);
        }

        if ($request->filled('expense_account_id')) {
            $query->where('expense_coa_id', $request->expense_account_id);
        }

        if ($request->filled('cash_account_id')) {
            $query->where('cash_coa_id', $request->cash_account_id);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
    }

    /**
     * Export expenses to CSV
     */
    public function export(Request $request)
    {
        $query = Expense::with(['expenseAccount', 'cashAccount']);

        $this->applyFilters($query, $request);

        $expenses = $query->orderBy('expense_date')->get();

        $csvData   = [];
        $csvData[] = ['Expense ID', 'Date', 'Expense Account', 'Cash/Bank Account', 'Amount', 'Description'];

        foreach ($expenses as $expense) {
            $csvData[] = [
                $expense->id,
                $expense->expense_date,
                $expense->expenseAccount->account_code . ' - ' . $expense->expenseAccount->account_name,
                $expense->cashAccount->account_code . ' - ' . $expense->cashAccount->account_name,
                $expense->amount,
                $expense->description,
            ];
        }

        $filename = 'expenses_' . now()->format('Y-m-d_His') . '.csv';

        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Get expense summary by account (for reports)
     */
    public function summaryByAccount(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->endOfMonth()->toDateString());

        $summary = Expense::with('expenseAccount')
            ->whereBetween('expense_date', [$dateFrom, $dateTo])
            ->selectRaw('expense_coa_id, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('expense_coa_id')
            ->orderByDesc('total_amount')
            ->get();

        return view('expenses.summary', compact('summary', 'dateFrom', 'dateTo'));
    }
}