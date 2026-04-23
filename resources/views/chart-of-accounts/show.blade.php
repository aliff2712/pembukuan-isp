@extends('layouts-main.app')

@section('title', 'Account Detail')
@section('page-title', 'Account Detail')

@section('content')

<div class="container-fluid">

<div class="card account-card shadow border-0">

    <!-- HEADER -->
    <div class="card-header account-header d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center gap-3">

            <div class="account-icon">
                <i class="fas fa-wallet"></i>
            </div>

            <div>
                <h5 class="mb-0 fw-bold text-white">Account Detail</h5>
                <small class="text-light opacity-75">
                    Informasi detail akun keuangan
                </small>
            </div>

        </div>

        <span class="badge account-type">
            {{ ucfirst($account->account_type) }}
        </span>

    </div>


    <!-- BODY -->
    <div class="card-body p-4">

        <div class="row g-4">

            <!-- ACCOUNT CODE -->
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Account Code</div>
                    <div class="info-value">
                        {{ $account->account_code }}
                    </div>
                </div>
            </div>

            <!-- ACCOUNT NAME -->
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Account Name</div>
                    <div class="info-value">
                        {{ $account->account_name }}
                    </div>
                </div>
            </div>

            <!-- TYPE -->
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Account Type</div>
                    <div class="info-value">
                        {{ ucfirst($account->account_type) }}
                    </div>
                </div>
            </div>

            <!-- CASH ACCOUNT -->
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Cash Account</div>

                    <div class="info-value">

                        @if($account->is_cash)
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle me-1"></i>
                                Yes
                            </span>
                        @else
                            <span class="badge badge-secondary">
                                No
                            </span>
                        @endif

                    </div>

                </div>
            </div>

        </div>

        <!-- ACTION -->
        <div class="mt-4 pt-3 border-top d-flex justify-content-end">

            <a href="{{ route('chart-of-accounts.index') }}"
               class="btn btn-navy px-4">
                <i class="fas fa-arrow-left me-1"></i>
                Back
            </a>

        </div>

    </div>

</div>

</div>


<style>

/* CARD */
.account-card{
    border-radius:14px;
    overflow:hidden;
}

/* HEADER */
.account-header{
    background:#0f172a;
}

/* ICON */
.account-icon{
    width:45px;
    height:45px;
    border-radius:10px;
    background:rgba(255,255,255,.15);
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    font-size:18px;
}

/* TYPE BADGE */
.account-type{
    background:rgba(255,255,255,.2);
    color:white;
    font-weight:500;
    padding:6px 12px;
}

/* INFO BOX */
.info-box{
    background:#f8fafc;
    border-radius:10px;
    padding:16px;
    transition:all .2s ease;
}

.info-box:hover{
    transform:translateY(-2px);
    box-shadow:0 6px 15px rgba(0,0,0,.08);
}

/* LABEL */
.info-label{
    font-size:12px;
    color:#64748b;
    margin-bottom:4px;
}

/* VALUE */
.info-value{
    font-weight:600;
    color:#0f172a;
}

/* BADGES */
.badge-success{
    background:#16a34a;
}

.badge-secondary{
    background:#64748b;
}

/* BUTTON NAVY */
.btn-navy{
    background:#0f172a;
    color:white;
}

.btn-navy:hover{
    background:#1e293b;
    color:white;
}

</style>


<script>

/* micro interaction */
document.querySelectorAll('.info-box').forEach(box => {

    box.addEventListener('mouseenter', () => {
        box.style.border = "1px solid #0f172a";
    });

    box.addEventListener('mouseleave', () => {
        box.style.border = "1px solid transparent";
    });

});

</script>

@endsection