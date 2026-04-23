<?php

namespace App\Imports;

use App\Models\Transaksi;
use App\Models\FinanceSetting;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class TransaksiImport implements ToModel, WithStartRow
{
    public function startRow(): int 
    {
        return 4;
    }

    public function model(array $row)
    {
        // Normalisasi total
        $rawTotal = $row[16] ?? 0;
        $total = (int) str_replace(['.', ','], '', $rawTotal);

        $tanggal = now();

        // Ambil setting
        $setting = FinanceSetting::first();

        // Tentukan jatuh tempo
        if ($setting && $setting->default_due_date) {
            $jatuhTempo = Carbon::parse($setting->default_due_date);
        } else {
            $jatuhTempo = Carbon::parse($tanggal)->addDays(7);
        }

        $namaCustomer = $row[2] ?? '-';
        $periode      = $row[17] ?? null;

        // Cek duplikat berdasarkan nama_customer + periode
        // Jika sudah ada → skip, tidak menimpa data apapun
        $existing = Transaksi::where('nama_customer', $namaCustomer)
            ->where('deskripsi->periode', $periode)
            ->first();

        if ($existing) {
            return null;
        }

        // Belum ada → insert baru dengan status unpaid
        return new Transaksi([
            'nama_customer' => $namaCustomer,
            'tanggal'       => $tanggal,
            'jatuh_tempo'   => $jatuhTempo,
            'total'         => $total,
            'status'        => 'unpaid',
            'deskripsi'     => [
                'phone'     => $row[3] ?? null,
                'paket'     => $row[5] ?? null,
                'area'      => $row[6] ?? null,
                'alamat'    => $row[7] ?? null,
                'internet'  => $row[8] ?? null,
                'tv'        => $row[11] ?? null,
                'periode'   => $periode,
                'koordinat' => $row[18] ?? null,
                'sales'     => $row[21] ?? null,
            ],
        ]);
    }
}