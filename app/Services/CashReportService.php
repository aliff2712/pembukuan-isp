<?php
namespace App\Services;
use Carbon\Carbon;
use App\Models\BeatInvoice; 
use Illuminate\Support\Facades\DB;
class CashReportService
{
    public function dailyDetail()
    {
        return DB::table('journal_lines as jl')
            ->join('journal_entries as je', 'je.id', '=', 'jl.journal_entry_id')
            ->where('jl.account_code', '1101')
            ->orderBy('je.journal_date')
            ->select(
                'je.journal_date as tanggal',
                'je.description as keterangan',
                'jl.debit as kas_masuk',
                'jl.credit as kas_keluar',
                DB::raw('SUM(jl.debit - jl.credit) OVER (ORDER BY je.journal_date, jl.id) as saldo')
            )
            ->get();
    }
}
