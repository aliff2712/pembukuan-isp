<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class TransaksiSheet implements FromQuery, WithMapping, WithHeadings, WithChunkReading
{
    protected ?int $bulan;
    protected int $tahun;
    protected int $rowNumber = 0;

    public function __construct(?int $bulan, int $tahun)
    {
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function query()
    {
        return DB::table('transaksis')
            ->when($this->bulan, function ($query) {
                $query->whereMonth('tanggal', $this->bulan);
            })
            ->whereYear('tanggal', $this->tahun)
            ->select(
                'kode_transaksi',
                'nama_customer',
                'tanggal',
                'jatuh_tempo',
                'total',
                'status',
                'paid_at'
            )
            ->orderBy('tanggal');
    }

    public function map($transaksi): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $transaksi->kode_transaksi,
            $transaksi->nama_customer,
            $transaksi->tanggal 
                ? date('d/m/Y', strtotime($transaksi->tanggal)) 
                : '-',
            $transaksi->jatuh_tempo 
                ? date('d/m/Y', strtotime($transaksi->jatuh_tempo)) 
                : '-',
            $transaksi->total,
            strtoupper($transaksi->status),
            $transaksi->paid_at 
                ? date('d/m/Y H:i', strtotime($transaksi->paid_at)) 
                : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Transaksi',
            'Customer',
            'Tanggal',
            'Jatuh Tempo',
            'Total',
            'Status',
            'Dibayar Pada',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}