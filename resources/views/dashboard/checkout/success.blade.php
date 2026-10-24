@extends('layouts.app')

@section('title', '¡Slot Adquirido con Éxito! — Un Minuto Para Vender')

@section('styles')
<style>
    .success-card {
        max-width: 680px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 16px;
        padding: 2.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        text-align: center;
        border: 1px solid #e2e8f0;
    }

    .success-icon-wrap {
        width: 72px;
        height: 72px;
        background: #ecfdf5;
        border: 2px solid #10b981;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        color: #10b981;
        font-size: 2.2rem;
    }

    .slot-time-badge {
        background: #021230;
        color: #fff;
        padding: 1.25rem;
        border-radius: 12px;
        margin: 1.5rem 0;
        border: 2px solid var(--brand-deep-navy);
    }

    .live-pulsing-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #fee2e2;
        color: #ef4444;
        font-weight: 900;
        font-size: 0.85rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        margin-bottom: 0.5rem;
        animation: pulse 1.5s infinite;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.8; transform: scale(1.04); }
    }
</style>
@endsection

@section('content')
<div class="success-card">
    <div class="success-icon-wrap">
        ✓
    </div>

    <span style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #10b981;">
        Mercado Pago Sandbox &bull; Transacción Aprobada
    </span>

    <h1 style="font-size: 1.75rem; font-weight: 900; color: var(--brand-deep-navy); margin: 0.5rem 0 0.75rem;">
        ¡Slot de 60 Segundos Asignado!
    </h1>

    <p style="color: #64748b; font-size: 0.95rem; line-height: 1.5;">
        El anuncio <strong>"{{ $advertisement->title }}"</strong> de <strong>{{ $advertisement->business->name }}</strong> ha sido encolado y programado de manera atómica en la región de <strong>{{ $advertisement->region->name }}</strong>.
    </p>

    @if ($slot)
        @php
            $isLiveNow = $slot->starts_at->lessThanOrEqualTo(now()) && $slot->ends_at->greaterThan(now());
        @endphp

        <div class="slot-time-badge">
            @if ($isLiveNow)
                <div class="live-pulsing-badge">
                    <span>🔴</span> ¡EN VIVO EN ESTE MOMENTO!
                </div>
            @else
                <div style="font-size: 0.8rem; color: var(--brand-secondary-orange); font-weight: 800; text-transform: uppercase; margin-bottom: 0.35rem;">
                    Turno en Cola FIFO Asignado
                </div>
            @endif

            <div style="font-size: 1.4rem; font-weight: 900; color: #fff; margin-top: 0.25rem;">
                {{ $slot->starts_at->format('H:i:s') }} &mdash; {{ $slot->ends_at->format('H:i:s') }}
            </div>

            <div style="font-size: 0.85rem; color: #94a3b8; margin-top: 0.35rem;">
                Duración: Exactamente 60 segundos &bull; Región: {{ $advertisement->region->name }}
            </div>
        </div>
    @endif

    @if ($latestPayment)
        <div style="display: flex; justify-content: space-between; font-size: 0.8rem; color: #64748b; margin-bottom: 1.5rem; padding: 0.5rem 1rem; background: #f8fafc; border-radius: 8px;">
            <span>ID Pago: <code>{{ $latestPayment->provider_payment_id }}</code></span>
            <span>Monto Pagado: <strong>${{ number_format($latestPayment->amount, 2) }} {{ $latestPayment->currency }}</strong></span>
        </div>
    @endif

    <div style="display: flex; flex-direction: column; gap: 0.75rem; margin-top: 1rem;">
        <a href="{{ route('viewer.region', $advertisement->region->slug) }}" class="btn-primary" style="padding: 1rem; font-size: 1.05rem; justify-content: center; background: var(--brand-primary-orange); box-shadow: 0 8px 24px rgba(240, 85, 0, 0.45);" target="_blank">
            🔴 Ver Mi Anuncio en la Página Principal (Visor en Vivo) →
        </a>

        <a href="{{ route('dashboard') }}" class="btn-secondary" style="justify-content: center; padding: 0.75rem;">
            Volver al Panel de Anuncios
        </a>
    </div>
</div>
@endsection
