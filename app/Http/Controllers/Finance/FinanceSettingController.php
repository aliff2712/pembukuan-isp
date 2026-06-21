<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinanceSetting;
use App\Models\Transaksi;
use Carbon\Carbon;

class FinanceSettingController extends Controller
{
    public function edit()
    {
        $setting = FinanceSetting::first();

        if (!$setting) {
            $setting = FinanceSetting::create([
                'default_due_day' => 10
            ]);
        }

        return view('finance.setting.edit', compact('setting'));
    }

    public function update(Request $request)
{
    $request->validate([
        'default_due_day' => 'required|integer|min:1|max:31'
    ]);

    $dueDay = (int) $request->default_due_day;

    // 1️⃣ Simpan setting
    $setting = FinanceSetting::first();

    if (!$setting) {
        $setting = new FinanceSetting();
    }

    $setting->default_due_day = $dueDay;
    $setting->save();

    // 2️⃣ Sinkron semua transaksi
    $transaksis = Transaksi::all();

    foreach ($transaksis as $trx) {

        $tanggal = \Carbon\Carbon::parse($trx->tanggal);

        // ambil jumlah hari di bulan itu
        $lastDayOfMonth = $tanggal->copy()->endOfMonth()->day;

        // kalau setting lebih besar dari jumlah hari bulan itu
        $safeDueDay = min($dueDay, $lastDayOfMonth);

        $jatuhTempo = $tanggal->copy()->day($safeDueDay);

        if ($tanggal->day > $safeDueDay) {
            $jatuhTempo->addMonth();
        }

        $trx->jatuh_tempo = $jatuhTempo;

        // sinkron status
        if ($jatuhTempo->startOfDay()->lt(now()->startOfDay())) {
            $trx->status = 'overdue';
        } else {
            $trx->status = 'unpaid';
        }

        $trx->save();
    }

    return redirect()
        ->route('finance.setting.edit')
        ->with('success', 'Setting & seluruh transaksi berhasil disinkronkan.');
}



public function store(Request $request)
{
    $validated = $request->validate([
        'tanggal' => 'required|date',
        'nama_customer' => 'required|string',
        'total' => 'required|numeric',
        'deskripsi' => 'nullable|array',
    ]);

    $setting = FinanceSetting::first();
    $dueDay = (int) ($setting?->default_due_day ?? 10);

    $tanggal = Carbon::parse($validated['tanggal']);

    // ambil jumlah hari maksimal bulan itu
    $lastDay = $tanggal->copy()->endOfMonth()->day;

    // pakai hari yang aman
    $safeDueDay = min($dueDay, $lastDay);

    $jatuhTempo = $tanggal->copy()->day($safeDueDay);

    if ($tanggal->day > $safeDueDay) {
        $jatuhTempo->addMonth();
    }

    Transaksi::create([
        'tanggal' => $validated['tanggal'],
        'nama_customer' => $validated['nama_customer'],
        'total' => $validated['total'],
        'deskripsi' => isset($validated['deskripsi']) 
            ? json_encode($validated['deskripsi']) 
            : null,
        'status' => 'unpaid',
        'jatuh_tempo' => $jatuhTempo->toDateString(),
    ]);

    return redirect()->back()->with('success', 'Transaksi berhasil disimpan.');
}

}
