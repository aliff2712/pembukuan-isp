<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {
            if (Schema::hasColumn('finance_settings', 'default_jatuh_tempo_date')) {
                $table->dropColumn('default_jatuh_tempo_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('finance_settings', function (Blueprint $table) {
            $table->date('default_jatuh_tempo_date')->nullable();
        });
    }
};

