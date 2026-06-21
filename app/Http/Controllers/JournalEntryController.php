<?php

namespace App\Http\Controllers;

use App\Enums\JournalSourceType;
use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class JournalEntryController extends Controller
{
    /**
     * Display a listing of journal entries
     */
    public function index(Request $request)
    {
        $query = JournalEntry::query();

        $this->applyFilters($query, $request);

        // ─── Eager load: lines.coa hanya untuk halaman aktif (via paginate) ─
        // withCount('lines') agar view bisa pakai $entry->lines_count tanpa load semua lines
        $entries = $query
            ->withCount('lines')
            ->orderBy('journal_date', 'desc')
            ->orderBy('id', 'desc')
            ->paginate(20)
            ->appends($request->query());

        // Lazy eager load 'lines.coa' hanya untuk halaman yang sedang ditampilkan
        // (paginate sudah membatasi set, jadi ini aman)
        $entries->load('lines.coa');

        // ─── Stats: 1 query aggregate, bukan 4 query terpisah ───────────────
        $stats = $this->getStats();

        // Source types untuk filter dropdown — ambil dari enum (tidak dari DB bebas)
        $sourceTypes = JournalSourceType::values();

        return view('journal-entries.index', compact('entries', 'stats', 'sourceTypes'));
    }

    /**
     * Display the specified journal entry with details
     */
    public function show($id)
    {
        $entry = JournalEntry::with(['lines' => fn($q) => $q->orderBy('id')])
            ->findOrFail($id);

        $totalDebit  = $entry->lines->sum('debit');
        $totalCredit = $entry->lines->sum('credit');
        $isBalanced  = abs($totalDebit - $totalCredit) < 0.01;

        return view('journal-entries.show', compact('entry', 'totalDebit', 'totalCredit', 'isBalanced'));
    }

    /**
     * Show journal entries by date (daily ledger)
     */
    public function daily(Request $request)
    {
        $date = $request->get('date', now()->toDateString());

        // Eager load 'lines' sekaligus (sudah baik, tidak ada masalah di sini)
        $entries = JournalEntry::with('lines')
            ->whereDate('journal_date', $date)
            ->orderBy('id')
            ->get();

        // Hitung summary dari collection in-memory (tidak perlu query tambahan)
        $summary = [
            'date'          => $date,
            'total_entries' => $entries->count(),
            'total_debit'   => $entries->sum('total_debit'),
            'total_credit'  => $entries->sum('total_credit'),
        ];

        return view('journal-entries.daily', compact('entries', 'summary', 'date'));
    }

    /**
     * Export journal entries to CSV
     */
    public function export(Request $request)
    {
        $query = JournalEntry::query();

        $this->applyFilters($query, $request);

        // Chunk untuk menghindari memory peak pada dataset besar
        $csvData   = [];
        $csvData[] = ['Date', 'Description', 'Source', 'Reference', 'Account Code', 'Account Name', 'Debit', 'Credit'];

        $query->with('lines')->orderBy('journal_date')->chunk(200, function ($entries) use (&$csvData) {
            foreach ($entries as $entry) {
                foreach ($entry->lines as $line) {
                    $csvData[] = [
                        $entry->journal_date,
                        $entry->description,
                        $entry->source_type,
                        $entry->reference_no,
                        $line->account_code,
                        $line->account_name,
                        $line->debit,
                        $line->credit,
                    ];
                }
            }
        });

        $filename = 'journal_entries_' . now()->format('Y-m-d_His') . '.csv';

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
     * Get journal summary by account (for reports)
     */
    public function summaryByAccount(Request $request)
    {
        $dateFrom = $request->get('date_from', now()->startOfMonth()->toDateString());
        $dateTo   = $request->get('date_to',   now()->endOfMonth()->toDateString());

        $summary = JournalLine::join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->whereBetween('journal_entries.journal_date', [$dateFrom, $dateTo])
            ->selectRaw('
                journal_lines.account_code,
                journal_lines.account_name,
                SUM(journal_lines.debit)                              as total_debit,
                SUM(journal_lines.credit)                             as total_credit,
                (SUM(journal_lines.debit) - SUM(journal_lines.credit)) as balance
            ')
            ->groupBy('journal_lines.account_code', 'journal_lines.account_name')
            ->orderBy('journal_lines.account_code')
            ->get();

        return view('journal-entries.summary', compact('summary', 'dateFrom', 'dateTo'));
    }

    /**
     * API endpoint for getting journal entries (AJAX)
     */
    public function api(Request $request)
    {
        $query = JournalEntry::query();

        if ($request->filled('source_type')) {
            // Whitelist: hanya allow nilai yang terdaftar di enum
            if (in_array($request->source_type, JournalSourceType::values(), true)) {
                $query->where('source_type', $request->source_type);
            }
        }

        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        $entries = $query->with('lines')
            ->orderBy('journal_date', 'desc')
            ->limit(50)
            ->get();

        return response()->json($entries);
    }

    // =========================================================
    // HELPER: Centralized filter (DRY — dipakai index & export)
    // =========================================================

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->where('journal_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('journal_date', '<=', $request->date_to);
        }

        if ($request->filled('source_type')) {
            // Whitelist: hanya allow nilai yang terdaftar di enum
            if (in_array($request->source_type, JournalSourceType::values(), true)) {
                $query->where('source_type', $request->source_type);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description',  'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->whereMonth('journal_date', $request->month)
                  ->whereYear('journal_date', $request->year);
        }
    }

    // =========================================================
    // HELPER: Stats summary
    // Sebelumnya: 4 query terpisah
    // Sekarang:   1 query aggregate (CASE WHEN)
    // =========================================================

    private function getStats(): array
    {
        $thisMonth = now()->month;
        $thisYear  = now()->year;

        $row = JournalEntry::selectRaw("
            COUNT(*)                                                                       as total_entries,
            SUM(CASE WHEN MONTH(journal_date) = ? AND YEAR(journal_date) = ? THEN 1 ELSE 0 END) as this_month,
            COALESCE(SUM(total_debit),  0)                                                 as total_debit,
            COALESCE(SUM(total_credit), 0)                                                 as total_credit
        ", [$thisMonth, $thisYear])->first();

        return [
            'total_entries' => (int)   ($row->total_entries ?? 0),
            'this_month'    => (int)   ($row->this_month    ?? 0),
            'total_debit'   => (float) ($row->total_debit   ?? 0),
            'total_credit'  => (float) ($row->total_credit  ?? 0),
        ];
    }
}