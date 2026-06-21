<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RawBeatImport;
use App\Models\BeatSubscriptionStaging;
use Illuminate\Support\Facades\DB;

class BeatTransformStaging extends Command
{
    protected $signature = 'beat:transform-staging {batch?}';
    protected $description = 'Transform RAW Beat data into clean staging (PIPELINE SAFE)';

    public function handle(): int
    {
        $batch = $this->argument('batch');

        $rows = RawBeatImport::query()
            ->when($batch, fn ($q) => $q->where('import_batch_id', $batch))
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            $this->warn('No RAW data found');
            return Command::SUCCESS;
        }

        /**
         * ===============================
         * Normalizer WAJIB konsisten
         * ===============================
         */
        $normalize = fn ($v) =>
            strtolower(trim(preg_replace('/\s+/', ' ', (string) $v)));

        /**
         * ===============================
         * Ambil HEADER (row pertama)
         * ===============================
         */
        $headers = array_map($normalize, $rows->first()->raw_payload);

        // Debug: tampilkan header yang terbaca
        $this->line("Headers detected: " . implode(', ', $headers));

        // minimal sanity check
        if (!in_array('nama', $headers)) {
            $this->error('Header tidak valid: kolom "nama" tidak ditemukan');
            return Command::FAILURE;
        }

        /**
         * ===============================
         * Mapping excel → staging
         * ===============================
         */
        $map = collect([
            // Basic Info
            'nama'              => 'customer_name',
            'telepon'           => 'phone',
            'ppoe'              => 'pppoe',  // ✅ Perbaikan
            'pppoe'             => 'pppoe',  // Support kedua variasi
            'paket'             => 'package_name',
            'area'              => 'area',
            'alamat'            => 'address',
            
            // Pricing
            'biaya'             => 'base_price',
            'biaya tambahan 1'  => 'extra_fee_1',
            'biaya tambahan 2'  => 'extra_fee_2',
            
            // Notes
            'rincian tambahan 1' => 'extra_note',
            'keterangan'        => 'extra_note',
            
            // Admin
            'admin by'          => 'admin_by',
            'admin'             => 'admin_by',
            
            // Billing
            'tanggal tagihan'   => 'billing_day',
        ])
            ->mapWithKeys(fn ($v, $k) => [$normalize($k) => $v])
            ->toArray();

        DB::transaction(function () use ($rows, $headers, $map) {
            foreach ($rows->skip(1) as $raw) {
                $assoc = [];
                $errors = [];

                // Check if this raw record has already been staged
                $existingStaging = BeatSubscriptionStaging::where('raw_id', $raw->id)->first();
                if ($existingStaging) {
                    $this->line("Skipping raw_id {$raw->id} - already staged");
                    continue;
                }

                $payload = $raw->raw_payload;

                // Pad payload kalau kolom kurang
                if (count($payload) < count($headers)) {
                    $payload = array_pad($payload, count($headers), null);
                }

                foreach ($headers as $i => $key) {
                    $assoc[$key] = $payload[$i] ?? null;
                }

                /**
                 * ===============================
                 * Build staging dasar
                 * ===============================
                 */
                $staging = [
                    'import_batch_id' => $raw->import_batch_id,
                    'raw_id'          => $raw->id,

                    'billing_day'     => null,
                    'period_month'    => null,
                    'period_year'     => null,

                    'status'          => 'valid',
                    'error_reason'    => null,
                ];

                /**
                 * ===============================
                 * Ambil billing period dari batch
                 * ===============================
                 */
                // contoh: 20260121_080022 → Jan 2026
                if (preg_match('/^(\d{4})(\d{2})/', $raw->import_batch_id, $m)) {
                    $staging['period_year']  = (int) $m[1];
                    $staging['period_month'] = (int) $m[2];
                } else {
                    $errors[] = 'Invalid import_batch_id format';
                }

                /**
                 * ===============================
                 * Map kolom Excel
                 * ===============================
                 */
                // Group mappings by target field for smart assignment
                $targetMappings = [];
                foreach ($map as $excelKey => $dbKey) {
                    if (!isset($targetMappings[$dbKey])) {
                        $targetMappings[$dbKey] = [];
                    }
                    $targetMappings[$dbKey][] = $excelKey;
                }
                
                // Assign using first non-empty source value
                foreach ($targetMappings as $dbKey => $sources) {
                    $value = null;
                    foreach ($sources as $excelKey) {
                        $candidate = $assoc[$excelKey] ?? null;
                        if (is_string($candidate)) {
                            $candidate = trim($candidate);
                        }
                        if ($candidate !== null && $candidate !== '') {
                            $value = $candidate;
                            break; // Use first found value
                        }
                    }
                    
                    // Now handle different field types
                    if ($dbKey === 'billing_day') {
                        if ($value !== null && is_numeric($value)) {
                            $day = (int) $value;
                            if ($day >= 1 && $day <= 31) {
                                $staging['billing_day'] = $day;
                            } else {
                                $errors[] = "Invalid billing_day: {$value}";
                            }
                        }
                    } elseif (in_array($dbKey, ['base_price', 'extra_fee_1', 'extra_fee_2'])) {
                        if ($value !== null && $value !== '') {
                            $cleanValue = preg_replace('/[^0-9]/', '', (string) $value);
                            $staging[$dbKey] = $cleanValue !== '' ? (int) $cleanValue : null;
                        } else {
                            $staging[$dbKey] = null;
                        }
                    } else {
                        $staging[$dbKey] = $value;
                    }
                }

                // ✅ Gabungkan Rincian Tambahan 1 & 2 (Opsi 2)
                $rincian1 = trim($assoc['rincian tambahan 1'] ?? '');
                $rincian2 = trim($assoc['rincian tambahan 2'] ?? '');
                if ($rincian1 || $rincian2) {
                    $staging['extra_note'] = trim($rincian1 . ' ' . $rincian2) ?: null;
                }

                /**
                 * ===============================
                 * Validasi minimum bisnis
                 * ===============================
                 */
                if (empty($staging['customer_name'])) {
                    $errors[] = 'customer_name empty';
                }

                if (empty($staging['base_price']) || $staging['base_price'] <= 0) {
                    $errors[] = 'base_price empty or invalid';
                }

                /**
                 * ===============================
                 * Finalize validity
                 * ===============================
                 */
                if (!empty($errors)) {
                    $staging['status']       = 'invalid';
                    $staging['error_reason'] = implode('; ', $errors);
                }

                BeatSubscriptionStaging::create($staging);
            }
        });

        $this->info('Beat staging transform completed (PIPELINE SAFE)');
        return Command::SUCCESS;
    }
}