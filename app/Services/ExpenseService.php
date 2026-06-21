<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Illuminate\Support\Facades\DB;

class ExpenseService
{
    public function record(array $data): Expense
    {
        return DB::transaction(function () use ($data) {

            /**
             * 1. Simpan transaksi bisnis (expense)
             */
            $expense = Expense::create([
                'expense_date'   => $data['expense_date'],
                'expense_coa_id' => $data['expense_coa_id'],
                'cash_coa_id'    => $data['cash_coa_id'],
                'amount'         => $data['amount'],
                'description'    => $data['description'] ?? null,
            ]);

            /**
             * 2. Get account info
             */
            $expenseAccount = ChartOfAccount::findOrFail($data['expense_coa_id']);
            $cashAccount = ChartOfAccount::findOrFail($data['cash_coa_id']);

            /**
             * 3. Journal header (journal_entries)
             */
            $journalEntry = JournalEntry::create([
                'journal_date'   => $data['expense_date'],
                'description'    => $data['description'] ?? 'Pengeluaran operasional',
                'source_type'    => 'expense',
                'source_id'      => $expense->id,
                'total_debit'    => $data['amount'],  // ✅ TAMBAHKAN INI
                'total_credit'   => $data['amount'],  // ✅ TAMBAHKAN INI
            ]);

            /**
             * 4. Debit → Beban
             */
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_code'     => $expenseAccount->account_code,
                'account_name'     => $expenseAccount->account_name,
                'debit'            => $data['amount'],
                'credit'           => 0,
            ]);

            /**
             * 5. Kredit → Kas / Bank
             */
            JournalLine::create([
                'journal_entry_id' => $journalEntry->id,
                'account_code'     => $cashAccount->account_code,
                'account_name'     => $cashAccount->account_name,
                'debit'            => 0,
                'credit'           => $data['amount'],
            ]);

            return $expense;
        });
    }
}