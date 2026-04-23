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
        Schema::create('daily_voucher_sales', function (Blueprint $table) {
    $table->id();

    $table->date('sale_date')->unique();
    $table->integer('total_transactions');
    $table->decimal('total_amount', 14, 2);
    $table->string('source')->default('MIKHMON');
    $table->unsignedBigInteger('import_batch_id')->nullable();


    $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_voucher_sales');
    }
};
