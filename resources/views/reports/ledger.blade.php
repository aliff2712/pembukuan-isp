{{-- @extends('layouts-main.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">BUKU BESAR (GENERAL LEDGER)</h1>
        <p class="text-gray-600">Periode: {{ \Carbon\Carbon::create($year, $month)->locale('id')->isoFormat('MMMM YYYY') }}</p>
    </div>

    <form method="get" class="mb-6 flex gap-2 items-center bg-gray-50 p-4 rounded">
        <label class="text-sm font-medium">Bulan:</label>
        <select name="month" class="border p-2 rounded">
            @for($m = 1; $m <= 12; $m++)
                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create(null, $m)->locale('id')->isoFormat('MMMM') }}
                </option>
            @endfor
        </select>
        
        <label class="text-sm font-medium">Tahun:</label>
        <select name="year" class="border p-2 rounded">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
            @endfor
        </select>
        
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
        <a href="{{ url()->current() }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="px-4 py-3 text-left w-24">Kode</th>
                    <th class="px-4 py-3 text-left">Nama Akun</th>
                    <th class="px-4 py-3 text-left w-24">Tipe</th>
                    <th class="px-4 py-3 text-right w-32">Debit</th>
                    <th class="px-4 py-3 text-right w-32">Kredit</th>
                    <th class="px-4 py-3 text-right w-32">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalDebit = 0;
                    $totalCredit = 0;
                @endphp
                
                @forelse($rows as $row)
                    @php
                        $totalDebit += $row->debit;
                        $totalCredit += $row->credit;
                        
                        // Tentukan badge warna berdasarkan tipe akun
                        $badgeColor = match($row->account_type ?? 'unknown') {
                            'asset' => 'bg-blue-100 text-blue-800',
                            'liability' => 'bg-red-100 text-red-800',
                            'equity' => 'bg-green-100 text-green-800',
                            'revenue' => 'bg-purple-100 text-purple-800',
                            'expense' => 'bg-orange-100 text-orange-800',
                            default => 'bg-gray-100 text-gray-800'
                        };
                    @endphp
                    
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-4 py-3 font-mono text-sm">{{ $row->account_code }}</td>
                        <td class="px-4 py-3">{{ $row->account_name }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badgeColor }}">
                                {{ strtoupper($row->account_type ?? '-') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-mono">
                            {{ $row->debit > 0 ? number_format($row->debit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono">
                            {{ $row->credit > 0 ? number_format($row->credit, 0, ',', '.') : '-' }}
                        </td>
                        <td class="px-4 py-3 text-right font-mono {{ $row->balance < 0 ? 'text-red-600' : '' }}">
                            {{ number_format($row->balance, 0, ',', '.') }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                            <p class="text-lg">📊 Tidak ada transaksi pada periode ini</p>
                            <p class="text-sm mt-2">Pilih bulan dan tahun yang berbeda</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            
            @if($rows->count() > 0)
            <tfoot>
                <tr class="bg-gray-800 text-white font-bold border-t-2">
                    <td colspan="3" class="px-4 py-3 text-right">TOTAL:</td>
                    <td class="px-4 py-3 text-right font-mono">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                    <td class="px-4 py-3 text-right font-mono">
                        {{ number_format($totalDebit - $totalCredit, 0, ',', '.') }}
                    </td>
                </tr>
                
                @php
                    $difference = abs($totalDebit - $totalCredit);
                    $isBalanced = $difference < 1;
                @endphp
                
                @if(!$isBalanced)
                <tr class="bg-yellow-100 border-t">
                    <td colspan="6" class="px-4 py-3">
                        <div class="flex items-center text-yellow-800">
                            <span class="text-xl mr-2">⚠</span>
                            <span class="font-semibold">Perhatian: Total Debit dan Kredit tidak seimbang (Selisih: Rp {{ number_format($difference, 0, ',', '.') }})</span>
                        </div>
                    </td>
                </tr>
                @endif
            </tfoot>
            @endif
        </table>
    </div>

    <div class="mt-6 grid md:grid-cols-3 gap-4">
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded">
            <p class="text-sm text-blue-800 font-semibold">Total Debit</p>
            <p class="text-2xl font-bold text-blue-900 font-mono">Rp {{ number_format($totalDebit, 0, ',', '.') }}</p>
        </div>
        <div class="bg-red-50 border-l-4 border-red-600 p-4 rounded">
            <p class="text-sm text-red-800 font-semibold">Total Kredit</p>
            <p class="text-2xl font-bold text-red-900 font-mono">Rp {{ number_format($totalCredit, 0, ',', '.') }}</p>
        </div>
        <div class="bg-green-50 border-l-4 border-green-600 p-4 rounded">
            <p class="text-sm text-green-800 font-semibold">Jumlah Akun</p>
            <p class="text-2xl font-bold text-green-900">{{ $rows->count() }} akun</p>
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
    .no-print, form, button {
        display: none !important;
    }
    body {
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }
}
</style>
@endsection --}}