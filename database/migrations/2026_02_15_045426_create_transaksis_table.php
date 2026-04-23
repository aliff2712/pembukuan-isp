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
    Schema::create('transaksis', function (Blueprint $table) {
        $table->id();
        $table->string('kode_transaksi')->unique();
        $table->string('nama_customer');
        $table->date('tanggal');
        $table->date('jatuh_tempo');
        $table->bigInteger('total')->default(0);
        $table->string('status')->default('paid');
        $table->text('deskripsi')->nullable();
        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};