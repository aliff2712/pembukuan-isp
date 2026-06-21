<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SummaryBulananSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        protected Collection $transaksis,
        protected Collection $vouchers,
        protected Collection $otherIncomes,
        protected Collection $expenses,
        protected int $bulan,
        protected int $tahun
    ) {}

  public function array(): array
{
    $label = Carbon::create($this->tahun, $this->bulan)->translatedFormat('F Y');

    $memberPaid   = $this->transaksis->where('status', 'paid')->sum('total');
    $memberUnpaid = $this->transaksis->where('status', 'unpaid')->sum('total');
    $voucher      = $this->vouchers->sum('total_amount');
    $other        = $this->otherIncomes->sum('amount');

    // Sesuai Image 2: total tidak termasuk memberUnpaid
    $totalPendapatan = $memberPaid + $voucher + $other;

    return [
        // Judul digabung dalam 1 baris
        ['LAPORAN KEUANGAN DHS FINANCE - ' . strtoupper($label)],
        [''],

        ['RINGKASAN PENDAPATAN'],
        ['Sumber', 'Nominal'],           // ← 'Sumber', bukan 'Keterangan'
        ['Member - Paid',   $memberPaid],
        ['Member - Unpaid', $memberUnpaid],
        ['Voucher',         $voucher],
        ['Other Income',    $other],
        [''],
        ['TOTAL PENDAPATAN BERSIH', $totalPendapatan], // ← label lengkap
    ];
}

public function styles(Worksheet $sheet): array
{
    return [
        1 => ['font' => ['bold' => true, 'size' => 14]],
        3 => ['font' => ['bold' => true]],
        4 => ['font' => ['bold' => true]],
        10 => ['font' => ['bold' => true]],
    ];
}

    public function title(): string
    {
        return 'Summary Bulanan';
    }
}