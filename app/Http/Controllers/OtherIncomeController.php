<?php

namespace App\Http\Controllers;

use App\Models\ChartOfAccount;
use App\Models\OtherIncome;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class OtherIncomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incomes = OtherIncome::with(['createdBy', 'incomeCoa', 'cashCoa'])
            ->orderBy('income_date', 'desc')
            ->paginate(15);

        return view('other-incomes.index', [
            'incomes' => $incomes,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Akun pendapatan lain (revenue)
        $incomeAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->orderBy('account_code')
            ->get();

        // Akun kas / bank (asset yang ditandai is_cash)
        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_cash', true)
            ->orderBy('account_code')
            ->get();

        return view('other-incomes.create', compact('incomeAccounts', 'cashAccounts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'income_date'   => 'required|date',
            'description'   => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'amount'        => 'required|numeric|min:1',
            'income_coa_id' => 'required|exists:chart_of_accounts,id',
            'cash_coa_id'   => 'required|exists:chart_of_accounts,id',
        ], [
            'income_date.required'   => 'Tanggal harus diisi',
            'description.required'   => 'Deskripsi harus diisi',
            'amount.required'        => 'Jumlah harus diisi',
            'income_coa_id.required' => 'Akun pendapatan harus dipilih',
            'cash_coa_id.required'   => 'Akun kas/bank harus dipilih',
        ]);

        $validated['created_by'] = Auth::id();

        // Model event `created` di OtherIncome akan otomatis membuat JournalEntry
        OtherIncome::create($validated);

        return Redirect::route('other-incomes.index')
            ->with('success', 'Income berhasil ditambahkan dan jurnal otomatis telah dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $income = OtherIncome::with(['createdBy', 'incomeCoa', 'cashCoa', 'postedJournal.lines'])
            ->findOrFail($id);

        return view('other-incomes.show', ['income' => $income]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $income = OtherIncome::findOrFail($id);

        if ($income->isPosted()) {
            return Redirect::route('other-incomes.show', $income)
                ->with('error', 'Tidak bisa mengedit income yang sudah di-posting!');
        }

        $incomeAccounts = ChartOfAccount::where('account_type', 'revenue')
            ->orderBy('account_code')
            ->get();

        $cashAccounts = ChartOfAccount::where('account_type', 'asset')
            ->where('is_cash', true)
            ->orderBy('account_code')
            ->get();

        return view('other-incomes.edit', compact('income', 'incomeAccounts', 'cashAccounts'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $income = OtherIncome::findOrFail($id);

        if ($income->isPosted()) {
            return Redirect::back()
                ->with('error', 'Tidak bisa mengubah income yang sudah di-posting!');
        }

        $validated = $request->validate([
            'income_date'   => 'required|date',
            'description'   => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'amount'        => 'required|numeric|min:1',
            'income_coa_id' => 'required|exists:chart_of_accounts,id',
            'cash_coa_id'   => 'required|exists:chart_of_accounts,id',
        ], [
            'income_coa_id.required' => 'Akun pendapatan harus dipilih',
            'cash_coa_id.required'   => 'Akun kas/bank harus dipilih',
        ]);

        // Model event `updated` akan otomatis menghapus jurnal lama dan membuat yang baru
        $income->update($validated);

        return Redirect::route('other-incomes.show', $income)
            ->with('success', 'Income berhasil diubah dan jurnal otomatis telah diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $income = OtherIncome::findOrFail($id);

        if ($income->isPosted()) {
            return Redirect::back()
                ->with('error', 'Tidak bisa menghapus income yang sudah di-posting!');
        }

        // Model event `deleting` akan otomatis menghapus jurnal terkait
        $income->delete();

        return Redirect::route('other-incomes.index')
            ->with('success', 'Income dan jurnal terkait berhasil dihapus!');
    }
}