@extends('layouts-main.app')
@section('title', __('Income / Pendapatan Lain'))
@section('page-title', __('Pendapatan Lain'))

@section('content')

<style>

/* ===============================
   PAGE HEADER
=================================*/

.page-header{
    background: linear-gradient(135deg,#9a3412,#c2410c);
    color:white;
    padding:22px;
    border-radius:14px;
    margin-bottom:20px;
}

.page-header h1{
    font-size:20px;
    font-weight:600;
}

.page-header p{
    opacity:.9;
    font-size:13px;
    margin-bottom:0;
}

/* ===============================
   CARD
=================================*/

.income-card{
    border:none;
    border-radius:14px;
    overflow:hidden;
}

/* ===============================
   DARK TABLE
=================================*/

.table-dark-custom{
    background:#0f172a;
    color:#e2e8f0;
}

.table-dark-custom thead{
    background:#020617;
}

.table-dark-custom thead th{
    color:#94a3b8;
    border-bottom:1px solid #1e293b;
}

.table-dark-custom tbody tr{
    border-bottom:1px solid #1e293b;
}

.table-dark-custom tbody tr:hover{
    background:#1e293b;
}

.table-dark-custom td{
    border-color:#1e293b;
}

/* amount tetap orange */
.amount{
    font-weight:600;
    color:#fb923c;
}

/* ===============================
   AMOUNT
=================================*/

.amount{
    font-weight:600;
    color:#c2410c;
}

/* ===============================
   BADGES
=================================*/

.badge-posted{
    background:#dcfce7;
    color:#166534;
}

.badge-recorded{
    background:#fff7ed;
    color:#9a3412;
}

/* ===============================
   BUTTONS
=================================*/

.btn-modern{
    border-radius:8px;
}

/* ===============================
   EMPTY STATE
=================================*/

.empty-state{
    padding:60px 20px;
    text-align:center;
}

.empty-state i{
    font-size:50px;
    color:#9ca3af;
}

.empty-state h5{
    margin-top:16px;
    font-weight:600;
}

/* ===============================
   MOBILE
=================================*/

@media (max-width:768px){

.page-header{
    flex-direction:column;
    align-items:flex-start !important;
    gap:12px;
}

.page-header h1{
    font-size:18px;
}

.page-header p{
    font-size:12px;
}

.table-modern td{
    font-size:13px;
}

.table-modern thead{
    font-size:10px;
}

.btn-group{
    flex-direction:column;
    gap:4px;
}

}
/* ===============================
   DARK BUTTON
=================================*/

.btn-outline-primary{
    border-color:#334155;
    color:#60a5fa;
}

.btn-outline-primary:hover{
    background:#1e40af;
    color:white;
}

.btn-outline-warning{
    border-color:#334155;
    color:#facc15;
}

.btn-outline-warning:hover{
    background:#ca8a04;
    color:white;
}

.btn-outline-danger{
    border-color:#334155;
    color:#f87171;
}

.btn-outline-danger:hover{
    background:#dc2626;
    color:white;
}
/* ===============================
   TABLE TEXT IMPROVEMENT
=================================*/

.table-modern th{
    font-weight:600;
    font-size:12px;
    letter-spacing:.6px;
    text-transform:uppercase;
    padding:14px 16px;
}

.table-modern td{
    padding:14px 16px;
    font-size:14px;
    line-height:1.4;
}

/* tanggal */
.table-modern td:first-child{
    font-weight:500;
    color:#cbd5f5;
}

/* deskripsi */
.table-modern td:nth-child(2){
    max-width:320px;
    white-space:normal;
}

/* notes */
.table-modern small{
    display:block;
    margin-top:4px;
    font-size:12px;
    color:#94a3b8;
}

/* amount */
.amount{
    font-size:15px;
    font-weight:700;
    letter-spacing:.3px;
}

/* user */
.table-modern td:nth-child(5){
    font-size:13px;
    color:#cbd5f5;
}

/* aksi */
.table-modern td:last-child{
    text-align:center;
}
</style>


<div class="container-fluid py-4">

<!-- HEADER -->
<div class="page-header d-flex justify-content-between align-items-center">

<div>

<h1>
<i class="fas fa-coins me-2"></i>
Catatan Pendapatan Lain
</h1>

<p>
Kelola semua pendapatan lain yang tidak berasal dari penjualan utama.
</p>

</div>

<div class="d-flex gap-2">

<a href="{{ route('dashboard') }}" class="btn btn-light btn-modern">
<i class="fas fa-arrow-left me-2"></i>
Back
</a>

<a href="{{ route('other-incomes.create') }}" class="btn btn-light btn-modern">
<i class="fas fa-plus me-2"></i>
Tambah Income
</a>

</div>

</div>


@if ($errors->any())
<div class="alert alert-danger">
<strong>Error!</strong>
<ul class="mb-0">
@foreach ($errors->all() as $error)
<li>{{ $error }}</li>
@endforeach
</ul>
</div>
@endif


@if (session('success'))
<div class="alert alert-success">
{{ session('success') }}
</div>
@endif


<div class="card income-card shadow-sm">

<div class="table-responsive">

@if ($incomes->count() > 0)

<table class="table-dark table-bordered table-modern mb-0">

<thead>
<tr>
<th width="12%">Tanggal</th>
<th width="35%">Deskripsi</th>
<th width="18%">Jumlah</th>
<th width="15%">Status</th>
<th width="12%">Dibuat Oleh</th>
<th width="8%">Aksi</th>
</tr>
</thead>

<tbody>

@foreach ($incomes as $income)

<tr>

<td class="fw-semibold">
{{ $income->income_date->format('d M Y') }}
</td>

<td>

{{ $income->description }}

@if ($income->notes)

<br>

<small class="text-muted">
{{ $income->notes }}
</small>

@endif

</td>

<td class="text-end amount">
Rp {{ number_format($income->amount,0,',','.') }}
</td>

<td>

@if ($income->isPosted())

<span class="badge badge-posted">
<i class="fas fa-check-circle me-1"></i>
Posted
</span>

@else

<span class="badge badge-recorded">
<i class="fas fa-pencil-alt me-1"></i>
Recorded
</span>

@endif

</td>

<td>
<small>{{ $income->createdBy->name ?? 'Unknown' }}</small>
</td>

<td>

<div class="btn-group btn-group-sm">

<a href="{{ route('other-incomes.show',$income) }}"
class="btn btn-outline-primary"
title="Detail">

<i class="fas fa-eye"></i>

</a>

@if (!$income->isPosted())

<a href="{{ route('other-incomes.edit',$income) }}"
class="btn btn-outline-warning"
title="Edit">

<i class="fas fa-edit"></i>

</a>

<button type="button"
class="btn btn-outline-danger"
onclick="confirmDelete('{{ route('other-incomes.destroy',$income) }}')">

<i class="fas fa-trash"></i>

</button>

@endif

</div>

</td>

</tr>

@endforeach

</tbody>

</table>

<div class="p-3">
{{ $incomes->links() }}
</div>

@else

<div class="empty-state">

<i class="fas fa-inbox"></i>

<h5>Belum Ada Data Income</h5>

<p class="text-muted">
Tambahkan pendapatan lain untuk mulai mencatat transaksi.
</p>

</div>

@endif

</div>

</div>



</div>


<form id="deleteForm" method="POST" style="display:none">
@csrf
@method('DELETE')
</form>

<script>

function confirmDelete(url){

Swal.fire({
    title: 'Yakin hapus data?',
    text: "Data yang dihapus tidak bisa dikembalikan!",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#dc2626',
    cancelButtonColor: '#64748b',
    confirmButtonText: 'Ya, Hapus!',
    cancelButtonText: 'Batal',
    background: "#1e293b",
    color: "#e2e8f0"

}).then((result) => {

    if (result.isConfirmed) {

        document.getElementById('deleteForm').action = url
        document.getElementById('deleteForm').submit()

    }

});

}



</script>

@endsection