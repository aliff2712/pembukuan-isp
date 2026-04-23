@extends('layouts-main.test')

@section('title', 'Register')

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
.input-group-text,
.btn-toggle{
    background: rgba(255,255,255,.06);
    border:1px solid rgba(255,255,255,.08);
    color:white;
    border-radius:0 12px 12px 0;
}

/* BUTTON */
.btn-register{
    background: linear-gradient(135deg,#3b82f6,#2563eb);
    border:none;
    border-radius:12px;
    padding:12px;
    font-weight:600;
    transition:.3s;
}

.btn-register:hover{
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
                <i class="fas fa-user-plus"></i>
            </div>

            <h4 class="fw-bold mb-1">
                Buat Akun Baru
            </h4>

            <small class="text-light opacity-75">
                Daftar untuk mengakses DHS Finance
            </small>

        </div>


        {{-- Alert --}}
        @if(session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 small">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif


        <form method="POST" action="{{ route('register') }}" id="registerForm">
        @csrf


        {{-- Name --}}
        <div class="mb-3">

            <label class="form-label small opacity-75">
                Nama Lengkap
            </label>

            <input type="text"
                name="name"
                class="form-control"
                value="{{ old('name') }}"
                placeholder="John Doe"
                required>

        </div>


        {{-- Email --}}
        <div class="mb-3">

            <label class="form-label small opacity-75">
                Email
            </label>

            <input type="email"
                name="email"
                class="form-control"
                value="{{ old('email') }}"
                placeholder="admin@dhsfinance.com"
                required>

        </div>


        {{-- Password --}}
        <div class="mb-3">

            <label class="form-label small opacity-75">
                Password
            </label>

            <div class="input-group">

                <input type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Minimal 8 karakter"
                    required>

                <button type="button"
                    class="input-group-text"
                    id="togglePassword">

                    <i class="fas fa-eye"></i>

                </button>

            </div>

        </div>


        {{-- Confirm --}}
        <div class="mb-4">

            <label class="form-label small opacity-75">
                Konfirmasi Password
            </label>

            <div class="input-group">

                <input type="password"
                    name="password_confirmation"
                    id="password_confirmation"
                    class="form-control"
                    placeholder="Ulangi password"
                    required>

                <button type="button"
                    class="input-group-text"
                    id="togglePasswordConfirmation">

                    <i class="fas fa-eye"></i>

                </button>

            </div>

        </div>


        {{-- Submit --}}
        <button class="btn btn-register w-100 text-white"
            id="registerBtn">

            <span class="btn-text">

                <i class="fas fa-user-plus me-2"></i>
                Daftar Sekarang

            </span>

            <span class="spinner-border spinner-border-sm d-none"></span>

        </button>


        {{-- Divider --}}
        <div class="text-center mt-4">

            <div class="d-flex align-items-center mb-3 opacity-50">
                <div class="flex-grow-1 border-top"></div>
                <small class="mx-3">atau</small>
                <div class="flex-grow-1 border-top"></div>
            </div>

            <small class="opacity-75">
                Sudah punya akun?
            </small>

            <a href="{{ route('login') }}" class="fw-semibold ms-1">
                Login sekarang
            </a>

        </div>


        </form>

    </div>

</div>



<script>

// Toggle password
function toggle(inputId, btnId){

    let btn=document.getElementById(btnId);

    btn.onclick=function(){

        let input=document.getElementById(inputId);
        let icon=this.querySelector('i');

        if(input.type==='password'){

            input.type='text';
            icon.classList.replace('fa-eye','fa-eye-slash');

        }else{

            input.type='password';
            icon.classList.replace('fa-eye-slash','fa-eye');

        }

    }

}

toggle("password","togglePassword");
toggle("password_confirmation","togglePasswordConfirmation");


// Loading button
document.getElementById('registerForm').onsubmit=function(){

    let btn=document.getElementById('registerBtn');

    btn.disabled=true;

    btn.querySelector('.btn-text').classList.add('d-none');

    btn.querySelector('.spinner-border').classList.remove('d-none');

};

</script>


@endsection