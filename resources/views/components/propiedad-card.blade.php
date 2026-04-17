<div class="bg-white rounded-2xl overflow-hidden shadow-sm border border-gray-100 card-hover group">
    <div class="relative overflow-hidden aspect-[4/3]">
        <a href="{{ route('properties.show', $propiedad->slug) }}" class="flex items-center justify-center w-full h-full bg-gray-900">
            <img src="{{ $propiedad->cover_image_url }}"
                 alt="{{ $propiedad->name }}"
                 loading="lazy"
                 style="max-width:100%;max-height:100%;width:auto;height:auto;display:block;"
                 class="group-hover:scale-105 transition-transform duration-500"
                 onerror="this.src='https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&q=80'">
        </a>
        @if($propiedad->featured)
        <div class="absolute top-3 left-3 pointer-events-none">
            <span class="bg-amber-400 text-amber-900 text-xs font-bold px-2.5 py-1 rounded-full">Destacada</span>
        </div>
        @endif
        <div class="absolute top-3 right-3 flex items-center gap-2">
            @php $isFav = auth()->check() && auth()->user()->favorites()->where('property_id', $propiedad->id)->exists(); @endphp
            <div x-data="{ fav: {{ $isFav ? 'true' : 'false' }}, busy: false, sparks: [] }" class="relative">
                <button type="button"
                        @click="if(!busy){
                            @auth
                            busy=true; fav=!fav;
                            if(fav){
                                for(let i=0;i<7;i++){
                                    sparks.push({ id: Date.now()+i, x: (Math.random()*44)-22, delay: i*90 });
                                }
                                setTimeout(()=>{ sparks=[] }, 1200);
                            }
                            fetch('{{ route('favorites.toggle', $propiedad->slug) }}', { method:'POST', headers:{ 'X-CSRF-TOKEN':'{{ csrf_token() }}', 'Accept':'application/json' } }).finally(()=>{ busy=false })
                            @else
                            window.location='{{ route('favorites.login-and-save', $propiedad->slug) }}'
                            @endauth
                        }"
                        class="w-8 h-8 rounded-full bg-white/90 backdrop-blur-sm shadow-sm flex items-center justify-center transition-all hover:scale-110"
                        :class="fav ? 'text-red-500' : 'text-gray-400 hover:text-red-400'"
                        title="Guardar en favoritos">
                    <svg class="w-4 h-4" :fill="fav ? 'currentColor' : 'none'" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </button>
                <template x-for="s in sparks" :key="s.id">
                    <span class="float-heart text-red-500 text-xs select-none"
                          :style="`left:calc(50% + ${s.x}px - 6px); bottom:50%; animation-delay:${s.delay}ms;`">❤</span>
                </template>
            </div>
            <div class="bg-white/90 backdrop-blur-sm text-gray-800 text-sm font-bold px-3 py-1 rounded-xl shadow-sm pointer-events-none">
                ${{ number_format($propiedad->price_per_day, 0, ',', '.') }}<span class="text-xs font-normal text-gray-500">/dia</span>
            </div>
        </div>
    </div>
    <div class="p-5">
        <div class="flex items-start justify-between gap-2 mb-2">
            <a href="{{ route('properties.show', $propiedad->slug) }}" class="text-base font-bold text-gray-900 hover:text-indigo-600 transition-colors leading-snug line-clamp-1">
                {{ $propiedad->name }}
            </a>
            @if($propiedad->rating > 0 && \App\Models\Setting::get('reviews_enabled', '1') === '1')
            <div class="flex items-center gap-1 flex-shrink-0">
                <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                <span class="text-sm font-semibold text-gray-700">{{ $propiedad->rating }}</span>
                <span class="text-xs text-gray-400">({{ $propiedad->reviews_count }})</span>
            </div>
            @endif
        </div>
        <p class="text-sm text-gray-500 flex items-center gap-1 mb-3">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $propiedad->city }}, {{ $propiedad->state }}
        </p>
        <div class="flex items-center gap-4 text-xs text-gray-500 mb-4">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                {{ $propiedad->capacity }} personas
            </span>
            @if($propiedad->bedrooms)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/></svg>
                {{ $propiedad->bedrooms }} hab.
            </span>
            @endif
            @if($propiedad->bathrooms)
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                {{ $propiedad->bathrooms }} baños
            </span>
            @endif
        </div>
        <div class="flex gap-1.5 flex-wrap mb-4">
            @php $amenitiesAll = \App\Models\Property::amenitiesList(); @endphp
            @foreach(array_slice((array)$propiedad->amenities, 0, 3) as $amenity)
            @if(isset($amenitiesAll[$amenity]))
            <span class="bg-indigo-50 text-indigo-700 text-xs font-medium px-2.5 py-1 rounded-full">
                {{ $amenitiesAll[$amenity]['label'] }}
            </span>
            @endif
            @endforeach
            @if(count((array)$propiedad->amenities) > 3)
            <span class="bg-gray-100 text-gray-500 text-xs font-medium px-2.5 py-1 rounded-full">
                +{{ count((array)$propiedad->amenities) - 3 }} mas
            </span>
            @endif
        </div>
        <a href="{{ route('properties.show', $propiedad->slug) }}" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition-all">
            Ver más
        </a>
    </div>
</div>
