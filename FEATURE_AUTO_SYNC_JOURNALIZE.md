# 📋 FEATURE: Auto-Sync Pelanggan & Auto-Journalize Transaksi

## 📌 **KONTEKS PROYEK**

### Project Overview
- **Nama:** Laravel Pembukuan (Sistem Akuntansi ISP)
- **Volume Data:** 227 pelanggan aktif, ~20-50 transaksi/hari
- **Billing Cycle:** Tgl 11 setiap bulan (tidak semuanya bayar langsung)
- **API Stability:** Stabil, bisa handle 200+ data per request tanpa timeout
- **Compliance:** Perlu audit diadakan untuk SPT/tax compliance
- **History Policy:** KEEP FULL HISTORY (soft-delete, tidak hard-delete)

---

## 🎯 **TUJUAN FITUR**

### 1. **Auto-Sync Pelanggan (Daily)**
- Sync data pelanggan dari Billing API setiap hari pukul 2 AM
- Track perubahan status (`is_active`): aktif ↔ inactive
- Update data pelanggan: harga, diskon, tagihan, area
- Soft-delete: `is_active = 0` (jangan hard-delete)

### 2. **Auto-Journalize Transaksi (Immediate)**
- Transaksi `status_approval = 'approved'` → auto-journalize ke ledger
- Transaksi `status_approval = 'flagged'` → tunggu manual review
- Journal entries: DR 1101 (Kas), CR 4102 (Pendapatan)
- Otomatis create `journal_entries` & `journal_lines`

### 3. **Monthly Audit Reconciliation**
- End of month: bandingkan DB total vs API total
- Detect divergence dan log audit trail
- Compliance & audit trail untuk SPT

---

## 📊 **SISTEM YANG SUDAH ADA**

### Existing API Services
```php
BillingApiService::getPelanggan()      // GET /api/pelanggan
BillingApiService::getTransaksiLunas() // GET /api/transaksi-lunas?bulan=YYYY-MM
```

### Existing Controllers
```
PelangganController::import()              // Manual button (rate limited 5x/60s)
SinkronTransaksiController::import()       // Manual button, input bulan YYYY-MM
```

### Existing Services
```
PaymentImportService::process()            // Validate, detect anomaly, create sinkron_transaksi
SinkronJournalizeService::journalize()     // Create journal entries & lines
```

### Existing Models
```
SinkronPelanggan  // Master data pelanggan dari API
SinkronTransaksi  // Transaksi lunas dengan approval workflow
```

### Existing Database Tables
```
sinkron_pelanggan
  - id_pelanggan_billing (UK)
  - nama, phone, paket, harga_paket, area, ip_address
  - diskon, total_tagihan, tanggal_register, status
  - created_at, updated_at

sinkron_transaksi
  - id_transaksi_billing (UK)
  - kode_transaksi, nama_pelanggan, jumlah, tanggal_bayar
  - metode, area, paket, dibayar_oleh, bulan_tagihan
  - status, status_approval, flag_reason, raw_data
  - is_journalized, journalized_at, is_locked
  - reviewed_by, reviewed_at, approved_by, approved_at
  - created_at, updated_at

journal_entries, journal_lines
  (Already exist - auto-created dari sinkron_transaksi yang approved)
```

---

## 🔧 **TODO: IMPLEMENTATION PLAN**

### Phase 1: Infrastructure (Days 1-2)

#### 1.1 Create audit_log table (Migration)
```
Table: audit_log
- id (PK)
- entity_type (sinkron_pelanggan|sinkron_transaksi|journal_entry)
- entity_id
- user_id (nullable - 'system' untuk automated)
- action (CREATE|UPDATE|DELETE|APPROVE|REJECT|SYNC|AUDIT)
- old_value
- new_value
- description
- metadata (JSON)
- created_at
```

#### 1.2 Add is_active column to sinkron_pelanggan (Migration)
```
- is_active: boolean (default: true)
- Soft-delete indicator (0 = inactive, 1 = active)
```

#### 1.3 Update SinkronPelanggan model
```php
protected $fillable[] = 'is_active';
protected $casts['is_active'] = 'boolean';
```

---

### Phase 2: Daily Sync Job (Days 2-3)

#### 2.1 Create DailySyncCustomersJob
```php
// app/Jobs/DailySyncCustomersJob.php
- Fetch pelanggan dari BillingApiService::getPelanggan()
- Loop setiap pelanggan:
  - Compare dengan DB (by id_pelanggan_billing)
  - updateOrCreate: nama, harga, diskon, area, is_active
  - Log ke audit_log: OLD → NEW values
- Handle errors: log warning, tidak crash scheduler
```

#### 2.2 Register Job di Kernel (Scheduling)
```php
// app/Console/Kernel.php
$schedule->job(new DailySyncCustomersJob)
    ->dailyAt('02:00')
    ->name('sync-customers-daily')
    ->onOneServer()
    ->withoutOverlapping();
```

---

### Phase 3: Audit Reconciliation (Days 3-4)

#### 3.1 Create MonthlyAuditReconciliationJob
```php
// app/Jobs/MonthlyAuditReconciliationJob.php
- Query DB: SUM(jumlah) WHERE status_approval='approved' AND MONTH=current
- Query API: GET /api/audit-summary?bulan=YYYY-MM
- Compare totals
- If match: log 'PASSED'
- If divergence: identify divergent transactions, log 'FAILED'
- Alert admin (email) jika divergence
```

#### 3.2 Register Job di Kernel
```php
$schedule->job(new MonthlyAuditReconciliationJob)
    ->monthlyOn(28, '23:00')  // Run 3 hari sebelum akhir bulan
    ->name('audit-reconciliation-monthly')
    ->onOneServer();
```

#### 3.3 Create AuditLog Model & Repository
```php
// app/Models/AuditLog.php
// app/Repositories/AuditLogRepository.php
```

---

### Phase 4: Update Services (Days 4-5)

#### 4.1 Update PaymentImportService
- Add logging ke audit_log saat create sinkron_transaksi
- Track: who (user_id), when, status_approval, flag_reason

#### 4.2 Update SinkronJournalizeService
- Add logging ke audit_log saat create journal_entries
- Track: journal_entry_id, source_id, created_by (system)

#### 4.3 Update PelangganController
- Add audit logging saat manual import
- Track changes dari API

---

### Phase 5: Dashboard & Monitoring (Days 5-6)

#### 5.1 Create Audit Dashboard Route
```
GET /admin/audit-log           // View all audit logs
GET /admin/sync-status         // View latest sync status
GET /admin/audit-summary       // Monthly reconciliation summary
```

#### 5.2 Create Audit Views
```
resources/views/admin/audit-log/index.blade.php
resources/views/admin/sync-status.blade.php
resources/views/admin/audit-summary.blade.php
```

#### 5.3 Create Audit Services
```php
AuditReportService::getMonthlyReconciliation()
AuditReportService::getSyncHistory()
AuditReportService::getTransaksiDivergence()
```

---

### Phase 6: Testing & Validation (Days 6-7)

#### 6.1 Unit Tests
```
Tests/Feature/DailySyncCustomersJobTest.php
Tests/Feature/MonthlyAuditReconciliationJobTest.php
Tests/Unit/AuditLogRepositoryTest.php
```

#### 6.2 Feature Tests
```
Tests/Feature/PaymentImportWithAuditTest.php
Tests/Feature/SinkronJournalizeWithAuditTest.php
```

#### 6.3 Manual Testing Checklist
```
[ ] Daily sync pelanggan berjalan otomats
[ ] is_active tracking: aktif → inactive → aktif
[ ] Auto-journalize untuk approved transaksi
[ ] Flagged transaksi tunggu manual review
[ ] Monthly audit reconciliation berjalan
[ ] Audit log tercatat lengkap
[ ] Dashboard menampilkan audit summary
[ ] Export audit report
[ ] Email alert untuk divergence
```

---

## 🏗️ **ARCHITECTURE DECISION**

### Scheduling Strategy
- **Daily Sync:** 2:00 AM (off-peak) ✅
- **Manual Import:** On-demand button (tetap ada) ✅
- **Monthly Audit:** 28 setiap bulan, 11 PM (3 hari sebelum akhir bulan) ✅

### Data Retention Strategy
- **Soft-Delete:** `is_active = 0` (bukan hard-delete) ✅
- **History:** KEEP FULL HISTORY untuk audit trail ✅
- **Queries:** Auto-filter `WHERE is_active = 1` untuk "active customers" ✅

### Journalization Strategy
- **Auto:** Untuk `status_approval = 'approved'` ✅
- **Manual Review:** Untuk `status_approval = 'flagged'` ✅
- **Locked:** Setelah 10 menit tidak bisa diubah ✅

### Error Handling
- **API Down:** Log warning, retry next day, no blocking ✅
- **Duplicate:** Detect by `id_transaksi_billing`, skip ✅
- **Anomaly:** Flag untuk manual review (jangan auto-reject) ✅
- **Divergence:** Alert admin, block bulan close sampai resolved ✅

---

## 📈 **TIMELINE & EFFORT ESTIMATE**

| Phase | Task | Effort | Est. Days |
|-------|------|--------|-----------|
| 1 | Infrastructure (migrations, models) | 2h | 0.5 |
| 2 | Daily Sync Job | 4h | 1 |
| 3 | Audit Reconciliation Job | 4h | 1 |
| 4 | Update Services & Logging | 4h | 1 |
| 5 | Dashboard & Monitoring | 6h | 1.5 |
| 6 | Testing & Validation | 6h | 1.5 |
| **TOTAL** | | **26h** | **~6-7 hari** |

---

## 🎯 **NEXT STEPS (Untuk Sesi Berikutnya)**

1. ✅ **Review & Approval** - Confirm implementation plan dengan user
2. 🔄 **Start Phase 1** - Create migrations & models
3. 🔄 **Start Phase 2** - Build DailySyncCustomersJob
4. 🔄 **Start Phase 3** - Build MonthlyAuditReconciliationJob
5. 🔄 **Integration** - Connect all services
6. 🔄 **Testing** - Unit tests & feature tests
7. 🔄 **Deployment** - Setup scheduler & monitoring

---

## 📝 **DOKUMENTASI LENGKAP**

Lihat file `DIAGRAMS.md` untuk:
- ✅ Workflow Rekomendasi Utama
- ✅ Pelanggan Sync Process Detail
- ✅ Transaksi Import & Journalize Flow
- ✅ Monthly Audit Reconciliation
- ✅ Soft Delete vs Hard Delete Comparison
- ✅ Database Schema (ER Diagram)
- ✅ Monthly Audit Checklist

---

## 🔗 **FILE REFERENCES**

### Existing Code
- `app/Services/BillingApiService.php`
- `app/Services/PaymentImportService.php`
- `app/Services/SinkronJournalizeService.php`
- `app/Http/Controllers/PelangganController.php`
- `app/Http/Controllers/SinkronTransaksiController.php`
- `app/Models/SinkronPelanggan.php`
- `app/Models/SinkronTransaksi.php`

### Config
- `config/services.billing.url` - Billing API endpoint
- `config/services.billing.key` - API key

### Database
- `database/migrations/` - Existing migrations

---

## 💡 **KEY PRINCIPLES**

1. **Zero Data Loss** - Soft-delete, keep full history ✅
2. **Audit Trail** - Track semua changes (who, what, when) ✅
3. **Fail-Safe** - Job gagal tidak crash system, log & retry ✅
4. **Compliance** - SPT audit requirements terpenuhi ✅
5. **Minimal Disruption** - Auto-process background, manual buttons tetap ada ✅
6. **Observable** - Dashboard untuk monitor sync & audit status ✅

---

**Status:** ✅ Ready for implementation  
**Last Updated:** 2026-06-23  
**Owner:** AI Assistant  

