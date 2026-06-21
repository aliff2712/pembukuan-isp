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
        Schema::create('raw_mikhmon_imports', function (Blueprint $table) {
            $table->id();
            $table->string('import_batch_id');
            $table->integer('row_number');
            $table->string('date_raw');
            $table->string('time_raw');
            $table->string('username');
            $table->string('profile');
            $table->text('comment')->nullable();
            $table->string('price_raw');
            $table->json('raw_payload');
            $table->timestamp('imported_at');


            $table->timestamps();
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
