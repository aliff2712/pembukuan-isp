<?php

namespace App\Exports;

use App\Exports\Sheets\LaporanSummarySheet;
use App\Exports\Sheets\TransaksiSheet;
use App\Exports\Sheets\PerBulanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class LaporanExport implements WithMultipleSheets
{
    protected string $type;
    protected ?int $bulan;
    protected int $tahun;

    public function __construct(string $type, ?int $bulan, int $tahun)
    {
        $this->type = $type;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    public function sheets(): array
    {
        return array_filter([
            new LaporanSummarySheet(
                $this->type,
                $this->bulan,
                $this->tahun
            ),

            $this->type === 'tahunan'
                ? new PerBulanSheet($this->tahun)
                : null,

            new TransaksiSheet(
                $this->bulan,
                $this->tahun
            ),
        ]);
    }
}