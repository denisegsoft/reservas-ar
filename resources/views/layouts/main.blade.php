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
    @if($__env->hasSection('og_image'))
    <meta property="og:image" content="@yield('og_image')">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="ReservaTuEspacio | @yield('title', 'Inicio')">
    <meta name="twitter:description" content="@yield('description', 'Encontrá y reservá quintas, salones y espacios para eventos en Argentina.')">
    @if($__env->hasSection('og_image'))
    <meta name="twitter:image" content="@yield('og_image')">
    @endif

    @stack('head')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @stack('styles')
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
                    <a href="{{ route('register') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition-all shadow-sm hover:shadow-indigo-200">Registrarse</a>
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
                        <span class="hidden sm:block text-sm font-medium text-gray-700 max-w-[120px] truncate">{{ auth()->user()->full_name }}</span>
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
                                {{ auth()->user()->full_name }}
                                @if(auth()->user()->hasSubscription())
                                <svg class="w-3.5 h-3.5 text-amber-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                @endif
                            </p>
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
            <p class="text-xs">© {{ date('Y') }} ReservaTuEspacio. Todos los derechos reservados.</p>
            <a href="{{ route('support') }}" class="text-xs hover:text-white transition-colors">Soporte</a>
        </div>
    </div>
</footer>

@stack('scripts')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
</body>
</html>
