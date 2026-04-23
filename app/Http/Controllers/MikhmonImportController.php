<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessMikhmonImportJob;
use App\Models\MikhmonImportLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MikhmonImportController extends Controller
{
    public function importForm()
    {
        return view('voucher-sales.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:10240'],
        ]);

        $path = $request->file('csv_file')->store('mikhmon_temp');

        $log = MikhmonImportLog::create([
            'user_id' => Auth::id(),
            'status'  => 'processing',
            'log'     => null,
        ]);

        ProcessMikhmonImportJob::dispatch(
            storage_path("app/{$path}"),
            Auth::id(),
            $log->id,
        );

        return redirect()->route('voucher-sales.import.status', $log->id)
            ->with('info', 'Pipeline sedang diproses di background...');
    }

    public function status(string $id)
    {
        $log = MikhmonImportLog::findOrFail($id);

        return view('voucher-sales.import-status', compact('log'));
    }
}