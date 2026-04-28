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
    Schema::table('sinkron_transaksi', function (Blueprint $table) {
        $table->boolean('is_locked')->default(false);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sinkron_transaksi', function (Blueprint $table) {
            //
        });
    }
};
