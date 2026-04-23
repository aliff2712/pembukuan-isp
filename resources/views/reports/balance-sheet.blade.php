{{-- @extends('layouts-main.app')

@section('content')
<div class="max-w-6xl mx-auto p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold mb-2">NERACA (BALANCE SHEET)</h1>
        <p class="text-gray-600">Per Tanggal: {{ date('d F Y', strtotime($date)) }}</p>
    </div>

    <form method="get" class="mb-6 flex gap-2 items-center bg-gray-50 p-4 rounded">
        <label class="text-sm font-medium">Per Tanggal:</label>
        <input type="date" name="date" value="{{ $date }}" class="border p-2 rounded" />
        <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Tampilkan</button>
        <a href="{{ url()->current() }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600">Reset</a>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="grid md:grid-cols-2 gap-6 p-6">
            
            <!-- KOLOM KIRI: ASET -->
            <div>
                <div class="bg-blue-600 text-white px-4 py-3 -mx-6 -mt-6 mb-4">
                    <h2 class="font-bold text-lg">ASET (ASSETS)</h2>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Nama Akun</th>
                            <th class="px-3 py-2 text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($assets as $row)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $row->account_code }}</td>
                            <td class="px-3 py-2">{{ $row->account_name }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($row->balance, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-center text-gray-500 italic">Tidak ada data aset</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-blue-100 font-bold border-t-2 border-blue-600">
                            <td colspan="2" class="px-3 py-3">TOTAL ASET</td>
                            <td class="px-3 py-3 text-right font-mono text-base">{{ number_format($totalAssets, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <!-- KOLOM KANAN: LIABILITAS & EKUITAS -->
            <div>
                <!-- LIABILITAS -->
                <div class="bg-red-600 text-white px-4 py-3 -mx-6 -mt-6 mb-4">
                    <h2 class="font-bold text-lg">LIABILITAS (LIABILITIES)</h2>
                </div>

                <table class="w-full text-sm mb-6">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Nama Akun</th>
                            <th class="px-3 py-2 text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($liabilities as $row)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $row->account_code }}</td>
                            <td class="px-3 py-2">{{ $row->account_name }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($row->balance, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-center text-gray-500 italic">Tidak ada data liabilitas</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="bg-red-100 font-bold border-t-2 border-red-600">
                            <td colspan="2" class="px-3 py-3">TOTAL LIABILITAS</td>
                            <td class="px-3 py-3 text-right font-mono text-base">{{ number_format($totalLiabilities, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- EKUITAS -->
                <div class="bg-green-600 text-white px-4 py-3 -mx-6 mb-4">
                    <h2 class="font-bold text-lg">EKUITAS (EQUITY)</h2>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-3 py-2 text-left">Kode</th>
                            <th class="px-3 py-2 text-left">Nama Akun</th>
                            <th class="px-3 py-2 text-right">Jumlah (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equity as $row)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $row->account_code }}</td>
                            <td class="px-3 py-2">{{ $row->account_name }}</td>
                            <td class="px-3 py-2 text-right font-mono">{{ number_format($row->balance, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-3 py-3 text-center text-gray-500 italic">Tidak ada data ekuitas</td>
                        </tr>
                        @endforelse
                        
                        <!-- Laba/Rugi Tahun Berjalan -->
                        <tr class="border-t bg-yellow-50">
                            <td class="px-3 py-2 font-semibold">-</td>
                            <td class="px-3 py-2 font-semibold">Laba/(Rugi) Tahun Berjalan</td>
                            <td class="px-3 py-2 text-right font-mono font-semibold {{ $netIncome >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                {{ number_format($netIncome, 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr class="bg-green-100 font-bold border-t-2 border-green-600">
                            <td colspan="2" class="px-3 py-3">TOTAL EKUITAS</td>
                            <td class="px-3 py-3 text-right font-mono text-base">{{ number_format($totalEquity, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>

                <!-- Total Liabilitas + Ekuitas -->
                <div class="mt-4 bg-gray-800 text-white px-3 py-3 rounded">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-base">TOTAL LIABILITAS + EKUITAS</span>
                        <span class="font-mono font-bold text-lg">{{ number_format($totalLiabilitiesEquity, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Validasi Balance -->
        <div class="px-6 pb-6">
            @php
                $difference = $totalAssets - $totalLiabilitiesEquity;
                $isBalanced = abs($difference) < 1; // Toleransi pembulatan
            @endphp
            
            @if($isBalanced)
                <div class="bg-green-100 border-l-4 border-green-600 p-4 rounded">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">✓</span>
                        <div>
                            <p class="font-bold text-green-800">Neraca Seimbang (Balanced)</p>
                            <p class="text-sm text-green-700">Total Aset = Total Liabilitas + Ekuitas</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-red-100 border-l-4 border-red-600 p-4 rounded">
                    <div class="flex items-center">
                        <span class="text-2xl mr-3">⚠</span>
                        <div>
                            <p class="font-bold text-red-800">Neraca Tidak Seimbang!</p>
                            <p class="text-sm text-red-700">Selisih: Rp {{ number_format(abs($difference), 0, ',', '.') }}</p>
                            <p class="text-xs text-red-600 mt-1">Periksa kembali pencatatan jurnal Anda</p>
                        </div>
                    </div>
                </div>
            @endif
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