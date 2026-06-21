<?php

namespace App\Models;

use App\Models\JournalLine;
use Illuminate\Database\Eloquent\Model;

class JournalEntry extends Model
{
    protected $casts = [
    'journal_date' => 'datetime',
];

    protected $fillable = [
        'journal_date',
        'description',
        'source_type',
        'source_id',
        'reference_no',
        'total_debit',
        'total_credit'
    ];

    public function lines()
    {
        return $this->hasMany(JournalLine::class);
    }
}
