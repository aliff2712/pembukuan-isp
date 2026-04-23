<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sinkron_transaksi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_transaksi_billing')->unique(); // cegah duplikat
            $table->string('kode_transaksi')->nullable();
            $table->string('nama_pelanggan');
            $table->string('area')->nullable();
            $table->string('paket')->nullable();
            $table->decimal('jumlah', 15, 2)->default(0);
            $table->string('metode')->nullable();
            $table->string('dibayar_oleh')->nullable();
            $table->string('bulan_tagihan')->nullable();
            $table->timestamp('tanggal_bayar')->nullable();
            $table->string('status')->default('lunas');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sinkron_transaksi');
    }
};