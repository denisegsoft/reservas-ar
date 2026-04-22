<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <style>[x-cloak]{display:none!important}</style>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ReservaTuEspacio | @yield('title', 'Inicio')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAzMiAzMic+PHJlY3Qgd2lkdGg9JzMyJyBoZWlnaHQ9JzMyJyByeD0nOCcgZmlsbD0nIzRmNDZlNScvPjxwYXRoIGQ9J00xNiA2TDQgMTZoNHYxMGg3di03aDJ2N2g3VjE2aDR6JyBmaWxsPSd3aGl0ZScvPjxjaXJjbGUgY3g9JzIzJyBjeT0nMTAnIHI9JzUnIGZpbGw9JyMxMGI5ODEnLz48cGF0aCBkPSdNMjEgMTBsMS41IDEuNUwyNSA4LjUnIHN0cm9rZT0nd2hpdGUnIHN0cm9rZS13aWR0aD0nMS41JyBzdHJva2UtbGluZWNhcD0ncm91bmQnIHN0cm9rZS1saW5lam9pbj0ncm91bmQnIGZpbGw9J25vbmUnLz48L3N2Zz4=">
    <meta name="description" content="@yield('description', 'Reservá quintas, salones y espacios para eventos en Argentina. Más de 500 propiedades verificadas. Encontrá el lugar ideal para tu cumpleaños, casamiento o reunión de empresa.')">
    <meta name="keywords" content="@yield('keywords', 'reserva quintas, alquiler salones eventos, quintas Buenos Aires, espacios para eventos Argentina, reservar quinta online')">
    <meta name="robots" content="index, follow">
    <meta name="author" content="ReservaTuEspacio">
    <link rel="canonical" href="@yield('canonical', url()->current())">

    {{-- Open Graph --}}
    <meta property="og:site_name" content="ReservaTuEspacio">
    <meta property="og:locale" content="es_AR">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="ReservaTuEspacio | @yield('title', 'Inicio')">
    <meta property="og:description" content="@yield('description', 'Encontrá y reservá quintas, salones y espacios para eventos en Argentina. Más de 500 propiedades verificadas.')">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="@yield('og_image', asset('images/propiedad-placeholder.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ReservaTuEspacio | @yield('title', 'Inicio')">
    <meta name="twitter:description" content="@yield('description', 'Encontrá y reservá quintas, salones y espacios para eventos en Argentina.')">
    <meta name="twitter:image" content="@yield('og_image', asset('images/propiedad-placeholder.jpg'))">

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @stack('styles')
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-VN26PRW2LK"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-VN26PRW2LK');
    </script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">

{{-- Navbar --}}
<nav class="bg-white/95 backdrop-blur-sm shadow-sm sticky top-0 z-50 border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
            <a href="{{ route('home') }}" class="flex items-center group-hover:opacity-80 transition-opacity">
                <x-application-logo class="h-9 w-auto" />
            </a>

            @unless($__env->hasSection('minimal_layout'))
            <div class="hidden md:flex items-center gap-6">
                <a href="{{ route('properties.index') }}" class="text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">Buscar Propiedades</a>
            </div>
            @endunless

            <div class="flex items-center gap-3">
                @guest
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-600 hover:text-indigo-600 transition-colors">Ingresar</a>
                    <a href="{{ route('register') }}" class="hidden-sm bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-sm hover:shadow-indigo-200">Registrarse</a>
                @endguest
                @auth
                @php
                    $navUnread = auth()->user()->unreadMessagesCount();
                    $navIsOwner = auth()->user()->isOwner() || auth()->user()->isAdmin();
                    $navIsAdmin = auth()->user()->isAdmin();
                @endphp
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                        <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->full_name }}" data-avatar class="w-8 h-8 rounded-full object-cover ring-2 ring-indigo-100">
                        @if(auth()->user()->full_name)
                        <span data-fullname class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">{{ auth()->user()->full_name }}</span>
                        @endif
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="open" @click.outside="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="nav-dropdown absolute right-0 mt-2 w-60 bg-white rounded-2xl shadow-xl border border-gray-100 py-1.5 z-50 origin-top-right text-nowrap overflow-y-auto" style="max-height:calc(100vh - 80px)">

                        {{-- User info --}}
                        <div class="px-4 py-3 border-b border-gray-100 mb-1">
                            <p class="text-sm font-semibold text-gray-800 flex items-center gap-1.5">
                                @if(auth()->user()->full_name)
                                    <span data-fullname>{{ auth()->user()->full_name }}</span>
                                @else
                                    <span data-fullname class="text-gray-400 italic">Sin nombre</span>
                                @endif
                                @if(auth()->user()->hasSubscription())
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endif
                            </p>
                            @if(!auth()->user()->full_name)
                            <a href="{{ route('profile.edit') }}" class="text-xs text-indigo-500 hover:text-indigo-700 transition-colors">
                                Completá tu nombre →
                            </a>
                            @endif
                        </div>

                        {{-- MI CUENTA --}}
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-4 pt-1 pb-0.5">Mi cuenta</p>
                        <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Mis Datos
                        </a>
                        <a href="{{ route('favorites.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
                            Favoritos
                        </a>
                        <a href="{{ route('messages.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                            Mensajes
                            @if($navUnread > 0)<span class="ml-auto bg-red-500 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-content shrink-0">{{ $navUnread }}</span>@endif
                        </a>

                        <a href="{{ route('reservations.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Reservas
                        </a>

                        {{-- PROPIETARIO --}}
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider px-4 pt-1 pb-0.5">Propietario</p>
                            <a href="{{ route('owner.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                Panel
                            </a>
                            <a href="{{ route('owner.properties.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-indigo-600 hover:bg-indigo-50 transition-colors font-medium text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Publicar Propiedad
                            </a>
                            <a href="{{ route('owner.properties.index') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                Mis Propiedades
                            </a>
                            <a href="{{ route('owner.reservations') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                Reservas recibidas
                                @php $pendingCount = auth()->check() && (auth()->user()->isOwner() || auth()->user()->isAdmin()) ? \App\Models\Reservation::forOwner(auth()->user())->where('status','pending')->count() : 0; @endphp
                                @if($pendingCount)
                                <span class="ml-auto bg-red-500 text-white text-[10px] font-bold min-w-[18px] h-[18px] rounded-full flex items-center justify-center px-1">{{ $pendingCount }}</span>
                                @endif
                            </a>
                            <a href="{{ route('owner.reservations.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Crear reserva
                            </a>
                        </div>

                        @if($navIsAdmin)
                        {{-- ADMIN --}}
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <p class="text-[10px] font-bold text-orange-400 uppercase tracking-wider px-4 pt-1 pb-0.5">Administración</p>
                            <a href="{{ route('admin.support') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                Soporte
                            </a>
                            <a href="{{ route('admin.suggestions') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                Sugerencias
                            </a>
                            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-orange-600 hover:bg-orange-50 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                Panel Admin
                            </a>
                        </div>
                        @endif

                        {{-- SUGERENCIAS --}}
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <a href="{{ route('suggestions.create') }}" class="flex items-center gap-2.5 px-4 py-2 text-sm text-gray-600 hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-nowrap">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                                Sugerencias
                            </a>
                        </div>

                        {{-- LOGOUT --}}
                        <div class="border-t border-gray-100 mt-1 pt-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="flex items-center gap-2.5 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endauth
            </div>
        </div>
    </div>
</nav>


<main>
    @if($__env->hasSection('sidebar'))
    {{-- Sidebar fixed (desktop only) --}}
    <aside id="app-sidebar">
        <div style="width:260px;min-width:260px;display:flex;flex-direction:column;height:100%">
            @yield('sidebar')
        </div>
    </aside>

    {{-- Pestaña para abrir sidebar cuando está cerrado --}}
    <button id="sb-open-tab"
            onclick="sidebarOpen()"
            style="position:fixed;top:50%;left:0;transform:translateY(-50%);z-index:41;
                   width:20px;height:52px;background:#4f46e5;border:none;cursor:pointer;
                   border-radius:0 10px 10px 0;color:#fff;align-items:center;justify-content:center;
                   box-shadow:4px 0 12px rgba(79,70,229,.4)">
        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Contenido --}}
    <div id="app-content">
        @yield('content')
    </div>

    @else
    @yield('content')
    @endif
</main>

<footer class="bg-gray-900 text-gray-400 ">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
        @unless($__env->hasSection('minimal_layout'))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-10">
            <div class="col-span-1 md:col-span-2">
                <div class="flex items-center gap-2.5 mb-4">
                    <div class="w-9 h-9 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    </div>
                    <span class="text-white font-bold text-xl">ReservaTuEspacio</span>
                </div>
                <p class="text-sm leading-relaxed mb-4 max-w-xs">Un lugar exclusivo para encontrar y reservar quintas, salones y espacios para eventos en Argentina.</p>
                <div class="flex items-center gap-2 bg-green-900/30 border border-green-800/50 text-green-400 text-xs font-medium px-3 py-2 rounded-xl w-fit">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Reservas verificadas y seguras
                </div>
                <a href="{{ route('support') }}" class="flex items-center gap-2 bg-indigo-900/30 border border-indigo-800/50 text-indigo-400 hover:text-indigo-300 text-xs font-medium px-3 py-2 rounded-xl w-fit transition-colors mt-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    ¿Necesitás ayuda? Contactá soporte
                </a>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm">Explorar</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('properties.index') }}" class="hover:text-white transition-colors">Buscar Propiedades</a></li>
                    <li><a href="{{ route('properties.index') }}?sort=newest" class="hover:text-white transition-colors">Nuevas Propiedades</a></li>
                    <li><a href="{{ route('properties.index') }}?sort=rating" class="hover:text-white transition-colors">Mejor Valoradas</a></li>
                    <li><a href="{{ route('properties.index') }}?sort=price_asc" class="hover:text-white transition-colors">Menor Precio</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-white font-semibold mb-4 text-sm">Para Propietarios</h3>
                <ul class="space-y-2.5 text-sm">
                    <li><a href="{{ route('register') }}" class="hover:text-white transition-colors">Publicar mi Propiedad</a></li>
                    @auth
                    @if(auth()->user()->isOwner() || auth()->user()->isAdmin())
                    <li><a href="{{ route('owner.dashboard') }}" class="hover:text-white transition-colors">Panel de Control</a></li>
                    <li><a href="{{ route('owner.properties.create') }}" class="hover:text-white transition-colors">Nueva Propiedad</a></li>
                    @endif
                    @endauth
                </ul>
            </div>
        </div>
        @endunless
        <div class="border-t border-gray-800 pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <p class="text-xs">© {{ date('Y') }} ReservaTuEspacio. Todos los derechos reservados. &mdash; <a href="{{ route('privacidad') }}" class="underline hover:text-white transition-colors">Política de privacidad</a></p>
            <a href="{{ route('support') }}" class="text-xs hover:text-white transition-colors">Soporte</a>
        </div>
    </div>
</footer>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

@auth
@if(auth()->user()->needs_password_change && request()->routeIs('owner.dashboard'))
@php
    $fp = session('first_publish') ?: [];
    $fpSlug = $fp['property_slug'] ?? optional(auth()->user()->propiedades()->latest()->first())->slug;
    $fpContactType = $fp['contact_type'] ?? (auth()->user()->email ? 'email' : 'teléfono');
@endphp
<div class="modal fade" id="firstPublishModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="max-width:440px;">
        <div class="modal-content border-0" style="border-radius:1.75rem;overflow:hidden;box-shadow:0 32px 64px -12px rgba(0,0,0,.3);">

            {{-- Header --}}
            <div style="position:relative;padding:1.75rem 1.5rem 1.5rem;background:linear-gradient(135deg,#4338ca 0%,#6d28d9 55%,#9333ea 100%);overflow:hidden;">
                <div style="position:absolute;width:110px;height:110px;border-radius:50%;background:rgba(255,255,255,.07);top:-32px;right:-32px;pointer-events:none;"></div>
                <div style="position:absolute;width:50px;height:50px;border-radius:50%;background:rgba(255,255,255,.1);top:18px;right:58px;pointer-events:none;"></div>
                <button type="button" data-bs-dismiss="modal"
                        style="position:absolute;top:14px;right:14px;width:30px;height:30px;background:rgba(255,255,255,.2);border:none;border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;transition:background .15s;"
                        onmouseover="this.style.background='rgba(255,255,255,.3)'" onmouseout="this.style.background='rgba(255,255,255,.2)'">
                    <svg width="13" height="13" fill="none" stroke="white" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                <div style="display:flex;align-items:center;gap:1rem;">
                    <div style="width:54px;height:54px;border-radius:1rem;background:rgba(255,255,255,.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="28" height="28" fill="none" stroke="white" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div>
                        <div style="font-size:.68rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:rgba(255,255,255,.6);margin-bottom:.25rem;">Publicación exitosa</div>
                        <div style="font-size:1.15rem;font-weight:900;color:#fff;line-height:1.2;">¡Tu propiedad está online!</div>
                    </div>
                </div>
            </div>

            {{-- Body --}}
            <div style="padding:1.4rem 1.5rem 0;">

                {{-- Ver propiedad --}}
                <a href="{{ $fpSlug ? route('properties.show', $fpSlug) : '#' }}" target="_blank"
                   style="display:flex;align-items:center;justify-content:center;gap:.5rem;width:100%;padding:.65rem 1rem;border-radius:.875rem;background:#eef2ff;border:1px solid #c7d2fe;color:#4338ca;font-size:.875rem;font-weight:600;text-decoration:none;margin-bottom:1.1rem;transition:background .15s;"
                   onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
                    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Ver mi propiedad
                </a>

                {{-- Aviso contraseña --}}
                <div style="display:flex;gap:.65rem;padding:.875rem;border-radius:.875rem;background:#fffbeb;border:1px solid #fde68a;margin-bottom:1.25rem;">
                    <svg style="flex-shrink:0;margin-top:1px;" width="16" height="16" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p style="margin:0 0 .2rem;font-size:.8rem;font-weight:700;color:#92400e;">Primera vez que ingresás</p>
                        <p style="margin:0;font-size:.78rem;color:#b45309;line-height:1.45;">Tu contraseña actual es el <strong>{{ $fpContactType }}</strong> que ingresaste. Te recomendamos cambiarla ahora.</p>
                    </div>
                </div>

                {{-- Form --}}
                <div id="fp-form-wrap">
                    <p style="margin:0 0 .875rem;font-size:.875rem;font-weight:600;color:#111827;">Cambiá tu contraseña</p>

                    <div id="fp-error" style="display:none;padding:.6rem .875rem;border-radius:.75rem;background:#fef2f2;border:1px solid #fecaca;color:#dc2626;font-size:.8rem;margin-bottom:.875rem;"></div>

                    <div style="margin-bottom:.75rem;">
                        <label style="display:block;font-size:.8rem;font-weight:500;color:#374151;margin-bottom:.35rem;">Nueva contraseña</label>
                        <div style="position:relative;">
                            <input type="password" id="fp-password" placeholder="Mínimo 8 caracteres" autocomplete="new-password"
                                   style="width:100%;padding:.625rem .875rem;padding-right:2.75rem;border:1px solid #d1d5db;border-radius:.75rem;font-size:.875rem;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,.15)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                            <button type="button" onclick="fpToggle('fp-password')" tabindex="-1"
                                    style="position:absolute;right:.625rem;top:50%;transform:translateY(-50%);background:none;border:none;padding:.2rem;cursor:pointer;color:#9ca3af;display:flex;align-items:center;"
                                    onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='#9ca3af'">
                                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div style="margin-bottom:1.1rem;">
                        <label style="display:block;font-size:.8rem;font-weight:500;color:#374151;margin-bottom:.35rem;">Confirmá la contraseña</label>
                        <div style="position:relative;">
                            <input type="password" id="fp-confirm" placeholder="Repetí tu nueva contraseña" autocomplete="new-password"
                                   style="width:100%;padding:.625rem .875rem;padding-right:2.75rem;border:1px solid #d1d5db;border-radius:.75rem;font-size:.875rem;outline:none;box-sizing:border-box;transition:border-color .15s,box-shadow .15s;"
                                   onfocus="this.style.borderColor='#6366f1';this.style.boxShadow='0 0 0 3px rgba(99,102,241,.15)'" onblur="this.style.borderColor='#d1d5db';this.style.boxShadow='none'">
                            <button type="button" onclick="fpToggle('fp-confirm')" tabindex="-1"
                                    style="position:absolute;right:.625rem;top:50%;transform:translateY(-50%);background:none;border:none;padding:.2rem;cursor:pointer;color:#9ca3af;display:flex;align-items:center;"
                                    onmouseover="this.style.color='#6366f1'" onmouseout="this.style.color='#9ca3af'">
                                <svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="button" onclick="fpSubmit()" id="fp-btn"
                            style="display:inline-flex;align-items:center;justify-content:center;gap:.4rem;width:100%;padding:.7rem 1.25rem;border-radius:.875rem;background:linear-gradient(135deg,#4338ca,#6d28d9);color:#fff;font-size:.875rem;font-weight:700;border:none;cursor:pointer;box-shadow:0 6px 20px rgba(99,102,241,.35);transition:box-shadow .2s;"
                            onmouseover="this.style.boxShadow='0 8px 25px rgba(99,102,241,.5)'" onmouseout="this.style.boxShadow='0 6px 20px rgba(99,102,241,.35)'">
                        <svg id="fp-spinner" width="16" height="16" fill="none" viewBox="0 0 24 24" style="display:none;animation:spin 1s linear infinite;">
                            <circle cx="12" cy="12" r="9" stroke="rgba(255,255,255,.3)" stroke-width="3"/>
                            <path d="M12 3a9 9 0 0 1 9 9" stroke="white" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                        <span id="fp-btn-text">Guardar contraseña</span>
                    </button>
                </div>
            </div>

            <div style="padding:.875rem 1.5rem 1.25rem;text-align:center;">
                <button type="button" data-bs-dismiss="modal"
                        style="background:none;border:none;font-size:.8rem;color:#9ca3af;cursor:pointer;transition:color .15s;"
                        onmouseover="this.style.color='#6b7280'" onmouseout="this.style.color='#9ca3af'">
                    Hacer esto después
                </button>
            </div>

        </div>
    </div>
</div>

<style>
@@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        new bootstrap.Modal(document.getElementById('firstPublishModal')).show();
    });

    window.fpToggle = function (id) {
        var inp = document.getElementById(id);
        inp.type = inp.type === 'password' ? 'text' : 'password';
    };

    window.fpSubmit = function () {
        var pw  = document.getElementById('fp-password').value;
        var pw2 = document.getElementById('fp-confirm').value;
        var err = document.getElementById('fp-error');
        var btn = document.getElementById('fp-btn');
        var spinner = document.getElementById('fp-spinner');
        var btnText = document.getElementById('fp-btn-text');

        err.style.display = 'none';

        if (pw.length < 8) { err.textContent = 'La contraseña debe tener al menos 8 caracteres.'; err.style.display = 'block'; return; }
        if (pw !== pw2)    { err.textContent = 'Las contraseñas no coinciden.'; err.style.display = 'block'; return; }

        btn.disabled = true;
        spinner.style.display = 'block';
        btnText.textContent = 'Guardando...';

        fetch('{{ route('password.set-initial') }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ password: pw, password_confirmation: pw2 }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                document.getElementById('fp-form-wrap').innerHTML =
                    '<div style="text-align:center;padding:1rem 0 .5rem;">'
                    + '<svg width="48" height="48" fill="none" stroke="#16a34a" stroke-width="2" viewBox="0 0 24 24" style="display:block;margin:0 auto .75rem;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                    + '<p style="margin:0 0 .25rem;font-size:.9rem;font-weight:700;color:#15803d;">¡Contraseña guardada!</p>'
                    + '<p style="margin:0 0 1.25rem;font-size:.8rem;color:#9ca3af;">Ya podés cerrar esta ventana.</p>'
                    + '<button type="button" data-bs-dismiss="modal" style="display:inline-flex;align-items:center;justify-content:center;gap:.4rem;padding:.65rem 1.75rem;border-radius:.875rem;background:linear-gradient(135deg,#4338ca,#6d28d9);color:#fff;font-size:.875rem;font-weight:700;border:none;cursor:pointer;box-shadow:0 4px 14px rgba(99,102,241,.35);">Cerrar</button>'
                    + '</div>';
            } else {
                btn.disabled = false; spinner.style.display = 'none'; btnText.textContent = 'Guardar contraseña';
                err.textContent = data.message || 'Ocurrió un error. Intentá nuevamente.'; err.style.display = 'block';
            }
        })
        .catch(function () {
            btn.disabled = false; spinner.style.display = 'none'; btnText.textContent = 'Guardar contraseña';
            err.textContent = 'Error de conexión. Intentá nuevamente.'; err.style.display = 'block';
        });
    };
}());
</script>
@endif
@endauth

</body>
</html>
