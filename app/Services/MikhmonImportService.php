<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\DailyVoucherSale;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\MikhmonSalesStaging;
use App\Models\RawMikhmonImport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class MikhmonImportService
{
    public array $log = [];

    public function importCsv(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found: {$path}");
        }

        $batchId     = now()->format('Ymd_His');
        $insertCount = 0;
        $skipCount   = 0;

        $file = fopen($path, 'r');

        // Fix: Hapus BOM jika ada
        $bom = fread($file, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($file);
        }

        while (($row = fgetcsv($file)) !== false) {
            if (count($row) < 2) continue;

            // Fix: trim kolom pertama sebelum cek
            $col0 = trim($row[0]);

            if (str_contains($col0, 'Selling Report')) continue;
            if (str_contains($col0, 'Total'))          continue;
            if (!is_numeric($col0))                    continue;

            $contentHash = md5(implode('|', [
                trim($row[1] ?? ''),
                trim($row[2] ?? ''),
                trim($row[3] ?? ''),
                trim($row[4] ?? ''),
                trim($row[6] ?? ''),
            ]));

            $alreadyExists = RawMikhmonImport::where('content_hash', $contentHash)->exists();
            if ($alreadyExists) {
                $skipCount++;
                continue;
            }

            RawMikhmonImport::create([
                'import_batch_id' => $batchId,
                'row_number'      => (int) $col0,
                'date_raw'        => trim($row[1]),
                'time_raw'        => trim($row[2]),
                'username'        => trim($row[3]),
                'profile'         => trim($row[4]),
                'comment'         => isset($row[5]) ? trim($row[5]) : null,
                'price_raw'       => isset($row[6]) ? trim($row[6]) : null,
                'raw_payload'     => json_encode($row),
                'content_hash'    => $contentHash,
                'imported_at'     => now(),
            ]);

            $insertCount++;
        }

        fclose($file);

        if ($insertCount === 0 && $skipCount === 0) {
            $this->log[] = "⚠️ Step 1 - Tidak ada baris valid di CSV. Cek format file.";
        } else {
            $this->log[] = "✅ Step 1 - Import selesai. Batch: {$batchId} | Inserted: {$insertCount} | Skipped: {$skipCount}";
        }

        return $batchId;
    }

    public function transform(): void
    {
        $rawRows      = RawMikhmonImport::whereDoesntHave('staging')->get();
        $successCount = 0;
        $skipCount    = 0;

        foreach ($rawRows as $raw) {
            try {
                $saleDatetime = Carbon::createFromFormat(
                    'M/d/Y H:i:s',
                    "{$raw->date_raw} {$raw->time_raw}"
                );
            } catch (\Exception) {
                $skipCount++;
                continue;
            }

            if (!$raw->price_raw) {
                $skipCount++;
                continue;
            }

            $price = (float) str_replace(['Rp', '.', ','], ['', '', '.'], $raw->price_raw);

            if ($price <= 0) {
                $skipCount++;
                continue;
            }

            MikhmonSalesStaging::firstOrCreate(
                ['raw_id' => $raw->id],
                [
                    'sale_datetime' => $saleDatetime,
                    'username'      => $raw->username,
                    'profile'       => $raw->profile,
                    'price'         => $price,
                    'batch_id'      => $raw->import_batch_id,
                ]
            );

            $successCount++;
        }

        $this->log[] = "✅ Step 2 - Transform selesai | Success: {$successCount} | Skipped: {$skipCount}";
    }

    public function aggregateDaily(): void
    {
        $rows = MikhmonSalesStaging::selectRaw('
                DATE(sale_datetime) as sale_date,
                COUNT(*) as total_transactions,
                SUM(price) as total_amount
            ')
            ->groupByRaw('DATE(sale_datetime)')
            ->get();

        foreach ($rows as $row) {
            DailyVoucherSale::updateOrCreate(
                ['sale_date' => $row->sale_date],
                [
                    'total_transactions' => $row->total_transactions,
                    'total_amount'       => $row->total_amount,
                ]
            );
        }

        $this->log[] = "✅ Step 3 - Agregasi harian selesai | {$rows->count()} tanggal diproses";
    }

    public function journalize(?string $date = null): void
    {
        $query = DailyVoucherSale::query();

        if ($date) {
            $query->where('sale_date', $date);
        }

        $sales = $query->get();

        if ($sales->isEmpty()) {
            $this->log[] = "⚠️ Step 4 - Tidak ada data penjualan harian ditemukan.";
            return;
        }

        $cashCoa           = ChartOfAccount::where('account_code', '1101')->firstOrFail();
        $voucherRevenueCoa = ChartOfAccount::where('account_code', '4101')->firstOrFail();

        $journalCount = 0;
        $skipCount    = 0;

        DB::transaction(function () use ($sales, $cashCoa, $voucherRevenueCoa, &$journalCount, &$skipCount) {
            foreach ($sales as $sale) {
                $exists = JournalEntry::where('source_type', 'mikhmon')
                    ->where('source_id', $sale->id)
                    ->exists();

                if ($exists) {
                    $skipCount++;
                    continue;
                }

                $entry = JournalEntry::create([
                    'journal_date'  => $sale->sale_date,
                    'description'   => 'Penjualan voucher harian',
                    'source_type'   => 'mikhmon',
                    'source_id'     => $sale->id,
                    'reference_no'  => null,
                    'total_debit'   => $sale->total_amount,
                    'total_credit'  => $sale->total_amount,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $cashCoa->id,
                    'debit'            => $sale->total_amount,
                    'credit'           => 0,
                ]);

                JournalLine::create([
                    'journal_entry_id' => $entry->id,
                    'coa_id'           => $voucherRevenueCoa->id,
                    'debit'            => 0,
                    'credit'           => $sale->total_amount,
                ]);

                $journalCount++;
            }
        });

        $this->log[] = "✅ Step 4 - Journalize selesai | Dibuat: {$journalCount} | Dilewati: {$skipCount}";
    }
}