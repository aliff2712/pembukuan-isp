<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {

            // hapus kolom lama
            if (Schema::hasColumn('finance_settings', 'default_due_date')) {
                $table->dropColumn('default_due_date');
            }

            // tambah kolom baru
            $table->tinyInteger('default_due_day')
                  ->default(10)
                  ->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {

            $table->date('default_due_date')->nullable();
            $table->dropColumn('default_due_day');
        });
    }
};
