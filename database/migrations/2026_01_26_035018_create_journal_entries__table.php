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
        Schema::create('journal_entries', function (Blueprint $table) {
        $table->id();
        $table->date('journal_date');
        $table->string('description');
        $table->string('source_type'); // mikhmon | beat | manual
        $table->unsignedBigInteger('source_id')->nullable();
        $table->string('reference_no')->nullable();
        $table->decimal('total_debit', 14, 2);
        $table->decimal('total_credit', 14, 2);
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('journal_entries_');
    }
};
