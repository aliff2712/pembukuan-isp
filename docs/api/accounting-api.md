# Accounting API Contract

Dokumen ini adalah **kontrak resmi antara Backend dan Frontend**  
untuk modul akuntansi & keuangan.

Semua response di sini adalah **final** dan **bersumber dari journal**.

---

## 1. Prinsip Umum (WAJIB DIPAHAMI)

1. Semua angka keuangan **tidak disimpan**, tapi **dihitung dari journal**
2. Frontend **dilarang menghitung saldo**
3. Frontend **tidak boleh mengubah data keuangan langsung**
4. Backend bertanggung jawab penuh atas:
   - Akuntansi
   - Konsistensi debit = kredit
5. Jika response berubah:
   - File ini **HARUS diupdate lebih dulu**

---

## 2. Dashboard Summary

### GET /api/dashboard/summary

Endpoint ini menyediakan ringkasan keuangan real-time untuk dashboard utama.

### Response
```json
{
  "cash": {
    "coa_code": "1101", // Contoh akun kas
    "balance": 12500000
  },
  "bank": {
    "coa_code": "1102", // Contoh akun bank
    "balance": 8400000
  },
  "accounts_receivable": {
    "coa_code": "1201", // Akun piutang usaha
    "balance": 6200000
  },
  "revenue": {
    "this_month": 18500000 // Total pendapatan bulan ini
  },
  "expense": {
    "this_month": 4300000 // Total beban bulan ini
  }
}
```

---

## 3. Ledger (Buku Besar per Akun)

### GET /api/ledger/{coa_code}

Menampilkan buku besar untuk satu akun, berisi daftar transaksi dalam periode tertentu.

### Query Params (optional)
- `from` (string, YYYY-MM-DD): Tanggal mulai periode.
- `to` (string, YYYY-MM-DD): Tanggal akhir periode.

### Response
```json
{
  "coa": {
    "code": "1101",
    "name": "Kas"
  },
  "opening_balance": 5000000, // Saldo awal periode
  "transactions": [
    {
      "date": "2026-01-03",
      "description": "Penjualan voucher harian",
      "debit": 150000,
      "credit": 0,
      "running_balance": 5150000 // Saldo berjalan
    },
    {
      "date": "2026-01-10",
      "description": "Biaya operasional",
      "debit": 0,
      "credit": 250000,
      "running_balance": 4900000 // Saldo berjalan
    }
  ],
  "closing_balance": 4900000 // Saldo akhir periode
}
```

---

## 4. Invoice List (Business View)

### GET /api/invoices

Menampilkan daftar invoice dari sudut pandang bisnis.

### Query Params (optional)
- `status` (string): Filter berdasarkan status (`unpaid`, `partial`, `paid`).

### Response
```json
{
  "data": [
    {
      "id": 21,
      "customer_name": "John Doe",
      "pppoe_username": "jd123",
      "package_name": "Paket 20 Mbps",
      "billing_period": "2026-01",
      "total_amount": 350000,
      "paid_amount": 150000,
      "outstanding_amount": 200000,
      "status": "partial" // Status bisnis: unpaid, partial, paid
    }
  ]
}
```

### Notes
- Status invoice (`unpaid`, `partial`, `paid`) adalah logika bisnis dan tidak selalu sama persis dengan saldo piutang (AR) secara akuntansi.

---

## 5. Create Payment

### POST /api/payments

Mencatat pembayaran customer dan otomatis membuat journal.

### Request
```json
{
  "invoice_id": 21,
  "payment_date": "2026-01-15",
  "amount": 200000,
  "paid_to_coa_code": "1101",
  "note": "Bayar sisa tagihan"
}
```

### Response
```json
{
  "status": "success",
  "payment_id": 88,
  "journal_created": true
}
```

### Notes
- `paid_to_coa_code` adalah kode akun (dari Chart of Account) kemana pembayaran diterima. Akun ini **harus** memiliki flag `is_cash = true` di database.
- Frontend sebaiknya menyediakan pilihan akun pembayaran (misal: Kas, Bank BCA, dll) yang didapat dari endpoint `GET /api/chart-of-accounts?is_cash=true` (perlu dibuat jika belum ada).
- Backend akan membuat journal otomatis: Debit ke `paid_to_coa_code`, Kredit ke Piutang Usaha.

---

## 6. Trial Balance

### GET /api/reports/trial-balance

Menghasilkan laporan neraca saldo untuk periode tertentu.

### Query Params
- `month` (string, YYYY-MM): Periode bulan yang diinginkan.

### Response
```json
{
  "period": "2026-01",
  "accounts": [
    {
      "coa_code": "1101",
      "name": "Kas",
      "debit": 13500000,
      "credit": 7600000
    },
    {
      "coa_code": "1201",
      "name": "Piutang Usaha",
      "debit": 9800000,
      "credit": 3600000
    }
  ],
  "total_debit": 23300000,
  "total_credit": 23300000
}

Validation

total_debit HARUS sama dengan total_credit

Jika tidak sama → bug akuntansi
7. Profit & Loss (Laba Rugi)
GET /api/reports/profit-loss

Query Params:

month (YYYY-MM)

Response
{
  "period": "2026-01",
  "revenue": {
    "voucher": 7200000,
    "service": 11300000,
    "other": 0,
    "total": 18500000
  },
  "expense": {
    "total": 4300000
  },
  "net_profit": 14200000
}

8. Balance Sheet (Neraca)
GET /api/reports/balance-sheet

Query Params:

as_of (YYYY-MM-DD)

Response
{
  "as_of": "2026-01-31",
  "assets": {
    "cash": 12500000,
    "bank": 8400000,
    "accounts_receivable": 6200000,
    "total": 27100000
  },
  "liabilities": {
    "total": 0
  },
  "equity": {
    "retained_earnings": 27100000
  }
}