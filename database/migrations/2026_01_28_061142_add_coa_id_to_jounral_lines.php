<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up()
{
    Schema::table('journal_lines', function (Blueprint $table) {
        $table->foreignId('coa_id')
              ->nullable()
              ->after('journal_entry_id')
              ->constrained('chart_of_accounts');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jounral_lines', function (Blueprint $table) {
            //
        });
    }
};
