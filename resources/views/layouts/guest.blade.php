<!DOCTYPE html>
<html lang="es" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ReservaTuEspacio | @yield('title', 'Acceder')</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0naHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmcnIHZpZXdCb3g9JzAgMCAzMiAzMic+PHJlY3Qgd2lkdGg9JzMyJyBoZWlnaHQ9JzMyJyByeD0nOCcgZmlsbD0nIzRmNDZlNScvPjxwYXRoIGQ9J00xNiA2TDQgMTZoNHYxMGg3di03aDJ2N2g3VjE2aDR6JyBmaWxsPSd3aGl0ZScvPjxjaXJjbGUgY3g9JzIzJyBjeT0nMTAnIHI9JzUnIGZpbGw9JyMxMGI5ODEnLz48cGF0aCBkPSdNMjEgMTBsMS41IDEuNUwyNSA4LjUnIHN0cm9rZT0nd2hpdGUnIHN0cm9rZS13aWR0aD0nMS41JyBzdHJva2UtbGluZWNhcD0ncm91bmQnIHN0cm9rZS1saW5lam9pbj0ncm91bmQnIGZpbGw9J25vbmUnLz48L3N2Zz4=">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased bg-gray-50">

<div class="min-h-screen flex">

    {{-- Panel izquierdo: marca --}}
    <div class="hidden lg:flex lg:w-1/2 xl:w-3/5 relative flex-col justify-between p-12 overflow-hidden"
         style="background: linear-gradient(145deg, #1e1b4b 0%, #312e81 30%, #4338ca 60%, #6d28d9 100%);">

        {{-- Decoracion de fondo --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-32 -left-32 w-96 h-96 rounded-full opacity-10" style="background:radial-gradient(circle,#a78bfa,transparent)"></div>
            <div class="absolute top-1/2 -right-24 w-72 h-72 rounded-full opacity-10" style="background:radial-gradient(circle,#60a5fa,transparent)"></div>
            <div class="absolute -bottom-20 left-1/3 w-80 h-80 rounded-full opacity-10" style="background:radial-gradient(circle,#c4b5fd,transparent)"></div>
            {{-- Grid sutil --}}
            <svg class="absolute inset-0 w-full h-full opacity-5" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                        <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#grid)"/>
            </svg>
        </div>

        {{-- Logo y nombre --}}
        <div class="relative z-10">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                {{-- Logo SVG: casa con calendario --}}
                <div class="w-12 h-12 relative flex-shrink-0">
                    <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                        {{-- Fondo del logo --}}
                        <rect width="48" height="48" rx="14" fill="white" fill-opacity="0.15"/>
                        {{-- Casa --}}
                        <path d="M24 10L8 22H12V38H22V30H26V38H36V22H40L24 10Z" fill="white" fill-opacity="0.9"/>
                        {{-- Puerta --}}
                        <rect x="20" y="30" width="8" height="8" rx="1" fill="#4338ca"/>
                        {{-- Ventana --}}
                        <rect x="14" y="24" width="6" height="5" rx="1" fill="#4338ca"/>
                        <rect x="28" y="24" width="6" height="5" rx="1" fill="#4338ca"/>
                        {{-- Badge calendario (check) --}}
                        <circle cx="37" cy="13" r="8" fill="#10b981"/>
                        <path d="M33.5 13L36 15.5L40.5 10.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <div>
                    <div class="text-2xl font-black text-white leading-tight tracking-tight">
                        Reserva<span class="text-violet-300">Tu</span>Espacio
                    </div>
                    <div class="text-xs text-indigo-300 font-medium">El espacio ideal para tu evento</div>
                </div>
            </a>
        </div>

        {{-- Tagline central --}}
        <div class="relative z-10">
            <h1 class="text-4xl xl:text-5xl font-black text-white leading-tight mb-6">
                El lugar perfecto<br>
                <span style="background:linear-gradient(135deg,#a78bfa,#60a5fa);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
                    para cada evento
                </span>
            </h1>
            <p class="text-indigo-200 text-lg leading-relaxed mb-10 max-w-md">
                Quintas, salones, espacios de eventos. Todo en un solo lugar. Reserva en segundos y paga de forma segura.
            </p>

            {{-- Features --}}
            <div class="space-y-4">
                <div class="flex items-center gap-3 text-white/80">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">Disponibilidad en tiempo real</span>
                </div>
                <div class="flex items-center gap-3 text-white/80">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">Reservas verificadas y seguras</span>
                </div>
                <div class="flex items-center gap-3 text-white/80">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <span class="text-sm font-medium">500+ espacios disponibles en Argentina</span>
                </div>
            </div>
        </div>

        {{-- Footer del panel --}}
        <div class="relative z-10 text-indigo-400 text-xs">
            © {{ date('Y') }} ReservaTuEspacio — Argentina
        </div>
    </div>

    {{-- Panel derecho: formulario --}}
    <div class="w-full lg:w-1/2 xl:w-2/5 flex flex-col justify-center px-6 sm:px-12 lg:px-16 xl:px-20 py-12">

        {{-- Logo mobile --}}
        <div class="lg:hidden flex items-center gap-3 mb-10">
            <div class="w-10 h-10 relative flex-shrink-0">
                <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <rect width="48" height="48" rx="14" fill="#4338ca"/>
                    <path d="M24 10L8 22H12V38H22V30H26V38H36V22H40L24 10Z" fill="white" fill-opacity="0.9"/>
                    <rect x="20" y="30" width="8" height="8" rx="1" fill="#4338ca"/>
                    <rect x="14" y="24" width="6" height="5" rx="1" fill="#4338ca"/>
                    <rect x="28" y="24" width="6" height="5" rx="1" fill="#4338ca"/>
                    <circle cx="37" cy="13" r="8" fill="#10b981"/>
                    <path d="M33.5 13L36 15.5L40.5 10.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <div class="text-xl font-black text-gray-900">
                Reserva<span class="text-indigo-600">Tu</span>Espacio
            </div>
        </div>

        {{-- Contenido del slot (form) --}}
        <div class="w-full max-w-sm mx-auto lg:mx-0">
            {{ $slot }}
        </div>

        {{-- Link volver --}}
        <div class="w-full max-w-sm mx-auto lg:mx-0 mt-8 pt-8 border-t border-gray-200">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-indigo-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al inicio
            </a>
        </div>
    </div>

</div>

</body>
</html>
