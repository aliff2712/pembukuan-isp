<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BeatSubscriptionStaging;
use App\Models\BeatInvoice;
use Illuminate\Support\Facades\DB;

class BeatGenerateInvoices extends Command
{
    protected $signature = 'beat:generate-invoices {batch?}';
    protected $description = 'Generate BEAT invoices from VALID staging data';

    public function handle(): int
    {
        $batch = $this->argument('batch');

        $query = BeatSubscriptionStaging::query()
            ->where('status', 'valid')
            ->whereDoesntHave('invoice');

        if ($batch) {
            $query->where('import_batch_id', $batch);
        }

        $stagings = $query->get();

        if ($stagings->isEmpty()) {
            // Debug: show what's available
            $totalStaging = BeatSubscriptionStaging::when($batch, fn ($q) => $q->where('import_batch_id', $batch))->count();
            $validStaging = BeatSubscriptionStaging::where('status', 'valid')->when($batch, fn ($q) => $q->where('import_batch_id', $batch))->count();
            $invoicedStaging = BeatSubscriptionStaging::whereHas('invoice')->when($batch, fn ($q) => $q->where('import_batch_id', $batch))->count();
            
            $this->warn("No staging data eligible for invoice generation");
            $this->line("Debug Info:");
            $this->line("  Total staging records: {$totalStaging}");
            $this->line("  Valid staging records: {$validStaging}");
            $this->line("  Already invoiced: {$invoicedStaging}");
            
            if ($totalStaging > 0 && $validStaging === 0) {
                $invalidStaging = BeatSubscriptionStaging::where('status', 'invalid')
                    ->when($batch, fn ($q) => $q->where('import_batch_id', $batch))
                    ->limit(10)
                    ->get();
                
                $this->line("\n  Sample of invalid staging records (first 10):");
                foreach ($invalidStaging as $invalid) {
                    $this->line("    - ID {$invalid->id} | Customer: {$invalid->customer_name} | Error: {$invalid->error_reason}");
                }
                
                // Show error frequency
                $errorCounts = BeatSubscriptionStaging::where('status', 'invalid')
                    ->when($batch, fn ($q) => $q->where('import_batch_id', $batch))
                    ->selectRaw("error_reason, COUNT(*) as count")
                    ->groupBy('error_reason')
                    ->get();
                
                $this->line("\n  Error Summary:");
                foreach ($errorCounts as $err) {
                    $this->line("    - {$err->error_reason}: {$err->count} records");
                }
            }
            
            return Command::SUCCESS;
        }

        $invoiceCount = 0;
        $skippedCount = 0;
        
        foreach ($stagings as $staging) {
            $amount = $staging->total_amount;

            if ($amount <= 0) {
                $this->line("Skipping staging_id {$staging->id} - invalid amount: {$amount}");
                $skippedCount++;
                continue;
            }

            // Validasi pppoe harus ada
            if (empty($staging->pppoe)) {
                $this->warn("Skipping staging_id {$staging->id} - pppoe is required");
                $skippedCount++;
                continue;
            }

            try {
                BeatInvoice::create([
                    'import_batch_id' => $staging->import_batch_id,
                    'staging_id'      => $staging->id,

                    'customer_name'   => $staging->customer_name,
                    'pppoe'           => $staging->pppoe,
                    'package_name'    => $staging->package_name,

                    'total_amount'    => $amount,

                    'billing_day'     => $staging->billing_day,
                    'period_month'    => $staging->period_month,
                    'period_year'     => $staging->period_year,

                    'status'          => 'draft',
                ]);
                $invoiceCount++;
            } catch (\Exception $e) {
                // Handle duplicate key or other errors
                if (strpos($e->getMessage(), 'Duplicate entry') !== false || 
                    strpos($e->getMessage(), '23000') !== false) {
                    $this->warn("Skipping staging_id {$staging->id} - invoice already exists for {$staging->pppoe} (period {$staging->period_month}/{$staging->period_year})");
                    $skippedCount++;
                } else {
                    throw $e;
                }
            }
        }
        
        $this->info("Beat invoices generated: $invoiceCount created, $skippedCount skipped");
        return Command::SUCCESS;
    }
}
