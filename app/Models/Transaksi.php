<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * @property \Carbon\Carbon $tanggal
 * @property \Carbon\Carbon $jatuh_tempo
 * @property \Carbon\Carbon|null $paid_at
 */
class Transaksi extends Model
{
        protected $fillable = [
            'kode_transaksi',
            'nama_customer',
            'tanggal',
            'jatuh_tempo',
            'total',
            'status',
            'deskripsi',
            'paid_at',  
        ];

        protected $casts = [
            'tanggal'    => 'date',
            'jatuh_tempo' => 'date',
            'paid_at'    => 'datetime',  
            'total'      => 'integer',
            'deskripsi'  => 'array',
        ];
    protected static function booted()
    {
        static::creating(function ($transaksi) {
    
            if (empty($transaksi->kode_transaksi)) {
                $transaksi->kode_transaksi =
                    'TRX-' . now()->format('Ymd') . '-' . strtoupper(Str::random(5));
            }
    
            if (empty($transaksi->status)) {
                $transaksi->status = 'unpaid';
            }
    
            if (empty($transaksi->jatuh_tempo)) {
    
                $setting = FinanceSetting::first();
    
                if ($setting && $setting->default_due_date) {
    
                    // FIX: jangan parse langsung
                    $transaksi->jatuh_tempo = Carbon::parse($setting->default_due_date);
    
                } else {
    
                    $transaksi->jatuh_tempo = Carbon::parse($transaksi->tanggal)->addDays(7);
                }
            }
        });
    }
    

    public function isOverdue(): bool
    {
        return $this->status === 'unpaid'
            && $this->jatuh_tempo
            && now()->greaterThan($this->jatuh_tempo);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'paid' => 'bg-success text-white',
            'unpaid' => 'bg-danger text-white',
            'overdue' => 'bg-warning text-dark',
            default => 'bg-secondary text-white',
        };
    }
}
