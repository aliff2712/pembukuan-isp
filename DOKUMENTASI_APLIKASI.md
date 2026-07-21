# Dokumentasi Aplikasi — Laravel Pembukuan

> Dokumentasi cara kerja aplikasi bookkeeping/akuntansi untuk usaha ISP & hotspot (RT/RW-Net).
> Catatan: fitur **audit pelanggan** (customer-audit) sengaja dikesampingkan dari dokumen ini karena masih fitur baru/dalam pengembangan.

---

## 1. Gambaran Umum

`laravel_pembukuan` adalah aplikasi pembukuan berbasis **Laravel 11** (PHP 8.2) yang mencatat keuangan sebuah usaha internet (ISP + hotspot voucher). Aplikasi ini:

1. **Menarik data** dari beberapa sumber:
   - **Billing API** eksternal (transaksi member yang sudah lunas + master pelanggan).
   - **Mikhmon** (ekspor CSV "Selling Report" penjualan voucher hotspot MikroTik).
   - **Beat** (pipeline tagihan langganan ISP dari file impor).
   - Input manual (pengeluaran / other income / transaksi lokal).
2. **Melakukan staging & review** data pembayaran (deteksi anomali, approval manual).
3. **Menjurnal** semua kejadian keuangan ke dalam sistem **double-entry** (`journal_entries` + `journal_lines`) terhadap Chart of Accounts.
4. **Menghasilkan laporan** (buku besar, arus kas, laporan bulanan/tahunan, ekspor Excel/PDF).
5. **Mengirim notifikasi** ke admin lewat **Telegram**.

Prinsip inti: **jurnal adalah satu-satunya sumber kebenaran (single source of truth)**. Semua laporan hanyalah agregasi dari jurnal — tidak ada saldo yang disimpan permanen, saldo dihitung on-the-fly dengan window function SQL.

---

## 2. Teknologi & Dependensi

| Komponen | Detail |
|---|---|
| Framework | Laravel `^11.0`, PHP `^8.2` |
| Auth | Laravel Breeze (session-based) |
| Database | MySQL (satu koneksi; **tidak ada** koneksi kedua ke DB billing) |
| Queue | `database` (tabel `jobs`) |
| Excel | `maatwebsite/excel` + `phpoffice/phpspreadsheet` |
| PDF | `barryvdh/laravel-dompdf` |
| Debug | `barryvdh/laravel-debugbar` |
| Integrasi luar | Billing API (HTTP), Mikhmon (CSV), Telegram Bot API |

Integrasi billing **bukan** koneksi database langsung, melainkan HTTP API yang dikonfigurasi di `config/services.php` (`billing.url`, `billing.key`, `billing.verify_ssl`).

---

## 3. Autentikasi & Otorisasi

- **Session-based** via Laravel Breeze (guard `web`).
- **Registrasi mandiri dinonaktifkan** (route register di-comment di `routes/auth.php`).
- Semua route aplikasi dibungkus middleware stack: `['auth', 'verified', 'admin']`
  - `auth` — harus login
  - `verified` — email harus terverifikasi
  - `admin` — alias custom → `App\Http\Middleware\EnsureUserIsAdmin`, memanggil `user()->isAdmin()`, abort **403** jika bukan admin.
- Hanya ada satu level peran: **admin vs non-admin** (field `role` pada model `User`, default `admin`).
- **Throttling**: named limiter `throttle:sinkron` dan `throttle:60,1` pada route sync import/export/delete; route auth pakai `throttle:6,1`; verifikasi email pakai `signed`. Beberapa controller juga menambahkan `RateLimiter` per-user di dalam kode.
- Aplikasi bergaya Laravel 11 (`bootstrap/app.php`, tanpa `Http/Kernel.php`).

Login default (dari seeder): `admin@example.com` / `password`.

---

## 4. Struktur Menu (Navigasi)

Sidebar utama (`resources/views/layouts-main/`):

- **Dashboard**
- **Penjualan Voucher** (voucher-sales) — penjualan voucher Mikhmon
- **Pengeluaran** (expenses)
- **Pendapatan Lain** (other-incomes)
- **Transaksi CSS** (sinkron.index) — transaksi billing tersinkron
- **Review Import** (pembukuan.staging) — staging pembayaran, menampilkan badge jumlah flagged
- **Daftar Pelanggan** (sinkron.pelanggan)
- **Chart of Accounts**
- **Journal Entries**
- **Laporan** (finance.laporan) — laporan keuangan

---

## 5. Skema Database (Inti)

### Akuntansi / Ledger (jantung aplikasi)

**`chart_of_accounts`** — master akun (COA).
- `account_code` (unik), `account_name`, `account_type` (asset/revenue/expense/liability/equity), `is_cash` (bool, default true).

**`journal_entries`** — header jurnal (skema aktif).
- `journal_date`, `description`, `source_type`, `source_id`, `reference_no`, `total_debit`, `total_credit`.
- **Unik `(source_type, source_id)`** → kunci idempotensi untuk semua proses penjurnalan.

**`journal_lines`** — baris debit/kredit.
- `journal_entry_id` (cascade), `coa_id` → `chart_of_accounts`, `debit`, `credit` (default 0).

**`journals`** *(legacy)* — header jurnal versi lama; hanya dibaca `LedgerService::daily()`.
**`coas`** *(legacy)* — tabel akun lama, tergantikan `chart_of_accounts`.

Pendukung: `journal_attachments`, `expenses` (`expense_coa_id`, `cash_coa_id`, `amount`), `other_incomes` (self-journalizing, `posted_journal_id`, status recorded/posted), `finance_settings` (`default_due_day`, default 10), `transaksis` (invoice lokal, `kode_transaksi` unik, status default paid).

### Pipeline Mikhmon (voucher)
- **`raw_mikhmon_imports`** — baris CSV mentah; `content_hash` (unik) untuk dedup.
- **`mikhmon_sales_staging`** — data bersih (1:1 ke raw, cascade).
- **`daily_voucher_sales`** — agregat per hari (`sale_date` unik, `total_transactions`, `total_amount`).
- **`mikhmon_import_logs`** — log run impor (status processing/done/failed).

### Pipeline Beat (langganan ISP)
`raw_beat_imports` → `beat_subscription_stagings` → `beat_invoices` (unik `pppoe+period`) → `beat_invoice_items` → `payments`.

### Sinkronisasi Billing
- **`sinkron_transaksi`** — transaksi billing lunas. Selain field transaksi, punya state jurnal (`is_journalized`, `journalized_at`), state workflow (`is_locked`, `status_approval` [pending/approved/flagged/rejected], `flag_reason`, `raw_data`, `reviewed_by/at`, `approved_by/at`). `id_transaksi_billing` unik (dedup).
- **`sinkron_pelanggan`** — master pelanggan (`id_pelanggan_billing` unik, `status`, `is_active`).
- **`sinkron_belum_bayar`** — tagihan belum dibayar (unik `id_pelanggan_billing + bulan`).
- **`payment_staging`** — tabel staging pembayaran versi lebih lama/kaya (punya status `duplicate`, `duplicate_of`).

### Seeder default (`ChartOfAccountsSeeder`)
10 akun: Aset (1101 Kas, 1102 Bank, 1201 Piutang Usaha), Pendapatan (4101 Pendapatan Voucher, 4201 Pendapatan Jasa, 4301 Pendapatan Lain-lain), Beban (5101 Alat, 5102 Bahan, 5103 Upah Kerja, 5109 Lain-lain).

> Catatan: kode `4102 Pendapatan Langganan Member` dipakai oleh proses penjurnalan billing, pastikan tersedia di COA.

---

## 6. Alur Double-Entry Accounting (End-to-End)

Struktur: `JournalEntry` (header) → banyak `JournalLine` (tiap baris `coa_id` + `debit` **atau** `credit`). Setiap kejadian bisnis memposting sepasang baris yang seimbang. Idempotensi dijaga pasangan `(source_type, source_id)` + unique index sebagai pengaman race condition.

**Empat sumber posting jurnal:**

| Sumber | Pemicu | Debit | Kredit | source_type |
|---|---|---|---|---|
| Pembayaran billing | Import STP / approval / retry | 1101 Kas | 4102 Pendapatan Langganan Member | `sinkron_billing` |
| Penjualan voucher (agregat harian) | `mikhmon:journalize` | 1101 Kas | 4101 Pendapatan Voucher | `mikhmon` |
| Pengeluaran | `ExpenseService::record` | akun beban (5xxx) | akun kas/bank | `expense` |
| Pendapatan lain | Event model `OtherIncome` | akun kas | akun pendapatan | `OtherIncome` |

### Siklus pembayaran billing (alur utama)

1. **Fetch** — `SinkronTransaksiController::import` memanggil `BillingApiService::getTransaksiLunas($bulan)`.
2. **Import + STP** — `PaymentImportService::process()`:
   - Validasi field wajib → dedup by `id_transaksi_billing` → deteksi anomali (`detectFlag`: jumlah ≤ 0, > Rp 10.000.000, metode tak dikenal, status bukan lunas).
   - Baris bersih → dibuat `approved` & langsung dijurnal.
   - Baris anomali → disimpan `flagged` + alasan, **tidak** dijurnal.
3. **Alert** — baris flagged memicu `TelegramNotificationService::sendFlaggedAlert()` (deep-link ke halaman staging).
4. **Review manual** — `PaymentApprovalService`:
   - `approve` / `bulkApprove` → set approved + jurnal dalam satu DB transaction.
   - `bulkReject` → hanya dalam grace window 10 menit; lewat itu baris auto-lock & jadi immutable.
5. **Safety net** — `SinkronJournalizeService::retryFailed()` (scheduler) menjurnal ulang baris `approved` yang `is_journalized = false`, sehingga kegagalan jurnal saat impor tidak menghilangkan pendapatan.
6. **Reversal** — `PaymentStaging::reject()` menunjukkan jalur undo: hapus journal entry, reset `is_journalized`, tandai rejected — hanya sebelum baris terkunci.

### Jurnal → Ledger → Laporan
- `LedgerService::monthly` → ringkasan per-akun (SUM debit, SUM kredit, saldo bersih).
- `LedgerService::kasLedger` & `CashReportService::dailyDetail` → buku besar saldo berjalan pakai window function `SUM(debit-credit) OVER (ORDER BY tanggal, id)`.
- Tidak ada saldo tersimpan → laporan selalu konsisten dengan jurnal.

---

## 7. Services (Lapisan Logika Bisnis)

- **BillingApiService** — HTTP client ke billing API (auth via query `api_key`). `getTransaksiLunas($bulan)` & `getPelanggan()`. Timeout 30s, 2 retry, TLS on. Tidak pernah throw — semua error dinormalkan ke `success: false`.
- **PaymentImportService** — engine STP pembayaran billing: validate → dedup → deteksi anomali → auto-approve+jurnal / flag. Mengembalikan ringkasan hitungan.
- **SinkronJournalizeService** — penjurnal inti billing (`journalize` mengembalikan **string status**: created / already_journalized / invalid_amount / coa_missing / failed). Punya `journalizeAll`, `journalizeByBulan`, `retryFailed`, `getSummary`.
- **PaymentApprovalService** — workflow approval manual (`approve`, `bulkApprove`, `bulkReject`) dengan guard final/locked.
- **SinkronTransaksiService** — util: lock (grace 10 menit), validate, map payload, `shouldSkipUpdate` (jangan ubah nominal yang sudah masuk buku).
- **CustomerSyncService** — sync master pelanggan dari billing (upsert by `id_pelanggan_billing`; deaktivasi pelanggan yang hilang dari API). *(Bagian audit-log di dalamnya dikesampingkan.)*
- **MikhmonImportService** — pipeline 4 langkah: `importCsv` (dedup content-hash) → `transform` → `aggregateDaily` → `journalize`.
- **ExpenseService** — `record()`: buat expense + jurnal (debit beban / kredit kas) dalam satu transaction.
- **LedgerService** — query laporan: `daily` (legacy schema), `monthly` (skema aktif), `kasLedger` (saldo berjalan).
- **CashReportService** — buku kas akun 1101 (`dailyDetail`).
- **TelegramNotificationService** — notifikasi Bot API (MarkdownV2), `sendFlaggedAlert` & `sendJournalSuccess`. Degrade gracefully jika token/chat kosong.

---

## 8. Controllers (Ringkas)

- **DashboardController** — saldo kas/bank (1101/1102), piutang, pendapatan & beban bulanan, mini-chart voucher 7 hari, chart pendapatan-vs-beban 6 bulan. `apiData` versi JSON.
- **ChartOfAccountController** — CRUD COA + ringkasan saldo per tipe. Blokir ubah kode / hapus akun yang sudah dipakai.
- **JournalEntryController** — view jurnal read-only: index (filter+statistik), show (cek balance debit/kredit), daily, summaryByAccount, export CSV, api JSON.
- **VoucherSaleController** — kelola penjualan voucher harian: index, show (+ jurnal terkait), reimport (via Artisan), void, export, chartData.
- **MikhmonImportController** — upload CSV → `Bus::chain` job (ImportCsv → Transform → AggregateDaily → Journalize → Cleanup → Finalize) + halaman status.
- **ExpenseController** — CRUD pengeluaran (pakai `ExpenseService`), export, summary. Update/destroy menulis ulang jurnal dalam transaction.
- **OtherIncomeController** — CRUD pendapatan lain; jurnal otomatis via event model; record `posted` tidak bisa diubah/hapus.
- **TransaksiController** — transaksi langganan member: index, import Excel, payment (blokir bayar ulang sebelum tanggal 10 bulan berikutnya), receipt (khusus paid).
- **LaporanController** — laporan bulanan/tahunan + ekspor Excel.
- **FinanceSettingController** — atur `default_due_day`; update menghitung ulang jatuh tempo & status transaksi.
- **FileConvertController** — konversi XLS/CSV → XLSX via PhpSpreadsheet.
- **SinkronTransaksiController** — sync transaksi lunas billing: index (auto-lock saat load), import (rate-limited, `PaymentImportService`), export, delete (hanya non-journalized), deleteById (blokir jika locked).
- **PaymentStagingController** — workflow approval atas `SinkronTransaksi`: index per status, show/edit/update (hanya jika actionable), approve/bulkApprove, reject/bulkReject.
- **PelangganController** — sync master pelanggan billing: index (filter+total), import (`CustomerSyncService`), export, delete (butuh konfirmasi string `DELETE_ALL`), deleteById.
- **ProfileController** — edit profil, update password, hapus akun.
- **Auth/**\* — controller Breeze standar (login/logout, reset & confirm password, verifikasi email).

---

## 9. Perintah Terjadwal (Scheduler)

Didefinisikan di `routes/console.php` (`App\Console\Kernel`).

| Waktu | Perintah | Fungsi |
|---|---|---|
| 00:01 | `transaksi:update-status` | Tandai transaksi unpaid/overdue lewat jatuh tempo |
| 02:00 | `billing:sync-customers` | Sync pelanggan dari billing (`withoutOverlapping`) |
| 11:08 | `mikhmon:import` | Import CSV Mikhmon |
| 11:10 | `mikhmon:transform` | Transform data mentah |
| 11:12 | `mikhmon:aggregate-daily` | Agregat penjualan harian |
| 11:14 | `mikhmon:journalize` | Jurnal voucher harian |
| 11:16–11:28 | `beat:*` / `journal:beat-*` | Pipeline Beat (import → staging → invoice → payment → jurnal → post) |

Job antrian (Mikhmon impor async): `ImportCsvJob`, `TransformDataJob`, `AggregateDailyJob`, `JournalizeJob`, `FinalizeImportJob`, `ProcessMikhmonImportJob`, `CleanupJob`.

---

## 10. Integrasi Eksternal

1. **Billing API** — sistem billing ISP upstream, auth `api_key` (query param). Endpoint: `/api/transaksi-lunas?bulan=` (transaksi lunas) & `/api/pelanggan` (master pelanggan). Envelope: `{success, data}`. Resilient: timeout 30s, 2 retry, TLS on.
2. **Mikhmon** — bukan API live; impor file CSV "Selling Report" (voucher hotspot MikroTik). Dedup via content-hash.
3. **Telegram** — outbound Bot API (`sendMessage`, MarkdownV2) ke satu chat admin. Untuk alert pembayaran flagged & notifikasi sukses jurnal.

---

## 11. Helper

- **`rupiah($angka)`** (`app/Helpers/helpers.php`) — format mata uang Indonesia: `Rp 1.500.000`. Null-safe, tanpa desimal.
- **`NumberToWordsHelper`** — "terbilang" untuk kwitansi/receipt.

---

## 12. Catatan / Known Issues

Beberapa ketidakkonsistenan ditemukan saat analisa (perlu diperhatikan saat pengembangan lanjut):

1. **Skema jurnal ganda** — `ExpenseService` menulis baris jurnal pakai kolom `account_code`/`account_name`, sedangkan writer lain (Mikhmon, Sinkron, OtherIncome) & `JournalLine::$fillable` pakai `coa_id`. Baris expense yang ditulis cara lama bisa tidak terbaca oleh join `LedgerService::monthly`/`kasLedger`. Terlihat seperti sisa migrasi skema.
2. `LedgerService::daily()` masih membaca skema lama (`journals` + `journal_entries` sebagai baris) → tidak melihat data yang ditulis service saat ini.
3. Route ekspor PDF `finance.laporan.export.pdf.*` menunjuk method `exportPdfBulanan`/`exportPdfTahunan` yang **belum diimplementasikan** (hanya Excel yang ada).
4. Scheduled `mikhmon:journalize` tidak cocok dengan signature command sebenarnya `journal:mikhmon` → entry terjadwal ini bisa gagal.
5. Route `chart-of-accounts.by-type` / `.cash` menunjuk `getByType`/`getCashAccounts` yang tidak ditemukan di controller.
6. Route `pembukuan.staging.journalize` menunjuk `journalizeApproved` yang tidak ditemukan di controller.
7. `FinanceSetting` menyimpan `default_due_day`, tapi `Transaksi` membaca `default_due_date` — beda penamaan.
8. `path.public` di `bootstrap/app.php` terikat hardcode ke path hosting produksi (`/home/cssorid/pembukuan.css.or.id`).

> Item 3–6 adalah referensi route ke method yang belum ada — kemungkinan sisa refactor atau fitur yang belum selesai. Sebaiknya diverifikasi sebelum route tersebut dipanggil di produksi.

