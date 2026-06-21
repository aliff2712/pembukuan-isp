@extends('layouts-main.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">LAPORAN ARUS KAS (CASH FLOW STATEMENT)</h1>
        <p class="text-gray-600">Periode: {{ date('d/m/Y', strtotime($from)) }} s/d {{ date('d/m/Y', strtotime($to)) }}</p>
    </div>

    <form method="get" class="mb-6 flex gap-2 items-center bg-gray-50 p-4 rounded">
        <label class="text-sm font-medium">Dari:</label>
        <input type="date" name="from" value="{{ $from }}" class="border p-2 rounded" />
        <label class="text-sm font-medium">Sampai:</label>
        <input type="date" name="to" value="{{ $to }}" class="border p-2 rounded" />
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
        <a href="{{ url()->current() }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <!-- Summary Cards -->
        <div class="grid md:grid-cols-4 gap-4 p-6 bg-gradient-to-r from-blue-50 to-purple-50">
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-blue-500">
                <p class="text-xs text-gray-600 font-semibold">Saldo Awal</p>
                <p class="text-xl font-bold text-gray-800 font-mono">{{ number_format($beginningBalance, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-green-500">
                <p class="text-xs text-gray-600 font-semibold">Kas Masuk</p>
                <p class="text-xl font-bold text-green-600 font-mono">{{ number_format($totalInflow, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-red-500">
                <p class="text-xs text-gray-600 font-semibold">Kas Keluar</p>
                <p class="text-xl font-bold text-red-600 font-mono">{{ number_format($totalOutflow, 0, ',', '.') }}</p>
            </div>
            <div class="bg-white p-4 rounded-lg shadow-sm border-l-4 border-purple-500">
                <p class="text-xs text-gray-600 font-semibold">Saldo Akhir</p>
                <p class="text-xl font-bold text-purple-600 font-mono">{{ number_format($endingBalance, 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="p-6">
            <h2 class="font-bold text-lg mb-4 text-gray-800">Detail Transaksi Kas</h2>
            
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="px-4 py-3 text-left w-28">Tanggal</th>
                            <th class="px-4 py-3 text-left">Deskripsi</th>
                            <th class="px-4 py-3 text-left w-40">Akun Kas/Bank</th>
                            <th class="px-4 py-3 text-right w-32">Kas Masuk</th>
                            <th class="px-4 py-3 text-right w-32">Kas Keluar</th>
                            <th class="px-4 py-3 text-right w-32">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $runningBalance = $beginningBalance;
                        @endphp

                        <tr class="bg-blue-50 font-semibold border-b-2">
                            <td colspan="5" class="px-4 py-3">Saldo Awal Periode</td>
                            <td class="px-4 py-3 text-right font-mono">{{ number_format($beginningBalance, 0, ',', '.') }}</td>
                        </tr>

                        @forelse($transactions as $tx)
                            @php
                                $runningBalance += ($tx->debit - $tx->credit);
                            @endphp
                            <tr class="border-t hover:bg-gray-50">
                                <td class="px-4 py-3">{{ date('d/m/Y', strtotime($tx->journal_date)) }}</td>
                                <td class="px-4 py-3">{{ $tx->description }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600">{{ $tx->account_name }}</td>
                                <td class="px-4 py-3 text-right font-mono {{ $tx->debit > 0 ? 'text-green-600 font-semibold' : '' }}">
                                    {{ $tx->debit > 0 ? number_format($tx->debit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono {{ $tx->credit > 0 ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $tx->credit > 0 ? number_format($tx->credit, 0, ',', '.') : '-' }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono">{{ number_format($runningBalance, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                    <p class="text-lg">💰 Tidak ada transaksi kas pada periode ini</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    @if($transactions->count() > 0)
                    <tfoot class="bg-gray-800 text-white font-bold">
                        <tr class="border-t-4">
                            <td colspan="3" class="px-4 py-4 text-right">TOTAL TRANSAKSI:</td>
                            <td class="px-4 py-4 text-right font-mono text-green-300">{{ number_format($totalInflow, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-mono text-red-300">{{ number_format($totalOutflow, 0, ',', '.') }}</td>
                            <td class="px-4 py-4 text-right font-mono"></td>
                        </tr>
                        <tr class="border-t-2">
                            <td colspan="3" class="px-4 py-4 text-right">PERUBAHAN KAS BERSIH:</td>
                            <td colspan="2" class="px-4 py-4 text-center font-mono text-lg {{ $netCashFlow >= 0 ? 'text-green-300' : 'text-red-300' }}">
                                {{ number_format($netCashFlow, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-4"></td>
                        </tr>
                        <tr class="border-t-2 bg-gray-900">
                            <td colspan="5" class="px-4 py-4 text-right text-lg">SALDO AKHIR PERIODE:</td>
                            <td class="px-4 py-4 text-right font-mono text-xl text-yellow-300">
                                {{ number_format($endingBalance, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Cash Flow Analysis -->
        @if($transactions->count() > 0)
        <div class="p-6 bg-gray-50 border-t">
            <h3 class="font-bold text-gray-800 mb-3">📊 Analisis Arus Kas</h3>
            <div class="grid md:grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded border">
                    <p class="text-sm text-gray-600 mb-2">Perubahan Kas Periode Ini</p>
                    <p class="text-2xl font-bold {{ $netCashFlow >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $netCashFlow >= 0 ? '+' : '' }} Rp {{ number_format($netCashFlow, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $netCashFlow >= 0 ? '↑ Kas meningkat' : '↓ Kas menurun' }}
                    </p>
                </div>
                
                <div class="bg-white p-4 rounded border">
                    <p class="text-sm text-gray-600 mb-2">Persentase Perubahan</p>
                    @php
                        $percentageChange = $beginningBalance != 0 ? ($netCashFlow / $beginningBalance) * 100 : 0;
                    @endphp
                    <p class="text-2xl font-bold {{ $percentageChange >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($percentageChange, 1) }}%
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        Dari saldo awal periode
                    </p>
                </div>
            </div>
        </div>
        @endif
    </div>

    <div class="mt-6 bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
        <h3 class="font-bold text-blue-900 mb-2">ℹ️ Catatan</h3>
        <ul class="text-sm text-blue-800 space-y-1">
            <li>• Laporan ini menampilkan transaksi dari akun Kas dan Bank (kode 1-1xxx)</li>
            <li>• Kas Masuk = Debit pada akun kas/bank</li>
            <li>• Kas Keluar = Kredit pada akun kas/bank</li>
            <li>• Untuk analisis lebih detail, gunakan metode langsung atau tidak langsung</li>
        </ul>
    </div>

    <div class="mt-4 text-sm text-gray-600 flex justify-between">
        <div>
            <p>Dicetak pada: {{ date('d/m/Y H:i:s') }}</p>
        </div>
        <div class="text-right">
            <button onclick="window.print()" class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                🖨️ Cetak
            </button>
        </div>
    </div>
</div>

<style>
@media print {
    .no-print, form, button {
        display: none !important;
    }
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection