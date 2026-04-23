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
                $table->index('coa_id');           // untuk filter by coa_id
                $table->index(['coa_id', 'debit', 'credit']); // composite index
            });
        }

        public function down()
        {
            Schema::table('journal_lines', function (Blueprint $table) {
                $table->dropIndex(['coa_id']);
            });
        }
};
