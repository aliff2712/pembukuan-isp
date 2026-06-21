<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('other_incomes', function (Blueprint $table) {
            // FK ke chart_of_accounts.id untuk akun pendapatan (revenue)
            $table->unsignedBigInteger('income_coa_id')->nullable()->after('amount');
            // FK ke chart_of_accounts.id untuk akun kas/bank (asset)
            $table->unsignedBigInteger('cash_coa_id')->nullable()->after('income_coa_id');

            $table->foreign('income_coa_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
            $table->foreign('cash_coa_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('other_incomes', function (Blueprint $table) {
            $table->dropForeign(['income_coa_id']);
            $table->dropForeign(['cash_coa_id']);
            $table->dropColumn(['income_coa_id', 'cash_coa_id']);
        });
    }
};