@extends('layouts-main.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Keuangan DHS Dipanet Hotspot Solution')

@section('content')

@php
    $profitMargin = $revenueThisMonth > 0
        ? (($revenueThisMonth - $expenseThisMonth) / $revenueThisMonth) * 100
        : 0;

    $nwcValue = ($cashBalance + $bankBalance) - $arBalance;

    $nwcPercentage = ($cashBalance + $bankBalance) > 0
        ? ($nwcValue / ($cashBalance + $bankBalance)) * 100
        : 0;
@endphp


{{-- ============================================================
     BARIS 1 — Kartu Ringkasan Saldo
     ============================================================ --}}
<div class="row">

    <div class="col-xl-4 col-md-6 mb-4">
        <x-dashboard.card
            title="Total Pendapatan"
            :value="'Rp ' . number_format($cashBalance, 0, ',', '.')"
            icon="fas fa-money-bill-wave"
            bg="bg-success-grey"
            icon-class="text-primary"
        />
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <x-dashboard.card
            title="Penjualan Voucher Bulan ini"
            :value="'Rp ' . number_format($voucherBalance['this_month_total'], 0, ',', '.')"
            icon="fas fa-university"
            bg="bg-success-dark"
            :href="route('voucher-sales.index')"
        />
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <x-dashboard.card
            title="Belum Bayar (Piutang Usaha)"
            :value="'Rp ' . number_format( $arBalance, 0, ',', '.')"
            icon="fas fa-file-invoice-dollar"
            bg="bg-dark-blue"
            :href="route('finance.transaksi.index', ['status' => 'unpaid'])"
        />
    </div>

    <div class="col-xl-4 col-md-6 mb-4">
        <x-dashboard.card
            title="Pembayaran Bulan Ini"
            :value="'Rp ' . number_format($paid, 0, ',', '.')"
            icon="fas fa-hand-holding-usd"
            bg="bg-dark-blue"
            :href="route('sinkron.index')"
        />
    </div>

</div>


{{-- ============================================================
     BARIS 2 — Grafik + Kartu Bulanan
     ============================================================ --}}
<div class="row">

    {{-- Grafik 6 Bulan --}}
    <div class="col-xl-8 col-lg-7">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">
                    Statistik Pendapatan vs Beban (6 Bulan Terakhir)
                </h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button"
                       data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in">
                        <div class="dropdown-header">Actions:</div>
                        <a class="dropdown-item" href="#">Download Report</a>
                        <a class="dropdown-item" href="#">View Details</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="chart-area" style="height: 320px;">
                    <canvas id="monthlyStatsChart"></canvas>
                </div>
                <hr>
                <div class="small text-muted">
                    <i class="fas fa-info-circle"></i>
                    Pendapatan berasal dari pembayaran invoice dan penjualan voucher.
                    Beban berasal dari pengeluaran operasional.
                </div>
            </div>
        </div>
    </div>

    {{-- Kartu Bulanan --}}
    <div class="col-xl-4 col-lg-5">
        <div class="row">

            {{-- Pendapatan Bulan Ini --}}
            <div class="col-12">
                <x-dashboard.card
                    title="Pendapatan (Bulan Ini)"
                    :value="'Rp ' . number_format($revenueThisMonth, 0, ',', '.')"
                    icon="fas fa-dollar-sign"
                    bg="card-warning-dark border-left-warning"
                    :subtitle="now()->isoFormat('MMMM YYYY')"
                />
            </div>

            {{-- Pendapatan Lain Bulan Ini --}}
            <div class="col-12 mt-3">
                <x-dashboard.card
                    title="Pendapatan Lain (Bulan Ini)"
                    :value="'Rp ' . number_format($otherIncomeThisMonth ?? 0, 0, ',', '.')"
                    icon="fas fa-wallet"
                    bg="bg-orange-soft border-left-orange"
                    :href="route('other-incomes.index')"
                    subtitle="Sumber pendapatan selain invoice & voucher"
                    icon-class="icon-orange"
                />
            </div>

            {{-- Pengeluaran Bulan Ini --}}
            <div class="col-12 mt-3">
                <x-dashboard.card
                    title="Pengeluaran (Bulan Ini)"
                    :value="'Rp ' . number_format($expenseThisMonth, 0, ',', '.')"
                    icon="fas fa-receipt"
                    bg="bg-darkred-soft border-left-darkred"
                    :href="route('expenses.index')"
                    :subtitle="now()->isoFormat('MMMM YYYY')"
                    icon-class="icon-darkred"
                />
            </div>

            {{-- Laba / Rugi Bulan Ini --}}
            <div class="col-12 mt-3">
                @php
                    $profit     = $revenueThisMonth - $expenseThisMonth;
                    $profitBg   = $profit >= 0 ? 'border-left-success' : 'border-left-danger';
                    $profitText = 'Rp ' . number_format($profit, 0, ',', '.');
                @endphp
                <x-dashboard.card
                    title="Laba/Rugi (Bulan Ini)"
                    :value="$profitText"
                    icon="fas fa-chart-line"
                    :bg="'card-cream ' . $profitBg"
                    :subtitle="'Margin: ' . number_format($profitMargin, 1) . '%'"
                    icon-class="text-muted"
                />
            </div>

        </div>
    </div>

</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const ctx = document.getElementById('monthlyStatsChart').getContext('2d');

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($monthlyStats['labels']),
            datasets: [
                {
                    label: 'Pendapatan',
                    data: @json($monthlyStats['revenue']),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                },
                {
                    label: 'Beban',
                    data: @json($monthlyStats['expense']),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            scales: {
                x: {
                    ticks: { color: '#ffffff' },
                    grid:  { color: 'rgba(255,255,255,0.05)' },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: '#ffffff',
                        callback: (v) => 'Rp ' + new Intl.NumberFormat('id-ID').format(v),
                    },
                    grid: { color: 'rgba(255,255,255,0.05)' },
                },
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: { color: '#ffffff' },
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleColor: '#ffffff',
                    bodyColor: '#ffffff',
                    borderColor: '#3b82f6',
                    borderWidth: 1,
                    callbacks: {
                        label: (ctx) => {
                            let label = ctx.dataset.label ? ctx.dataset.label + ': ' : '';
                            label += 'Rp ' + new Intl.NumberFormat('id-ID').format(ctx.parsed.y);
                            return label;
                        },
                    },
                },
            },
        },
    });

});
</script>
@endpush