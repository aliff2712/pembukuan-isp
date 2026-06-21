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
       Schema::create('expenses', function (Blueprint $table) {
    $table->id();

    $table->date('expense_date');

    // akun beban
    $table->foreignId('expense_coa_id')
          ->constrained('chart_of_accounts');

    // akun kas / bank
    $table->foreignId('cash_coa_id')
          ->constrained('chart_of_accounts');

    $table->decimal('amount', 14, 2);
    $table->text('description')->nullable();

    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
