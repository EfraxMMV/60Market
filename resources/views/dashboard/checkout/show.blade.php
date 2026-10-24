@extends('layouts.app')

@section('title', 'Comprar Slot de Emisión — Mercado Pago Sandbox')

@section('styles')
<style>
    .checkout-container {
        max-width: 960px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 860px) {
        .checkout-container {
            grid-template-columns: 1fr;
        }
    }

    .mp-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: #009EE3;
        color: #fff;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-weight: 800;
        font-size: 0.8rem;
        letter-spacing: 0.02em;
    }

    .sandbox-tag {
        background: #fef08a;
        color: #854d0e;
        border: 1px solid #fde047;
        font-size: 0.75rem;
        font-weight: 800;
        padding: 0.2rem 0.55rem;
        border-radius: 6px;
        text-transform: uppercase;
    }

    .price-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px dashed #e2e8f0;
        font-size: 0.92rem;
    }

    .price-row.total {
        border-top: 2px solid var(--brand-deep-navy);
        border-bottom: none;
        margin-top: 0.5rem;
        padding-top: 1rem;
        font-weight: 900;
        font-size: 1.25rem;
        color: var(--brand-deep-navy);
    }

    .queue-status-card {
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        border-radius: 10px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .queue-status-icon {
        font-size: 1.8rem;
    }

    .test-card-box {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        padding: 1rem;
        margin-top: 1rem;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="card-header-flex" style="margin-bottom: 2rem;">
    <div>
        <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.35rem;">
            <span class="mp-badge">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M21 7.28V5c0-1.1-.9-2-2-2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-2.28c.59-.35 1-.98 1-1.72V9c0-.74-.41-1.37-1-1.72zM20 9v6h-7V9h7zM5 19V5h14v2h-6c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h6v2H5z"/>
                </svg>
                Mercado Pago
            </span>
            <span class="sandbox-tag">Sandbox de Pruebas</span>
        </div>
        <h1 class="page-title">Adquisición de Slot Publicitario (60s)</h1>
        <p class="page-subtitle">Confirma tu pedido y programa tu turno en la cola regional de Un Minuto Para Vender.</p>
    </div>
    <div>
        <a href="{{ route('ads.edit', $advertisement) }}" class="btn-secondary">
            ← Volver al Editor
        </a>
    </div>
</div>

@if ($errors->has('payment_error'))
    <div style="background: #fee2e2; border: 1px solid #f87171; color: #b91c1c; padding: 1rem; border-radius: 10px; margin-bottom: 1.5rem; font-weight: 600;">
        ⚠️ {{ $errors->first('payment_error') }}
    </div>
@endif

<div class="checkout-container">
    <!-- Columna Izquierda: Detalles del Creativo y Cola -->
    <div>
        <!-- Estado de la Cola FIFO Regional -->
        <div class="queue-status-card">
            <div class="queue-status-icon">⏱️</div>
            <div>
                <div style="font-weight: 800; color: #1e3a8a; font-size: 0.95rem;">
                    Región de Emisión: <strong>{{ $advertisement->region->name }} ({{ $advertisement->region->state }})</strong>
                </div>
                <div style="font-size: 0.85rem; color: #3b82f6; margin-top: 0.2rem;">
                    @if ($queueStatus['total_pending_slots'] === 0)
                        🟢 <strong>Cola libre:</strong> Tu anuncio comenzará a transmitirse <strong>inmediatamente</strong> tras la confirmación.
                    @else
                        🟡 Hay <strong>{{ $queueStatus['total_pending_slots'] }}</strong> slot(s) en curso o programados. Tu turno iniciará en aprox. <strong>{{ $queueStatus['estimated_wait_seconds'] }}</strong> segundos.
                    @endif
                </div>
            </div>
        </div>

        <!-- Resumen del Anuncio -->
        <div class="card" style="padding: 1.5rem; margin-bottom: 1.5rem;">
            <h3 style="font-size: 1.05rem; font-weight: 800; color: var(--brand-deep-navy); margin-bottom: 1rem;">
                📢 Resumen del Anuncio a Emitir
            </h3>

            <div style="display: grid; grid-template-columns: 120px 1fr; gap: 1.25rem; align-items: start;">
                <div style="width: 120px; height: 90px; background: #021230; border-radius: 8px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    @if ($advertisement->content_type === 'html')
                        <div style="text-align: center; color: #fff; font-size: 0.7rem; font-weight: 700; padding: 0.5rem;">
                            <div style="font-size: 1.5rem;">✨</div>
                            HTML IA
                        </div>
                    @elseif ($advertisement->content_type === 'image' && $advertisement->assets->first())
                        <img src="{{ asset('storage/' . $advertisement->assets->first()->storage_key) }}" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                    @elseif ($advertisement->content_type === 'video')
                        <div style="text-align: center; color: #fff; font-size: 0.7rem; font-weight: 700;">
                            <div style="font-size: 1.5rem;">🎬</div>
                            Video
                        </div>
                    @else
                        <div style="font-size: 1.5rem;">📢</div>
                    @endif
                </div>

                <div>
                    <div style="font-size: 0.75rem; font-weight: 800; color: var(--brand-secondary-orange); text-transform: uppercase;">
                        {{ $advertisement->business->name }}
                    </div>
                    <div style="font-size: 1.1rem; font-weight: 800; color: var(--brand-deep-navy); margin: 0.2rem 0;">
                        {{ $advertisement->title }}
                    </div>
                    <div style="font-size: 0.8rem; color: #64748b;">
                        Tipo: <strong style="text-transform: uppercase;">{{ $advertisement->content_type }}</strong> &bull; Duración: <strong>60 segundos</strong>
                    </div>
                    <div style="font-size: 0.78rem; color: #0284c7; margin-top: 0.35rem; word-break: break-all;">
                        🔗 Destino: {{ $advertisement->destination_url }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Sandbox -->
        <div class="test-card-box">
            <div style="display: flex; align-items: center; gap: 0.4rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem;">
                💳 Entorno Sandbox Simulado (Sin Cobros Reales)
            </div>
            <p style="color: #64748b; margin-bottom: 0.5rem;">
                Este entorno simula la API de Mercado Pago / Mercado Libre. No se realizarán cargos a ninguna cuenta bancaria o tarjeta real. Al confirmar, el sistema validará el pago y asignará el slot en la cola FIFO regional inmediatamente.
            </p>
            <div style="background: #fff; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px dashed #cbd5e1; font-family: monospace; font-size: 0.8rem;">
                Tarjeta de Prueba: 4242 &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; 4242 | Exp: 12/28 | CVV: 123
            </div>
        </div>
    </div>

    <!-- Columna Derecha: Desglose de Precios y Formulario de Pago -->
    <div>
        <div class="card" style="padding: 1.75rem;">
            <h3 style="font-size: 1.15rem; font-weight: 800; color: var(--brand-deep-navy); margin-bottom: 1.25rem;">
                Detalle de Facturación
            </h3>

            @php
                $pricing = $preference['pricing'];
            @endphp

            <div class="price-row">
                <span>Slot Publicitario (60 segundos)</span>
                <strong>${{ number_format($pricing['slot_price'], 2) }} MXN</strong>
            </div>

            @if ($pricing['ai_base_price'] > 0)
                <div class="price-row">
                    <span>
                        Generación con IA (HTML Nativo)
                        <br><small style="color: #64748b; font-size: 0.75rem;">1 cobro por anuncio en sesión</small>
                    </span>
                    <strong>${{ number_format($pricing['ai_base_price'], 2) }} MXN</strong>
                </div>
            @endif

            @if ($pricing['ai_recharge_price'] > 0)
                <div class="price-row" style="background: #fef2f2; padding-left: 0.5rem; padding-right: 0.5rem; border-radius: 6px;">
                    <span style="color: #991b1b;">
                        Recargo por Reedición con IA
                        <br><small style="color: #b91c1c; font-size: 0.75rem;">Reedición tras borrador guardado</small>
                    </span>
                    <strong style="color: #991b1b;">${{ number_format($pricing['ai_recharge_price'], 2) }} MXN</strong>
                </div>
            @endif

            <div class="price-row total">
                <span>Total a Pagar:</span>
                <span style="color: var(--brand-primary-orange);">${{ number_format($pricing['total'], 2) }} MXN</span>
            </div>

            <form method="POST" action="{{ route('ads.checkout.process', $advertisement) }}" style="margin-top: 1.75rem;" id="checkoutForm">
                @csrf
                <input type="hidden" name="simulated_outcome" id="simulated_outcome" value="approved">

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 1rem; font-size: 1rem; background: #009EE3; border-color: #009EE3; box-shadow: 0 4px 14px rgba(0, 158, 227, 0.4);">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor">
                        <path d="M21 7.28V5c0-1.1-.9-2-2-2H5c-1.11 0-2 .9-2 2v14c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2v-2.28c.59-.35 1-.98 1-1.72V9c0-.74-.41-1.37-1-1.72zM20 9v6h-7V9h7zM5 19V5h14v2h-6c-1.1 0-2 .9-2 2v6c0 1.1.9 2 2 2h6v2H5z"/>
                    </svg>
                    Pagar y Programar Slot (${{ number_format($pricing['total'], 2) }} MXN)
                </button>

                <div style="margin-top: 1rem; text-align: center;">
                    <button type="button" onclick="simulateFailure()" style="background: none; border: none; color: #94a3b8; font-size: 0.78rem; text-decoration: underline; cursor: pointer;">
                        (Prueba: Simular pago rechazado en Sandbox)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function simulateFailure() {
        if (confirm('¿Deseas simular un rechazo de pago en Sandbox para comprobar la respuesta del sistema?')) {
            document.getElementById('simulated_outcome').value = 'rejected';
            document.getElementById('checkoutForm').submit();
        }
    }
</script>
@endsection
