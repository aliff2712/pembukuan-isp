<?php

namespace App\Http\Controllers;

use App\Models\PaymentStaging;
use App\Models\SinkronTransaksi;
use App\Services\FonnteNotificationService;
use App\Services\SinkronJournalizeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentStagingController extends Controller
{
  // ===================== INDEX =====================
    public function index(Request $request)
    {
        $totalPending  = PaymentStaging::pending()->count();
        $totalApproved = PaymentStaging::approved()->count();
        $totalFlagged  = PaymentStaging::flagged()->count();
        $totalRejected = PaymentStaging::rejected()->count();

        $statusFilter = $request->get('status', 'flagged');
        $allowedStatus = ['pending', 'approved', 'flagged', 'rejected'];
        if (!in_array($statusFilter, $allowedStatus)) $statusFilter = 'flagged';

        $query = PaymentStaging::where('status', $statusFilter)
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

        return view('pembukuan.staging.index', compact(
            'totalPending', 'totalApproved', 'totalFlagged', 'totalRejected',
            'stagedData', 'statusFilter'
        ));
    }
    // ===================== SHOW DETAIL =====================

    public function show(PaymentStaging $paymentStaging)
    {
        return view('pembukuan.staging.show', compact('paymentStaging'));
    }

    // ===================== EDIT FORM =====================

    public function edit(PaymentStaging $paymentStaging)
    {
        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah di-lock. Tidak dapat diedit.');
        }

        if ($paymentStaging->status !== 'flagged') {
            return back()->with('error', 'Hanya data flagged yang bisa diedit.');
        }

        return view('pembukuan.staging.edit', compact('paymentStaging'));
    }

    // ===================== UPDATE DATA =====================

    public function update(Request $request, PaymentStaging $paymentStaging)
    {
        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah di-lock. Tidak dapat diedit.');
        }

        $validated = $request->validate([
            'kode_transaksi' => 'nullable|string|max:50',
            'nama_pelanggan' => 'required|string|max:150',
            'jumlah'         => 'required|numeric|min:1|max:500000',
            'tanggal_bayar'  => 'required|date|before_or_equal:today',
            'area'           => 'nullable|string|max:100',
            'paket'          => 'nullable|string|max:100',
            'metode'         => 'nullable|string|max:50',
            'dibayar_oleh'   => 'nullable|string|max:100',
            'bulan_tagihan'  => 'nullable|string',
        ]);

        $paymentStaging->update($validated);

        Log::info('PaymentStaging: Data diedit', [
            'user'       => Auth::user()->name,
            'staging_id' => $paymentStaging->id,
            'source_ref' => $paymentStaging->source_ref,
        ]);

        return back()->with('success', 'Data berhasil diperbarui. Silakan approve untuk melanjutkan.');
    }

    // ===================== APPROVE + AUTO JURNAL =====================

    public function approve(Request $request, PaymentStaging $paymentStaging)
    {
        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah di-lock. Tidak dapat di-approve.');
        }

        if (!in_array($paymentStaging->status, ['flagged', 'pending'])) {
            return back()->with('error', 'Hanya data flagged/pending yang bisa di-approve.');
        }

        $paymentStaging->approve(Auth::id());

        // Auto-jurnal langsung setelah approve
        $result = $this->journalizeOne($paymentStaging);

        Log::info('PaymentStaging: Data di-approve', [
            'user'       => Auth::user()->name,
            'staging_id' => $paymentStaging->id,
            'source_ref' => $paymentStaging->source_ref,
            'journalized' => $result,
        ]);

        $message = $result
            ? 'Data berhasil di-approve dan dijurnal otomatis.'
            : 'Data berhasil di-approve, namun gagal dijurnal. Silakan coba jurnal manual.';

        return back()->with($result ? 'success' : 'warning', $message);
    }

    // ===================== REJECT =====================

    public function reject(Request $request, PaymentStaging $paymentStaging)
    {
        if ($paymentStaging->is_locked) {
            return back()->with('error', 'Data sudah di-lock. Tidak dapat di-reject.');
        }

        $paymentStaging->reject(Auth::id());

        Log::info('PaymentStaging: Data di-reject', [
            'user'       => Auth::user()->name,
            'staging_id' => $paymentStaging->id,
            'source_ref' => $paymentStaging->source_ref,
        ]);

        return back()->with('success', 'Data berhasil di-reject dan tidak akan dijurnal.');
    }

    // ===================== BULK APPROVE + AUTO JURNAL =====================

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:payment_staging,id',
        ]);

        $approved   = 0;
        $journalized = 0;

        foreach ($request->ids as $id) {
            $staging = PaymentStaging::find($id);

            if ($staging && !$staging->is_locked && $staging->status === 'flagged') {
                $staging->approve(Auth::id());
                $approved++;

                if ($this->journalizeOne($staging)) {
                    $journalized++;
                }
            }
        }

        Log::info('PaymentStaging: Bulk approve', [
            'user'        => Auth::user()->name,
            'approved'    => $approved,
            'journalized' => $journalized,
        ]);

        return back()->with('success', "Approve: {$approved} data | Dijurnal: {$journalized} data.");
    }

    // ===================== BULK REJECT =====================

    public function bulkReject(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:payment_staging,id',
        ]);

        $count = 0;
        foreach ($request->ids as $id) {
            $staging = PaymentStaging::find($id);

            if ($staging && !$staging->is_locked && $staging->status === 'flagged') {
                $staging->reject(Auth::id());
                $count++;
            }
        }

        Log::info('PaymentStaging: Bulk reject', [
            'user'  => Auth::user()->name,
            'count' => $count,
        ]);

        return back()->with('success', "Berhasil reject {$count} data.");
    }

    // ===================== JURNAL MANUAL (fallback) =====================

    /**
     * Jurnal manual untuk approved yang belum terjurnal.
     * Digunakan sebagai fallback jika auto-jurnal gagal.
     */
    public function journalizeApproved(Request $request)
    {
        $request->validate([
            'bulan' => 'nullable|string',
        ]);

        $query = PaymentStaging::approved()
            ->where('is_journalized', false)
            ->where('is_locked', false);

        if ($request->filled('bulan')) {
            $query->where('bulan_tagihan', 'like', $request->bulan . '%');
        }

        $stagedPayments = $query->get();

        if ($stagedPayments->isEmpty()) {
            return back()->with('info', 'Tidak ada data approved yang perlu dijurnal.');
        }

        $journalCount = 0;
        foreach ($stagedPayments as $staged) {
            if ($this->journalizeOne($staged)) {
                $journalCount++;
            }
        }

        Log::info('PaymentStaging: Jurnal manual', [
            'user'        => Auth::user()->name,
            'total'       => $stagedPayments->count(),
            'journalized' => $journalCount,
        ]);

        return back()->with('success', "Jurnal dibuat: {$journalCount} dari {$stagedPayments->count()} data.");
    }

    // ===================== PRIVATE: Journalize single record =====================

    /**
     * Proses jurnal untuk satu record staging.
     * Return true jika berhasil, false jika gagal.
     */
    private function journalizeOne(PaymentStaging $staged): bool
    {
        // Jangan jurnal ulang yang sudah dijurnal
        if ($staged->is_journalized) {
            return true;
        }

        try {
            $journalizer = new SinkronJournalizeService();

            // Upsert ke SinkronTransaksi dari data staging
            $sinkronTransaksi = SinkronTransaksi::updateOrCreate(
                ['id_transaksi_billing' => $staged->source_ref],
                [
                    'kode_transaksi' => $staged->kode_transaksi,
                    'nama_pelanggan' => $staged->nama_pelanggan,
                    'jumlah'         => $staged->jumlah,
                    'tanggal_bayar'  => $staged->tanggal_bayar,
                    'area'           => $staged->area,
                    'paket'          => $staged->paket,
                    'metode'         => $staged->metode,
                    'dibayar_oleh'   => $staged->dibayar_oleh,
                    'bulan_tagihan'  => $staged->bulan_tagihan,
                    'status'         => 'lunas',
                ]
            );

            if ($journalizer->journalize($sinkronTransaksi)) {
                $staged->markAsJournalized();

                // Lock jika sudah melewati 10 menit
                if ($sinkronTransaksi->shouldBeLocked()) {
                    $sinkronTransaksi->lock();
                }

                return true;
            }

            return false;

        } catch (\Exception $e) {
            Log::error('PaymentStaging: Gagal jurnal', [
                'staging_id' => $staged->id,
                'source_ref' => $staged->source_ref,
                'error'      => $e->getMessage(),
            ]);
            return false;
        }
    }
}