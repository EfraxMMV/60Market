<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Un Minuto Para Vender</title>
    <meta name="description"
        content="Plataforma de anuncios locales en vivo de exactamente 60 segundos con compra directa.">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon con el logotipo oficial -->
    <link rel="icon" type="image/png" href="{{ asset('images/60Logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        :root {
            /* Paleta oficial corporativa */
            --brand-primary-orange: #F05500;
            --brand-secondary-orange: #E8955D;
            --brand-pure-white: #FEFEFE;
            --brand-slate-blue: #4C6189;
            --brand-deep-navy: #032765;

            /* Fondos y superficies derivados */
            --bg-canvas: rgba(2, 18, 48, 0.92);
            --header-bg: #FEFEFE;
            --dock-bg: rgba(2, 22, 58, 0.96);
            --border-subtle: rgba(76, 97, 137, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html,
        body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--brand-deep-navy);
            color: var(--brand-pure-white);
        }

        /* Contenedor general que ocupa el 100% del viewport */
        .app-viewport {
            display: flex;
            flex-direction: column;
            width: 100vw;
            height: 100vh;
            position: relative;
        }

        /* -------------------------------------------------------------
           1. ENCABEZADO SUPERIOR (Header compacto de 64px)
           ------------------------------------------------------------- */
        header {
            height: 64px;
            min-height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.5rem;
            background: var(--header-bg);
            border-bottom: 1px solid var(--border-subtle);
            backdrop-filter: blur(14px);
            z-index: 40;
            position: relative;
        }

        .brand-wrapper {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-logo-img {
            height: 44px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 2px 6px rgba(0, 0, 0, 0.3));
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--brand-deep-navy);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .brand-title span {
            color: var(--brand-primary-orange);
        }

        .header-center {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: var(--brand-deep-navy);
        }

        .region-select {
            background: rgba(2, 18, 48, 0.8);
            border: 1px solid var(--brand-deep-navy);
            color: var(--brand-pure-white);
            padding: 0.4rem 0.8rem;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            outline: none;
            transition: all 0.2s ease;
        }

        .region-select:hover,
        .region-select:focus {
            border-color: var(--brand-secondary-orange);
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn-publish {
            background: rgba(240, 85, 0, 0.12);
            color: var(--brand-secondary-orange);
            border: 1px solid rgba(240, 85, 0, 0.4);
            padding: 0.45rem 0.9rem;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.85rem;
            text-decoration: none;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .btn-publish:hover {
            background: var(--brand-primary-orange);
            color: var(--brand-pure-white);
            border-color: var(--brand-primary-orange);
        }

        /* -------------------------------------------------------------
           2. ÁREA DEL ANUNCIO (Cubre toda la pantalla entre header y dock)
           ------------------------------------------------------------- */
        main.ad-stage {
            flex: 1;
            width: 100%;
            height: calc(100vh - 64px - 84px);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, var(--bg-canvas) 0%, var(--brand-slate-blue) 75%);
            overflow: hidden;
        }

        .ad-content-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Creativos optimizados y siempre centrados */
        .ad-stage-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
            margin: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        .ad-stage-video {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            display: block;
            margin: auto;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.4);
        }

        .ad-image-container {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        /* Creativo HTML / Dinámico */
        .ad-html-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        /* Banner de información superpuesta sobre el anuncio (Sutil y elegante) */
        .ad-floating-banner {
            position: absolute;
            top: 1.5rem;
            left: 1.5rem;
            background: rgba(3, 39, 101, 0.75);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(232, 149, 93, 0.25);
            padding: 0.75rem 1.25rem;
            border-radius: 12px;
            max-width: 480px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
            z-index: 10;
            animation: fadeIn 0.4s ease;
        }

        .ad-floating-business {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--brand-secondary-orange);
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .ad-floating-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--brand-pure-white);
            margin-top: 0.2rem;
            line-height: 1.25;
        }

        .ad-floating-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.35rem;
            margin-top: 0.5rem;
        }

        .ad-tag-chip {
            background: rgba(76, 97, 137, 0.4);
            border: 1px solid rgba(76, 97, 137, 0.6);
            color: #e2e8f0;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
        }

        /* Pantalla de espera cuando la cola está vacía */
        .empty-queue-display {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2rem;
            max-width: 560px;
        }

        .empty-icon {
            width: 72px;
            height: 72px;
            margin-bottom: 1.25rem;
            color: var(--brand-secondary-orange);
            opacity: 0.9;
        }

        .empty-queue-display h2 {
            font-size: 1.75rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
            color: var(--brand-pure-white);
        }

        .empty-queue-display p {
            color: #94a3b8;
            font-size: 1rem;
            line-height: 1.5;
            margin-bottom: 1.5rem;
        }

        /* ── CREATIVO FALLBACK (cola vacía → pre-registro) ─────────────── */
        .fallback-stage {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 2.5rem 1.5rem;
            width: 100%;
            height: 100%;
            background: radial-gradient(ellipse at 60% 40%, rgba(240, 85, 0, 0.13) 0%, transparent 70%),
                radial-gradient(ellipse at 30% 70%, rgba(76, 97, 137, 0.18) 0%, transparent 60%);
            position: relative;
            overflow: hidden;
        }

        .fallback-stage::before {
            content: '';
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(-45deg,
                    transparent,
                    transparent 48px,
                    rgba(240, 85, 0, 0.04) 48px,
                    rgba(240, 85, 0, 0.04) 50px);
            pointer-events: none;
        }

        .fallback-logo-ring {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--brand-primary-orange) 0%, #c44100 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
            box-shadow: 0 0 0 12px rgba(240, 85, 0, 0.12), 0 0 0 24px rgba(240, 85, 0, 0.05);
            animation: ring-pulse 3s ease-in-out infinite;
        }

        @keyframes ring-pulse {

            0%,
            100% {
                box-shadow: 0 0 0 12px rgba(240, 85, 0, 0.12), 0 0 0 24px rgba(240, 85, 0, 0.05);
            }

            50% {
                box-shadow: 0 0 0 18px rgba(240, 85, 0, 0.08), 0 0 0 34px rgba(240, 85, 0, 0.03);
            }
        }

        .fallback-logo-ring img {
            width: 56px;
            height: 56px;
            object-fit: contain;
            filter: brightness(0) invert(1);
        }

        .fallback-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            background: rgba(240, 85, 0, 0.15);
            border: 1px solid rgba(240, 85, 0, 0.4);
            color: var(--brand-secondary-orange);
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            padding: 0.3rem 0.8rem;
            border-radius: 9999px;
            margin-bottom: 1rem;
        }

        .fallback-title {
            font-size: clamp(1.8rem, 4vw, 3rem);
            font-weight: 900;
            line-height: 1.1;
            color: var(--brand-pure-white);
            margin-bottom: 1.1rem;
            letter-spacing: -0.03em;
        }

        .fallback-title .highlight {
            color: var(--brand-primary-orange);
            display: inline-block;
        }

        .fallback-subtitle {
            font-size: clamp(1rem, 2vw, 1.25rem);
            color: #94a3b8;
            line-height: 1.6;
            max-width: 540px;
            margin-bottom: 2rem;
        }

        .btn-notify {
            background: var(--brand-primary-orange);
            color: var(--brand-pure-white);
            border: none;
            padding: 1rem 2.5rem;
            border-radius: 9999px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            box-shadow: 0 8px 24px -4px rgba(240, 85, 0, 0.65), 0 3px 10px rgba(0, 0, 0, 0.35);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            text-decoration: none;
            letter-spacing: -0.01em;
        }

        .btn-notify:hover {
            background: #d84d00;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 32px -4px rgba(240, 85, 0, 0.8), 0 5px 14px rgba(0, 0, 0, 0.45);
        }

        .btn-notify:active {
            transform: translateY(0) scale(0.98);
        }

        .fallback-secondary-link {
            display: inline-block;
            margin-top: 0.9rem;
            font-size: 0.85rem;
            color: var(--brand-slate-blue);
            text-decoration: underline;
            text-underline-offset: 3px;
            transition: color 0.2s;
        }

        .fallback-secondary-link:hover {
            color: var(--brand-secondary-orange);
        }

        /* ── MODAL PRE-REGISTRO (Optimizado para Móviles) ──────────────── */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(2, 10, 30, 0.85);
            backdrop-filter: blur(8px);
            z-index: 200;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.75rem;
            /* Reducido ligeramente para aprovechar pantallas angostas */
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            overflow-y: auto;
            /* Permite hacer scroll en el overlay si el contenido desborda */
            -webkit-overflow-scrolling: touch;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal-card {
            background: var(--brand-deep-navy);
            border: 1px solid rgba(240, 85, 0, 0.25);
            border-radius: 20px;
            padding: 2rem 1.5rem;
            /* Padding adaptado para móviles */
            max-width: 440px;
            width: 100%;
            position: relative;
            box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6), 0 0 0 1px rgba(240, 85, 0, 0.1);
            transform: translateY(16px);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);

            /* LÓGICA DE RESPONSIVIDAD MÓVIL (EVITA DESAJUSTES) */
            max-height: 88vh;
            max-height: 88dvh;
            /* Altura dinámica para barras de navegación móviles */
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            /* Scroll interno en pantallas pequeñas o con teclado abierto */
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
        }

        .modal-overlay.active .modal-card {
            transform: translateY(0);
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: rgba(76, 97, 137, 0.25);
            border: none;
            color: #94a3b8;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 5;
        }

        .modal-close:hover {
            background: rgba(240, 85, 0, 0.2);
            color: var(--brand-secondary-orange);
        }

        .modal-icon {
            width: 52px;
            height: 52px;
            background: linear-gradient(135deg, var(--brand-primary-orange), #c44100);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .modal-title {
            font-size: 1.35rem;
            /* Ligeramente ajustado para evitar saltos de línea molestos en móviles */
            font-weight: 800;
            color: var(--brand-pure-white);
            margin-bottom: 0.4rem;
            letter-spacing: -0.02em;
        }

        .modal-subtitle {
            font-size: 0.88rem;
            color: #ffffffff;
            line-height: 1.5;
            margin-bottom: 1.25rem;
        }

        .modal-form-group {
            margin-bottom: 0.9rem;
        }

        .modal-label {
            display: block;
            font-size: 0.78rem;
            font-weight: 700;
            color: var(--brand-secondary-orange);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 0.4rem;
        }

        .modal-input {
            width: 100%;
            background: rgba(76, 97, 137, 0.15);
            border: 1px solid rgba(76, 97, 137, 0.35);
            color: var(--brand-pure-white);
            padding: 0.75rem 1rem;
            border-radius: 10px;
            font-size: 1rem;
            /* Fijo en 16px equivalentes para evitar zoom automático indeseado en iOS */
            font-family: inherit;
            outline: none;
            transition: border-color 0.2s, background 0.2s;
            box-sizing: border-box;
        }

        .modal-input:focus {
            border-color: var(--brand-primary-orange);
            background: rgba(240, 85, 0, 0.06);
        }

        .modal-input::placeholder {
            color: #64748b;
        }

        .modal-role-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.6rem;
            margin-bottom: 1.1rem;
        }

        .role-btn {
            background: rgba(76, 97, 137, 0.15);
            border: 1.5px solid rgba(76, 97, 137, 0.35);
            color: #94a3b8;
            padding: 0.65rem 0.5rem;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-align: center;
            transition: all 0.2s;
            font-family: inherit;
            white-space: nowrap;
            /* Evita que quiebren mal los textos de los roles */
        }

        .role-btn.active,
        .role-btn:hover {
            border-color: var(--brand-primary-orange);
            background: rgba(240, 85, 0, 0.12);
            color: var(--brand-secondary-orange);
        }

        .modal-submit {
            width: 100%;
            background: var(--brand-primary-orange);
            color: var(--brand-pure-white);
            border: none;
            padding: 0.9rem 1rem;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 800;
            cursor: pointer;
            font-family: inherit;
            transition: all 0.2s;
            letter-spacing: -0.01em;
            margin-top: 0.5rem;
        }

        .modal-submit:hover {
            background: #d84d00;
            transform: translateY(-1px);
        }

        .modal-submit:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .modal-feedback {
            margin-top: 0.75rem;
            font-size: 0.88rem;
            text-align: center;
            min-height: 1.2em;
            border-radius: 8px;
            padding: 0.5rem;
        }

        .modal-feedback.success {
            color: #4ade80;
            background: rgba(74, 222, 128, 0.08);
        }

        .modal-feedback.error {
            color: #f87171;
            background: rgba(248, 113, 113, 0.08);
        }

        .modal-legal {
            margin-top: 0.75rem;
            font-size: 0.75rem;
            color: #475569;
            text-align: center;
            line-height: 1.4;
        }

        /* -------------------------------------------------------------
           3. DOCK INFERIOR (Contador por debajo del todo + Botón Comprar)
           ------------------------------------------------------------- */
        footer.bottom-dock {
            height: 84px;
            min-height: 84px;
            background: var(--brand-pure-white);
            border-top: 1px solid var(--brand-deep-navy);
            backdrop-filter: blur(16px);
            z-index: 40;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        /* Barra de progreso de 60 segundos superior al dock */
        .timer-progress-track {
            width: 100%;
            height: 5px;
            background: rgba(2, 18, 48, 0.9);
            position: absolute;
            top: 0;
            left: 0;
        }

        .timer-progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--brand-primary-orange), var(--brand-secondary-orange));
            width: 100%;
            transition: width 1s linear;
            box-shadow: 0 0 10px rgba(240, 85, 0, 0.6);
        }

        .dock-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 2rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
            gap: 1.5rem;
        }

        /* Bloque de tiempo y estado en vivo */
        .dock-left-timer {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .live-tag {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #f87171;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            padding: 0.35rem 0.65rem;
            border-radius: 9999px;
        }

        .live-pulse-dot {
            width: 7px;
            height: 7px;
            background: #ef4444;
            border-radius: 50%;
            animation: pulse-red 1.5s infinite;
        }

        @keyframes pulse-red {
            0% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
            }

            70% {
                transform: scale(1.15);
                box-shadow: 0 0 0 5px rgba(239, 68, 68, 0);
            }

            100% {
                transform: scale(0.9);
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
            }
        }

        .countdown-box {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            background: rgba(3, 39, 101, 0.8);
            border: 1px solid var(--brand-slate-blue);
            padding: 0.35rem 0.85rem;
            border-radius: 10px;
        }

        .countdown-number {
            font-size: 1.5rem;
            font-weight: 900;
            font-variant-numeric: tabular-nums;
            color: var(--brand-pure-white);
            line-height: 1;
        }

        .countdown-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--brand-secondary-orange);
            text-transform: uppercase;
        }

        /* Bloque central del dock: resumen de la oferta */
        .dock-center-info {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;
        }

        .dock-ad-business {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--brand-primary-orange);
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .dock-ad-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--brand-deep-navy);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* Bloque derecho del dock: Chip de estatus/región */
        .dock-right-status {
            display: flex;
            align-items: center;
        }

        .dock-region-chip {
            background: rgba(3, 39, 101, 0.07);
            color: var(--brand-deep-navy);
            border: 1px solid rgba(3, 39, 101, 0.16);
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            font-size: 0.82rem;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        /* Botón de compra flotante sobrepuesto en la parte inferior del anuncio y centrado */
        .ad-floating-action {
            position: absolute;
            bottom: 2.25rem;
            left: 50%;
            transform: translateX(-50%);
            z-index: 30;
            display: flex;
            align-items: center;
            justify-content: center;
            animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            pointer-events: auto;
        }

        .btn-comprar {
            background-color: var(--brand-primary-orange) !important;
            color: var(--brand-pure-white) !important;
            border: none;
            padding: 0.95rem 2.75rem;
            border-radius: 9999px;
            font-size: 1.2rem;
            font-weight: 900;
            letter-spacing: -0.01em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            cursor: pointer;
            box-shadow: 0 10px 30px -4px rgba(240, 85, 0, 0.7), 0 4px 14px rgba(0, 0, 0, 0.4);
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
            white-space: nowrap;
        }

        .btn-comprar:hover {
            background-color: #d84d00 !important;
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 14px 36px -4px rgba(240, 85, 0, 0.85), 0 6px 18px rgba(0, 0, 0, 0.5);
        }

        .btn-comprar:active {
            transform: translateY(0) scale(0.98);
        }

        .btn-comprar svg {
            width: 22px;
            height: 22px;
            transition: transform 0.2s ease;
        }

        .btn-comprar:hover svg {
            transform: translateX(4px);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Adaptabilidad en dispositivos móviles */
        @media (max-width: 768px) {
            header {
                padding: 0 1rem;
            }

            .brand-title {
                font-size: 1.05rem;
            }

            .dock-content {
                padding: 0 1rem;
                gap: 0.75rem;
            }

            .dock-center-info {
                display: none;
                /* Ocultar texto en móviles para priorizar timer y status */
            }

            .ad-floating-action {
                bottom: 1.25rem;
                width: 90%;
            }

            .btn-comprar {
                width: 100%;
                justify-content: center;
                padding: 0.85rem 1.5rem;
                font-size: 1.05rem;
            }

            .ad-floating-banner {
                top: 1rem;
                left: 1rem;
                right: 1rem;
                max-width: none;
            }
        }
    </style>
</head>

<body>

    <div class="app-viewport">

        <!-- 1. ENCABEZADO SUPERIOR -->
        <header>
            <a href="/" class="brand-wrapper">
                <img src="{{ asset('images/60Logo.png') }}" alt="Logo Un Minuto Para Vender" class="brand-logo-img">
                <span class="brand-title">
                    Un Minuto Para <span>Vender</span>
                </span>
            </a>

            <div class="header-center">
                <span>📍 Región:</span>
                <select class="region-select" id="regionSelector" onchange="changeRegion(this.value)">
                    @foreach ($availableRegions as $r)
                        <option value="{{ $r->slug }}" {{ $r->id === $currentRegion->id ? 'selected' : '' }}>
                            {{ $r->name }} ({{ $r->state }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="header-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn-publish">
                        <span>📊</span> Mi Panel
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn-publish">
                        <span>+</span> Publicar anuncio
                    </a>
                @endauth
            </div>
        </header>

        <!-- 2. ESCENARIO DEL ANUNCIO (Cubre toda la pantalla entre header y dock) -->
        <main class="ad-stage" id="adStage">
            @php
                $hasAd = $initialData['has_ad'] ?? false;
                $initAd = $hasAd ? $initialData['advertisement'] : null;
                $initSlot = $hasAd ? $initialData['slot'] : null;
            @endphp

            <!-- Banner flotante con metadatos del anuncio -->
            <div class="ad-floating-banner" id="adFloatingBanner"
                style="{{ $hasAd ? 'display: block;' : 'display: none;' }}">
                <span class="ad-floating-business"
                    id="floatingBusinessName">{{ $hasAd ? $initAd['business_name'] : '' }}</span>
                <h1 class="ad-floating-title" id="floatingTitle">{{ $hasAd ? $initAd['title'] : '' }}</h1>
                <div class="ad-floating-tags" id="floatingTags">
                    @if ($hasAd)
                        @foreach ($initAd['tags'] as $tag)
                            <span class="ad-tag-chip">#{{ $tag }}</span>
                        @endforeach
                    @endif
                </div>
            </div>

            <!-- Contenedor dinámico del anuncio a pantalla completa -->
            <div class="ad-content-wrapper" id="adContentWrapper">
                @if ($hasAd)
                    @if ($initAd['content_type'] === 'html' && !empty($initAd['generated_html']))
                        <div class="ad-html-container" style="width: 100%; height: 100%; padding: 0;">
                            <iframe srcdoc="{{ $initAd['generated_html'] }}"
                                style="width: 100%; height: 100%; border: none; overflow: hidden; pointer-events: none; display: block;"></iframe>
                        </div>
                    @elseif ($initAd['content_type'] === 'image' && count($initAd['assets']) > 0)
                        <div class="ad-image-container">
                            <img src="{{ $initAd['assets'][0]['url'] }}" alt="{{ $initAd['title'] }}" class="ad-stage-image">
                        </div>
                    @elseif ($initAd['content_type'] === 'video' && count($initAd['assets']) > 0)
                        <div class="ad-image-container">
                            <video src="{{ $initAd['assets'][0]['url'] }}" autoplay muted loop playsinline
                                class="ad-stage-video"></video>
                        </div>
                    @endif
                @else
                    {{-- Creativo Fallback: cola vacía → "Próximamente Un Minuto Para Vender" --}}
                    <div class="fallback-stage" id="fallbackStage">
                        <div class="fallback-logo-ring">
                            <img src="{{ asset('images/60Logo.png') }}" alt="60Market Logo">
                        </div>
                        <span class="fallback-badge">⏳ Próximamente</span>
                        <h2 class="fallback-title">
                            Un Minuto Para
                            <span class="highlight">Vender</span>
                        </h2>
                        <p class="fallback-subtitle">
                            Un minuto puede cambiar una venta. Se de los primeros comercios en
                            publicar tu oferta local en <strong style="color: #FEFEFE;">{{ $currentRegion->name }}</strong>.
                        </p>
                        <button class="btn-notify" id="openPreregisterModal" onclick="openModal()">
                            🔔 Avisarme del lanzamiento
                        </button>
                        <a href="{{ route('register') }}" class="fallback-secondary-link">
                            ¿Tienes un negocio? Regístrate como anunciante →
                        </a>
                    </div>
                @endif
            </div>

            <!-- Botón de compra flotante sobrepuesto en la parte inferior del anuncio y centrado -->
            <div class="ad-floating-action" id="adFloatingAction"
                style="{{ $hasAd ? 'display: flex;' : 'display: none;' }}">
                <a href="{{ $hasAd ? route('ads.click', ['advertisementId' => $initAd['id'], 'regionId' => $currentRegion->id]) : '#' }}"
                    id="buyButton" class="btn-comprar" target="_blank" rel="noopener noreferrer">
                    <span>Comprar</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </main>

        <!-- 3. DOCK INFERIOR (Contador colocado por debajo del todo) -->
        <footer class="bottom-dock" id="bottomDock">
            <!-- Barra de progreso de 60s continua -->
            <div class="timer-progress-track">
                <div class="timer-progress-fill" id="timerProgressFill" style="width: 100%;"></div>
            </div>

            <div class="dock-content">
                <!-- Bloque izquierdo: Temporizador en vivo -->
                <div class="dock-left-timer">
                    <div class="live-tag">
                        <span class="live-pulse-dot"></span>
                        <span id="liveTagText">{{ $hasAd ? 'EN VIVO' : 'EN ESPERA' }}</span>
                    </div>

                    <div class="countdown-box">
                        <span class="countdown-number"
                            id="countdownNumber">{{ $hasAd ? $initSlot['remaining_seconds'] : '--' }}</span>
                        <span class="countdown-label">seg</span>
                    </div>
                </div>

                <!-- Bloque central: Información resumida -->
                <div class="dock-center-info" id="dockCenterInfo"
                    style="{{ $hasAd ? 'display: flex;' : 'display: none;' }}">
                    <span class="dock-ad-business" id="dockBusiness">{{ $hasAd ? $initAd['business_name'] : '' }}</span>
                    <span class="dock-ad-title" id="dockTitle">{{ $hasAd ? $initAd['title'] : '' }}</span>
                </div>

                <!-- Bloque derecho: Región activa -->
                <div class="dock-right-status" id="dockRightStatus">
                    <span class="dock-region-chip">📍 {{ $currentRegion->name }}</span>
                </div>
            </div>
        </footer>

    </div>

    <!-- Script de Sincronización y Cuenta Regresiva de 60 Segundos -->
    <script>
        const REGION_SLUG = @json($currentRegion->slug);
        let currentSlotData = {!! json_encode($initialData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) !!};
        let countdownTimer = null;
        let remainingSeconds = 0;
        const SLOT_DURATION = 60;

        function renderSlot(data) {
            currentSlotData = data;
            const contentWrapper = document.getElementById('adContentWrapper');
            const floatingBanner = document.getElementById('adFloatingBanner');
            const adFloatingAction = document.getElementById('adFloatingAction');
            const dockCenterInfo = document.getElementById('dockCenterInfo');
            const countdownNumber = document.getElementById('countdownNumber');
            const liveTagText = document.getElementById('liveTagText');
            const timerProgressFill = document.getElementById('timerProgressFill');

            clearInterval(countdownTimer);

            if (!data.has_ad) {
                // Estado de cola vacía: mostrar creativo fallback de pre-registro
                floatingBanner.style.display = 'none';
                adFloatingAction.style.display = 'none';
                dockCenterInfo.style.display = 'none';
                liveTagText.textContent = 'PRÓXIMAMENTE';
                countdownNumber.textContent = '--';
                timerProgressFill.style.width = '0%';

                contentWrapper.innerHTML = `
                    <div class="fallback-stage">
                        <div class="fallback-logo-ring">
                            <img src="/images/60Logo.png" alt="60Market Logo">
                        </div>
                        <span class="fallback-badge">⏳ Próximamente</span>
                        <h2 class="fallback-title">
                            Próximamente&hellip;<br>
                            <span class="highlight">Un Minuto Para Vender</span>
                        </h2>
                        <p class="fallback-subtitle">
                            Un minuto puede cambiar una venta. Sé de los primeros comercios en
                            publicar tu oferta local en <strong style="color: #FEFEFE;">${data.region.name}</strong>.
                        </p>
                        <button class="btn-notify" onclick="openModal()">
                            🔔 Avisarme del lanzamiento
                        </button>
                        <a href="/register" class="fallback-secondary-link">
                            ¿Tienes un negocio? Regístrate como anunciante &rarr;
                        </a>
                    </div>
                `;

                const retryInterval = data.next_slot_in_seconds
                    ? Math.min(data.next_slot_in_seconds * 1000, 10000)
                    : 15000;
                setTimeout(fetchCurrentSlot, retryInterval);
                return;
            }

            // Anuncio activo en vivo
            const ad = data.advertisement;
            const slot = data.slot;
            remainingSeconds = slot.remaining_seconds;

            liveTagText.textContent = 'EN VIVO';
            floatingBanner.style.display = 'block';
            adFloatingAction.style.display = 'flex';
            dockCenterInfo.style.display = 'flex';

            // Actualizar metadatos
            document.getElementById('floatingBusinessName').textContent = ad.business_name;
            document.getElementById('floatingTitle').textContent = ad.title;
            document.getElementById('dockBusiness').textContent = ad.business_name;
            document.getElementById('dockTitle').textContent = ad.title;

            // Tags
            const tagsContainer = document.getElementById('floatingTags');
            tagsContainer.innerHTML = '';
            (ad.tags || []).forEach(tag => {
                const chip = document.createElement('span');
                chip.className = 'ad-tag-chip';
                chip.textContent = `#${tag}`;
                tagsContainer.appendChild(chip);
            });

            // Creativo a pantalla completa optimizado y centrado (Imagen, Video o HTML IA)
            if (ad.content_type === 'html' && ad.generated_html) {
                contentWrapper.innerHTML = `
                    <div class="ad-html-container" style="width: 100%; height: 100%; padding: 0;">
                        <iframe srcdoc="${escapeHtmlAttr(ad.generated_html)}" style="width: 100%; height: 100%; border: none; overflow: hidden; pointer-events: none; display: block;"></iframe>
                    </div>
                `;
            } else if (ad.content_type === 'image' && ad.assets && ad.assets.find(a => a.type === 'image')) {
                const imageAsset = ad.assets.find(a => a.type === 'image');
                contentWrapper.innerHTML = `
                    <div class="ad-image-container">
                        <img src="${imageAsset.url}" alt="${ad.title}" class="ad-stage-image">
                    </div>
                `;
            } else if (ad.content_type === 'video' && ad.assets && ad.assets.find(a => a.type === 'video')) {
                const videoAsset = ad.assets.find(a => a.type === 'video');
                contentWrapper.innerHTML = `
                    <div class="ad-image-container">
                        <video src="${videoAsset.url}" autoplay muted loop playsinline class="ad-stage-video"></video>
                    </div>
                `;
            } else {
                // Banner visual inmersivo por defecto
                contentWrapper.innerHTML = `
                    <div class="ad-image-container">
                        <div style="text-align: center; padding: 2rem; max-width: 800px; z-index: 5;">
                            <div style="font-size: 5rem; margin-bottom: 1.5rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">📢</div>
                            <h2 style="font-size: 2.8rem; font-weight: 900; color: #FEFEFE; line-height: 1.15; margin-bottom: 1rem; text-shadow: 0 3px 12px rgba(0,0,0,0.6);">
                                ${ad.title}
                            </h2>
                            <p style="font-size: 1.35rem; color: #E8955D; font-weight: 600;">
                                Oferta exclusiva para ${data.region.name}
                            </p>
                        </div>
                    </div>
                `;
            }

            // Botón de compra (redirección y tracking)
            const buyBtn = document.getElementById('buyButton');
            buyBtn.href = `/ads/${ad.id}/click/${data.region.id}`;

            // Registrar impresión anónima
            fetch('/api/ads/impression', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ advertisement_id: ad.id, region_id: data.region.id })
            }).catch(() => { });

            // Cronómetro
            updateTimerDisplay();
            countdownTimer = setInterval(() => {
                remainingSeconds--;
                updateTimerDisplay();

                if (remainingSeconds <= 0) {
                    clearInterval(countdownTimer);
                    fetchCurrentSlot();
                }
            }, 1000);
        }

        function updateTimerDisplay() {
            const countdownNumber = document.getElementById('countdownNumber');
            const timerProgressFill = document.getElementById('timerProgressFill');
            const safeSec = Math.max(0, remainingSeconds);

            countdownNumber.textContent = safeSec;
            const percentage = (safeSec / SLOT_DURATION) * 100;
            timerProgressFill.style.width = `${percentage}%`;
        }

        async function fetchCurrentSlot() {
            try {
                const response = await fetch(`/api/viewer/${REGION_SLUG}`);
                if (response.ok) {
                    const data = await response.json();
                    renderSlot(data);
                }
            } catch (err) {
                setTimeout(fetchCurrentSlot, 5000);
            }
        }

        function changeRegion(slug) {
            window.location.href = `/r/${slug}`;
        }

        function escapeHtmlAttr(str) {
            if (!str) return '';
            return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        }

        document.addEventListener('DOMContentLoaded', () => {
            renderSlot(currentSlotData);
        });
    </script>

    <!-- ========================================================
         MODAL DE PRE-REGISTRO — Incremento 5
         D02: comprador anónimo; al menos email o teléfono.
         ======================================================== -->
    <div class="modal-overlay" id="preregisterModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
        <div class="modal-card">
            <button class="modal-close" onclick="closeModal()" aria-label="Cerrar">✕</button>

            <div class="modal-icon">🔔</div>
            <h2 class="modal-title" id="modalTitle">Avisarme del lanzamiento</h2>
            <p class="modal-subtitle">
                Ingresa tu contacto y te notificamos cuando el espacio publicitario
                de tu región esté disponible.
            </p>

            <form id="preregisterForm" onsubmit="submitPreregister(event)" novalidate>
                @csrf

                <!-- Tipo de usuario -->
                <label class="modal-label">Soy…</label>
                <div class="modal-role-group" id="roleGroup">
                    <button type="button" class="role-btn active" data-role="shopper" id="roleShopperBtn"
                        onclick="selectRole('shopper')">
                        🛍️ Comprador
                    </button>
                    <button type="button" class="role-btn" data-role="merchant" id="roleMerchantBtn"
                        onclick="selectRole('merchant')">
                        🏪 Comerciante
                    </button>
                </div>
                <input type="hidden" id="selectedRole" name="user_type" value="shopper">

                <!-- Contacto único: correo o teléfono (el backend detecta y normaliza el formato) -->
                <div class="modal-form-group">
                    <label class="modal-label" for="leadContact">Correo electrónico o WhatsApp</label>
                    <input type="text" id="leadContact" name="contact" class="modal-input" required maxlength="255"
                        placeholder="tucorreo@ejemplo.com o +52 312 123 4567" autocomplete="email" inputmode="email">
                </div>

                <!-- Región oculta -->
                <input type="hidden" id="leadRegionId" name="region_id" value="{{ $currentRegion->id }}">

                <button type="submit" class="modal-submit" id="submitLeadBtn">
                    🚀 Avisarme del lanzamiento
                </button>
            </form>

            <div class="modal-feedback" id="leadFeedback"></div>

            <p class="modal-legal">
                Sin spam. Solo te contactamos cuando la plataforma esté lista en tu región.
            </p>
        </div>
    </div>

    <script>
        // ── Modal helpers ──────────────────────────────────────────────────
        function openModal() {
            document.getElementById('preregisterModal').classList.add('active');
            document.getElementById('leadContact').focus();
        }

        function closeModal() {
            document.getElementById('preregisterModal').classList.remove('active');
        }

        // Cerrar al hacer clic fuera del card
        document.getElementById('preregisterModal').addEventListener('click', function (e) {
            if (e.target === this) closeModal();
        });

        // Cerrar con Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeModal();
        });

        // ── Selección de rol ───────────────────────────────────────────────
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('.role-btn').forEach(btn => {
                btn.classList.toggle('active', btn.dataset.role === role);
            });
        }

        // ── Envío AJAX ─────────────────────────────────────────────────────
        const SUBMIT_LABEL = '🚀 Avisarme del lanzamiento';
        // Validación ligera en cliente (el servidor es la fuente de verdad).
        const EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        const PHONE_RE = /^\+?\d{8,15}$/;

        function setFeedback(message, type) {
            const feedback = document.getElementById('leadFeedback');
            feedback.textContent = message;
            feedback.className = 'modal-feedback' + (type ? ' ' + type : '');
        }

        function resetSubmitButton() {
            const submitBtn = document.getElementById('submitLeadBtn');
            submitBtn.disabled = false;
            submitBtn.textContent = SUBMIT_LABEL;
        }

        async function submitPreregister(e) {
            e.preventDefault(); // Sin recarga de página

            const contact = document.getElementById('leadContact').value.trim();
            const userType = document.getElementById('selectedRole').value;
            const regionId = document.getElementById('leadRegionId').value;
            const submitBtn = document.getElementById('submitLeadBtn');

            const normalizedPhone = contact.replace(/[\s\-().]/g, '');
            if (!contact) {
                setFeedback('Ingresa tu correo electrónico o teléfono.', 'error');
                return;
            }
            if (!EMAIL_RE.test(contact) && !PHONE_RE.test(normalizedPhone)) {
                setFeedback('Ingresa un correo válido o un teléfono de 8 a 15 dígitos.', 'error');
                return;
            }

            // Estado de carga
            submitBtn.disabled = true;
            submitBtn.textContent = 'Enviando…';
            setFeedback('', null);

            try {
                // URL relativa: hereda el esquema/host de la página (evita mixed content tras proxy).
                const res = await fetch('{{ route('pre-register.store', [], false) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        contact: contact,
                        user_type: userType,
                        region_id: regionId ? parseInt(regionId, 10) : null,
                    }),
                });

                // Parseo defensivo: si el servidor responde HTML (p. ej. página de error),
                // no lo tratamos como fallo de red sino como error del servidor.
                const json = await res.json().catch(() => ({}));

                if (res.ok && json.success) {
                    setFeedback(json.message, 'success');
                    document.getElementById('preregisterForm').reset();
                    selectRole('shopper');
                    submitBtn.textContent = '✓ ¡Registrado!';
                    setTimeout(() => {
                        closeModal();
                        setFeedback('', null);
                        resetSubmitButton();
                    }, 3000);
                    return;
                }

                // 422 validación · 419 CSRF/sesión expirada · 429 demasiados intentos · 5xx servidor
                const firstValidationError = json.errors ? Object.values(json.errors)[0]?.[0] : null;
                let message = firstValidationError || json.message;
                if (res.status === 419) message = 'Tu sesión expiró. Recarga la página e intenta de nuevo.';
                if (res.status === 429) message = 'Demasiados intentos. Espera un minuto e intenta de nuevo.';
                if (res.status >= 500 || !message) message = 'Ocurrió un error en el servidor. Intenta más tarde.';

                console.error('[pre-register] HTTP', res.status, json);
                setFeedback(message, 'error');
                resetSubmitButton();
            } catch (err) {
                // Solo llega aquí ante un fallo real de red (fetch rechazado).
                console.error('[pre-register] network error', err);
                setFeedback('No se pudo conectar. Revisa tu conexión e intenta de nuevo.', 'error');
                resetSubmitButton();
            }
        }
    </script>
</body>

</html>