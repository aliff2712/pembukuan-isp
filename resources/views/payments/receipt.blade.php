<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            border: 2px solid #333;
            padding: 20px;
        }

        /* Header */
        .header {
            border-bottom: 3px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-content {
            display: table;
            width: 100%;
        }

        .company-info {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .company-info h1 {
            font-size: 24px;
            color: #2c3e50;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .company-info p {
            font-size: 11px;
            color: #555;
            margin: 2px 0;
        }

        .receipt-info {
            display: table-cell;
            width: 40%;
            text-align: right;
            vertical-align: top;
        }

        .receipt-title {
            font-size: 28px;
            font-weight: bold;
            color: #27ae60;
            margin-bottom: 5px;
        }

        .receipt-number {
            font-size: 14px;
            color: #555;
            margin-bottom: 3px;
        }

        .receipt-date {
            font-size: 12px;
            color: #555;
        }

        /* Customer & Invoice Info */
        .info-section {
            margin: 25px 0;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .info-column {
            display: table-cell;
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }

        .info-box {
            background: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
        }

        .info-box h3 {
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #ccc;
            padding-bottom: 5px;
        }

        .info-row {
            margin: 8px 0;
            display: table;
            width: 100%;
        }

        .info-label {
            display: table-cell;
            width: 40%;
            font-weight: bold;
            color: #555;
        }

        .info-value {
            display: table-cell;
            width: 60%;
            color: #333;
        }

        /* Payment Details Table */
        .payment-details {
            margin: 30px 0;
        }

        .payment-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .payment-table thead {
            background: #34495e;
            color: white;
        }

        .payment-table th {
            padding: 12px;
            text-align: left;
            font-weight: bold;
            font-size: 12px;
        }

        .payment-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #ddd;
        }

        .payment-table tbody tr:hover {
            background: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Amount Box */
        .amount-box {
            background: #27ae60;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .amount-label {
            font-size: 14px;
            margin-bottom: 5px;
        }

        .amount-value {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .amount-words {
            font-size: 12px;
            font-style: italic;
            opacity: 0.9;
        }

        /* Invoice Summary */
        .invoice-summary {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }

        .summary-table {
            width: 100%;
            margin-top: 10px;
        }

        .summary-table td {
            padding: 5px 0;
        }

        .summary-table .label {
            width: 70%;
            font-weight: bold;
        }

        .summary-table .value {
            width: 30%;
            text-align: right;
        }

        .summary-table .total-row {
            border-top: 2px solid #ffc107;
            padding-top: 10px;
            font-size: 14px;
            font-weight: bold;
        }

        /* Status Badge */
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: bold;
        }

        .status-paid {
            background: #27ae60;
            color: white;
        }

        .status-partial {
            background: #f39c12;
            color: white;
        }

        .status-unpaid {
            background: #e74c3c;
            color: white;
        }

        /* Notes */
        .notes-section {
            margin: 20px 0;
            padding: 15px;
            background: #f8f9fa;
            border-left: 4px solid #3498db;
        }

        .notes-section h4 {
            font-size: 13px;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .notes-section p {
            font-size: 11px;
            color: #555;
            line-height: 1.6;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 50px;
            display: table;
            width: 100%;
        }

        .signature-box {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }

        .signature-line {
            border-top: 1px solid #333;
            margin-top: 80px;
            padding-top: 10px;
            font-weight: bold;
        }

        .signature-label {
            font-size: 11px;
            color: #777;
            margin-bottom: 5px;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #333;
            text-align: center;
            font-size: 10px;
            color: #777;
        }

        .footer p {
            margin: 3px 0;
        }

        /* Watermark */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 100px;
            color: rgba(39, 174, 96, 0.1);
            font-weight: bold;
            z-index: -1;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }
            
            .container {
                border: none;
            }
        }
    </style>
</head>
<body>
    <div class="watermark">PAID</div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="company-info">
                    <h1>{{ $company['name'] }}</h1>
                    <p>{{ $company['address'] }}</p>
                    <p>Phone: {{ $company['phone'] }} | Email: {{ $company['email'] }}</p>
                </div>
                <div class="receipt-info">
                    <div class="receipt-title">KUITANSI</div>
                    <div class="receipt-number">No: {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}</div>
                    <div class="receipt-date">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d F Y') }}</div>
                </div>
            </div>
        </div>

        <!-- Customer & Invoice Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-column">
                    <div class="info-box">
                        <h3>INFORMASI PELANGGAN</h3>
                        <div class="info-row">
                            <span class="info-label">Nama</span>
                            <span class="info-value">: {{ $invoice->customer_name }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">PPPoE</span>
                            <span class="info-value">: {{ $invoice->pppoe }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Paket</span>
                            <span class="info-value">: {{ $invoice->package_name ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Hari Tagih</span>
                            <span class="info-value">: {{ $invoice->billing_day }}</span>
                        </div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-box">
                        <h3>INFORMASI PEMBAYARAN</h3>
                        <div class="info-row">
                            <span class="info-label">Periode</span>
                            <span class="info-value">: {{ $invoice->period_month }}/{{ $invoice->period_year }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Bayar</span>
                            <span class="info-value">: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Metode</span>
                            <span class="info-value">: 
                                @if($payment->method == 'cash')
                                    💵 Cash (Tunai)
                                @else
                                    🏦 Transfer Bank
                                @endif
                            </span>
                        </div>
                        @if($payment->reference)
                        <div class="info-row">
                            <span class="info-label">Referensi</span>
                            <span class="info-value">: {{ $payment->reference }}</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Amount -->
        <div class="amount-box">
            <div class="amount-label">JUMLAH PEMBAYARAN</div>
            <div class="amount-value">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
            <div class="amount-words">
                
            </div>
        </div>

        <!-- Invoice Summary -->
        <div class="invoice-summary">
            <h4 style="margin-bottom: 5px; color: #856404;">📊 RINGKASAN INVOICE</h4>
            <table class="summary-table">
                <tr>
                    <td class="label">Total Invoice</td>
                    <td class="value">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="label">Total Terbayar</td>
                    <td class="value" style="color: #27ae60;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                </tr>
                <tr class="total-row">
                    <td class="label">Sisa Tagihan</td>
                    <td class="value" style="color: {{ $outstanding > 0 ? '#e74c3c' : '#27ae60' }};">
                        Rp {{ number_format($outstanding, 0, ',', '.') }}
                    </td>
                </tr>
                <tr>
                    <td class="label">Status Invoice</td>
                    <td class="value">
                        @if($outstanding <= 0)
                            <span class="status-badge status-paid">LUNAS</span>
                        @elseif($totalPaid > 0)
                            <span class="status-badge status-partial">SEBAGIAN</span>
                        @else
                            <span class="status-badge status-unpaid">BELUM BAYAR</span>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Payment History (if multiple payments) -->
        @if($invoice->payments->count() > 1)
        <div class="payment-details">
            <h3 style="margin-bottom: 10px; color: #2c3e50;">📜 RIWAYAT PEMBAYARAN</h3>
            <table class="payment-table">
                <thead>
                    <tr>
                        <th width="5%">No</th>
                        <th width="20%">Tanggal</th>
                        <th width="35%">Jumlah</th>
                        <th width="20%">Metode</th>
                        <th width="20%">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($invoice->payments->sortBy('payment_date') as $index => $p)
                    <tr style="{{ $p->id == $payment->id ? 'background: #d4edda; font-weight: bold;' : '' }}">
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ \Carbon\Carbon::parse($p->payment_date)->format('d M Y') }}</td>
                        <td class="text-right">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td>
                            @if($p->method == 'cash')
                                Cash
                            @else
                                Bank
                            @endif
                        </td>
                        <td class="text-center">
                            @if($p->id == $payment->id)
                                <span class="status-badge status-paid">SAAT INI</span>
                            @else
                                ✓
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot style="background: #f8f9fa;">
                    <tr>
                        <th colspan="2" class="text-right">TOTAL:</th>
                        <th class="text-right">Rp {{ number_format($totalPaid, 0, ',', '.') }}</th>
                        <th colspan="2"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
        @endif

        <!-- Notes -->
        @if($payment->note)
        <div class="notes-section">
            <h4>📝 CATATAN</h4>
            <p>{{ $payment->note }}</p>
        </div>
        @endif

        <!-- Important Notes -->
        <div class="notes-section" style="border-left-color: #e74c3c;">
            <h4>⚠️ CATATAN PENTING</h4>
            <p>
                1. Kuitansi ini adalah bukti pembayaran yang sah.<br>
                2. Harap simpan kuitansi ini sebagai bukti pembayaran.<br>
                3. Untuk pertanyaan, hubungi customer service kami.<br>
                4. Pembayaran yang sudah diterima tidak dapat dikembalikan.
            </p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Penerima Pembayaran,</div>
                <div class="signature-line">
                    {{ $company['name'] }}
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-label">Pelanggan,</div>
                <div class="signature-line">
                    {{ $invoice->customer_name }}
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $company['name'] }}</strong></p>
            <p>{{ $company['address'] }} | {{ $company['phone'] }} | {{ $company['email'] }}</p>
            <p style="margin-top: 10px; font-size: 9px;">
                Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }} | 
                Kuitansi No: {{ str_pad($payment->id, 6, '0', STR_PAD_LEFT) }}
            </p>
        </div>
    </div>
</body>
</html>