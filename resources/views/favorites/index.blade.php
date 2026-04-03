@extends('layouts.main')
@section('title', 'Mis Favoritos')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-gray-900">Mis Favoritos</h1>
            <p class="text-sm text-gray-500 mt-1">Los espacios que guardaste</p>
        </div>
        <a href="{{ route('properties.index') }}" class="text-sm font-semibold text-indigo-600 hover:text-indigo-700 flex items-center gap-1">
            Explorar mas
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>

    @if($favorites->isEmpty())
        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-16 text-center">
            <div class="w-16 h-16 bg-red-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                </svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Aun no tenes favoritos</h3>
            <p class="text-gray-500 text-sm mb-6">Hace click en el corazón de cualquier espacio para guardarlo aqui.</p>
            <a href="{{ route('properties.index') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2.5 rounded-xl transition-all text-sm">
                Explorar espacios
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($favorites as $propiedad)
                @include('components.propiedad-card', compact('propiedad'))
            @endforeach
        </div>

        <div class="mt-10">
            {{ $favorites->links() }}
        </div>
    @endif
</div>

@endsection
