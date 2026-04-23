<?php

namespace App\Enums;

enum JournalSourceType: string
{
    case Mikhmon        = 'mikhmon';
    case Expense        = 'expense';
    case OtherIncome    = 'OtherIncome';
    case BeatPayment    = 'BeatPayment';
    case BeatInvoice    = 'BeatInvoice';
    case SinkronBilling = 'sinkron_billing';

    /**
     * Kembalikan semua value sebagai array string.
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Label tampilan untuk UI (dropdown, export, dll).
     */
    public function label(): string
    {
        return match($this) {
            self::Mikhmon        => 'Voucher Mikhmon',
            self::Expense        => 'Pengeluaran',
            self::OtherIncome    => 'Pendapatan Lain',
            self::BeatPayment    => 'Beat Payment',
            self::BeatInvoice    => 'Beat Invoice',
            self::SinkronBilling => 'Tagihan Billing',
        };
    }
}
