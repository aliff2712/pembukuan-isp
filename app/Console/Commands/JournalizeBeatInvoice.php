<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\BeatInvoice;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;

class JournalizeBeatInvoice extends Command
{
    protected $signature = 'journal:beat-invoice {batch?}';
    protected $description = 'Generate journal for Beat invoices (AR → Revenue)';

    public function handle(): int
    {
        $batch = $this->argument('batch');

        $query = BeatInvoice::query();

        if ($batch) {
            $query->where('import_batch_id', $batch);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->info('No Beat invoices found.');
            return Command::SUCCESS;
        }

        // === COA ===
        $arCoa = ChartOfAccount::where('account_code', '1201')->firstOrFail(); // Piutang Usaha
        $revenueCoa = ChartOfAccount::where('account_code', '4201')->firstOrFail(); // Pendapatan Jasa

        DB::beginTransaction();

        try {
            foreach ($invoices as $invoice) {

                // === anti double journal ===
                $exists = JournalEntry::where('source_type', 'BeatInvoice')
                    ->where('source_id', $invoice->id)
                    ->exists();

                if ($exists) {
                    $this->line("Skipped invoice #{$invoice->id} (already journaled)");
                    continue;
                }

                if ($invoice->total_amount <= 0) {
                    $this->warn("Skipped invoice #{$invoice->id} (zero amount)");
                    continue;
                }

                /**
                 * 1. Journal header
                 */
                $entry = JournalEntry::create([
                    'journal_date'  => now()->toDateString(),
                    'description'   => 'Invoice Beat ' . $invoice->customer_name,
                    'source_type'   => 'BeatInvoice',
                    'source_id'     => $invoice->id,
                    'total_debit'   => $invoice->total_amount,
                    'total_credit' => $invoice->total_amount,
                ]);

                /**
                 * 2. Debit → Piutang Usaha
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $arCoa->id,
                    'debit'            => $invoice->total_amount,
                    'credit'           => 0,
                ]);

                /**
                 * 3. Credit → Pendapatan Jasa
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $revenueCoa->id,
                    'debit'            => 0,
                    'credit'           => $invoice->total_amount,
                ]);

                $this->info("Journal created for invoice #{$invoice->id}");
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
