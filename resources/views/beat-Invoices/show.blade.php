@extends('layouts-main.app')
@section('title', __('Invoice :id', ['id' => $invoice->id]))
@section('page-title', __('Invoice Details'))
@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-8">
            <h4>{{ $invoice->customer_name }}</h4>
            <div class="text-muted">{{ $invoice->pppoe }} - {{ $invoice->package_name }}</div>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('beat-invoices.pdf', $invoice->id) }}" class="btn btn-secondary" target="_blank">{{ __('Download PDF') }}</a>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>{{ __('Period') }}</strong>
                    <div>{{ $invoice->period_month }}/{{ $invoice->period_year }}</div>
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Billing Day') }}</strong>
                    <div>{{ $invoice->billing_day }}</div>
                </div>
                <div class="col-md-4">
                    <strong>{{ __('Total') }}</strong>
                    <div>Rp {{ number_format($invoice->total_amount,0,',','.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">{{ __('Payments') }}</div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Method') }}</th>
                            <th>{{ __('Note') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoice->payments as $p)
                            <tr>
                                <td>{{ $p->created_at->format('Y-m-d') }}</td>
                                <td>Rp {{ number_format($p->amount,0,',','.') }}</td>
                                <td>{{ $p->method ?? '-' }}</td>
                                <td>{{ $p->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">{{ __('No payments recorded') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3 text-end">
                <strong>{{ __('Outstanding') }}: </strong>
                Rp {{ number_format($invoice->outstanding_amount,0,',','.') }}
            </div>
        </div>
    </div>
</div>
@endsection
