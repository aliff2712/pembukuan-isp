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
    ];

    protected $casts = [
        'tanggal_bayar' => 'datetime',
        'jumlah'        => 'decimal:2',
    ];
}