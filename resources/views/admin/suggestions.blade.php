@extends('layouts.main')
@section('title', 'Admin - Sugerencias')
@section('sidebar') @include('components.user-sidebar') @endsection
@section('content')

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <div class="mb-8">
        <h1 class="text-2xl font-black text-gray-900">Sugerencias</h1>
        <p class="text-gray-500 text-sm mt-1">Solicitudes e ideas enviadas por los usuarios.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 rounded-2xl px-5 py-4 mb-6 text-sm font-medium">
        {{ session('success') }}
    </div>
    @endif

    @if($suggestions->isEmpty())
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
        <p class="text-gray-400 text-sm">Aún no hay sugerencias.</p>
    </div>
    @else
    <div class="space-y-4">
        @foreach($suggestions as $s)
        @php
            $statusColors = [
                'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                'reviewed' => 'bg-blue-50 text-blue-700 border-blue-200',
                'done'     => 'bg-green-50 text-green-700 border-green-200',
            ];
            $statusLabels = ['pending' => 'Pendiente', 'reviewed' => 'Revisada', 'done' => 'Resuelta'];
        @endphp
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
            <div class="flex items-start justify-between gap-4 flex-wrap">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap mb-1">
                        <h3 class="font-bold text-gray-900 text-sm">{{ $s->title }}</h3>
                        <span class="text-xs border px-2 py-0.5 rounded-full font-medium {{ $statusColors[$s->status] }}">
                            {{ $statusLabels[$s->status] }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-400 mb-3">
                        {{ $s->user?->full_name ?? 'Usuario eliminado' }}
                        @if($s->user)<span class="text-gray-300">#{{ $s->user->id }}</span>@endif
                        · {{ $s->created_at->diffForHumans() }}
                    </p>
                    <p class="text-sm text-gray-600 whitespace-pre-line">{{ $s->description }}</p>

                    @if($s->attachments)
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach($s->attachments as $path)
                        <a href="{{ Storage::url($path) }}" target="_blank"
                           class="flex items-center gap-1.5 text-xs text-indigo-600 bg-indigo-50 border border-indigo-100 px-3 py-1.5 rounded-lg hover:bg-indigo-100 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/></svg>
                            {{ basename($path) }}
                        </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                <form action="{{ route('admin.suggestions.status', $s) }}" method="POST" class="flex items-center gap-2 shrink-0">
                    @csrf @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                            class="text-xs border border-gray-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 outline-none cursor-pointer">
                        <option value="pending"  {{ $s->status === 'pending'  ? 'selected' : '' }}>Pendiente</option>
                        <option value="reviewed" {{ $s->status === 'reviewed' ? 'selected' : '' }}>Revisada</option>
                        <option value="done"     {{ $s->status === 'done'     ? 'selected' : '' }}>Resuelta</option>
                    </select>
                </form>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $suggestions->links() }}
    </div>
    @endif

</div>

@endsection
