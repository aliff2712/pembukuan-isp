<?php

namespace App\Exports\Sheets;

use App\Models\Transaksi;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class PerBulanSheet implements FromQuery, WithHeadings, WithChunkReading
{
    protected int $tahun;

    public function __construct(int $tahun)
    {
        $this->tahun = $tahun;
    }

    public function query(): Builder
    {
        return Transaksi::query()
            ->whereYear('tanggal', $this->tahun)
            ->selectRaw('MONTH(tanggal) as bulan, SUM(total) as total')
            ->groupByRaw('MONTH(tanggal)')
            ->orderByRaw('MONTH(tanggal)');
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Total Transaksi',
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}