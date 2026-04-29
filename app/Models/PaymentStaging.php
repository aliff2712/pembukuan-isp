<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class PaymentStaging extends Model
{
    protected $table = 'payment_staging';

    protected $fillable = [
        'source_ref', 'kode_transaksi', 'nama_pelanggan', 'jumlah',
        'tanggal_bayar', 'area', 'paket', 'metode', 'dibayar_oleh',
        'bulan_tagihan', 'raw_data', 'status', 'flag_reason',
        'is_journalized', 'journalized_at', 'is_locked', 'locked_at',
        'reviewed_by', 'reviewed_at', 'approved_by', 'approved_at',
    ];

    protected $casts = [
        'tanggal_bayar'  => 'datetime',
        'journalized_at' => 'datetime',
        'locked_at'      => 'datetime',
        'reviewed_at'    => 'datetime',
        'approved_at'    => 'datetime',
        'jumlah'         => 'decimal:2',
        'is_journalized' => 'boolean',
        'is_locked'      => 'boolean',
        'raw_data'       => 'json',
    ];

    // =========================================================
    // Relations
    // =========================================================

    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    // =========================================================
    // Scopes
    // =========================================================

    public function scopePending($q)  { return $q->where('status', 'pending'); }
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeFlagged($q)  { return $q->where('status', 'flagged'); }
    public function scopeRejected($q) { return $q->where('status', 'rejected'); }
    public function scopeNotLocked($q){ return $q->where('is_locked', false); }

    // =========================================================
    // Methods
    // =========================================================

    public function shouldBeLocked(): bool
    {
        return $this->created_at->diffInMinutes(now()) >= 10;
    }

    public function approve(int $userId = null): bool
    {
        if ($this->is_locked) return false;

        $this->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);

        return true;
    }

    public function reject(int $userId = null): void
    {
        // Hapus jurnal terkait jika belum locked
        if ($this->is_journalized && !$this->is_locked) {
            JournalEntry::where('source_type', 'sinkron_billing')
                ->whereIn('source_id', fn($q) =>
                    $q->select('id')->from('sinkron_transaksi')
                      ->where('id_transaksi_billing', $this->source_ref)
                )
                ->delete(); // journal_lines ikut terhapus via cascade

            SinkronTransaksi::where('id_transaksi_billing', $this->source_ref)
                ->update(['is_journalized' => false, 'journalized_at' => null]);

            Log::info('PaymentStaging: Jurnal dihapus saat reject', ['source_ref' => $this->source_ref]);
        }

        $this->update([
            'status'         => 'rejected',
            'is_journalized' => false,
            'approved_by'    => $userId,
            'approved_at'    => now(),
        ]);
    }

    public function flag(string $reason): void
    {
        $this->update(['status' => 'flagged', 'flag_reason' => $reason]);
    }

    public function lock(): void
    {
        $this->update(['is_locked' => true, 'locked_at' => now()]);
    }

    public function markAsJournalized(): void
    {
        $this->update(['is_journalized' => true, 'journalized_at' => now()]);
    }
}