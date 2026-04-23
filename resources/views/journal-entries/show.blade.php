@extends('layouts-main.app')

@section('title', __('Journal Entry Details'))
@section('page-title', __('Journal Entry Details'))

@section('content')
<div class="container-fluid">

    @if ($message = Session::get('success'))
        <div class="alert alert-success">{{ $message }}</div>
    @endif

    <div class="card shadow">
        <div class="card-body">

            <h5 class="card-title mb-4">
                {{ __('Journal Entry') }} #{{ $entry->id }}
            </h5>

            {{-- HEADER INFO --}}
            <table class="table table-bordered table-dark">
                <tr>
                    <th width="200">{{ __('Date') }}</th>
                    <td>{{ \Carbon\Carbon::parse($entry->journal_date)->format('Y-m-d') }}</td>
                </tr>

                <tr>
                    <th>{{ __('Description') }}</th>
                    <td>{{ $entry->description }}</td>
                </tr>

                <tr>
                    <th>{{ __('Source Type') }}</th>
                    <td>{{ $entry->source_type ?? '-' }}</td>
                </tr>

                <tr>
                    <th>{{ __('Reference No') }}</th>
                    <td>{{ $entry->reference_no ?? '-' }}</td>
                </tr>

                <tr>
                    <th>{{ __('Created At') }}</th>
                    <td>{{ $entry->created_at->format('Y-m-d H:i:s') }}</td>
                </tr>

            </table>

            {{-- JOURNAL LINES --}}
            <h6 class="mt-4">{{ __('Journal Lines') }}</h6>

            <table class="table table-striped table-bordered table-dark">
                <thead>
                    <tr>
                        <th>Account Code</th>
                        <th>Account Name</th>
                        <th class="text-end">Debit</th>
                        <th class="text-end">Credit</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($entry->lines as $line)
                        <tr>
                            <td>{{ $line->account_code }}</td>
                            <td>{{ $line->account_name }}</td>
                            <td class="text-end">
                                {{ $line->debit ? number_format($line->debit,0,',','.') : '-' }}
                            </td>
                            <td class="text-end">
                                {{ $line->credit ? number_format($line->credit,0,',','.') : '-' }}
                            </td>
                        </tr>
                    @endforeach

                </tbody>

                <tfoot class="">
                    <tr class="fw-bold">
                        <td colspan="2" class="text-end">TOTAL</td>
                        <td class="text-end">{{ number_format($totalDebit,0,',','.') }}</td>
                        <td class="text-end">{{ number_format($totalCredit,0,',','.') }}</td>
                    </tr>

                    <tr>
                        <td colspan="4" class="text-center">
                            @if($isBalanced)
                                <span class="badge bg-success">Balanced</span>
                            @else
                                <span class="badge bg-danger">Not Balanced</span>
                            @endif
                        </td>
                    </tr>
                </tfoot>

            </table>

            <a href="{{ route('journal-entries.index') }}"
               class="btn btn-secondary mt-3">
               {{ __('Back to List') }}
            </a>

        </div>
    </div>

</div>
@endsection
