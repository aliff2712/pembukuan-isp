<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_date',
        'expense_coa_id',
        'cash_coa_id',
        'amount',
        'description',
    ];

    public function expenseAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'expense_coa_id');
    }

    public function cashAccount()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_coa_id');
    }
}
