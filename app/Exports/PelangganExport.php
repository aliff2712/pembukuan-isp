<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PelangganExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    use Exportable;

    public function __construct(protected $pelanggan) {}

    public function collection()
    {
        return $this->pelanggan;
    }

    public function headings(): array
    {
        return [
            'No', 'Nama', 'Phone', 'Paket', 'Harga Paket',
            'Area', 'Diskon (Rp)', 'Total Tagihan',
            'Tanggal Register', 'Status',
        ];
    }

    public function map($p): array
    {
        static $i = 0;
        $i++;

        $harga        = (float) $p->harga_paket;
        $diskon       = (float) $p->diskon;       // nominal langsung, misal 30000
        $totalTagihan = $harga - $diskon;          // pastikan kalkulasi benar

        return [
            $i,
            $p->nama,
            $p->phone,
            $p->paket,
            $harga,
            $p->area,
            $diskon,                               // tampil sebagai nominal Rp
            $totalTagihan,
            $p->tanggal_register,
            $p->status,
        ];
    }

    // Bold header row
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}