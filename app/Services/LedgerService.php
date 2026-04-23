<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LedgerService
{
    
    /**
     * 
     * Ledger harian
     */
    public function daily(Carbon $date)
    {
        return DB::table('journal_entries')
            ->join('journals', 'journals.id', '=', 'journal_entries.journal_id')
            ->selectRaw('
                journals.journal_date as date,
                journal_entries.account_code,
                journal_entries.account_name,
                SUM(CASE WHEN journal_entries.type = "debit" THEN journal_entries.amount ELSE 0 END) as debit,
                SUM(CASE WHEN journal_entries.type = "credit" THEN journal_entries.amount ELSE 0 END) as credit
            ')
            ->whereDate('journals.journal_date', $date->toDateString())
            ->groupBy(
                'journals.journal_date',
                'journal_entries.account_code',
                'journal_entries.account_name'
            )
            ->orderBy('journal_entries.account_code')
            ->get();
    }

    /**
     * Ledger bulanan
     */
    public function monthly(int $year, int $month)
{
    return DB::table('journal_lines as jl')
        ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
        ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.coa_id')
        ->whereYear('je.journal_date', $year)
        ->whereMonth('je.journal_date', $month)
        ->groupBy('jl.coa_id', 'coa.account_code', 'coa.account_name')
        ->orderBy('coa.account_code')
        ->select(
            'coa.account_code',
            'coa.account_name',
            DB::raw('SUM(jl.debit) as debit'),
            DB::raw('SUM(jl.credit) as credit'),
            DB::raw('SUM(jl.debit - jl.credit) as balance')
        )
        ->get();
}

   public function kasLedger(int $coaId)
{
    return DB::table('journal_lines as jl')
        ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
        ->join('chart_of_accounts as coa', 'coa.id', '=', 'jl.coa_id')
        ->where('jl.coa_id', $coaId)
        ->orderBy('je.journal_date')
        ->orderBy('je.id')
        ->orderBy('jl.id')
        ->select(
            'je.journal_date',
            'je.description',
            'jl.debit',
            'jl.credit',
            DB::raw('
                SUM(jl.debit - jl.credit)
                OVER (ORDER BY je.journal_date, je.id, jl.id)
                AS balance
            ')
        )
        ->get();
}

}


