<div class="row align-items-center">
    <div class="col-md-8">
        <h6 class="mb-2">Hapus Akun</h6>
        <p class="text-muted mb-0 small">
            Setelah akun Anda dihapus, semua data akan dihapus secara permanen. 
            Tindakan ini tidak dapat dibatalkan.
        </p>
    </div>
    <div class="col-md-4 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
            <i class="fas fa-trash me-1"></i> Hapus Akun
        </button>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus Akun
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Peringatan!</strong> Tindakan ini tidak dapat dibatalkan.
                    </div>
                    
                    <p>Apakah Anda yakin ingin menghapus akun Anda? Semua data akan hilang secara permanen.</p>
                    
                    <div class="mb-3">
                        <label for="delete_password" class="form-label fw-semibold">
                            Masukkan password Anda untuk konfirmasi:
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="delete_password" 
                            class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                            placeholder="Password Anda"
                            required
                        >
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-1"></i> Ya, Hapus Akun Saya
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Show delete modal if there's validation error
    @error('password', 'userDeletion')
        document.addEventListener('DOMContentLoaded', function() {
            const deleteModal = new bootstrap.Modal(document.getElementById('deleteAccountModal'));
            deleteModal.show();
        });
    @enderror
</script>
@endpush