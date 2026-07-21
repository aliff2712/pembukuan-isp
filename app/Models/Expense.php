<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'reference_no',
        'expense_date',
        'expense_coa_id',
        'cash_coa_id',
        'amount',
        'description',
    ];

    protected static function booted(): void
    {
        static::creating(function (Expense $expense) {
            if (empty($expense->reference_no)) {
                $expense->reference_no = static::generateReferenceNo();
            }
        });
    }

    protected static function generateReferenceNo(): string
    {
        $prefix = 'EXP-' . now()->format('Ymd') . '-';

        // lock agar tidak duplikat saat ada insert bersamaan
        $last = static::query()
            ->where('reference_no', 'like', $prefix . '%')
            ->lockForUpdate()
            ->orderByDesc('reference_no')
            ->value('reference_no');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function expenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_coa_id');
    }

    public function cashAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_coa_id');
    }
}
