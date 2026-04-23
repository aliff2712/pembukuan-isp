<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\ChartOfAccount;

class JournalizeBeatPayment extends Command
{
    protected $signature = 'journal:beat-payment {date?}';
    protected $description = 'Generate journal for Beat payments (Cash/Bank → AR)';

    public function handle(): int
    {
        $date = $this->argument('date');

        $query = Payment::query();

        if ($date) {
            $query->whereDate('payment_date', $date);
        }

        $payments = $query->get();

        if ($payments->isEmpty()) {
            $this->info('No payments found.');
            return Command::SUCCESS;
        }

        // === COA ===
        $cashCoa = ChartOfAccount::where('account_code', '1101')->firstOrFail(); // Kas
        $bankCoa = ChartOfAccount::where('account_code', '1102')->firstOrFail(); // Bank
        $arCoa   = ChartOfAccount::where('account_code', '1201')->firstOrFail(); // Piutang Usaha

        DB::beginTransaction();

        try {
            foreach ($payments as $payment) {

                // === anti double journal ===
                $exists = JournalEntry::where('source_type', 'BeatPayment')
                    ->where('source_id', $payment->id)
                    ->exists();

                if ($exists) {
                    $this->line("Skipped payment #{$payment->id} (already journaled)");
                    continue;
                }

                if ($payment->amount <= 0) {
                    $this->warn("Skipped payment #{$payment->id} (zero amount)");
                    continue;
                }

                $cashOrBankCoa = $payment->method === 'bank'
                    ? $bankCoa
                    : $cashCoa;

                /**
                 * 1. Journal header
                 */
                $entry = JournalEntry::create([
                    'journal_date'  => $payment->payment_date,
                    'description'   => 'Payment invoice #' . $payment->invoice_id,
                    'source_type'   => 'BeatPayment',
                    'source_id'     => $payment->id,
                    'total_debit'   => $payment->amount,
                    'total_credit' => $payment->amount,
                ]);

                /**
                 * 2. Debit → Kas / Bank
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $cashOrBankCoa->id,
                    'debit'            => $payment->amount,
                    'credit'           => 0,
                ]);

                /**
                 * 3. Credit → Piutang Usaha
                 */
                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $arCoa->id,
                    'debit'            => 0,
                    'credit'           => $payment->amount,
                ]);

                $this->info("Journal created for payment #{$payment->id}");
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
