<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('finance_settings', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('default_jatuh_tempo_day')->default(10); // default jatuh tempo tanggal 10
            $table->timestamps();
        });

        // Insert default row
        DB::table('finance_settings')->insert([
            'default_jatuh_tempo_day' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_settings');
    }
};
