@extends('layouts.main')
@section('title', 'Admin - Soporte')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Tickets de soporte</h1>
        <p class="text-gray-500 text-sm mt-1">Mensajes enviados por usuarios desde el formulario de soporte.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl px-5 py-4 mb-6 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    @if($tickets->isEmpty())
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
        <p class="text-gray-400 text-sm">No hay tickets de soporte.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($tickets as $t)
        @php
            $statusColors = [
                'open'        => 'bg-amber-50 text-amber-700 border-amber-200',
                'in_progress' => 'bg-blue-50 text-blue-700 border-blue-200',
                'closed'      => 'bg-gray-50 text-gray-500 border-gray-200',
            ];
            $statusLabels = ['open' => 'Abierto', 'in_progress' => 'En proceso', 'closed' => 'Cerrado'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h3 class="font-bold text-gray-900 text-sm">{{ $t->subject }}</h3>
                        <span class="text-xs border px-2 py-0.5 rounded-full font-medium {{ $statusColors[$t->status] }}">
                            {{ $statusLabels[$t->status] }}
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-xs text-gray-400 mb-3 flex-wrap">
                        <span>{{ $t->user?->full_name ?? 'Invitado' }}@if($t->user) <span class="text-gray-300">#{{ $t->user->id }}</span>@endif</span>
                        <span>{{ $t->email }}</span>
                        @if($t->phone)<span>{{ $t->phone }}</span>@endif
                        <span>{{ $t->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $t->message }}</p>
                </div>

                <form action="{{ route('admin.support.status', $t) }}" method="POST" class="shrink-0">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                            class="text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                        <option value="open"        {{ $t->status === 'open'        ? 'selected' : '' }}>Abierto</option>
                        <option value="in_progress" {{ $t->status === 'in_progress' ? 'selected' : '' }}>En proceso</option>
                        <option value="closed"      {{ $t->status === 'closed'      ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $tickets->links() }}
    </div>
    @endif

</div>

@endsection
