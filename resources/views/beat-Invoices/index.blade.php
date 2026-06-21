@extends('layouts-main.app')
@section('title', __('Beat Invoices'))
@section('page-title', __('Beat Invoices'))
@section('content')
<div class="container-fluid">

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif
{{-- 
    <div class="row mb-4">
        <div class="col-md-6">
            <h1 class="h3">{{ __('Beat Invoices') }}</h1>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('beat-invoices.export') }}" class="btn btn-secondary">{{ __('Export') }}</a>
        </div>
    </div> --}}

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-xs text-muted">{{ __('Total Invoices') }}</div>
                <div class="h5">{{ number_format($stats['total'] ?? 0) }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3">
                <div class="text-xs text-muted">{{ __('Total Amount') }}</div>
                <div class="h5">Rp {{ number_format($stats['total_amount'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 border-danger">
                <div class="text-xs text-muted">
                    <i class="fas fa-times-circle text-danger"></i> {{ __('Unpaid') }}
                </div>
                <div class="h5 text-danger">{{ number_format($stats['unpaid_count'] ?? 0) }}</div>
            </div>
        </div>
        {{-- <div class="col-md-3">
            <div class="card p-3 border-warning">
                <div class="text-xs text-muted">
                    <i class="fas fa-exclamation-circle text-warning"></i> {{ __('Partial') }}
                </div>
                <div class="h5 text-warning">{{ number_format($stats['partial_count'] ?? 0) }}</div>
            </div>
        </div>
    </div> --}}

    <div class="card shadow">
        <div class="card-body">
            <form method="GET" class="row g-2 mb-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search customer / pppoe / package" value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="unpaid" @selected(request('status')=='unpaid')>Unpaid</option>
                        <option value="paid" @selected(request('status')=='paid')>Paid</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button class="btn btn-primary" type="submit">{{ __('Search') }}</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>{{ __('Customer') }}</th>
                            <th>{{ __('PPPoE') }}</th>
                            <th>{{ __('Package') }}</th>
                            <th>{{ __('Period') }}</th>
                            <th class="text-end">{{ __('Total') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th class="text-center">{{ __('Action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                            <tr>
                                <td>{{ $inv->id }}</td>
                                <td>{{ $inv->customer_name }}</td>
                                <td>{{ $inv->pppoe }}</td>
                                <td>{{ $inv->package_name }}</td>
                                <td>{{ $inv->period_month }}/{{ $inv->period_year }}</td>
                                <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                                <td>{{ ucfirst($inv->status) }}</td>
                                <td class="text-center">
                                    <a href="{{ route('beat-invoices.show', $inv->id) }}" class="btn btn-sm btn-info">{{ __('View') }}</a>
                                    <a href="{{ route('beat-invoices.pdf', $inv->id) }}" class="btn btn-sm btn-secondary" target="_blank">PDF</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">{{ __('No invoices found') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination Info -->
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted small">
                    {{ __('Showing') }} <strong>{{ $invoices->firstItem() ?? 0 }}</strong> {{ __('to') }} <strong>{{ $invoices->lastItem() ?? 0 }}</strong> {{ __('of') }} <strong>{{ $invoices->total() }}</strong> {{ __('invoices') }}
                </div>
            </div>

            <!-- Pagination -->
            <nav class="mt-4">
                {{ $invoices->links('pagination::bootstrap-4') }}
            </nav>
        </div>
    </div>

</div>
@endsection
