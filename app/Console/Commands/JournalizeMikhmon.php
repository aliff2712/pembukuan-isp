<?php

namespace App\Console\Commands;

use App\Models\DailyVoucherSale;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class JournalizeMikhmon extends Command
{
    protected $signature = 'journal:mikhmon {date?}';
    protected $description = 'Generate journal from daily voucher sales';

    public function handle(): int
    {
        $date = $this->argument('date');

        $query = DailyVoucherSale::query();
        if ($date) {
            $query->where('sale_date', $date);
        }

        $sales = $query->get();

        if ($sales->isEmpty()) {
            $this->info('No daily voucher sales found.');
            return Command::SUCCESS;
        }

        // === ambil COA (single source of truth) ===
        $cashCoa = ChartOfAccount::where('account_code', '1101')->firstOrFail();   // Kas
        $voucherRevenueCoa = ChartOfAccount::where('account_code', '4101')->firstOrFail(); // Pendapatan Voucher

        DB::beginTransaction();

        try {
            foreach ($sales as $sale) {

                // === anti double journal ===
                $exists = JournalEntry::where('source_type', 'mikhmon')
                    ->where('source_id', $sale->id)
                    ->exists();

                if ($exists) {
                    $this->line("Skipped {$sale->sale_date} (already journaled)");
                    continue;
                }

                /**
                 * 1. Journal header
                 */
                $entry = JournalEntry::create([
                    'journal_date'  => $sale->sale_date,
                    'description'   => 'Penjualan voucher harian',
                    'source_type'   => 'mikhmon',
                    'source_id'     => $sale->id,
                    'reference_no'  => null,
                    'total_debit'   => $sale->total_amount,
                    'total_credit' => $sale->total_amount,
                ]);

                /**
                 * 2. Debit → Kas
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $cashCoa->id,
                    'debit'            => $sale->total_amount,
                    'credit'           => 0,
                ]);

                /**
                 * 3. Kredit → Pendapatan Voucher
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $voucherRevenueCoa->id,
                    'debit'            => 0,
                    'credit'           => $sale->total_amount,
                ]);

                $this->info("Journal created for {$sale->sale_date}");
            }

            DB::commit();
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());
            return Command::FAILURE;
        }
    }
}
