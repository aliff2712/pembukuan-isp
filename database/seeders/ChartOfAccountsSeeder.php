<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [

            // === ASSET ===
            [
                'account_code' => '1101',
                'account_name' => 'Kas',
                'account_type' => 'asset',
                'is_cash'      => '1'
            ],
            [
                'account_code' => '1102',
                'account_name' => 'Bank',
                'account_type' => 'asset',
                'is_cash'      => '1'
            ],
            [
                'account_code' => '1201',
                'account_name' => 'Piutang Usaha',
                'account_type' => 'asset',
                'is_cash'      => '0'
            ],

            // === REVENUE ===
            [
                'account_code' => '4101',
                'account_name' => 'Pendapatan Voucher',
                'account_type' => 'revenue',
                'is_cash'      => '0'
            ],
            [
                'account_code' => '4102',
                'account_name' => 'Pendapatan Langganan Member',
                'account_type' => 'revenue',
                'is_cash'      => '0',
                

            ],
            [
                'account_code' => '4201',
                'account_name' => 'Pendapatan Jasa',
                'account_type' => 'revenue',
                'is_cash'      => '0'
            ],
            [
                'account_code' => '4301',
                'account_name' => 'Pendapatan Lain-lain',
                'account_type' => 'revenue',
                'is_cash'      => '0'
            ],

            // === EXPENSE ===
            [
                'account_code' => '5101',
                'account_name' => 'Beban Alat',
                'account_type' => 'expense',
                'is_cash'      => '0'
            ],
            [
                'account_code' => '5102',
                'account_name' => 'Beban Bahan',
                'account_type' => 'expense',
                'is_cash'      => '0'
            ],
            [
                'account_code' => '5103',
                'account_name' => 'Beban Upah Kerja',
                'account_type' => 'expense',
                'is_cash'      => '0'
            ],
            
            [
                'account_code' => '5109',
                'account_name' => 'Beban Lain-lain',
                'account_type' => 'expense',
                'is_cash'      => '0'
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(
                ['account_code' => $account['account_code']],
                $account
            );
        }
    }
}
