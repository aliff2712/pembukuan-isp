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
        'is_locked'
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah'        => 'decimal:2',
        'is_journalized' => 'boolean',
        'journalized_at' => 'datetime',
        'is_locked' => 'boolean',
    ];
    public function shouldBeLocked(): bool
{
    return $this->created_at->diffInMinutes(now()) >= 10;
}

}