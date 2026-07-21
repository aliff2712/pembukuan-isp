<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('reference_no')->nullable()->after('id');
        });

        // isi reference_no untuk data lama, urut per tanggal
        $expenses = DB::table('expenses')
            ->orderBy('expense_date')
            ->orderBy('id')
            ->get(['id', 'expense_date']);

        $counters = [];

        foreach ($expenses as $expense) {
            $datePart = date('Ymd', strtotime($expense->expense_date));
            $counters[$datePart] = ($counters[$datePart] ?? 0) + 1;

            DB::table('expenses')
                ->where('id', $expense->id)
                ->update([
                    'reference_no' => 'EXP-' . $datePart . '-' . str_pad($counters[$datePart], 4, '0', STR_PAD_LEFT),
                ]);
        }

        Schema::table('expenses', function (Blueprint $table) {
            $table->unique('reference_no');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropUnique(['reference_no']);
            $table->dropColumn('reference_no');
        });
    }
};
