<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * @property \Carbon\Carbon $sale_date
 */
class DailyVoucherSale extends Model
{
    protected $table = 'daily_voucher_sales';

    protected $fillable = [
        'sale_date',
        'total_transactions',
        'total_amount',
    ];
    protected $casts = [
        'sale_date' => 'date',
        'total_transactions' => 'integer',
        'total_amount' => 'integer',
    ];
}
