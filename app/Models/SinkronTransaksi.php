<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinkronTransaksi extends Model
{
    protected $table = 'sinkron_transaksi';

    protected $fillable = [
        'id_transaksi_billing',
        'kode_transaksi',
        'nama_pelanggan',
        'area',
        'paket',
        'jumlah',
        'metode',
        'dibayar_oleh',
        'bulan_tagihan',
        'tanggal_bayar',
        'status',
        'is_journalized',
        'journalized_at',
        'is_locked',
        'status_approval',
        'flag_reason',
        'raw_data',
        'reviewed_by',
        'reviewed_at',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah'        => 'decimal:2',
        'is_journalized' => 'boolean',
        'journalized_at' => 'datetime',
        'is_locked' => 'boolean',
        'raw_data' => 'array',
        'reviewed_at' => 'datetime',
        'approved_at' => 'datetime',
    ];
    public function shouldBeLocked(): bool
    {
        return $this->created_at->diffInMinutes(now()) >= 10;
    }

    // Scopes
    public function scopePending($query)   { return $query->where('status_approval', 'pending'); }
    public function scopeApproved($query)  { return $query->where('status_approval', 'approved'); }
    public function scopeFlagged($query)   { return $query->where('status_approval', 'flagged'); }
    public function scopeRejected($query)  { return $query->where('status_approval', 'rejected'); }

    // Helpers
    public function isFinal(): bool
    {
        return in_array($this->status_approval, ['approved', 'rejected']);
    }

    public function isActionable(): bool
    {
        return !$this->isFinal() && !$this->is_locked;
    }

    public function reject(int $userId): void
    {
        $this->update([
            'status_approval' => 'rejected',
            'reviewed_by'     => $userId,
            'reviewed_at'     => now(),
        ]);
    }

}