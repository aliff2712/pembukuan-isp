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
            $table->enum('status_approval', ['pending', 'approved', 'flagged', 'rejected'])
                ->default('approved')
                ->after('status')
                ->index();
            $table->text('flag_reason')->nullable()->after('status_approval');
            $table->json('raw_data')->nullable()->after('flag_reason');
            
            $table->unsignedBigInteger('reviewed_by')->nullable()->after('is_locked');
            $table->timestamp('reviewed_at')->nullable()->after('reviewed_by');
            $table->unsignedBigInteger('approved_by')->nullable()->after('reviewed_at');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sinkron_transaksi', function (Blueprint $table) {
            $table->dropColumn([
                'status_approval', 'flag_reason', 'raw_data', 
                'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at'
            ]);
        });
    }
};
