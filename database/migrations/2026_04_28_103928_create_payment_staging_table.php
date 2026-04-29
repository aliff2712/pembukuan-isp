<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_staging', function (Blueprint $table) {
            $table->id();

            // ===== Data Source =====
            $table->unsignedBigInteger('source_ref')->unique()->comment('id_transaksi_billing dari API');

            // ===== Data Transaksi =====
            $table->string('kode_transaksi')->nullable();
            $table->string('nama_pelanggan');
            $table->decimal('jumlah', 15, 2);
            $table->timestamp('tanggal_bayar')->nullable();
            $table->string('area')->nullable();
            $table->string('paket')->nullable();
            $table->string('metode')->nullable();
            $table->string('dibayar_oleh')->nullable();
            $table->string('bulan_tagihan')->nullable();

            // ===== Raw Data (JSON Snapshot) =====
            $table->json('raw_data')->nullable()->comment('Full payload dari API untuk referensi');

            // ===== Status & Validation =====
            $table->enum('status', ['pending', 'approved', 'flagged', 'rejected'])
                ->default('pending')
                ->index();
            $table->text('flag_reason')->nullable()->comment('Alasan kenapa data di-flag');

            // ===== Journalizing Control =====
            $table->boolean('is_journalized')->default(false)->index();
            $table->timestamp('journalized_at')->nullable();
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();

            // ===== Audit Trail =====
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('User yang review');
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable()->comment('User yang approve');
            $table->timestamp('approved_at')->nullable();

            // ===== Timestamps =====
            $table->timestamps();

            // ===== Indexes =====
            $table->index(['status', 'created_at']);
            $table->index('bulan_tagihan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_staging');
    }
};
