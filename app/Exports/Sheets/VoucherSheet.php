<?php
namespace App\Exports\Sheets;

use App\Models\DailyVoucherSale;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VoucherSheet implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    public function __construct(protected ?int $bulan, protected int $tahun) {}

    public function collection()
    {
        $q = DailyVoucherSale::whereYear('sale_date', $this->tahun);
        if ($this->bulan) $q->whereMonth('sale_date', $this->bulan);

        return $q->orderBy('sale_date', 'desc')->get()->map(fn($v, $i) => [
            'No'                => $i + 1,
            'Tanggal'           => $v->sale_date->format('d/m/Y'),
            'Source'            => $v->source,
            'Total Transaksi'   => $v->total_transactions,
            'Total Amount'      => $v->total_amount,
        ]);
    }

    public function headings(): array
    {
        return ['No', 'Tanggal', 'Source', 'Total Transaksi', 'Total Amount'];
    }

    public function title(): string { return 'Voucher'; }

    public function styles(Worksheet $sheet): array
    {
        return [1 => ['font' => ['bold' => true]]];
    }
}

