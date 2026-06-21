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
        Schema::create('sinkron_belum_bayar', function (Blueprint $table) {
            $table->id();

            // Referensi ke pelanggan di billing system
            $table->unsignedBigInteger('id_pelanggan_billing');

            // Data pelanggan (denormalized agar tidak butuh join)
            $table->string('nama_pelanggan');
            $table->string('area')->nullable();
            $table->string('paket')->nullable();

            // Komponen tagihan (sesuai struktur .md)
            $table->decimal('harga_paket',      15, 2)->default(0);
            $table->decimal('biaya_tambahan_1', 15, 2)->default(0);
            $table->decimal('biaya_tambahan_2', 15, 2)->default(0);
            $table->decimal('diskon',            5, 2)->default(0);  // persen
            $table->decimal('total_tagihan',    15, 2)->default(0);  // hasil kalkulasi

            // Periode tagihan, format YYYY-MM
            $table->string('bulan', 7);

            // Status dari billing (belum_lunas, jatuh_tempo, dll)
            $table->string('status')->default('belum_lunas');

            // Mencegah duplikasi per pelanggan per bulan
            $table->unique(['id_pelanggan_billing', 'bulan'], 'uniq_pelanggan_bulan');

            // Index untuk query cepat
            $table->index('bulan');
            $table->index('area');
            $table->index('status');
            $table->index('id_pelanggan_billing');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sinkron_belum_bayar');
    }
};
