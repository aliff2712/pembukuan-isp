
<?php

use App\Http\Controllers\ChartOfAccountController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\FileConvertController;
use App\Http\Controllers\Finance\FinanceSettingController;
use App\Http\Controllers\JournalEntryController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MikhmonImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\VoucherSaleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SinkronImportController;

// =====================================================================
// PUBLIC ROUTES
// =====================================================================
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return redirect()->route('login');
    });
});

// =====================================================================
// AUTH ROUTES (Breeze)
// =====================================================================
require __DIR__.'/auth.php';

// =====================================================================
// PROTECTED ROUTES
// =====================================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // -----------------------------------------------------------------
    // DASHBOARD
    // -----------------------------------------------------------------
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/data', [DashboardController::class, 'apiData'])->name('dashboard.api');

    // -----------------------------------------------------------------
    // CHART OF ACCOUNTS
    // -----------------------------------------------------------------
    Route::resource('chart-of-accounts', ChartOfAccountController::class);
    Route::get('/api/chart-of-accounts/by-type', [ChartOfAccountController::class, 'getByType'])
        ->name('chart-of-accounts.by-type');
    Route::get('/api/chart-of-accounts/cash', [ChartOfAccountController::class, 'getCashAccounts'])
        ->name('chart-of-accounts.cash');

    // -----------------------------------------------------------------
    // JOURNAL ENTRIES
    // -----------------------------------------------------------------
    Route::prefix('journal-entries')->name('journal-entries.')->group(function () {
        Route::get('/',        [JournalEntryController::class, 'index'])->name('index');
        Route::get('/daily',   [JournalEntryController::class, 'daily'])->name('daily');
        Route::get('/summary', [JournalEntryController::class, 'summaryByAccount'])->name('summary');
        Route::get('/export',  [JournalEntryController::class, 'export'])->name('export');
        Route::get('/{id}',    [JournalEntryController::class, 'show'])->name('show');
    });
    Route::get('/api/journal-entries', [JournalEntryController::class, 'api'])
        ->name('journal-entries.api');

    // -----------------------------------------------------------------
    // VOUCHER SALES (Mikhmon)
    // -----------------------------------------------------------------
    Route::prefix('voucher-sales')->name('voucher-sales.')->group(function () {
        Route::get('/',                          [VoucherSaleController::class, 'index'])->name('index');
        Route::get('/import',                    [MikhmonImportController::class, 'importForm'])->name('import');
        Route::post('/import',                   [MikhmonImportController::class, 'import'])->name('import.store');
        Route::get('/import/{id}/status',        [MikhmonImportController::class, 'status'])->name('import.status');
        Route::get('/reimport/form',             [VoucherSaleController::class, 'reimportForm'])->name('reimport-form');
        Route::post('/reimport',                 [VoucherSaleController::class, 'reimport'])->name('reimport');
        Route::get('/export',                    [VoucherSaleController::class, 'export'])->name('export');
        Route::get('/{id}',                      [VoucherSaleController::class, 'show'])->name('show');
        Route::delete('/{id}',                   [VoucherSaleController::class, 'void'])->name('void');
    });
    Route::get('/api/voucher-sales/chart', [VoucherSaleController::class, 'chartData'])
        ->name('voucher-sales.chart');

    // -----------------------------------------------------------------
    // EXPENSES
    // -----------------------------------------------------------------
    Route::resource('expenses', ExpenseController::class);
    Route::get('/expenses-export',  [ExpenseController::class, 'export'])->name('expenses.export');
    Route::get('/expenses-summary', [ExpenseController::class, 'summaryByAccount'])->name('expenses.summary');

    // -----------------------------------------------------------------
    // OTHER INCOME
    // -----------------------------------------------------------------
    Route::resource('other-incomes', \App\Http\Controllers\OtherIncomeController::class);

    // -----------------------------------------------------------------
    // PROFILE
    // -----------------------------------------------------------------
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
    Route::get('/profile',            [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',          [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile',         [ProfileController::class, 'destroy'])->name('profile.destroy');

    // -----------------------------------------------------------------
    // FINANCE - TRANSAKSI
    // -----------------------------------------------------------------
    Route::prefix('finance/transaksi')->name('finance.transaksi.')->group(function () {
        Route::get('/',                       [TransaksiController::class, 'index'])->name('index');
        Route::get('/import',                 [TransaksiController::class, 'importForm'])->name('import.form');
        Route::post('/import',                [TransaksiController::class, 'import'])->name('import');
        Route::get('/{transaksi}/payment',    [TransaksiController::class, 'paymentForm'])->name('payment.form');
        Route::patch('/{transaksi}/payment',  [TransaksiController::class, 'processPayment'])->name('payment.process');
        Route::delete('/{transaksi}',         [TransaksiController::class, 'destroy'])->name('destroy');
        Route::get('/{transaksi}/receipt',    [TransaksiController::class, 'receipt'])->name('receipt');
        Route::get('/{transaksi}',            [TransaksiController::class, 'show'])->name('show'); // paling bawah
    });

    // -----------------------------------------------------------------
    // FINANCE - LAPORAN
    // -----------------------------------------------------------------
    Route::prefix('finance/laporan')->name('finance.laporan.')->group(function () {
        Route::get('/',                       [LaporanController::class, 'index'])->name('index');
        Route::get('/bulanan',                [LaporanController::class, 'bulanan'])->name('bulanan');
        Route::get('/tahunan',                [LaporanController::class, 'tahunan'])->name('tahunan');
        Route::get('/export/excel/bulanan',   [LaporanController::class, 'exportExcelBulanan'])->name('export.excel.bulanan');
        Route::get('/export/excel/tahunan',   [LaporanController::class, 'exportExcelTahunan'])->name('export.excel.tahunan');
        Route::get('/export/pdf/bulanan',     [LaporanController::class, 'exportPdfBulanan'])->name('export.pdf.bulanan');
        Route::get('/export/pdf/tahunan',     [LaporanController::class, 'exportPdfTahunan'])->name('export.pdf.tahunan');
    });

    // -----------------------------------------------------------------
    // FINANCE - SETTING
    // -----------------------------------------------------------------
    Route::prefix('finance/setting')->name('finance.setting.')->group(function () {
        Route::get('/',  [FinanceSettingController::class, 'edit'])->name('edit');
        Route::post('/', [FinanceSettingController::class, 'update'])->name('update');
    });

    // -----------------------------------------------------------------
    // FILE CONVERTER
    // -----------------------------------------------------------------
    Route::get('/converter',  [FileConvertController::class, 'index'])->name('converter.index');
    Route::post('/converter', [FileConvertController::class, 'convert'])->name('converter.convert');

    // -----------------------------------------------------------------
   // -----------------------------------------------------------------
    // SINKRON BILLING - TRANSAKSI
    // -----------------------------------------------------------------
    Route::get('/pembukuan/sinkron',         [SinkronImportController::class, 'index'])->name('sinkron.index');
    Route::post('/pembukuan/sinkron/import', [SinkronImportController::class, 'import'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.import');
    Route::get('/pembukuan/sinkron/export',  [SinkronImportController::class, 'exportTransaksi'])
        ->middleware('throttle:60,1')
        ->name('sinkron.export');
    Route::delete('/pembukuan/sinkron/delete', [SinkronImportController::class, 'deleteTransaksi'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.deleteTransaksi');
    Route::delete('/pembukuan/sinkron/{id}', [SinkronImportController::class, 'deleteTransaksiById'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.deleteTransaksiById');
    
    // -----------------------------------------------------------------
    // SINKRON BILLING - PELANGGAN
    // -----------------------------------------------------------------
    Route::get('/pembukuan/pelanggan',            [SinkronImportController::class, 'pelanggan'])->name('sinkron.pelanggan');
    Route::post('/pembukuan/pelanggan/import',    [SinkronImportController::class, 'importPelanggan'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.pelanggan.import');
    Route::get('/pembukuan/pelanggan/export',     [SinkronImportController::class, 'exportPelanggan'])
        ->middleware('throttle:60,1')
        ->name('sinkron.pelanggan.export');
    Route::delete('/pembukuan/pelanggan/delete', [SinkronImportController::class, 'deletePelanggan'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.deletePelanggan');
    Route::delete('/pembukuan/pelanggan/{id}', [SinkronImportController::class, 'deletePelangganById'])
        ->middleware('throttle:sinkron')
        ->name('sinkron.deletePelangganById');
        
       // -----------------------------------------------------------------
    // SINKRON BILLING - BELUM BAYAR
    // -----------------------------------------------------------------
    Route::get('/pembukuan/belum-bayar', 
        [SinkronImportController::class, 'belumBayar']
    )->name('sinkron.belum-bayar');
    
    Route::post('/pembukuan/belum-bayar/import', 
        [SinkronImportController::class, 'importBelumBayar']
    )->middleware('throttle:sinkron')
     ->name('sinkron.belum-bayar.import');
     
    Route::delete('/pembukuan/belum-bayar/delete', 
        [SinkronImportController::class, 'deleteBelumBayar']
    )->middleware('throttle:sinkron')
     ->name('sinkron.deleteBelumBayar');
    Route::delete('/pembukuan/belum-bayar/{id}', 
        [SinkronImportController::class, 'deleteBelumBayarById']
    )->middleware('throttle:sinkron')
     ->name('sinkron.deleteBelumBayarById');

}); // end middleware auth + verified