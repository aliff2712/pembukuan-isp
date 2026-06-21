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
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sinkron_pelanggan MODIFY diskon DECIMAL(15, 2) DEFAULT 0");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sinkron_belum_bayar MODIFY diskon DECIMAL(15, 2) DEFAULT 0");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sinkron_pelanggan MODIFY diskon DECIMAL(5, 2) DEFAULT 0");
        \Illuminate\Support\Facades\DB::statement("ALTER TABLE sinkron_belum_bayar MODIFY diskon DECIMAL(5, 2) DEFAULT 0");
    }
};
