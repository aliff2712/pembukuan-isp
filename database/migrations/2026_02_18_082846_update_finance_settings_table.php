<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {

            // Tambah kolom baru
            $table->date('default_jatuh_tempo_date')->nullable()->after('id');

            // Optional: hapus kolom lama
            $table->dropColumn('default_jatuh_tempo_day');
        });
    }

    public function down(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {

            $table->tinyInteger('default_jatuh_tempo_day')->default(10);
            $table->dropColumn('default_jatuh_tempo_date');
        });
    }
};
