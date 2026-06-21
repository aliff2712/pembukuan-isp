<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Receipt {{ $transaksi->kode_transaksi }}</title>

    <style>
        body {
            background: #f3f4f6;
            margin: 0;
            font-family: "Courier New", monospace;
        }

        .receipt-wrapper {
            width: 360px;
            margin: 40px auto;
        }

        .receipt {
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }

        .logo img {
            width: 80px;
        }

        h4 {
            text-align: center;
            margin: 5px 0;
            font-weight: bold;
        }

        small {
            display: block;
            text-align: center;
            margin-bottom: 10px;
        }

        hr {
            border-top: 1px dashed #000;
            margin: 10px 0;
        }

        .row-between {
            display: flex;
            justify-content: space-between;
            margin-bottom: 4px;
        }

        .bold {
            font-weight: bold;
        }

        .text-center {
            text-align: center;
        }

        .status-paid {
            color: green;
            font-weight: bold;
        }

        .status-unpaid {
            color: red;
            font-weight: bold;
        }

        .btn-area {
            text-align: center;
            margin-top: 20px;
        }

        .btn-modern {
            padding: 8px 16px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            margin: 0 5px;
            transition: 0.2s;
        }

        .btn-print {
            background: #111827;
            color: white;
        }

        .btn-back {
            background: #e5e7eb;
        }

        .btn-modern:hover {
            transform: translateY(-2px);
        }

        @media print {
            body {
                background: white;
            }

            .btn-area {
                display: none;
            }

            .receipt-wrapper {
                margin: 0;
            }

            .receipt {
                box-shadow: none;
                border-radius: 0;
            }
        }
    </style>
</head>
<body>

@php
    $jatuhTempo = $transaksi->jatuh_tempo;
@endphp

<div class="receipt-wrapper">
    <div class="receipt">
    <div class="logo">
    <img src="{{ asset('assets/img/dhs-logo.png') }}" 
         alt="DHS Logo"
         style="
            width:100px;
            height:100px;
            object-fit:cover;
            border-radius:50%;
         ">
</div>


        <h4>DHS DIPANET</h4>
        <small>Hotspot Solution</small>

        <hr>

        <div class="row-between">
            <span>No Transaksi</span>
            <span>{{ $transaksi->kode_transaksi }}</span>
        </div>

        <div class="row-between">
            <span>Customer</span>
            <span>{{ $transaksi->nama_customer }}</span>
        </div>

        <div class="row-between">
            <span>Tanggal</span>
            <span>{{ $transaksi->tanggal->format('d/m/Y') }}</span>
        </div>

        <div class="row-between">
            <span>Jatuh Tempo</span>
            <span>{{ $jatuhTempo->format('d/m/Y') }}</span>
        </div>

        <div class="row-between">
            <span>Status</span>
            <span class="{{ $transaksi->status === 'paid' ? 'status-paid' : 'status-unpaid' }}">
                {{ strtoupper($transaksi->status) }}
            </span>
        </div>

        <hr>

        <div class="row-between bold">
            <span>TOTAL</span>
            <span>Rp {{ number_format($transaksi->total,0,',','.') }}</span>
        </div>

        @if($transaksi->status === 'paid')
            <div class="row-between">
                <span>Dibayar Pada</span>
                <span>{{ \Carbon\Carbon::parse($transaksi->paid_at)->format('d/m/Y H:i') }}</span>
            </div>
        @endif

        <hr>

        <div class="text-center">
            *** TERIMA KASIH *** <br>
            Simpan struk ini sebagai bukti pembayaran
        </div>

    </div>

    <div class="btn-area">
        <button onclick="window.print()" class="btn-modern btn-print">
            Print
        </button>

        <button onclick="window.close()" class="btn-modern btn-back">
            Tutup
        </button>
    </div>
</div>

</body>
</html>
