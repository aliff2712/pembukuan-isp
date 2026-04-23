<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('sinkron_pelanggan', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('id_pelanggan_billing')->unique();
        $table->string('nama');
        $table->string('phone')->nullable();
        $table->string('paket')->nullable();
        $table->decimal('harga_paket', 15, 2)->default(0);
        $table->string('area')->nullable();
        $table->string('ip_address')->nullable();
        $table->decimal('diskon', 5, 2)->default(0);
        $table->decimal('total_tagihan', 15, 2)->default(0);
        $table->date('tanggal_register')->nullable();
        $table->string('status')->default('aktif');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sinkron_pelanggan');
    }
};
