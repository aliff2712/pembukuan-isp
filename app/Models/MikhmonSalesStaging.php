<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikhmonSalesStaging extends Model
{
     protected $table = 'mikhmon_sales_staging';

    protected $fillable = [
        'raw_id',
        'sale_datetime',
        'username',
        'profile',
        'price',
        'batch_id',
    ];
     public function raw()
    {
        return $this->belongsTo(
            RawMikhmonImport::class,
            'raw_id',
            'id'
        );
    }
}
