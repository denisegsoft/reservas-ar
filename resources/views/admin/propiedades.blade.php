@extends('layouts.main')
@section('title', 'Admin - Propiedades')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-black text-gray-900">Gestion de Propiedades</h1>
        <div class="flex gap-3">
            <a href="?status=pending" class="px-4 py-2 rounded-xl text-sm font-medium {{ request('status') === 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-100 text-gray-700' }}">Pendientes</a>
            <a href="?status=active" class="px-4 py-2 rounded-xl text-sm font-medium {{ request('status') === 'active' ? 'bg-green-500 text-white' : 'bg-gray-100 text-gray-700' }}">Activas</a>
            <a href="?" class="px-4 py-2 rounded-xl text-sm font-medium {{ !request('status') ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700' }}">Todas</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Propiedad</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Propietario</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Ciudad</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Precio</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Estado</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($propiedades as $propiedad)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $propiedad->cover_image_url }}" class="w-10 h-10 rounded-xl object-cover flex-shrink-0"
                                 onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=100&q=80'">
                            <p class="text-sm font-semibold text-gray-800">{{ $propiedad->name }}</p>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ $propiedad->owner->full_name }}</td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ $propiedad->city }}</td>
                    <td class="py-3 px-4 text-sm font-semibold text-gray-800">${{ number_format($propiedad->price_per_day, 0, ',', '.') }}</td>
                    <td class="py-3 px-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $propiedad->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $propiedad->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $propiedad->status === 'inactive' ? 'bg-red-100 text-red-700' : '' }}">
                            {{ ucfirst($propiedad->status) }}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        <div class="flex gap-2">
                            @if($propiedad->status === 'pending')
                            <form action="{{ route('admin.properties.approve', $propiedad) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-green-100 hover:bg-green-200 text-green-700 font-medium px-3 py-1.5 rounded-lg transition-colors">Aprobar</button>
                            </form>
                            @endif
                            @if($propiedad->status !== 'inactive')
                            <form action="{{ route('admin.properties.reject', $propiedad) }}" method="POST">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-red-50 hover:bg-red-100 text-red-600 font-medium px-3 py-1.5 rounded-lg transition-colors">Desactivar</button>
                            </form>
                            @endif
                            <a href="{{ route('properties.show', $propiedad->slug) }}" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium px-3 py-1.5 rounded-lg transition-colors">Ver</a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $propiedades->links() }}</div>
</div>

@endsection
