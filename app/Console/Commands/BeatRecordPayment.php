<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Payment;
use App\Models\BeatInvoice;
use App\Models\Journal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BeatRecordPayment extends Command
{
    protected $signature = 'beat:record-payment 
        {invoice_id} 
        {amount} 
        {--date=} 
        {--method=cash} 
        {--note=}';

    protected $description = 'Record manual payment for BEAT invoice';

    public function handle(): int
    {
        $invoice = BeatInvoice::findOrFail($this->argument('invoice_id'));
        $amount  = (int) $this->argument('amount');

        if ($amount <= 0) {
            $this->error('Invalid payment amount');
            return Command::FAILURE;
        }

        DB::transaction(function () use ($invoice, $amount) {
            $payment = Payment::create([
                'invoice_id'   => $invoice->id,
                'payment_date' => $this->option('date')
                    ? Carbon::parse($this->option('date'))
                    : now(),
                'amount'       => $amount,
                'method'       => $this->option('method'),
                'note'         => $this->option('note'),
            ]);

            $journal = Journal::create([
                'reference_type' => 'Payment',
                'reference_id'   => $payment->id,
                'journal_date'   => $payment->payment_date,
            ]);

            // DEBIT: Cash
            $journal->entries()->create([
                'account_code' => '1000',
                'debit'        => $amount,
            ]);

            // CREDIT: Accounts Receivable
            $journal->entries()->create([
                'account_code' => '1100',
                'credit'       => $amount,
            ]);
        });

        $this->info('Payment recorded successfully');
        return Command::SUCCESS;
    }
}
