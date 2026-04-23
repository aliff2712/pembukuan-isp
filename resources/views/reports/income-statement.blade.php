{{-- @extends('layouts-main.app')

@section('content')
<div class="max-w-5xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">LAPORAN LABA RUGI</h1>
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
        <!-- PENDAPATAN / REVENUE -->
        <div class="border-b">
            <div class="bg-blue-50 px-4 py-3">
                <h2 class="font-bold text-lg">PENDAPATAN</h2>
            </div>
            <table class="w-full">
                <thead class="bg-gray-100 text-sm">
                    <tr>
                        <th class="px-4 py-2 text-left w-32">Kode</th>
                        <th class="px-4 py-2 text-left">Nama Akun</th>
                        <th class="px-4 py-2 text-right w-40">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenues as $row)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm">{{ $row->account_code }}</td>
                        <td class="px-4 py-2">{{ $row->account_name }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($row->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500 italic">Tidak ada data pendapatan</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-blue-100 font-bold border-t-2">
                        <td colspan="2" class="px-4 py-3">Total Pendapatan</td>
                        <td class="px-4 py-3 text-right font-mono text-lg">{{ number_format($totalRevenue, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- BEBAN / EXPENSES -->
        <div class="border-b">
            <div class="bg-red-50 px-4 py-3 mt-4">
                <h2 class="font-bold text-lg">BEBAN OPERASIONAL</h2>
            </div>
            <table class="w-full">
                <thead class="bg-gray-100 text-sm">
                    <tr>
                        <th class="px-4 py-2 text-left w-32">Kode</th>
                        <th class="px-4 py-2 text-left">Nama Akun</th>
                        <th class="px-4 py-2 text-right w-40">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $row)
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-2 text-sm">{{ $row->account_code }}</td>
                        <td class="px-4 py-2">{{ $row->account_name }}</td>
                        <td class="px-4 py-2 text-right font-mono">{{ number_format($row->amount, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-center text-gray-500 italic">Tidak ada data beban</td>
                    </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-red-100 font-bold border-t-2">
                        <td colspan="2" class="px-4 py-3">Total Beban Operasional</td>
                        <td class="px-4 py-3 text-right font-mono text-lg">{{ number_format($totalExpense, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- RINGKASAN -->
        <div class="p-6 bg-gray-50">
            <table class="w-full text-base">
                <tr class="border-b">
                    <td class="py-2 font-semibold">Laba Kotor (Gross Profit)</td>
                    <td class="py-2 text-right font-mono w-40">{{ number_format($grossProfit, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-b">
                    <td class="py-2 font-semibold">Laba Operasional (Operating Profit)</td>
                    <td class="py-2 text-right font-mono w-40">{{ number_format($operatingProfit, 0, ',', '.') }}</td>
                </tr>
                <tr class="border-t-2 border-gray-800">
                    <td class="py-3 font-bold text-lg">
                        LABA/(RUGI) BERSIH
                        @if($netProfit >= 0)
                            <span class="text-green-600">(Net Profit)</span>
                        @else
                            <span class="text-red-600">(Net Loss)</span>
                        @endif
                    </td>
                    <td class="py-3 text-right font-mono font-bold text-xl {{ $netProfit >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ number_format($netProfit, 0, ',', '.') }}
                    </td>
                </tr>
            </table>
        </div>
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
    .no-print {
        display: none !important;
    }
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection --}}