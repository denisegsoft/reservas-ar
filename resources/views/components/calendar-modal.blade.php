{{--
    Shared calendar modal for reservation availability.
    Props:
      $title      — header title (default "Disponibilidad")
      $locked     — (bool) show subscription-required message instead of calendar (default false)
      $footerLink — (optional) ['href' => '...', 'label' => '...'] shown left of Cerrar button
--}}
@props([
    'title'      => 'Disponibilidad',
    'locked'     => false,
    'footerLink' => null,
])

<div class="modal fade" id="calendarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border:none;border-radius:1.25rem;overflow:hidden;box-shadow:0 25px 50px -12px rgba(0,0,0,0.25)">
            <div class="modal-header" style="border-bottom:1px solid #f3f4f6;padding:1.25rem 1.5rem">
                <div>
                    <h5 class="modal-title" style="font-size:1rem;font-weight:700;color:#111827;margin:0">{{ $title }}</h5>
                    <p id="cal-nombre" style="font-size:.8rem;color:#6b7280;margin:0"></p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="padding:1.5rem">
                @if($locked)
                <div style="text-align:center;padding:2rem 1rem">
                    <div style="width:56px;height:56px;background:#eef2ff;border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem">
                        <svg style="width:28px;height:28px;color:#6366f1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <p style="color:#111827;font-weight:700;font-size:1rem;margin-bottom:.5rem">Suscripción requerida</p>
                    <p style="color:#6b7280;font-size:.875rem;margin-bottom:1.5rem">Para ver las reservas recibidas necesitás activar tu cuenta de propietario.</p>
                    <a href="{{ route('subscription.payment') }}"
                       style="display:inline-flex;align-items:center;gap:8px;background:#4f46e5;color:#fff;font-weight:600;padding:.75rem 1.5rem;border-radius:.75rem;text-decoration:none;font-size:.875rem">
                        Activar suscripción
                    </a>
                </div>
                @else
                <div id="cal-calendar"></div>
                <div id="cal-detail"></div>
                @endif
            </div>
            <div class="modal-footer" style="border-top:1px solid #f3f4f6;padding:.75rem 1.5rem">
                @if($footerLink)
                <a href="{{ $footerLink['href'] }}" style="font-size:.8rem;color:#4f46e5;font-weight:600;text-decoration:none">
                    {{ $footerLink['label'] }}
                </a>
                @endif
                <button type="button" class="btn btn-sm" data-bs-dismiss="modal" style="margin-left:auto;background:#f3f4f6;border:none;border-radius:10px;padding:6px 16px;font-size:.8rem">Cerrar</button>
            </div>
        </div>
    </div>
</div>
