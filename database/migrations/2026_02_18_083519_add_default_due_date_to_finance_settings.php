<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {
            $table->date('default_due_date')->nullable()->after('id');

            if (Schema::hasColumn('finance_settings', 'default_jatuh_tempo_day')) {
                $table->dropColumn('default_jatuh_tempo_day');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {
            $table->tinyInteger('default_jatuh_tempo_day')->default(10);
            $table->dropColumn('default_due_date');
        });
    }
};
