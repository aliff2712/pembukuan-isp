<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RawBeatImport extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'import_batch_id',
        'row_number',
        'raw_payload',
        'imported_at',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'imported_at' => 'datetime',
    ];
}
