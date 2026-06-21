<form method="POST" action="{{ route('profile.update') }}" id="profileForm">
    @csrf
    @method('PATCH')

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label fw-semibold">
            <i class="fas fa-user text-muted me-1"></i> Nama Lengkap
        </label>
        <input 
            type="text" 
            name="name" 
            id="name" 
            class="form-control @error('name') is-invalid @enderror" 
            value="{{ old('name', $user->name) }}" 
            required
            autofocus
            autocomplete="name"
        >
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Email -->
    <div class="mb-3">
        <label for="email" class="form-label fw-semibold">
            <i class="fas fa-envelope text-muted me-1"></i> Email Address
        </label>
        <input 
            type="email" 
            name="email" 
            id="email" 
            class="form-control @error('email') is-invalid @enderror" 
            value="{{ old('email', $user->email) }}" 
            required
            autocomplete="username"
        >
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
        
        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning mt-2 small">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Email Anda belum diverifikasi.
                <form method="POST" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">
                        Kirim ulang link verifikasi
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Registered At -->
    <div class="mb-3">
        <label class="form-label fw-semibold">
            <i class="fas fa-calendar text-muted me-1"></i> Terdaftar Sejak
        </label>
        <input 
            type="text" 
            class="form-control" 
            value="{{ $user->created_at->format('d F Y, H:i') }}" 
            disabled
            readonly
        >
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="fas fa-save me-1"></i> Simpan Perubahan
        </button>
    </div>
</form>