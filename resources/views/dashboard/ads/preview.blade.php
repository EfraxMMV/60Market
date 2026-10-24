<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vista Previa: {{ $advertisement->title }} — Un Minuto Para Vender</title>
    <link rel="icon" type="image/png" href="{{ asset('images/60Logo.png') }}">

    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        :root {
            --brand-primary-orange: #F05500;
            --brand-secondary-orange: #E8955D;
            --brand-pure-white: #FEFEFE;
            --brand-slate-blue: #4C6189;
            --brand-deep-navy: #032765;
            --bg-canvas: rgba(2, 18, 48, 0.92);
            --header-bg: #FEFEFE;
            --dock-bg: #FEFEFE;
            --border-subtle: rgba(76, 97, 137, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--brand-deep-navy);
            color: var(--brand-pure-white);
        }

        .app-viewport {
            display: flex;
            flex-direction: column;
            width: 100vw;
            height: 100vh;
            position: relative;
        }

        /* Banner de Modo Vista Previa */
        .preview-badge-banner {
            background: linear-gradient(90deg, #d97706, #f59e0b);
            color: #000;
            font-size: 0.8rem;
            font-weight: 800;
            padding: 0.35rem 1rem;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .preview-badge-banner a {
            color: #000;
            background: rgba(255, 255, 255, 0.6);
            padding: 0.15rem 0.6rem;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 900;
        }

        /* Header */
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
        }

        .brand-title {
            font-size: 1.25rem;
            font-weight: 800;
            letter-spacing: -0.02em;
            color: var(--brand-deep-navy);
        }

        .brand-title span {
            color: var(--brand-primary-orange);
        }

        /* Escenario del Anuncio */
        main.ad-stage {
            flex: 1;
            width: 100%;
            height: calc(100vh - 64px - 84px - 28px);
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at center, var(--bg-canvas) 0%, var(--brand-slate-blue) 75%);
            overflow: hidden;
        }

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

        .ad-tag-chip {
            background: rgba(76, 97, 137, 0.4);
            border: 1px solid rgba(76, 97, 137, 0.6);
            color: #e2e8f0;
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.15rem 0.5rem;
            border-radius: 9999px;
            display: inline-block;
            margin-top: 0.4rem;
            margin-right: 0.25rem;
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
            transition: all 0.25s ease;
        }

        .btn-comprar:hover {
            background-color: #d84d00 !important;
            transform: translateY(-3px) scale(1.03);
        }

        .btn-comprar svg {
            width: 22px;
            height: 22px;
        }

        /* Dock Inferior */
        footer.bottom-dock {
            height: 84px;
            min-height: 84px;
            background: var(--brand-pure-white);
            border-top: 1px solid var(--brand-deep-navy);
            position: relative;
            display: flex;
            flex-direction: column;
            z-index: 40;
        }

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
            width: 85%;
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
        }

        .countdown-box {
            display: flex;
            align-items: baseline;
            gap: 0.25rem;
            background: rgba(3, 39, 101, 0.85);
            border: 1px solid var(--brand-slate-blue);
            padding: 0.35rem 0.85rem;
            border-radius: 10px;
        }

        .countdown-number {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--brand-pure-white);
        }

        .countdown-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--brand-secondary-orange);
            text-transform: uppercase;
        }
    </style>
</head>
<body>
    <div class="app-viewport">
        <!-- Banner de modo vista previa -->
        <div class="preview-badge-banner">
            <span>👁️ Modo Previsualización de Anuncio (60 Segundos)</span>
            <a href="{{ route('dashboard') }}">← Volver al Panel</a>
        </div>

        <!-- Encabezado -->
        <header>
            <div class="brand-wrapper">
                <img src="{{ asset('images/60Logo.png') }}" alt="Logo" class="brand-logo-img">
                <span class="brand-title">Un Minuto Para <span>Vender</span></span>
            </div>

            <div style="font-size: 0.9rem; color: var(--brand-deep-navy); font-weight: 700;">
                📍 Región: {{ $advertisement->region->name }} ({{ $advertisement->region->state }})
            </div>

            <div>
                <a href="{{ route('ads.edit', $advertisement) }}" style="background: rgba(240, 85, 0, 0.1); color: var(--brand-primary-orange); border: 1px solid var(--brand-primary-orange); padding: 0.4rem 0.85rem; border-radius: 8px; font-size: 0.85rem; font-weight: 700; text-decoration: none;">
                    ✏️ Editar Borrador
                </a>
            </div>
        </header>

        <!-- Escenario del Anuncio -->
        <main class="ad-stage">
            <div class="ad-floating-banner">
                <span class="ad-floating-business">{{ $advertisement->business->name }}</span>
                <h1 class="ad-floating-title">{{ $advertisement->title }}</h1>
                <div>
                    @foreach ($advertisement->tags as $tag)
                        <span class="ad-tag-chip">#{{ $tag->name }}</span>
                    @endforeach
                </div>
            </div>

            <div style="width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                @if ($advertisement->content_type === 'html' && $advertisement->generated_html)
                    <div style="width: 100%; height: 100%; padding: 2rem;">
                        {!! $advertisement->generated_html !!}
                    </div>
                @else
                    <div style="text-align: center; padding: 2rem; max-width: 800px; z-index: 5;">
                        <div style="font-size: 5rem; margin-bottom: 1.5rem; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.4));">📢</div>
                        <h2 style="font-size: 2.8rem; font-weight: 900; color: #FEFEFE; line-height: 1.15; margin-bottom: 1rem; text-shadow: 0 3px 12px rgba(0,0,0,0.6);">
                            {{ $advertisement->title }}
                        </h2>
                        <p style="font-size: 1.35rem; color: #E8955D; font-weight: 600;">
                            Oferta exclusiva para {{ $advertisement->region->name }}
                        </p>
                    </div>
                @endif
            </div>

            <!-- Botón sobrepuesto centrado en la parte inferior -->
            <div class="ad-floating-action">
                <a href="{{ $advertisement->destination_url }}" class="btn-comprar" target="_blank" rel="noopener noreferrer">
                    <span>Comprar</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>
            </div>
        </main>

        <!-- Dock Inferior -->
        <footer class="bottom-dock">
            <div class="timer-progress-track">
                <div class="timer-progress-fill"></div>
            </div>

            <div class="dock-content">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div class="countdown-box">
                        <span class="countdown-number">51</span>
                        <span class="countdown-label">seg</span>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; justify-content: center;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--brand-primary-orange); text-transform: uppercase;">
                        {{ $advertisement->business->name }}
                    </span>
                    <span style="font-size: 1.1rem; font-weight: 800; color: var(--brand-deep-navy);">
                        {{ $advertisement->title }}
                    </span>
                </div>

                <div>
                    <span style="background: rgba(3, 39, 101, 0.07); color: var(--brand-deep-navy); border: 1px solid rgba(3, 39, 101, 0.16); padding: 0.4rem 0.9rem; border-radius: 9999px; font-size: 0.82rem; font-weight: 700;">
                        📍 {{ $advertisement->region->name }}
                    </span>
                </div>
            </div>
        </footer>
    </div>
</body>
</html>
