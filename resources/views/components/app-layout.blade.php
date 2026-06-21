@extends('layouts-main.app')

@section('title')
    @isset($header)
        @php
            $titleText = trim(strip_tags($header));
            if ($titleText === '') $titleText = 'Dashboard';
        @endphp
        {{ $titleText }}
    @else
        Dashboard
    @endisset
@endsection

@section('content')
    @isset($header)
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            {!! $header !!}
        </div>
    @endisset

    {!! $slot !!}
@endsection
