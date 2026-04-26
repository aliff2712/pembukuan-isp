<?php

namespace App\Exports;

use App\Models\DailyVoucherSale;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

// app/Exports/VoucherSalesExport.php

class VoucherSalesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    public function __construct(protected $sales) {}

    public function collection()
    {
        return $this->sales;
    }

    public function headings(): array
    {
        return ['Date', 'Total Transactions', 'Total Amount', 'Source', 'Import Batch', 'Created At'];
    }

    public function map($sale): array
    {
        return [
            $sale->sale_date,
            $sale->total_transactions,
            $sale->total_amount,
            $sale->source,
            $sale->import_batch_id,
            $sale->created_at,
        ];
    }
}
