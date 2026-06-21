<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawMikhmonImport extends Model
{
    protected $table = 'raw_mikhmon_imports';

    protected $fillable = [
        'import_batch_id',
        'row_number',
        'date_raw',
        'time_raw',
        'username',
        'profile',
        'comment',
        'price_raw',
        'raw_payload',
        'content_hash',  
        'imported_at',
    ];

    public $timestamps = false;

    public function staging()
    {
        return $this->hasOne(
            MikhmonSalesStaging::class,
            'raw_id',
            'id'
        );
    }
}