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
      Schema::create('beat_invoices', function (Blueprint $table) {
$table->id();


$table->string('import_batch_id');
$table->foreignId('staging_id')->constrained('beat_subscription_stagings')->onDelete('cascade');


$table->string('pppoe')->nullable()->index();
$table->string('customer_name');
$table->string('package_name')->nullable();


$table->unsignedTinyInteger('period_month');
$table->unsignedSmallInteger('period_year');


$table->unsignedTinyInteger('billing_day')->nullable();


$table->unsignedBigInteger('total_amount');


$table->enum('status', ['draft', 'issued', 'locked', 'paid', 'void'])
->default('draft');


$table->timestamp('issued_at')->nullable();
$table->timestamp('locked_at')->nullable();


$table->timestamps();


$table->unique(['pppoe', 'period_month', 'period_year']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beat_invoices');
    }
};
