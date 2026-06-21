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
        Schema::table('sinkron_transaksi', function (Blueprint $table) {
            $table->boolean('is_journalized')->default(false)->after('status');
            $table->timestamp('journalized_at')->nullable()->after('is_journalized');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sinkron_transaksi', function (Blueprint $table) {
            $table->dropColumn(['is_journalized', 'journalized_at']);
        });
    }
};
