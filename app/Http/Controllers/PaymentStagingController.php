<?php

namespace App\Http\Controllers;

use App\Models\SinkronTransaksi;
use App\Services\PaymentApprovalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentStagingController extends Controller
{
    // =========================================================
    // INDEX
    // =========================================================

    public function index(Request $request)
    {
        $statusFilter = $request->get('status', 'flagged'); // Default ke flagged karena yang pending auto-approved

        $totalPending   = SinkronTransaksi::pending()->count();
        $totalApproved  = SinkronTransaksi::approved()->count();
        $totalFlagged   = SinkronTransaksi::flagged()->count();
        $totalRejected  = SinkronTransaksi::rejected()->count();

        $query = SinkronTransaksi::where('status_approval', $statusFilter)
            ->orderBy('created_at', 'desc');

        if ($request->filled('bulan')) {
            $query->where('bulan_tagihan', 'like', $request->bulan . '%');
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_pelanggan', 'like', '%' . $request->search . '%')
                  ->orWhere('kode_transaksi', 'like', '%' . $request->search . '%');
            });
        }

        $stagedData = $query->paginate(20)->withQueryString();
        
        // Buat dummy totalDuplicate 0 agar view tidak error jika masih dipanggil
        $totalDuplicate = 0;

        return view('pembukuan.staging.index', compact(
            'totalPending', 'totalApproved', 'totalFlagged',
            'totalDuplicate', 'totalRejected',
            'stagedData', 'statusFilter'
        ));
    }

    // =========================================================
    // SHOW / EDIT / UPDATE
    // =========================================================

    public function show(SinkronTransaksi $paymentStaging)
    {
        return view('pembukuan.staging.show', compact('paymentStaging'));
    }

    public function edit(SinkronTransaksi $paymentStaging)
    {
        if (!$paymentStaging->isActionable()) {
            return back()->with('error', 'Data tidak bisa diedit.');
        }

        return view('pembukuan.staging.edit', compact('paymentStaging'));
    }

    public function update(Request $request, SinkronTransaksi $paymentStaging)
    {
        if (!$paymentStaging->isActionable()) {
            return back()->with('error', 'Data tidak bisa diedit.');
        }

        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:150',
            'jumlah'         => 'required|numeric|min:1|max:500000000',
            'tanggal_bayar'  => 'required|date|before_or_equal:today',
        ]);

        $paymentStaging->update($validated);

        return back()->with('success', 'Data diperbarui.');
    }

    // =========================================================
    // APPROVE
    // =========================================================

    public function approve(SinkronTransaksi $paymentStaging, PaymentApprovalService $service)
    {
        // Guard double approve di controller — sebelum masuk service
        if ($paymentStaging->isFinal()) {
            return back()->with('warning', 'Data sudah final, tidak bisa diubah.');
        }

        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah terkunci.');
        }

        try {
            $service->approve($paymentStaging, Auth::id());
            return back()->with('success', 'Data berhasil di-approve dan dijurnalkan.');
        } catch (\Throwable $e) {
            Log::error('Approve gagal', [
                'id'    => $paymentStaging->id,
                'error' => $e->getMessage(),
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // BULK APPROVE
    // =========================================================

    public function bulkApprove(Request $request, PaymentApprovalService $service)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:sinkron_transaksi,id',
        ]);

        $result = $service->bulkApprove($request->ids, Auth::id());

        $message = "Approve selesai: {$result['success']} berhasil";
        if ($result['failed'] > 0)  $message .= " | {$result['failed']} gagal";
        if ($result['skipped'] > 0) $message .= " | {$result['skipped']} dilewati";

        $type = $result['failed'] > 0 ? 'warning' : 'success';

        return back()->with($type, $message);
    }

    // =========================================================
    // REJECT
    // =========================================================

    public function reject(SinkronTransaksi $paymentStaging)
    {
        if ($paymentStaging->isFinal()) {
            return back()->with('warning', 'Data sudah final, tidak bisa diubah.');
        }

        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah terkunci.');
        }

        $paymentStaging->reject(Auth::id());

        return back()->with('success', 'Data berhasil di-reject.');
    }

    // =========================================================
    // BULK REJECT
    // =========================================================

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:sinkron_transaksi,id',
        ]);

        $rows = SinkronTransaksi::whereIn('id', $request->ids)
            ->whereNotIn('status_approval', ['approved', 'rejected'])
            ->where('is_locked', false)
            ->get();

        $skipped = count($request->ids) - $rows->count();
        $success = 0;

        foreach ($rows as $staging) {
            $staging->reject(Auth::id());
            $success++;
        }

        $message = "Reject selesai: {$success} berhasil";
        if ($skipped > 0) $message .= " | {$skipped} dilewati";

        return back()->with('success', $message);
    }
}