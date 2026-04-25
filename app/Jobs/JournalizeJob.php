<?php

namespace App\Jobs;

use App\Models\ChartOfAccount;
use App\Models\MikhmonImportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JournalizeJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;

    private const COA_KAS             = '1101';
    private const COA_VOUCHER_REVENUE = '4101';

    public function __construct(
        public string $batchId,
        public ?int $importLogId = null,
    ) {}

    /**
     * Execute the job.
     *
     * FIX:
     * - Step 2 sebelumnya query ke mikhmon_sales_staging untuk count,
     *   padahal cukup query langsung ke daily_voucher_sales.
     * - Query INSERT sebelumnya INNER JOIN ke mikhmon_sales_staging,
     *   yang tidak diperlukan karena daily_voucher_sales sudah berisi
     *   data yang sudah diagregasi. Join ini menyebabkan hasil 0 rows
     *   atau duplikat tergantung kondisi staging.
     * - Semua query sekarang hanya bergantung pada daily_voucher_sales
     *   dan journal_entries/journal_lines.
     */
    public function handle(): void
    {
        Log::info('JournalizeJob: Starting', [
            'batch_id'  => $this->batchId,
            'import_id' => $this->importLogId,
        ]);

        try {
            // Step 1: Validasi COA ada
            $coaKas     = ChartOfAccount::where('account_code', self::COA_KAS)->first();
            $coaRevenue = ChartOfAccount::where('account_code', self::COA_VOUCHER_REVENUE)->first();

            if (!$coaKas || !$coaRevenue) {
                $missing = collect([self::COA_KAS, self::COA_VOUCHER_REVENUE])
                    ->filter(fn($code) => !ChartOfAccount::where('account_code', $code)->exists())
                    ->implode(', ');

                throw new \RuntimeException("COA {$missing} not found. Please run seeder.");
            }

            // Step 2: Cari daily_voucher_sales yang belum dijurnalkan
            //
            // FIX: Sebelumnya query ini join ke mikhmon_sales_staging
            // untuk filter by batch_id. Ini salah karena:
            // 1. staging bisa sudah tidak sinkron dengan daily_voucher_sales
            // 2. join ke staging menghasilkan multiple rows per sale_date
            //    sehingga count bisa misleading
            //
            // Solusi: Query langsung ke daily_voucher_sales, filter yang
            // belum ada di journal_entries (source_type = 'mikhmon')
            $unjournalizedSales = DB::table('daily_voucher_sales')
                ->whereNotIn('id', function ($query) {
                    $query->select('source_id')
                        ->from('journal_entries')
                        ->where('source_type', 'mikhmon')
                        ->whereNotNull('source_id');
                })
                ->count();

            if ($unjournalizedSales === 0) {
                Log::warning('JournalizeJob: No unjournalized daily sales found', [
                    'batch_id' => $this->batchId,
                ]);

                // Update log dan lanjut ke CleanupJob
                if ($this->importLogId) {
                    MikhmonImportLog::find($this->importLogId)?->update([
                        'status' => 'processing',
                        'log'    => "⚠️ Tahap 4: Tidak ada data baru untuk dijurnalkan.\n"
                                 . "   Melanjutkan ke tahap berikutnya...",
                    ]);
                }

                return;
            }

            // Step 3: Buat journal entries dan lines dalam satu transaksi
            DB::transaction(function () use ($coaKas, $coaRevenue) {

                // 3A: INSERT journal_entries dari daily_voucher_sales
                //
                // FIX: Hapus INNER JOIN ke mikhmon_sales_staging.
                // Cukup filter daily_voucher_sales yang belum ada
                // di journal_entries dengan source_type = 'mikhmon'.
                $entriesCount = DB::insert(
                    'INSERT INTO journal_entries
                    (journal_date, description, source_type, source_id, reference_no, total_debit, total_credit, created_at, updated_at)
                    SELECT
                        dvs.sale_date,
                        \'Penjualan voucher harian\',
                        \'mikhmon\',
                        dvs.id,
                        NULL,
                        dvs.total_amount,
                        dvs.total_amount,
                        NOW(),
                        NOW()
                    FROM daily_voucher_sales dvs
                    WHERE dvs.id NOT IN (
                        SELECT source_id
                        FROM journal_entries
                        WHERE source_type = \'mikhmon\'
                        AND source_id IS NOT NULL
                    )
                    ON DUPLICATE KEY UPDATE
                        updated_at = NOW()
                    '
                );

                Log::info('JournalizeJob: Entries created', [
                    'batch_id' => $this->batchId,
                    'entries'  => $entriesCount,
                ]);

                // 3B: INSERT journal_lines DEBIT (Kas)
                //
                // FIX: Hapus join ke mikhmon_sales_staging.
                // Filter berdasarkan journal_entries yang belum punya
                // journal_lines dengan coa_id = kas.
                $debitsCount = DB::insert(
                    'INSERT INTO journal_lines
                    (journal_entry_id, coa_id, debit, credit, created_at, updated_at)
                    SELECT
                        je.id,
                        ?,
                        je.total_debit,
                        0,
                        NOW(),
                        NOW()
                    FROM journal_entries je
                    WHERE je.source_type = \'mikhmon\'
                        AND je.id NOT IN (
                            SELECT journal_entry_id
                            FROM journal_lines
                            WHERE coa_id = ?
                            AND journal_entry_id IS NOT NULL
                        )
                    ',
                    [$coaKas->id, $coaKas->id]
                );

                Log::info('JournalizeJob: Debit lines created', [
                    'batch_id' => $this->batchId,
                    'debits'   => $debitsCount,
                ]);

                // 3C: INSERT journal_lines CREDIT (Pendapatan Voucher)
                //
                // FIX: Sama seperti 3B, hapus join ke staging.
                // Filter berdasarkan journal_entries yang belum punya
                // journal_lines dengan coa_id = revenue.
                $creditsCount = DB::insert(
                    'INSERT INTO journal_lines
                    (journal_entry_id, coa_id, debit, credit, created_at, updated_at)
                    SELECT
                        je.id,
                        ?,
                        0,
                        je.total_credit,
                        NOW(),
                        NOW()
                    FROM journal_entries je
                    WHERE je.source_type = \'mikhmon\'
                        AND je.id NOT IN (
                            SELECT journal_entry_id
                            FROM journal_lines
                            WHERE coa_id = ?
                            AND journal_entry_id IS NOT NULL
                        )
                    ',
                    [$coaRevenue->id, $coaRevenue->id]
                );

                Log::info('JournalizeJob: Credit lines created', [
                    'batch_id'  => $this->batchId,
                    'credits'   => $creditsCount,
                ]);
            });

            Log::info('JournalizeJob: Completed successfully', [
                'batch_id'  => $this->batchId,
                'import_id' => $this->importLogId,
            ]);

            if ($this->importLogId) {
                MikhmonImportLog::find($this->importLogId)?->update([
                    'status' => 'processing',
                    'log'    => "✅ Tahap 4: Membuat jurnal otomatis selesai.\n"
                             . "   Melanjutkan ke tahap berikutnya...",
                ]);
            }

        } catch (\Exception $e) {
            Log::error('JournalizeJob: Failed', [
                'batch_id'  => $this->batchId,
                'error'     => $e->getMessage(),
                'import_id' => $this->importLogId,
            ]);
            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('JournalizeJob: FAILED', [
            'batch_id'  => $this->batchId,
            'error'     => $exception->getMessage(),
            'import_id' => $this->importLogId,
        ]);

        if ($this->importLogId) {
            MikhmonImportLog::find($this->importLogId)?->update([
                'status' => 'failed',
                'log'    => "❌ Tahap 4 gagal: Terjadi kesalahan saat membuat jurnal otomatis.\n"
                         . "   Detail: {$exception->getMessage()}\n"
                         . "   Silakan hubungi admin atau coba lagi.",
            ]);
        }
    }
}