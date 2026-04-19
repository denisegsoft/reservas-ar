@auth
@php
    $user    = auth()->user();
    $isOwner = $user->isOwner() || $user->isAdmin();
    $isAdmin = $user->isAdmin();
    $unread  = $user->unreadMessagesCount();
    $pendingReservations = $isOwner ? \App\Models\Reservation::forOwner($user)->where('status', 'pending')->count() : 0;

    if (!function_exists('sidebarNavItem')) {
        function sidebarNavItem(string $label, string $routeName, string $svgPath, ?int $badge = null): string {
            $active  = request()->routeIs($routeName);
            $url     = route($routeName);
            $bgColor = $active ? 'rgba(255,255,255,0.15)' : 'transparent';
            $color   = $active ? '#ffffff' : 'rgba(199,210,254,0.85)';
            $weight  = $active ? '600' : '400';
            $bar     = $active
                ? '<span style="width:3px;height:18px;background:#818cf8;border-radius:2px;margin-left:auto;flex-shrink:0"></span>'
                : '';
            $bHtml   = $badge
                ? "<span style=\"margin-left:auto;background:#ef4444;color:#fff;font-size:10px;font-weight:700;min-width:18px;height:18px;border-radius:9px;display:flex;align-items:center;justify-content:center;padding:0 4px;flex-shrink:0\">{$badge}</span>"
                : '';
            $icon = "<svg style=\"width:17px;height:17px;flex-shrink:0\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"1.75\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"{$svgPath}\"/></svg>";
            return "<a href=\"{$url}\"
                style=\"display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;
                        text-decoration:none;font-size:13.5px;font-weight:{$weight};
                        color:{$color};background:{$bgColor};
                        transition:background .15s,color .15s\"
                onmouseenter=\"if(this.dataset.active!='1'){this.style.background='rgba(255,255,255,0.08)';this.style.color='#fff'}\"
                onmouseleave=\"if(this.dataset.active!='1'){this.style.background='transparent';this.style.color='rgba(199,210,254,0.85)'}\"
                data-active=\"" . ($active ? '1' : '0') . "\"
            >{$icon}<span style=\"flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis\">{$label}</span>{$bHtml}{$bar}</a>";
        }
    }

    function sidebarLabel(string $text, string $color = 'rgba(129,140,248,0.5)'): string {
        return "<p style=\"font-size:10px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;
                           color:{$color};padding:0 10px;margin:0 0 5px\">{$text}</p>";
    }
@endphp

<div style="display:flex;flex-direction:column;height:100%">

    {{-- ── Header ── --}}
    <div style="padding:14px 12px 12px;border-bottom:1px solid rgba(255,255,255,0.08);flex-shrink:0">
        <div style="display:flex;align-items:center;gap:10px">
            <div style="position:relative;flex-shrink:0">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->full_name }}"
                     style="width:38px;height:38px;border-radius:9px;object-fit:cover;border:1.5px solid rgba(255,255,255,0.2)">
                <span style="position:absolute;bottom:-2px;right:-2px;width:9px;height:9px;
                             background:#34d399;border-radius:50%;border:2px solid #1e1b4b"></span>
            </div>
            <div style="min-width:0;flex:1">
                <p style="color:#fff;font-size:13px;font-weight:600;
                           white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
                           margin:0;line-height:1.3;display:flex;align-items:center;gap:4px">
                    {{ $user->full_name }}
                    @if($user->hasSubscription())
                    <svg style="width:12px;height:12px;flex-shrink:0;color:#fbbf24" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    @endif
                </p>
                <span style="font-size:11px;font-weight:500;
                    color:{{ $isAdmin ? '#fbbf24' : ($isOwner ? '#a5b4fc' : '#94a3b8') }}">
                    {{ $isAdmin ? 'Administrador' : ($isOwner ? 'Propietario' : 'Cliente') }}
                </span>
            </div>
            {{-- Botón cerrar sidebar --}}
            <button onclick="sidebarClose()"
                    title="Cerrar panel"
                    style="flex-shrink:0;width:26px;height:26px;border-radius:7px;
                           display:flex;align-items:center;justify-content:center;
                           color:rgba(165,180,252,0.5);background:transparent;
                           border:none;cursor:pointer;transition:background .15s,color .15s"
                    onmouseenter="this.style.background='rgba(255,255,255,0.1)';this.style.color='#fff'"
                    onmouseleave="this.style.background='transparent';this.style.color='rgba(165,180,252,0.5)'">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── Navegación ── --}}
    <nav style="flex:1;padding:10px 8px;display:flex;flex-direction:column;gap:18px;overflow-y:auto">

        <div>
            {!! sidebarLabel('Mi cuenta') !!}
            <div style="display:flex;flex-direction:column;gap:1px">
                {!! sidebarNavItem('Mis Datos', 'profile.edit', 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z') !!}
                {!! sidebarNavItem('Favoritos', 'favorites.index', 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z') !!}
                {!! sidebarNavItem('Mensajes', 'messages.index', 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z', $unread ?: null) !!}
                 {!! sidebarNavItem('Reservas', 'reservations.index', 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z') !!}
            </div>
        </div>


        <div>
            {!! sidebarLabel('Propietario') !!}
            <div style="display:flex;flex-direction:column;gap:1px">
                {!! sidebarNavItem('Panel', 'owner.dashboard', 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6') !!}
                 @php $pub = request()->routeIs('owner.properties.create'); @endphp
                <a href="{{ route('owner.properties.create') }}"
                   style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:9px;
                          text-decoration:none;font-size:13.5px;margin-top:6px;
                          background:rgba(99,102,241,{{ $pub ? '.3' : '.12' }});
                          color:{{ $pub ? '#fff' : '#c7d2fe' }};font-weight:{{ $pub ? '600' : '400' }};
                          transition:background .15s,color .15s"
                   onmouseenter="this.style.background='rgba(99,102,241,0.28)';this.style.color='#fff'"
                   onmouseleave="this.style.background='rgba(99,102,241,{{ $pub ? '.3' : '.12' }})';this.style.color='{{ $pub ? '#fff' : '#c7d2fe' }}'">
                    <span style="width:17px;height:17px;flex-shrink:0;background:rgba(255,255,255,0.18);
                                 border-radius:5px;display:flex;align-items:center;justify-content:center;
                                 color:#fff;font-size:15px;font-weight:700;line-height:1">+</span>
                    <span style="flex:1;white-space:nowrap">Publicar Propiedad</span>
                </a>


                {!! sidebarNavItem('Mis Propiedades', 'owner.properties.index', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4') !!}
                {!! sidebarNavItem('Reservas recibidas', 'owner.reservations', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', $pendingReservations ?: null) !!}
                {!! sidebarNavItem('Crear reserva', 'owner.reservations.create', 'M12 4v16m8-8H4') !!}

            </div>
        </div>


        @if($isAdmin)
        <div>
            {!! sidebarLabel('Administración', 'rgba(251,191,36,0.55)') !!}
            <div style="display:flex;flex-direction:column;gap:1px">
                {!! sidebarNavItem('Soporte', 'admin.support', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z') !!}
                {!! sidebarNavItem('Sugerencias', 'admin.suggestions', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z') !!}
                {!! sidebarNavItem('Panel Admin', 'admin.dashboard', 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z') !!}
            </div>
        </div>
        @endif

    </nav>

    {{-- ── Footer ── --}}
    <div style="border-top:1px solid rgba(255,255,255,0.08);padding:8px;flex-shrink:0">
         {!! sidebarNavItem('Sugerencias', 'suggestions.create', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z') !!}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="width:100%;display:flex;align-items:center;gap:10px;padding:8px 10px;
                           border-radius:9px;font-size:13.5px;color:rgba(252,165,165,0.7);
                           background:transparent;border:none;cursor:pointer;text-align:left;
                           transition:background .15s,color .15s"
                    onmouseenter="this.style.background='rgba(239,68,68,0.12)';this.style.color='#fca5a5'"
                    onmouseleave="this.style.background='transparent';this.style.color='rgba(252,165,165,0.7)'">
                <svg style="width:17px;height:17px;flex-shrink:0" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
                <span style="white-space:nowrap">Cerrar Sesión</span>
            </button>
        </form>
    </div>

</div>
@endauth
