<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JournalLine extends Model
{
    protected $fillable = [
        'journal_entry_id',
        'coa_id',
        'debit',
        'credit',
    ];

    public function coa()
    {
        return $this->belongsTo(ChartOfAccount::class);
    }
}


