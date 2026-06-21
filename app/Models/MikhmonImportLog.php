<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MikhmonImportLog extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'log',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isDone(): bool
    {
        return $this->status === 'done';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }
}