# 📊 Diagram - Workflow Auto-Sync & Journalize

## 1️⃣ WORKFLOW REKOMENDASI UTAMA

```mermaid
graph TD
    A["📅 Sistem Scheduler<br/>(Laragon Task)"] 
    
    A -->|"Every day 2:00 AM<br/>(off-peak)"| B["Job: SyncCustomersDaily"]
    B -->|"GET /api/pelanggan"| C["External Billing API"]
    C -->|"Response: 200+ customers"| D["Process & Compare"]
    D -->|"updateOrCreate + track is_active"| E["sinkron_pelanggan table<br/>✅ Fresh data"]
    E -->|"Log changes"| F["audit_log table<br/>Who/What/When"]
    
    G["📥 User Import Button<br/>(Manual Trigger)"] -->|"GET /api/transaksi-lunas<br/>?bulan=2026-06"| C
    C -->|"Response: Transaksi bulan ini"| H["PaymentImportService"]
    H -->|"VALIDATE + DETECT ANOMALY"| I["Decision Tree"]
    I -->|"Valid data"| J["✅ APPROVED<br/>status_approval=approved"]
    I -->|"Suspicious"| K["⚠️ FLAGGED<br/>status_approval=flagged<br/>flag_reason=reason"]
    I -->|"Invalid"| L["❌ SKIPPED<br/>Logged warning"]
    
    J -->|"Auto-trigger"| M["✅ JOURNALIZE<br/>DR 1101 Kas<br/>CR 4102 Pendapatan"]
    M -->|"Create entry + lines"| N["journal_entries table<br/>journal_lines table"]
    K -->|"Waiting for..."| O["👤 Manual Review<br/>Admin dapat alert"]
    O -->|"Approve/Reject"| P["Update status_approval<br/>+ approved_by + reviewed_at"]
    P -->|"If approved"| M
    
    N -->|"End of month"| Q["🔍 Monthly Audit<br/>Reconciliation Job"]
    Q -->|"Compare: DB Total vs API Total"| R["Match? ✅ or Divergence? ⚠️"]
    R -->|"Log audit result"| F
    
    style E fill:#99ff99
    style N fill:#99ccff
    style F fill:#ffcc99
    style Q fill:#ffffcc
```

---

## 2️⃣ PELANGGAN SYNC PROCESS DETAIL

```mermaid
graph TD
    A["External Billing API<br/>GET /api/pelanggan"] -->|"Response: 200+ data"| B["Process Loop"]
    
    B --> C{Check each customer<br/>by id_pelanggan_billing}
    
    C -->|"New customer"| D["CREATE<br/>sinkron_pelanggan"]
    C -->|"Existing + unchanged"| E["SKIP<br/>No update needed"]
    C -->|"Existing + data changed"| F["UPDATE<br/>sinkron_pelanggan"]
    
    F --> G{What changed?}
    
    G -->|"Harga/Diskon/Tagihan"| H["✏️ Update field<br/>Log: 'Harga 500k→600k'"]
    G -->|"is_active: 1→0"| I["📍 Mark Inactive<br/>Log: 'Status inactive'<br/>Auto-exclude from active list"]
    G -->|"is_active: 0→1"| J["♻️ Reactivate<br/>Log: 'Status active'"]
    
    H --> K["audit_log entry<br/>- user_id: 'system'<br/>- action: 'sync'<br/>- old_value<br/>- new_value<br/>- changed_at"]
    
    I --> K
    J --> K
    D --> K
    E --> L["End"]
    
    K --> L
    
    style D fill:#99ff99
    style F fill:#ffff99
    style I fill:#ff9999
    style J fill:#99ccff
    style K fill:#ccccff
```

---

## 3️⃣ TRANSAKSI IMPORT & JOURNALIZE FLOW

```mermaid
graph TD
    A["Admin klik<br/>Import Transaksi"] -->|"Input: bulan=2026-06"| B["BillingApiService<br/>getTransaksiLunas"]
    
    B -->|"GET /api/transaksi-lunas<br/>?api_key=xxx&bulan=2026-06"| C["Billing API"]
    C -->|"Response: 20-50 transaksi"| D["PaymentImportService<br/>process"]
    
    D --> E["Loop: setiap transaksi"]
    
    E --> F["1. VALIDATE<br/>- Field required<br/>- Type check<br/>- Date valid"]
    
    F -->|"Invalid"| G["❌ SKIP<br/>Log warning"]
    F -->|"Valid"| H["2. CHECK DUPLICATE<br/>by id_transaksi_billing"]
    
    H -->|"Duplicate found"| I["🔄 DUPLICATE<br/>Already in DB"]
    H -->|"Not duplicate"| J["3. DETECT ANOMALY"]
    
    J -->|"jumlah ≤ 0"| K["⚠️ FLAG<br/>Invalid amount"]
    J -->|"jumlah > 10jt"| L["⚠️ FLAG<br/>Amount > limit"]
    J -->|"metode not in list"| M["⚠️ FLAG<br/>Invalid method"]
    J -->|"status ≠ lunas"| N["⚠️ FLAG<br/>Invalid status"]
    J -->|"All pass ✓"| O["✅ APPROVED<br/>Auto-journalize"]
    
    K --> P["Create sinkron_transaksi<br/>status_approval='flagged'<br/>flag_reason=reason"]
    L --> P
    M --> P
    N --> P
    
    P --> Q["⏸️ WAIT<br/>Manual review needed"]
    
    O --> R["Create sinkron_transaksi<br/>status_approval='approved'<br/>approved_at=now"]
    
    R -->|"Auto-trigger"| S["SinkronJournalizeService<br/>journalize"]
    
    S --> T["Create journal_entry<br/>source_type='sinkron_billing'<br/>source_id=transaksi.id"]
    
    T --> U["Create journal_lines<br/>- DR 1101 Kas<br/>- CR 4102 Pendapatan"]
    
    U --> V["Update sinkron_transaksi<br/>is_journalized=true<br/>journalized_at=now"]
    
    G --> W["End"]
    I --> W
    Q --> W
    V --> W
    
    style O fill:#99ff99
    style R fill:#99ff99
    style V fill:#99ccff
    style P fill:#ffff99
    style Q fill:#ffff99
    style K fill:#ff9999
    style L fill:#ff9999
```

---

## 4️⃣ MONTHLY AUDIT RECONCILIATION

```mermaid
graph TD
    A["Scheduled Job<br/>End of Month<br/>tgl 28-31"] -->|"Trigger"| B["MonthlyAuditReconciliationJob"]
    
    B --> C["Step 1: Hitung DB Total"]
    C --> D["SELECT SUM jumlah<br/>FROM sinkron_transaksi<br/>WHERE status_approval='approved'<br/>AND MONTH=current_month<br/>Result: Rp X"]
    
    B --> E["Step 2: Hitung API Total"]
    E --> F["GET /api/audit-summary<br/>?bulan=YYYY-MM<br/>Response: total_api = Rp Y"]
    
    B --> G["Step 3: Compare"]
    G --> H{Rp X == Rp Y?}
    
    H -->|"YES ✅"| I["AUDIT PASSED"]
    H -->|"NO ❌"| J["AUDIT FAILED"]
    
    I --> K["Create audit_log<br/>- status: 'PASSED'<br/>- db_total: Rp X<br/>- api_total: Rp X<br/>- divergence: 0<br/>- notes: 'OK'"]
    
    J --> L["Identify divergence"]
    L --> M["Get divergent transactions<br/>in_db_but_not_api or<br/>in_api_but_not_db"]
    
    M --> N["Create audit_log<br/>- status: 'FAILED'<br/>- db_total: Rp X<br/>- api_total: Rp Y<br/>- divergence: Rp Z<br/>- details: JSON"]
    
    N --> O["🚨 Alert Admin<br/>Email: Audit reconciliation failed"]
    
    K --> P["Log to audit_log table"]
    O --> P
    
    P --> Q["Dashboard shows<br/>Audit status"]
    
    style I fill:#99ff99
    style K fill:#99ff99
    style J fill:#ff9999
    style N fill:#ff9999
    style O fill:#ff9999
    style Q fill:#ccccff
```

---

## 5️⃣ SOFT DELETE vs HARD DELETE COMPARISON

```mermaid
graph TD
    A["June: PT ABC bayar Rp 5jt"]
    A --> B["sinkron_transaksi #789"]
    B --> C["journal_entries #123"]
    C --> D["journal_lines #456-457"]
    
    A --> E["July: PT ABC inactive"]
    
    E --> F{Strategy?}
    
    F -->|"HARD DELETE ❌"| G["DELETE all PT ABC data"]
    F -->|"SOFT DELETE ✅"| H["UPDATE is_active=0"]
    
    G --> I["August Report:<br/>Revenue = Rp 0 ❌"]
    G --> J["Audit trail loss ❌"]
    G --> K["SPT invalid ❌"]
    
    H --> L["August Report:<br/>Revenue = Rp 5jt ✅"]
    H --> M["Audit trail intact ✅"]
    H --> N["SPT valid ✅"]
    
    I --> O["❌ FAIL AUDIT<br/>Data tidak lengkap"]
    J --> O
    K --> O
    
    L --> P["✅ PASS AUDIT<br/>History complete<br/>Compliant"]
    M --> P
    N --> P
    
    style I fill:#ff9999
    style J fill:#ff9999
    style K fill:#ff9999
    style O fill:#ff6666
    style L fill:#99ff99
    style M fill:#99ff99
    style N fill:#99ff99
    style P fill:#66ff66
```

---

## 6️⃣ DATABASE SCHEMA YANG DIPERLUKAN

```mermaid
erDiagram
    SINKRON_PELANGGAN ||--o{ SINKRON_TRANSAKSI : contains
    SINKRON_PELANGGAN ||--o{ AUDIT_LOG : tracked
    SINKRON_TRANSAKSI ||--o{ JOURNAL_ENTRIES : journalized
    JOURNAL_ENTRIES ||--o{ JOURNAL_LINES : has
    
    SINKRON_PELANGGAN {
        bigint id PK
        bigint id_pelanggan_billing UK "Unique key dari API"
        string nama
        string phone
        string paket
        decimal harga_paket
        string area
        string ip_address
        decimal diskon
        decimal total_tagihan
        date tanggal_register
        string status
        boolean is_active "NEW: track active status"
        timestamp created_at
        timestamp updated_at
    }
    
    SINKRON_TRANSAKSI {
        bigint id PK
        bigint id_transaksi_billing UK "Unique key dari API"
        string kode_transaksi
        string nama_pelanggan FK
        string area
        string paket
        decimal jumlah
        string metode
        string dibayar_oleh
        string bulan_tagihan
        timestamp tanggal_bayar
        string status
        enum status_approval "pending|approved|flagged|rejected"
        text flag_reason "Alasan jika flagged"
        json raw_data
        boolean is_journalized
        timestamp journalized_at
        boolean is_locked
        bigint reviewed_by FK
        timestamp reviewed_at
        bigint approved_by FK
        timestamp approved_at
        timestamp created_at
        timestamp updated_at
    }
    
    AUDIT_LOG {
        bigint id PK
        string entity_type "sinkron_pelanggan|sinkron_transaksi|journal_entry"
        bigint entity_id
        bigint user_id "null jika system"
        string action "CREATE|UPDATE|DELETE|APPROVE|REJECT|SYNC|AUDIT"
        string old_value
        string new_value
        string description
        json metadata
        timestamp created_at
    }
    
    JOURNAL_ENTRIES {
        bigint id PK
        string source_type "sinkron_billing|mikhmon|manual"
        bigint source_id
        date tanggal_entry
        text deskripsi
        timestamp created_at
    }
    
    JOURNAL_LINES {
        bigint id PK
        bigint journal_entry_id FK
        string account_code "1101, 4102, etc"
        enum tipe "debit|credit"
        decimal amount
        timestamp created_at
    }
```

---

## 7️⃣ MONTHLY AUDIT CHECKLIST

```mermaid
graph TD
    A["Monthly Audit Checklist"] --> B["Critical Items"]
    
    B --> B1["✓ Total Amount Match<br/>DB Total vs API Total"]
    B --> B2["✓ Transaction Count<br/>DB Count vs API Count"]
    B --> B3["✓ Journal Balance<br/>Total DR = Total CR"]
    
    A --> C["Data Quality Items"]
    
    C --> C1["✓ New Customers Added"]
    C --> C2["✓ Inactive Customers Detected"]
    C --> C3["✓ Flagged Transactions Reviewed"]
    
    A --> D["Compliance Items"]
    
    D --> D1["✓ Duplicate Detection"]
    D --> D2["✓ Data Integrity<br/>No null required fields"]
    D --> D3["✓ Source Traceability<br/>All entries have source"]
    
    B1 --> E["Result: PASS ✅ or FAIL ❌"]
    B2 --> E
    B3 --> E
    
    E --> F["Create audit_log entry<br/>with status + notes"]
    
    style B1 fill:#ff9999
    style B2 fill:#ff9999
    style B3 fill:#ff9999
    style E fill:#ccccff
```

---

## 📝 QUICK REFERENCE

### Architecture Decision
- **Daily Sync:** 2:00 AM (off-peak)
- **Manual Import:** On-demand button
- **Auto-Journalize:** For approved transactions
- **Manual Review:** For flagged transactions
- **Monthly Audit:** End of month reconciliation
- **History Policy:** Soft-delete (is_active=0), keep all history

### Volume
- Customers: 227 aktif
- Transactions: ~20-50/hari
- Billing cycle: Tgl 11 setiap bulan

### Key Tables
- `sinkron_pelanggan` + NEW: `is_active` column
- `sinkron_transaksi` (already has status_approval)
- NEW: `audit_log` (for compliance tracking)
- `journal_entries` (auto-created from approved transaksi)

### Key Services
- `BillingApiService` (HTTP calls to API)
- `PaymentImportService` (validate + process transaksi)
- `SinkronJournalizeService` (create journal entries)
- NEW: `DailySyncCustomersJob` (scheduled sync)
- NEW: `MonthlyAuditReconciliationJob` (audit)
