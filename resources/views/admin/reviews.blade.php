@extends('layouts.main')
@section('title', 'Admin - Reseñas')
@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-2xl font-black text-gray-900 mb-8">Moderacion de Reseñas</h1>

    @if($reviews->count())
    <div class="space-y-4">
        @foreach($reviews as $review)
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-3">
                    <img src="{{ $review->user?->avatar_url ?? 'https://ui-avatars.com/api/?name=?&background=6366f1&color=fff&size=128' }}" class="w-10 h-10 rounded-full flex-shrink-0">
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <p class="font-semibold text-gray-800 text-sm">{{ $review->user?->full_name ?? '—' }}</p>
                            <span class="text-gray-400 text-xs">sobre</span>
                            <p class="font-semibold text-indigo-600 text-sm">{{ $review->property?->name ?? '—' }}</p>
                        </div>
                        <div class="flex items-center gap-0.5 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                            <svg class="w-3.5 h-3.5 {{ $i <= $review->rating ? 'text-amber-400' : 'text-gray-200' }}" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-gray-600 text-sm">{{ $review->comment }}</p>
                        <p class="text-gray-400 text-xs mt-2">{{ $review->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div class="flex flex-col gap-2 flex-shrink-0">
                    <span class="text-xs px-2.5 py-1 rounded-full {{ $review->approved ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }} font-medium">
                        {{ $review->approved ? 'Aprobada' : 'Pendiente' }}
                    </span>
                    @if(!$review->approved)
                    <form action="{{ route('admin.reviews.approve', $review) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="w-full text-xs bg-green-100 hover:bg-green-200 text-green-700 font-medium px-3 py-1.5 rounded-lg transition-colors">Aprobar</button>
                    </form>
                    @endif
                    <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST"
                          onsubmit="return confirm('Eliminar esta resena?')">
                        @csrf @method('DELETE')
                        <button class="w-full text-xs bg-red-50 hover:bg-red-100 text-red-600 font-medium px-3 py-1.5 rounded-lg transition-colors">Eliminar</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
    @else
    <div class="text-center py-16 bg-white rounded-2xl border border-dashed border-gray-200">
        <p class="text-gray-400">No hay reseñas todavia.</p>
    </div>
    @endif
</div>

@endsection
