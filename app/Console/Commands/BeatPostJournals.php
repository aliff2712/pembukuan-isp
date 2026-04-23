<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BeatInvoice;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BeatPostJournals extends Command
{
    protected $signature = 'beat:post-journals {batch?}';
    protected $description = 'Post accounting journal for BEAT invoices';

    public function handle(): int
    {
        $batch = $this->argument('batch');

        $query = BeatInvoice::query()
            ->doesntHave('journal');

        if ($batch) {
            $query->where('import_batch_id', $batch);
        }

        $invoices = $query->get();

        if ($invoices->isEmpty()) {
            $this->warn('No invoices eligible for journal posting');
            return Command::SUCCESS;
        }

        $createdCount = 0;
        foreach ($invoices as $invoice) {
            try {
                $journal = Journal::create([
                    'reference_type' => 'BeatInvoice',
                    'reference_id'   => $invoice->id,
                    'journal_date'   => Carbon::create(
                        $invoice->period_year,
                        $invoice->period_month,
                        1
                    ),
                ]);

                // DEBIT: Accounts Receivable
                $journal->entries()->create([
                    'account_code' => '1100',
                    'debit'        => $invoice->total_amount,
                ]);

                // CREDIT: Revenue
                $journal->entries()->create([
                    'account_code' => '4100',
                    'credit'       => $invoice->total_amount,
                ]);
                
                $createdCount++;
            } catch (\Exception $e) {
                $this->warn("Failed to post journal for invoice {$invoice->id}: " . $e->getMessage());
            }
        }

        $this->info("Journal posting completed: $createdCount journals created");
        return Command::SUCCESS;
    }
}
