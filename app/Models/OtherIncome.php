<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
/**
 * @property \Carbon\Carbon $income_date
 */
class OtherIncome extends Model
{
    protected $fillable = [
        'income_date',
        'description',
        'notes',
        'amount',
        'status',
        'posted_journal_id',
        'created_by',
        'income_coa_id',
        'cash_coa_id',
    ];

    protected $casts = [
        'income_date' => 'date',
        'amount' => 'integer',
    ];

    // =========================================================
    // Relationships
    // =========================================================

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function postedJournal()
    {
        return $this->belongsTo(JournalEntry::class, 'posted_journal_id');
    }

    public function incomeCoa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'income_coa_id');
    }

    public function cashCoa()
    {
        return $this->belongsTo(ChartOfAccount::class, 'cash_coa_id');
    }

    // =========================================================
    // Helpers
    // =========================================================

    public function isPosted(): bool
    {
        return $this->status === 'posted';
    }

    // =========================================================
    // Auto Journal via Model Events
    // =========================================================

    protected static function booted(): void
    {
        static::created(function (OtherIncome $income) {
            $income->createJournalEntry();
        });

        static::updated(function (OtherIncome $income) {
            if ($income->isPosted()) {
                return;
            }

            // Hapus jurnal lama lalu buat ulang
            if ($income->posted_journal_id) {
                $oldJournal = JournalEntry::find($income->posted_journal_id);
                if ($oldJournal) {
                    $oldJournal->lines()->delete();
                    $oldJournal->delete();
                }

                static::withoutEvents(function () use ($income) {
                    $income->updateQuietly(['posted_journal_id' => null]);
                });
            }

            $income->createJournalEntry();
        });

        static::deleting(function (OtherIncome $income) {
            if ($income->posted_journal_id) {
                $journal = JournalEntry::find($income->posted_journal_id);
                if ($journal) {
                    $journal->lines()->delete();
                    $journal->delete();
                }
            }
        });
    }

    /**
     * Buat JournalEntry dan JournalLine untuk Other Income ini.
     */
    public function createJournalEntry(): void
    {
        // Ambil COA langsung dari DB (fresh query, hindari relasi yang belum ter-load)
        $incomeCoa = ChartOfAccount::find($this->income_coa_id);
        $cashCoa   = ChartOfAccount::find($this->cash_coa_id);

        if (! $incomeCoa || ! $cashCoa) {
            Log::warning('OtherIncome: COA tidak ditemukan, jurnal tidak dibuat.', [
                'other_income_id' => $this->id,
                'income_coa_id'   => $this->income_coa_id,
                'cash_coa_id'     => $this->cash_coa_id,
            ]);
            return;
        }

        try {
            DB::transaction(function () use ($incomeCoa, $cashCoa) {
                // 1. Buat Journal Entry
                $journal = JournalEntry::create([
                    'journal_date' => $this->income_date,
                    'description'  => 'Other Income: ' . $this->description,
                    'source_type'  => 'OtherIncome',
                    'source_id'    => $this->id,
                    'reference_no' => 'OI-' . str_pad($this->id, 5, '0', STR_PAD_LEFT),
                    'total_debit'  => $this->amount,
                    'total_credit' => $this->amount,
                ]);

                // 2. Buat Journal Lines langsung via model (bukan relasi)
                // Debit: Kas/Bank
                JournalLine::create([
                    'journal_entry_id' => $journal->id,
                    'coa_id'           => $cashCoa->id,
                    'debit'            => $this->amount,
                    'credit'           => 0,
                ]);

                // Kredit: Pendapatan Lain
                JournalLine::create([
                    'journal_entry_id' => $journal->id,
                    'coa_id'           => $incomeCoa->id,
                    'debit'            => 0,
                    'credit'           => $this->amount,
                ]);

                // 3. Update posted_journal_id tanpa trigger event (hindari infinite loop)
                $incomeId  = $this->id;
                $journalId = $journal->id;
                static::withoutEvents(function () use ($incomeId, $journalId) {
                    static::where('id', $incomeId)->update(['posted_journal_id' => $journalId]);
                });
            });
        } catch (\Throwable $e) {
            Log::error('OtherIncome: Gagal membuat jurnal.', [
                'other_income_id' => $this->id,
                'error'           => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}