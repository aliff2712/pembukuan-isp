<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring Sync Pelanggan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Monitoring Sync Pelanggan</h2>
        <form action="{{ route('sinkron.monitor.sync') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">Jalankan Sinkronisasi</button>
        </form>
    </div>

    @if(session('sync_status'))
        <div class="alert alert-{{ session('sync_success') ? 'success' : 'danger' }} mb-4">
            {{ session('sync_status') }}
        </div>
    @endif

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <h5 class="card-title">Total Pelanggan</h5>
                    <p class="display-6">{{ $summary['total_pelanggan'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <h5 class="card-title">Aktif</h5>
                    <p class="display-6">{{ $summary['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <h5 class="card-title">Nonaktif</h5>
                    <p class="display-6">{{ $summary['inactive'] }}</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <h5 class="card-title">Sync Terakhir</h5>
                    <p class="small">{{ optional($summary['latest_sync'])->created_at ? optional($summary['latest_sync'])->created_at->format('d M Y H:i') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">Log Audit Terbaru</div>
        <div class="card-body">
            <table class="table table-sm table-striped">
                <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>Detail</th>
                </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    <tr>
                        <td>{{ $log->created_at->format('d M Y H:i') }}</td>
                        <td>{{ $log->action }}</td>
                        <td>{{ $log->description }}</td>
                        <td><small>{{ $log->metadata['status'] ?? '-' }}</small></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-muted">Belum ada log audit.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
