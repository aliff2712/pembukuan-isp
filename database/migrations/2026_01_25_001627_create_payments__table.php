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
            Schema::create('payments', function (Blueprint $table) {
        $table->id();

        $table->foreignId('invoice_id')->nullable()->index();

        $table->date('payment_date');
        $table->unsignedBigInteger('amount');

        $table->string('method')->nullable(); // cash, transfer, dll
        $table->string('reference')->nullable();
        $table->text('note')->nullable();

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments_');
    }
};
