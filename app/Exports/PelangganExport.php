<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// app/Exports/PelangganExport.php

class PelangganExport implements FromCollection, WithHeadings, WithMapping
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
            'Area', 'Diskon (%)', 'Total Tagihan',
            'Tanggal Register', 'Status',
        ];
    }

    public function map($p): array
    {
        static $i = 0;
        $i++;

        return [
            $i,
            $p->nama,
            $p->phone,
            $p->paket,
            $p->harga_paket,
            $p->area,
            $p->diskon,
            $p->total_tagihan,
            $p->tanggal_register,
            $p->status,
        ];
    }
}