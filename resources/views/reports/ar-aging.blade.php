@extends('layouts-main.app')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-xl font-bold mb-4">
        Accounts Receivable Aging Report
    </h1>

    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2 text-left">Aging Bucket</th>
                <th class="border p-2 text-right">Total (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="border p-2">0 – 30 hari</td>
                <td class="border p-2 text-right">
                    {{ number_format($summary['0_30']) }}
                </td>
            </tr>
            <tr>
                <td class="border p-2">31 – 60 hari</td>
                <td class="border p-2 text-right">
                    {{ number_format($summary['31_60']) }}
                </td>
            </tr>
            <tr>
                <td class="border p-2">61 – 90 hari</td>
                <td class="border p-2 text-right">
                    {{ number_format($summary['61_90']) }}
                </td>
            </tr>
            <tr>
                <td class="border p-2 font-semibold">> 90 hari</td>
                <td class="border p-2 text-right font-semibold text-red-600">
                    {{ number_format($summary['90_plus']) }}
                </td>
            </tr>
        </tbody>
    </table>
</div>
@endsection
