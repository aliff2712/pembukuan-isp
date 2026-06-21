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
         Schema::create('beat_subscription_stagings', function (Blueprint $table) {
        $table->id();


        $table->string('import_batch_id')->index();
        $table->foreignId('raw_id')->constrained('raw_beat_imports');


        $table->string('customer_name')->nullable();
        $table->string('phone')->nullable();
        $table->string('pppoe')->nullable()->index();
        $table->string('package_name')->nullable();
        $table->string('area')->nullable();
        $table->text('address')->nullable();


        $table->unsignedInteger('base_price')->nullable();
        $table->unsignedInteger('extra_fee_1')->nullable();
        $table->unsignedInteger('extra_fee_2')->nullable();
        $table->text('extra_note')->nullable();
        $table->string('admin_by')->nullable();


        // Billing anchor (DAY only from file)
        $table->unsignedTinyInteger('billing_day')->nullable(); // 1–31


        // Accounting period (FROM BATCH)
        $table->unsignedTinyInteger('period_month');
        $table->unsignedSmallInteger('period_year');


        // Validation
        $table->enum('status', ['valid', 'invalid'])->default('valid');
        $table->text('error_reason')->nullable();


        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beat_subscription_stagings');
    }
};
