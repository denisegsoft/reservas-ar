@extends('layouts.main')
@section('title', 'Procesando reserva...')
@section('content')

<div class="min-h-screen flex items-center justify-center">
    <div class="text-center">
        <div class="animate-spin rounded-full h-10 w-10 border-4 border-indigo-600 border-t-transparent mx-auto mb-4"></div>
        <p class="text-gray-600 font-medium">Procesando tu reserva...</p>
    </div>
</div>

<form id="pending-form" action="{{ route('reservations.store', $propiedad->slug) }}" method="POST" class="hidden">
    @csrf
    @foreach($data as $key => $value)
        @if(!is_null($value))
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach
</form>

@push('scripts')
@vite(['resources/js/pages/reservations-process-pending.js'])
@endpush

@endsection
