<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SinkronBelumBayar extends Model
{
    protected $table = 'sinkron_belum_bayar';

    protected $fillable = [
        'id_pelanggan_billing',
        'nama_pelanggan',
        'area',
        'paket',
        'harga_paket',
        'biaya_tambahan_1',
        'biaya_tambahan_2',
        'diskon',
        'total_tagihan',
        'bulan',
        'status',
    ];

    protected $casts = [
        'harga_paket'      => 'decimal:2',
        'biaya_tambahan_1' => 'decimal:2',
        'biaya_tambahan_2' => 'decimal:2',
        'diskon'           => 'decimal:2',
        'total_tagihan'    => 'decimal:2',
    ];
}
