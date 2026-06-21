<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\DailyVoucherSale;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Artisan;

class VoucherSaleController extends Controller
{
    /**
     * Display a listing of voucher sales
     */
    public function index(Request $request)
    {
        $query = DailyVoucherSale::query();

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // FIX: Filter month & year bisa dipakai sendiri-sendiri (tidak harus keduanya)
        if ($request->filled('month')) {
            $query->whereMonth('sale_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }

        // Search by source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // FIX: Tambahkan withQueryString() agar filter tetap terbawa saat ganti halaman
        $sales = $query->orderBy('sale_date', 'asc')->paginate(20)->withQueryString();

        // Summary statistics
        $stats = $this->getStatistics($request);

        return view('voucher-sales.index', compact('sales', 'stats'));
    }

    /**
     * Display the specified voucher sale
     */
    public function show($id)
    {
        $sale = DailyVoucherSale::findOrFail($id);

        // Get journal entry related to this sale (if any)
        $journalEntry = DB::table('journal_entries')
            ->where('source_type', 'mikhmon')
            ->where('source_id', $sale->id)
            ->first();

        return view('voucher-sales.show', compact('sale', 'journalEntry'));
    }

    /**
     * Re-import voucher sales (trigger command)
     */
    public function reimport(Request $request)
    {
        try {
            $validated = $request->validate([
                'date_from' => 'nullable|date',
                'date_to' => 'nullable|date|after_or_equal:date_from',
                'force' => 'boolean',
            ]);

            // Build command parameters
            $params = [];

            if ($request->filled('date_from')) {
                $params['--date-from'] = $request->date_from;
            }

            if ($request->filled('date_to')) {
                $params['--date-to'] = $request->date_to;
            }

            if ($request->boolean('force')) {
                $params['--force'] = true;
            }

            // Execute artisan command
            $exitCode = Artisan::call('mikhmon:import', $params);
            $output = Artisan::output();

            if ($exitCode === 0) {
                return redirect()
                    ->route('voucher-sales.index')
                    ->with('success', 'Re-import berhasil dilakukan. ' . $output);
            } else {
                return redirect()
                    ->back()
                    ->withErrors(['import' => 'Re-import gagal. ' . $output]);
            }

        } catch (\Exception $e) {
            Log::error('Voucher reimport failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['import' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Show reimport form
     */
    public function reimportForm()
    {
        return view('voucher-sales.reimport');
    }

    /**
     * Void a voucher sale
     */
    public function void($id)
    {
        try {
            $sale = DailyVoucherSale::findOrFail($id);

            // Check if already voided
            if (isset($sale->voided_at)) {
                return redirect()
                    ->back()
                    ->withErrors(['void' => 'Voucher sale sudah di-void sebelumnya.']);
            }

            $sale->delete();

            return redirect()
                ->route('voucher-sales.index')
                ->with('success', 'Voucher sale tanggal ' . $sale->sale_date . ' berhasil di-void.');

        } catch (\Exception $e) {
            Log::error('Voucher void failed: ' . $e->getMessage());

            return redirect()
                ->back()
                ->withErrors(['void' => 'Gagal void voucher: ' . $e->getMessage()]);
        }
    }

    /**
     * Get sales statistics
     */
    private function getStatistics(Request $request)
    {
        $query = DailyVoucherSale::query();

        // Apply same filters
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        // FIX: Konsisten dengan filter di index()
        if ($request->filled('month')) {
            $query->whereMonth('sale_date', $request->month);
        }

        if ($request->filled('year')) {
            $query->whereYear('sale_date', $request->year);
        }

        return [
            'total_days'         => $query->count(),
            'total_transactions' => $query->sum('total_transactions'),
            'total_amount'       => $query->sum('total_amount'),
            'average_per_day'    => $query->avg('total_amount'),
            'this_month_total'   => DailyVoucherSale::whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('total_amount'),
            'last_import'        => DailyVoucherSale::max('updated_at'),
        ];
    }

    /**
     * Export voucher sales to CSV
     */
    public function export(Request $request)
    {
        $query = DailyVoucherSale::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date')->get();

        // Prepare CSV
        $csvData   = [];
        $csvData[] = ['Date', 'Total Transactions', 'Total Amount', 'Source', 'Import Batch', 'Created At'];

        foreach ($sales as $sale) {
            $csvData[] = [
                $sale->sale_date,
                $sale->total_transactions,
                $sale->total_amount,
                $sale->source,
                $sale->import_batch_id,
                $sale->created_at,
            ];
        }

        // Generate CSV
        $filename = 'voucher_sales_' . now()->format('Y-m-d_His') . '.csv';

        $handle = fopen('php://temp', 'r+');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * API endpoint for chart data
     */
    public function chartData(Request $request)
    {
        $months = $request->get('months', 6);

        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $monthData = DailyVoucherSale::whereMonth('sale_date', $date->month)
                ->whereYear('sale_date', $date->year)
                ->selectRaw('
                    SUM(total_transactions) as transactions,
                    SUM(total_amount) as amount
                ')
                ->first();

            $data[] = [
                'month'        => $date->format('M Y'),
                'transactions' => $monthData->transactions ?? 0,
                'amount'       => $monthData->amount ?? 0,
            ];
        }

        return response()->json($data);
    }
}