<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('payment_staging', function (Blueprint $table) {
        // Ubah enum status, tambah 'duplicate'
        DB::statement("ALTER TABLE payment_staging 
            MODIFY COLUMN status ENUM('pending','approved','flagged','duplicate','rejected') 
            DEFAULT 'pending'");

        // Kolom untuk menyimpan referensi ke data existing yang jadi penyebab duplicate
        $table->string('duplicate_of')->nullable()->after('flag_reason');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_stagging', function (Blueprint $table) {
            //
        });
    }
};
