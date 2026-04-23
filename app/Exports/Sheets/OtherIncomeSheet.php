<?php
namespace App\Exports\Sheets;

use App\Models\OtherIncome;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OtherIncomeSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected ?int $bulan, protected int $tahun) {}

    public function collection()
    {
        $q = OtherIncome::whereYear('income_date', $this->tahun);
        if ($this->bulan) $q->whereMonth('income_date', $this->bulan);

        return $q->orderBy('income_date', 'desc')->get()->map(fn($o, $i) => [
            'No'          => $i + 1,
            'Tanggal'     => $o->income_date->format('d/m/Y'),
            'Deskripsi'   => $o->description,
            'Notes'       => $o->notes ?? '-',
            'Amount'      => $o->amount,
            'Status'      => strtoupper($o->status),
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Deskripsi', 'Notes', 'Amount', 'Status'];
    }

    public function title(): string { return 'Other Income'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}