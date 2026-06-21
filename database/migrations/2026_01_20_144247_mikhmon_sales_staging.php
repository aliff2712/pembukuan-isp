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
        Schema::create('mikhmon_sales_staging', function (Blueprint $table) {
        $table->id();

        $table->foreignId('raw_id')
            ->constrained('raw_mikhmon_imports')
            ->cascadeOnDelete();

        $table->dateTime('sale_datetime');
        $table->string('username');
        $table->string('profile');
        $table->decimal('price', 12, 2);

        $table->string('batch_id');

        $table->timestamps();

        $table->unique(['raw_id']);
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
