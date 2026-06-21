<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. raw_mikhmon_imports: tambah content_hash + unique index ──
        Schema::table('raw_mikhmon_imports', function (Blueprint $table) {
            // content_hash: fingerprint tiap baris CSV berdasarkan isi datanya
            $table->string('content_hash', 32)->nullable()->after('raw_payload');

            // Unique constraint: satu kombinasi isi baris hanya boleh masuk sekali
            $table->unique('content_hash', 'uq_raw_mikhmon_content_hash');
        });

        // ── 2. mikhmon_sales_staging: pastikan raw_id unik (1 raw = 1 staging) ──
        Schema::table('mikhmon_sales_staging', function (Blueprint $table) {
            // Kalau belum ada unique di raw_id, tambahkan
            $table->unique('raw_id', 'uq_staging_raw_id');
        });

        // ── 3. daily_voucher_sales: pastikan sale_date unik ──
        // (updateOrCreate sudah logis, tapi constraint di DB level lebih aman)
        Schema::table('daily_voucher_sales', function (Blueprint $table) {
            $table->unique('sale_date', 'uq_daily_voucher_sale_date');
        });
    }

    public function down(): void
    {
        Schema::table('raw_mikhmon_imports', function (Blueprint $table) {
            $table->dropUnique('uq_raw_mikhmon_content_hash');
            $table->dropColumn('content_hash');
        });

        Schema::table('mikhmon_sales_staging', function (Blueprint $table) {
            $table->dropUnique('uq_staging_raw_id');
        });

        Schema::table('daily_voucher_sales', function (Blueprint $table) {
            $table->dropUnique('uq_daily_voucher_sale_date');
        });
    }
};