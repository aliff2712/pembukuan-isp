<?php

namespace App\Models;

use App\Models\JournalEntry;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    protected $fillable = [
        'reference_type',
        'reference_id',
        'journal_date',
    ];

    public function entries()
    {
        return $this->hasMany(JournalEntry::class);
    }

    public function journalable()
    {
        return $this->morphTo();
    }
}

