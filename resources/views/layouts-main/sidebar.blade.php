<ul class="navbar-nav modern-sidebar accordion h-100" id="accordionSidebar">

    <!-- BRAND -->
    <li class="sidebar-brand-wrapper">
        <div class="sidebar-brand d-flex align-items-center justify-content-center">
            <div class="sidebar-brand-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="sidebar-brand-text ms-2">DHS Finance</div>
        </div>
    </li>

    <hr class="sidebar-divider my-3">

    <!-- DASHBOARD -->
    <li class="nav-item {{ request()->routeIs('dashboard*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Transaksi</div>

    <!-- FINANCE TRANSAKSI -->
    <li class="nav-item {{ request()->routeIs('finance.transaksi*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.transaksi.index') }}">
            <i class="fas fa-exchange-alt"></i>
            <span>Transaksi</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('voucher-sales*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('voucher-sales.index') }}">
            <i class="fas fa-receipt"></i>
            <span>Penjualan Voucher</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('expenses*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('expenses.index') }}">
            <i class="fas fa-wallet"></i>
            <span>Pengeluaran</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('other-incomes*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('other-incomes.index') }}">
            <i class="fas fa-coins"></i>
            <span>Pendapatan Lain</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Billing ISP</div>

    <!-- SINKRON TRANSAKSI -->
    <li class="nav-item {{ request()->routeIs('sinkron.transaksi.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('sinkron.index') }}">
            <i class="fas fa-sync-alt"></i>
            <span>Sinkron Transaksi</span>
        </a>
    </li>

<!-- PAYMENT STAGING -->
<li class="nav-item {{ request()->routeIs('pembukuan.staging.*') ? 'active' : '' }}">
    <a class="nav-link" href="{{ route('pembukuan.staging.index') }}">
        <i class="fas fa-clipboard-check"></i>
        <span>Review Import</span>
        @php $totalFlagged = \App\Models\PaymentStaging::where('status', 'flagged')->count(); @endphp
        @if($totalFlagged > 0)
            <span class="badge bg-danger ms-1">{{ $totalFlagged }}</span>
        @endif
    </a>
</li>

    <!-- SINKRON PELANGGAN -->
    <li class="nav-item {{ request()->routeIs('sinkron.pelanggan.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('sinkron.pelanggan.index') }}">
            <i class="fas fa-users"></i>
            <span>Daftar Pelanggan</span>
        </a>
    </li>
        
        <!-- SINKRON PELANGGAN -->
    {{-- <li class="nav-item {{ request()->routeIs('sinkron.pelanggan.belum-bayar.index') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('sinkron.pelanggan.belum-bayar.index') }}">
            <i class="fas fa-users"></i>
            <span>Daftar Pelanggan Belum bayar</span>
        </a>
    </li> --}}

    <hr class="sidebar-divider">

    <div class="sidebar-heading">Akuntansi</div>

    <li class="nav-item {{ request()->routeIs('chart-of-accounts*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('chart-of-accounts.index') }}">
            <i class="fas fa-list"></i>
            <span>Chart of Accounts</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('journal-entries*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('journal-entries.index') }}">
            <i class="fas fa-book"></i>
            <span>Journal Entries</span>
        </a>
    </li>

    <li class="nav-item {{ request()->routeIs('finance.laporan*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('finance.laporan.index') }}">
            <i class="fas fa-chart-bar"></i>
            <span>Laporan</span>
        </a>
    </li>

</ul>

<style>
    /* ================================
   MODERN SIDEBAR RESPONSIVE
================================ */

.modern-sidebar {
    background: #0f172a;
    min-height: 100vh;
    height: 100%;
    padding-top: 1rem;
    overflow-y: auto;
    overflow-x: hidden;
    border-right: 1px solid rgba(255,255,255,0.05);
}

.sidebar-brand-wrapper {
    list-style: none;
}

.modern-sidebar .sidebar-brand {
    font-weight: 600;
    font-size: 0.95rem;
    color: #ffffff;
    padding: 1rem;
    border-radius: 14px;
    margin: 0 12px;
    background: rgba(255,255,255,0.03);
}

.modern-sidebar .sidebar-heading {
    font-size: 0.65rem;
    font-weight: 600;
    color: rgba(255,255,255,0.4);
    text-transform: uppercase;
    padding: 0 1.5rem;
    margin-top: 1.2rem;
    margin-bottom: 0.5rem;
}

.modern-sidebar .nav-link {
    color: rgba(255,255,255,0.75);
    padding: 0.7rem 1rem;
    margin: 4px 12px;
    border-radius: 12px;
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    transition: 0.2s ease;
}

.modern-sidebar .nav-link i {
    margin-right: 10px;
    font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
}

.modern-sidebar .nav-link:hover {
    background: rgba(59,130,246,0.12);
    color: #ffffff;
}

.modern-sidebar .nav-link:hover i {
    color: #3b82f6;
}

.modern-sidebar .nav-item.active .nav-link {
    background: #1e293b;
    color: #ffffff;
    font-weight: 500;
    box-shadow: inset 3px 0 0 #3b82f6;
}

/* ACTIVE COLOR PER MENU */
.nav-item.active:has(a[href*="finance/transaksi"]) .nav-link {
    background: #1e3a8a;
    box-shadow: inset 3px 0 0 #3b82f6;
}

.nav-item.active:has(a[href*="finance/laporan"]) .nav-link {
    background: #374151;
    box-shadow: inset 3px 0 0 #9ca3af;
}

.nav-item.active:has(a[href*="voucher-sales"]) .nav-link {
    background: #14532d;
    box-shadow: inset 3px 0 0 #22c55e;
}

.nav-item.active:has(a[href*="expenses"]) .nav-link {
    background: #7f1d1d;
    box-shadow: inset 3px 0 0 #ef4444;
}

.nav-item.active:has(a[href*="other-incomes"]) .nav-link {
    background: #7c2d12;
    box-shadow: inset 3px 0 0 #f97316;
}

.nav-item.active:has(a[href*="chart-of-accounts"]) .nav-link {
    background: #374151;
    box-shadow: inset 3px 0 0 #9ca3af;
}

.nav-item.active:has(a[href*="journal-entries"]) .nav-link {
    background: #374151;
    box-shadow: inset 3px 0 0 #9ca3af;
}

/* SINKRON - Ungu */
.nav-item.active:has(a[href*="pembukuan/sinkron"]) .nav-link {
    background: #3b0764;
    box-shadow: inset 3px 0 0 #a855f7;
}

/* PELANGGAN - Cyan */
.nav-item.active:has(a[href*="pembukuan/pelanggan"]) .nav-link {
    background: #164e63;
    box-shadow: inset 3px 0 0 #06b6d4;
}

.modern-sidebar .sidebar-divider {
    border-top: 1px solid rgba(255,255,255,0.06);
    margin: 1rem 1rem;
}

.modern-sidebar::-webkit-scrollbar { width: 6px; }
.modern-sidebar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

@media (max-width: 768px) {
    .modern-sidebar { min-height: auto; padding-top: 0.5rem; }
    .modern-sidebar .sidebar-brand { margin: 0 8px; padding: 0.8rem; font-size: 0.9rem; }
    .modern-sidebar .nav-link { margin: 4px 8px; padding: 0.65rem 0.9rem; font-size: 0.8rem; }
    .modern-sidebar .nav-link i { font-size: 0.85rem; }
}
</style>