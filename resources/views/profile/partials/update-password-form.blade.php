<form method="POST" action="{{ route('profile.password') }}" id="passwordForm">
    @csrf
    @method('PATCH')

    <!-- Current Password -->
    <div class="mb-3">
        <label for="current_password" class="form-label fw-semibold">
            <i class="fas fa-key text-muted me-1"></i> Password Saat Ini
        </label>
        <div class="input-group">
            <input 
                type="password" 
                name="current_password" 
                id="current_password" 
                class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                required
                autocomplete="current-password"
            >
            <button type="button" class="btn btn-outline-secondary" id="toggleCurrentPassword" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('current_password', 'updatePassword')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
    </div>

    <!-- New Password -->
    <div class="mb-3">
        <label for="password" class="form-label fw-semibold">
            <i class="fas fa-lock text-muted me-1"></i> Password Baru
        </label>
        <div class="input-group">
            <input 
                type="password" 
                name="password" 
                id="password" 
                class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                required
                autocomplete="new-password"
            >
            <button type="button" class="btn btn-outline-secondary" id="togglePassword" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
        @error('password', 'updatePassword')
            <div class="text-danger small mt-1">{{ $message }}</div>
        @enderror
        <small class="text-muted">Minimal 8 karakter</small>
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label fw-semibold">
            <i class="fas fa-lock text-muted me-1"></i> Konfirmasi Password Baru
        </label>
        <div class="input-group">
            <input 
                type="password" 
                name="password_confirmation" 
                id="password_confirmation" 
                class="form-control" 
                required
                autocomplete="new-password"
            >
            <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirmation" tabindex="-1">
                <i class="fas fa-eye"></i>
            </button>
        </div>
    </div>

    <div class="d-grid gap-2">
        <button type="submit" class="btn btn-warning text-white">
            <i class="fas fa-key me-1"></i> Ubah Password
        </button>
    </div>
</form>

@push('scripts')
<script>
    // Toggle password visibility
    function setupPasswordToggle(inputId, buttonId) {
        const button = document.getElementById(buttonId);
        if (!button) return;
        
        button.addEventListener('click', function() {
            const input = document.getElementById(inputId);
            const icon = this.querySelector('i');
            
            if (!input) return;
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    }

    setupPasswordToggle('current_password', 'toggleCurrentPassword');
    setupPasswordToggle('password', 'togglePassword');
    setupPasswordToggle('password_confirmation', 'togglePasswordConfirmation');

</script>
@endpush