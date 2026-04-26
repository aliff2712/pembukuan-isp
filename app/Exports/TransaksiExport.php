<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// app/Exports/TransaksiExport.php

class TransaksiExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(protected $transaksi) {}

    public function collection()
    {
        return $this->transaksi;
    }

    public function headings(): array
    {
        return [
            'Kode Transaksi', 'Nama Pelanggan', 'Area', 'Paket',
            'Jumlah (Rp)', 'Metode', 'Dibayar Oleh',
            'Bulan Tagihan', 'Tanggal Bayar', 'Status',
        ];
    }

    public function map($trx): array
    {
        return [
            $trx->kode_transaksi,
            $trx->nama_pelanggan,
            $trx->area,
            $trx->paket,
            $trx->jumlah,
            $trx->metode,
            $trx->dibayar_oleh,
            $trx->bulan_tagihan,
            $trx->tanggal_bayar,
            $trx->status,
        ];
    }
}