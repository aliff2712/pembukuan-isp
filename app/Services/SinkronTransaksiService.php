<?php

namespace App\Services;

use App\Models\SinkronTransaksi;
use Illuminate\Support\Facades\Log;

class SinkronTransaksiService
{
    private const GRACE_MINUTES = 10;

    public function isLocked(SinkronTransaksi $trx): bool
    {
        return $trx->is_locked || $trx->created_at->diffInMinutes(now()) >= self::GRACE_MINUTES;
    }

    public function autoLock(SinkronTransaksi $trx): void
    {
        if (!$trx->is_locked && $trx->created_at->diffInMinutes(now()) >= self::GRACE_MINUTES) {
            $trx->update(['is_locked' => true]);
        }
    }

    public function validate(array $trx): bool
    {
        $required = ['id_transaksi', 'kode_transaksi', 'nama_pelanggan', 'jumlah', 'tanggal_bayar'];

        foreach ($required as $field) {
            if (!isset($trx[$field])) {
                Log::warning("SinkronImport: field '{$field}' tidak ada", ['trx' => $trx]);
                return false;
            }
        }

        if (!is_numeric($trx['jumlah']) || (float) $trx['jumlah'] <= 0) {
            Log::warning('SinkronImport: jumlah tidak valid', ['jumlah' => $trx['jumlah']]);
            return false;
        }

        if (!strtotime($trx['tanggal_bayar'])) {
            Log::warning('SinkronImport: tanggal tidak valid', ['tanggal' => $trx['tanggal_bayar']]);
            return false;
        }

        return true;
    }

    public function map(array $trx, array $allowedMetode, array $allowedStatus): array
    {
        return [
            'kode_transaksi' => (string) substr($trx['kode_transaksi'] ?? '', 0, 50),
            'nama_pelanggan' => (string) substr($trx['nama_pelanggan'] ?? '', 0, 150),
            'area'           => $trx['area'] ?? null,
            'paket'          => $trx['paket'] ?? null,
            'jumlah'         => (float) $trx['jumlah'],
            'metode'         => in_array($trx['metode'] ?? '', $allowedMetode) ? $trx['metode'] : 'cash',
            'dibayar_oleh'   => $trx['dibayar_oleh'] ?? null,
            'bulan_tagihan'  => $trx['bulan_tagihan'] ?? null,
            'tanggal_bayar'  => $trx['tanggal_bayar'],
            'status'         => in_array($trx['status'] ?? '', $allowedStatus) ? $trx['status'] : 'lunas',
        ];
    }

    public function shouldSkipUpdate(SinkronTransaksi $existing, array $trx): bool
    {
        return $existing->is_journalized && (float) $existing->jumlah !== (float) $trx['jumlah'];
    }
}