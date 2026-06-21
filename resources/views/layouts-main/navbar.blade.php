<nav class="navbar navbar-expand modern-topbar px-3 px-md-4">

    <!-- Mobile Toggle (Offcanvas Trigger) -->
    <button class="btn btn-toggle d-md-none me-3"
            type="button"
            data-bs-toggle="offcanvas"
            data-bs-target="#mobileSidebar">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Page Title -->
    <div class="me-auto">
        <h5 class="mb-0 page-title">
            DHS Finance
        </h5>
    </div>

  
</nav>
<style>
    /* =============================
   PREMIUM NAVBAR v2
============================= */

/* =============================
   NAVY TOPBAR (MATCH SIDEBAR)
============================= */
/* ================= NAVBAR CORE ================= */

.modern-topbar {
    background: linear-gradient(90deg, #0f172a, #111827);
    border-bottom: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    min-height: 60px;
}

/* Title */
.page-title {
    font-weight: 600;
    font-size: 1rem;
    color: #ffffff;
}

/* Toggle */
.btn-toggle {
    background: rgba(255,255,255,0.08);
    border-radius: 10px;
    width: 38px;
    height: 38px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    transition: 0.2s;
}

.btn-toggle:hover {
    background: rgba(59,130,246,0.25);
    color: #ffffff;
}

/* Icon buttons */
.icon-button {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #cbd5e1;
    transition: 0.2s;
}

.icon-button:hover {
    background: rgba(59,130,246,0.25);
    color: #ffffff;
}

/* Avatar */
.modern-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 0.9rem;
}

/* Badge */
.notification-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    background: #3b82f6;
    color: #fff;
    font-size: 0.6rem;
    padding: 3px 6px;
    border-radius: 20px;
    font-weight: 600;
}

/* Dropdown */
.modern-dropdown {
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 20px 45px rgba(15,23,42,0.15);
    min-width: 260px;
}

.dropdown-header-modern {
    padding: 0.75rem 1rem;
    font-weight: 600;
    border-bottom: 1px solid #e2e8f0;
}

.dropdown-scroll {
    max-height: 280px;
    overflow-y: auto;
}

.dropdown-footer {
    padding: 0.6rem;
    border-top: 1px solid #e2e8f0;
}

.modern-item {
    border-radius: 12px;
    padding: 0.6rem 0.75rem;
    transition: 0.2s;
}

.modern-item:hover {
    background: #f1f5f9;
}

/* Status Dot */
.status-dot {
    position: absolute;
    bottom: 0;
    right: 2px;
    width: 10px;
    height: 10px;
    background: #22c55e;
    border-radius: 50%;
    border: 2px solid white;
}

/* ================= MOBILE ================= */

@media (max-width: 768px) {

    .page-title {
        font-size: 0.9rem;
    }

    .modern-dropdown {
        min-width: 90vw;
    }

}
</style>