<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\SinkronPelanggan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;

class CustomerSyncMonitorController extends Controller
{
    public function index(Request $request)
    {
        $summary = [
            'total_pelanggan' => SinkronPelanggan::count(),
            'active' => SinkronPelanggan::where('is_active', true)->count(),
            'inactive' => SinkronPelanggan::where('is_active', false)->count(),
            'latest_sync' => AuditLog::where('entity_type', 'sinkron_pelanggan')
                ->where('action', 'CREATE')
                ->latest('created_at')
                ->first(),
        ];

        $logs = AuditLog::where('entity_type', 'sinkron_pelanggan')
            ->latest('created_at')
            ->take(20)
            ->get();

        return view('pembukuan.sync-monitor', compact('summary', 'logs'));
    }

    public function runSync(Request $request)
    {
        $exitCode = Artisan::call('billing:sync-customers');
        $output = trim(Artisan::output());

        return redirect()->route('sinkron.monitor')
            ->with('sync_status', $output ?: 'Sinkronisasi selesai.')
            ->with('sync_success', $exitCode === 0);
    }
}
