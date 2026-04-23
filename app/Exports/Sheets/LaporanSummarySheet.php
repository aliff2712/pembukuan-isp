<?php

namespace App\Exports\Sheets;

use App\Models\DailyVoucherSale;
use App\Models\OtherIncome;
use App\Models\Transaksi;
use App\Models\Expense;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;


class LaporanSummarySheet implements FromArray, WithTitle, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    public function __construct(
        protected string $type,
        protected ?int $bulan,
        protected int $tahun
    ) {}

   public function array(): array
{
    $label = $this->type === 'bulanan'
        ? Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y')
        : (string) $this->tahun;

    $filterWaktu = fn($query, string $kolom) => $query
        ->whereYear($kolom, $this->tahun)
        ->when($this->bulan, fn($q) => $q->whereMonth($kolom, $this->bulan));

    $paid    = $filterWaktu(Transaksi::where('status', 'paid'),    'tanggal')->sum('total');
    $unpaid  = $filterWaktu(Transaksi::where('status', 'unpaid'),  'tanggal')->sum('total');
    $voucher = $filterWaktu(DailyVoucherSale::query(), 'sale_date')->sum('total_amount');
    $other   = $filterWaktu(OtherIncome::query(),     'income_date')->sum('amount');
    $expense = $filterWaktu(Expense::query(),          'expense_date')->sum('amount'); // ← tambah ini

    $totalPendapatan  = $paid + $voucher + $other;
    $labaKotor        = $totalPendapatan - $expense;

    return [
        ['LAPORAN KEUANGAN DHS FINANCE - ' . strtoupper($label)],
        [''],
        ['RINGKASAN PENDAPATAN'],
        ['Sumber', 'Nominal'],
        ['Member - Paid',   $paid],
        ['Member - Unpaid', $unpaid],
        ['Voucher',         $voucher],
        ['Other Income',    $other],
        [''],
        ['TOTAL PENDAPATAN BERSIH', $totalPendapatan],
        [''],
        ['RINGKASAN PENGELUARAN'],
        ['Keterangan', 'Nominal'],
        ['Total Expense', $expense],
        [''],
        ['LABA / RUGI', $labaKotor],
    ];
}
    public function title(): string
    {
        return 'Summary';
    }

    public function columnFormats(): array
    {
        return [
            'B' => '"Rp"#,##0',
        ];
    }
   public function styles(Worksheet $sheet): array
{
    return [
        1  => ['font' => ['bold' => true, 'size' => 14]], // Judul
        3  => ['font' => ['bold' => true]],               // RINGKASAN PENDAPATAN
        4  => ['font' => ['bold' => true]],               // Header tabel pendapatan
        10 => ['font' => ['bold' => true]],               // TOTAL PENDAPATAN BERSIH
        12 => ['font' => ['bold' => true]],               // RINGKASAN PENGELUARAN
        13 => ['font' => ['bold' => true]],               // Header tabel pengeluaran
        16 => ['font' => ['bold' => true, 'size' => 12]], // LABA / RUGI
    ];
}
}