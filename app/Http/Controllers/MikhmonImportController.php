<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ImportCsvJob;
use App\Jobs\TransformDataJob;
use App\Jobs\AggregateDailyJob;
use App\Jobs\JournalizeJob;
use App\Jobs\CleanupJob;
use App\Jobs\FinalizeImportJob;
use App\Models\MikhmonImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;

class MikhmonImportController extends Controller
{
    public function importForm()
    {
        return view('voucher-sales.import');
    }

    public function import(Request $request)
    {
        // 1. Validasi file
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        // 2. Simpan file
        $path     = $request->file('csv_file')->store('mikhmon_temp');
        $fullPath = storage_path("app/{$path}");

        // 3. Buat import log
        $log = MikhmonImportLog::create([
            'user_id' => Auth::id(),
            'status'  => 'processing',
            'log'     => '⏳ Import dimulai...',
        ]);

        // 4. Generate batch ID — satu UUID untuk seluruh pipeline
        $batchId = (string) Str::uuid();

        // 5. Dispatch job chain
        //
        // ✅ FIX: Tidak lagi menggunakan ProcessMikhmonImportJob sebagai wrapper.
        // ProcessMikhmonImportJob adalah job lama yang dispatch ulang ImportCsvJob
        // dengan argumen yang SALAH ($userId dikirim sebagai $batchId), sehingga
        // batchId tidak cocok dan seluruh pipeline skip ke step berikutnya tanpa
        // memproses data.
        //
        // Solusi: Dispatch langsung via Bus::chain dengan argumen yang benar.
        Bus::chain([
            new ImportCsvJob($fullPath, $batchId, $log->id),
            new TransformDataJob($batchId, $log->id),
            new AggregateDailyJob($batchId, $log->id),
            new JournalizeJob($batchId, $log->id),
            new CleanupJob($batchId, $log->id),
            new FinalizeImportJob($batchId, $log->id),
        ])
        ->catch(function (\Throwable $e) use ($log) {
            $log->update([
                'status' => 'failed',
                'log'    => "❌ Import error: {$e->getMessage()}",
            ]);
        })
        ->dispatch();

        // 6. Redirect ke halaman status
        return redirect()
            ->route('voucher-sales.import.status', $log->id)
            ->with('info', 'Import sedang diproses di background...');
    }

    public function status(string $id)
    {
        $log = MikhmonImportLog::findOrFail($id);

        return view('voucher-sales.import-status', compact('log'));
    }
}