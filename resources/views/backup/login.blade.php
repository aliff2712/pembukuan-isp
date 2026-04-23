@extends('layouts-main.test')

@section('title', 'Login')

@section('content')
<div class="login-wrapper">
    <div class="card login-card shadow-lg border-0">
        <div class="card-body p-4">

            <div class="text-center mb-4">
                <h3 class="fw-bold">Selamat Datang 👋</h3>
                <p class="text-muted small mb-0">Silakan login untuk melanjutkan</p>
            </div>

            {{-- Session Status --}}
            @if(session('status'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('status') }}
                </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Oops!</strong>
                    <ul class="mb-0 mt-2 small">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                {{-- Email --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Email</label>
                    <input 
                        type="email" 
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control rounded-3"
                        placeholder="admin@dhsfinance.com"
                        required
                    >
                </div>

                {{-- Password --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Password</label>
                    <div class="input-group">
                        <input 
                            type="password" 
                            name="password"
                            id="password"
                            class="form-control rounded-start-3"
                            placeholder="Masukkan password"
                            required
                        >
                        <button type="button" 
                                class="btn btn-light border" 
                                id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Remember --}}
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="remember">
                    <label class="form-check-label small">
                        Ingat saya
                    </label>
                </div>

                {{-- Submit --}}
                <div class="d-grid">
                    <button type="submit" 
                            class="btn btn-primary rounded-3 fw-semibold"
                            id="loginBtn">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt me-2"></i>Masuk
                        </span>
                        <span class="spinner-border spinner-border-sm d-none"></span>
                    </button>
                </div>

                {{-- Register Link --}}
                <div class="text-center mt-4">
                    <p class="text-muted small mb-2">Belum punya akun?</p>
                    <a href="{{ route('register') }}" 
                       class="btn btn-outline-light rounded-3 w-100 fw-semibold">
                        <i class="fas fa-user-plus me-2"></i>Buat Akun
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- Script --}}
<script>
document.addEventListener("DOMContentLoaded", function () {

    // ===== Hide Page Loader =====
    const loader = document.getElementById("pageLoader");
    if (loader) {
        setTimeout(() => {
            loader.style.opacity = "0";
            loader.style.visibility = "hidden";
            loader.style.pointerEvents = "none";
        }, 500);
    }

    // ===== Toggle Password =====
    const toggleBtn = document.getElementById("togglePassword");

    toggleBtn?.addEventListener("click", function () {
        const input = document.getElementById("password");
        const icon = this.querySelector("i");

        if (!input) return;

        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    });

});
</script>

@endsection