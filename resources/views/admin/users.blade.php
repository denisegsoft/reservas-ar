@extends('layouts.main')
@section('title', 'Admin - Usuarios')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-black text-gray-900 mb-8">Gestion de Usuarios</h1>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-100">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Usuario</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Email</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Rol</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Quintas</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Reservas</th>
                    <th class="text-left py-3 px-4 text-xs font-semibold text-gray-500 uppercase">Registro</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50/50">
                    <td class="py-3 px-4">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_url }}" class="w-8 h-8 rounded-full">
                            <span class="text-sm font-semibold text-gray-800">{{ $user->full_name }}</span>
                        </div>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ $user->email }}</td>
                    <td class="py-3 px-4">
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                            {{ $user->role === 'admin' ? 'bg-orange-100 text-orange-700' : '' }}
                            {{ $user->role === 'owner' ? 'bg-indigo-100 text-indigo-700' : '' }}
                            {{ $user->role === 'client' ? 'bg-gray-100 text-gray-700' : '' }}">
                            {{ ucfirst($user->role) }}
                        </span>
                    </td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ $user->propiedades_count }}</td>
                    <td class="py-3 px-4 text-sm text-gray-600">{{ $user->reservations_count }}</td>
                    <td class="py-3 px-4 text-sm text-gray-400">{{ $user->created_at->format('d/m/Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-6">{{ $users->links() }}</div>
</div>

@endsection