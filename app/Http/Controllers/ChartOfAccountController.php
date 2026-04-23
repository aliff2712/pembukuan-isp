<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\JournalLine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountController extends Controller
{
    public function index(Request $request)
    {
        $query = ChartOfAccount::query();

        // Filter by account type
        if ($request->filled('account_type')) {
            $query->where('account_type', $request->account_type);
        }

        // Search by code or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('account_code', 'like', "%{$search}%")
                  ->orWhere('account_name', 'like', "%{$search}%");
            });
        }

        // Filter cash accounts
        if ($request->filled('is_cash')) {
            $query->where('is_cash', $request->is_cash);
        }

        $accounts = $query->orderBy('account_code')->paginate(20);

        /*
        |--------------------------------------------------------------------------
        | SUMMARY BALANCE
        |--------------------------------------------------------------------------
        | journal_lines.coa_id = chart_of_accounts.account_code
        */

        $assetBalance = JournalLine::join(
                'chart_of_accounts',
                'journal_lines.coa_id',
                '=',
                'chart_of_accounts.account_code'
            )
            ->where('chart_of_accounts.account_type', 'asset')
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
            ->value('balance') ?? 0;

        $equityBalance = JournalLine::join(
                'chart_of_accounts',
                'journal_lines.coa_id',
                '=',
                'chart_of_accounts.account_code'
            )
            ->where('chart_of_accounts.account_type', 'equity')
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as balance')
            ->value('balance') ?? 0;

        $revenueBalance = JournalLine::join(
                'chart_of_accounts',
                'journal_lines.coa_id',
                '=',
                'chart_of_accounts.account_code'
            )
            ->where('chart_of_accounts.account_type', 'revenue')
            ->selectRaw('COALESCE(SUM(credit),0) - COALESCE(SUM(debit),0) as balance')
            ->value('balance') ?? 0;

        $expenseBalance = JournalLine::join(
                'chart_of_accounts',
                'journal_lines.coa_id',
                '=',
                'chart_of_accounts.account_code'
            )
            ->where('chart_of_accounts.account_type', 'expense')
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
            ->value('balance') ?? 0;

        $stats = [
            'total' => ChartOfAccount::count(),
            'asset_count' => ChartOfAccount::where('account_type', 'asset')->count(),
            'liability_count' => ChartOfAccount::where('account_type', 'liability')->count(),
            'equity_count' => ChartOfAccount::where('account_type', 'equity')->count(),
            'revenue_count' => ChartOfAccount::where('account_type', 'revenue')->count(),
            'expense_count' => ChartOfAccount::where('account_type', 'expense')->count(),

            'asset_balance' => $assetBalance,
            'equity_balance' => $equityBalance,
            'revenue_balance' => $revenueBalance,
            'expense_balance' => $expenseBalance,
        ];

        return view('chart-of-accounts.index', compact('accounts', 'stats'));
    }

    public function create()
    {
        $accountTypes = [
            'asset' => 'Asset (Aset)',
            'liability' => 'Liability (Kewajiban)',
            'equity' => 'Equity (Modal)',
            'revenue' => 'Revenue (Pendapatan)',
            'expense' => 'Expense (Beban)',
        ];

        return view('chart-of-accounts.create', compact('accountTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'is_cash' => 'boolean',
        ]);

        $validated['is_cash'] = $request->has('is_cash');

        $account = ChartOfAccount::create($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Account berhasil ditambahkan: ' . $account->account_code . ' - ' . $account->account_name);
    }

    public function show($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $usageCount = JournalLine::where('coa_id', $account->account_code)->count();

        $recentTransactions = DB::table('journal_lines')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('journal_lines.coa_id', $account->account_code)
            ->select(
                'journal_entries.journal_date',
                'journal_entries.description',
                'journal_lines.debit',
                'journal_lines.credit'
            )
            ->orderBy('journal_entries.journal_date', 'desc')
            ->limit(10)
            ->get();

        $balance = JournalLine::where('coa_id', $account->account_code)
            ->selectRaw('COALESCE(SUM(debit),0) - COALESCE(SUM(credit),0) as balance')
            ->value('balance') ?? 0;

        return view('chart-of-accounts.show', compact(
            'account',
            'usageCount',
            'recentTransactions',
            'balance'
        ));
    }

    public function edit($id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $accountTypes = [
            'asset' => 'Asset (Aset)',
            'liability' => 'Liability (Kewajiban)',
            'equity' => 'Equity (Modal)',
            'revenue' => 'Revenue (Pendapatan)',
            'expense' => 'Expense (Beban)',
        ];

        $hasTransactions = JournalLine::where('coa_id', $account->account_code)->exists();

        return view('chart-of-accounts.edit', compact(
            'account',
            'accountTypes',
            'hasTransactions'
        ));
    }

    public function update(Request $request, $id)
    {
        $account = ChartOfAccount::findOrFail($id);

        $validated = $request->validate([
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code,' . $id,
            'account_name' => 'required|string|max:255',
            'account_type' => 'required|in:asset,liability,equity,revenue,expense',
            'is_cash' => 'boolean',
        ]);

        $validated['is_cash'] = $request->has('is_cash');

        // Jika kode akun berubah & sudah ada transaksi → tolak
        if ($account->account_code !== $validated['account_code']) {

            $hasTransactions = JournalLine::where('coa_id', $account->account_code)->exists();

            if ($hasTransactions) {
                return redirect()
                    ->back()
                    ->withErrors([
                        'account_code' => 'Tidak dapat mengubah kode akun yang sudah memiliki transaksi.'
                    ])
                    ->withInput();
            }
        }

        $account->update($validated);

        return redirect()
            ->route('chart-of-accounts.index')
            ->with('success', 'Account berhasil diupdate: ' . $account->account_code . ' - ' . $account->account_name);
    }

    public function destroy($id)
    {
        $account = ChartOfAccount::findOrFail($id);
    
        $accountInfo = $account->account_code . ' - ' . $account->account_name;
    
        try {
    
            $account->delete();
    
            return redirect()
                ->route('chart-of-accounts.index')
                ->with('success', 'Account berhasil dihapus: ' . $accountInfo);
    
        } catch (\Illuminate\Database\QueryException $e) {
    
            return redirect()
                ->back()
                ->with('error', 'Account tidak dapat dihapus karena masih digunakan di transaksi.');
        }
    }
}