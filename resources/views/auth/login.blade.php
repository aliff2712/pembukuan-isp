@extends('layouts-main.test')

@section('title', 'Login')

@section('content')

<style>
:root{
    --navy-dark:#0f172a;
    --navy-main:#1e293b;
    --navy-soft:#334155;
    --navy-light:#475569;
    --blue-accent:#3b82f6;
}

/* BACKGROUND */
.login-wrapper{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    background: linear-gradient(135deg,#0f172a,#1e293b,#020617);
}

/* CARD */
.login-card{
    width:100%;
    max-width:420px;
    border-radius:20px;
    background: rgba(255,255,255,0.04);
    backdrop-filter: blur(18px);
    box-shadow: 0 25px 60px rgba(0,0,0,.35);
    padding:35px;
    color:white;
}

/* LOGO */
.login-logo{
    width:70px;
    height:70px;
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    border-radius:18px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-size:28px;
    margin:auto;
    margin-bottom:15px;
}

/* INPUT */
.login-card .form-control{
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
    color:white;
    border-radius:12px;
    padding:12px;
}

.login-card .form-control:focus{
    background: rgba(255,255,255,.08);
    border-color:#3b82f6;
    box-shadow:none;
    color:white;
}

.login-card .form-control::placeholder{
    color:rgba(255,255,255,.4);
}

/* INPUT GROUP */
.input-group-text{
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
    color:white;
    border-radius:0 12px 12px 0;
}

/* BUTTON */
.btn-login{
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    border:none;
    border-radius:12px;
    padding:12px;
    font-weight:600;
    transition:.3s;
}

.btn-login:hover{
    transform: translateY(-2px);
    box-shadow:0 10px 20px rgba(59,130,246,.3);
}

/* LINK */
.login-card a{
    color:#60a5fa;
    text-decoration:none;
}

.login-card a:hover{
    color:white;
}

/* ALERT */
.login-card .alert{
    border-radius:12px;
}
</style>
<div class="login-wrapper">
    <div class="login-card">
        <div class="text-center mb-4">
            <div class="login-logo">
               <img src="/assets/img/dhs-logo.png" alt="Logo" style="width: 100%; height: 100%; object-fit:contain; border-radius: 35%;">
            </div>
            <h4 class="fw-bold mb-1">
                DHS Finance
            </h4>
            <small class="text-light opacity-75">
                Login ke dashboard keuangan
            </small>
        </div>
        {{-- SUCCESS --}}
        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif
        {{-- ERROR --}}
        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif
        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf
            <!-- EMAIL -->
            <div class="mb-3">
                <label class="form-label small opacity-75">
                    Email
                </label>
                <input
                    type="email"
                    name="email"
                    class="form-control"
                    placeholder="admin@dhsfinance.com"
                    required>
            </div>
            <!-- PASSWORD -->
            <div class="mb-3">
                <label class="form-label small opacity-75">
                    Password
                </label>
                <div class="input-group">
                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control"
                        placeholder="Masukkan password"
                        required>
                    <button
                        class="input-group-text"
                        type="button"
                        id="togglePassword">

                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            <!-- REMEMBER -->
            <div class="d-flex justify-content-between mb-4">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="remember">
                    <label class="form-check-label small">
                        Ingat saya
                    </label>
                </div>
            </div>
            <!-- BUTTON -->
            <button
                class="btn btn-login w-100 text-white"
                id="loginBtn">
                <span class="btn-text">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Masuk
                </span>
                <span class="spinner-border spinner-border-sm d-none"></span>
            </button>
        </form>
        <div class="text-center mt-4">
<!--<div class="d-flex align-items-center mb-3 opacity-50">-->
<!--    <div class="flex-grow-1 border-top"></div>-->
<!--    <small class="mx-3">atau</small>-->
<!--    <div class="flex-grow-1 border-top"></div>-->
<!--</div>-->
<!--<small class="opacity-75">-->
<!--    Belum punya akun?-->
<!--</small>-->
<!--<a href="{{ route('register') }}"-->
<!--   class="fw-semibold ms-1">-->
<!--    Daftar sekarang-->
<!--</a>-->
</div>
    </div>
</div>
<script>
document.getElementById('togglePassword').onclick=function(){
    let input=document.getElementById('password');
    let icon=this.querySelector('i');
    if(input.type==='password'){
        input.type='text';
        icon.classList.replace('fa-eye','fa-eye-slash');

    }else{

        input.type='password';
        icon.classList.replace('fa-eye-slash','fa-eye');

    }

};



document.getElementById('loginForm').onsubmit=function(){

    let btn=document.getElementById('loginBtn');

    btn.disabled=true;

    btn.querySelector('.btn-text').classList.add('d-none');

    btn.querySelector('.spinner-border').classList.remove('d-none');

};
</script>
@endsection