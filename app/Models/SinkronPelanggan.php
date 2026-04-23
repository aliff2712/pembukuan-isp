<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinkronPelanggan extends Model
{
    protected $table = 'sinkron_pelanggan';

    protected $fillable = [
        'id_pelanggan_billing',
        'nama',
        'phone',
        'paket',
        'harga_paket',
        'area',
        'ip_address',
        'diskon',
        'total_tagihan',
        'tanggal_register',
        'status',
    ];

    protected $casts = [
        'tanggal_register' => 'date',
        'harga_paket'      => 'decimal:2',
        'total_tagihan'    => 'decimal:2',
    ];
}