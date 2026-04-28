<?php

namespace App\Exports\Sheets;

use App\Models\SinkronTransaksi;
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
        // FIX: Menggunakan SinkronTransaksi sebagai sumber data transaksi
        return SinkronTransaksi::query()
            ->when($this->bulan, function ($query) {
                $query->whereMonth('tanggal_bayar', $this->bulan);
            })
            ->whereYear('tanggal_bayar', $this->tahun)
            ->select(
                'kode_transaksi',
                'nama_pelanggan',
                'tanggal_bayar',
                'jumlah',
                'metode',
                'area',
                'paket',
                'status'
            )
            ->orderBy('tanggal_bayar');

        // DEPRECATED: Transaksi model sudah diganti dengan SinkronTransaksi
        // return DB::table('transaksis')
        //     ->when($this->bulan, function ($query) {
        //         $query->whereMonth('tanggal', $this->bulan);
        //     })
        //     ->whereYear('tanggal', $this->tahun)
        //     ->select(
        //         'kode_transaksi',
        //         'nama_customer',
        //         'tanggal',
        //         'jatuh_tempo',
        //         'total',
        //         'status',
        //         'paid_at'
        //     )
        //     ->orderBy('tanggal');
    }

    public function map($transaksi): array
    {
        $this->rowNumber++;

        return [
            $this->rowNumber,
            $transaksi->kode_transaksi,
            $transaksi->nama_pelanggan,
            $transaksi->tanggal_bayar
                ? date('d/m/Y', strtotime($transaksi->tanggal_bayar))
                : '-',
            $transaksi->jumlah,
            $transaksi->metode ?? '-',
            $transaksi->area ?? '-',
            $transaksi->paket ?? '-',
            strtoupper($transaksi->status ?? '-'),
        ];
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Transaksi',
            'Pelanggan',
            'Tanggal Bayar',
            'Jumlah',
            'Metode',
            'Area',
            'Paket',
            'Status',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
