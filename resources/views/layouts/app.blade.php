<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Empresarial') — Un Minuto Para Vender</title>
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
            --bg-canvas: #f8fafc;
            --border-subtle: #e2e8f0;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-canvas);
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Barra de navegación superior */
        .navbar {
            background-color: var(--brand-pure-white);
            border-bottom: 1px solid var(--border-subtle);
            padding: 0 2rem;
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 50;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
        }

        .brand-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
        }

        .brand-logo {
            height: 42px;
            width: auto;
            object-fit: contain;
        }

        .brand-text {
            font-size: 1.15rem;
            font-weight: 800;
            color: var(--brand-deep-navy);
            letter-spacing: -0.02em;
        }

        .brand-text span {
            color: var(--brand-primary-orange);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }

        .nav-link {
            text-decoration: none;
            color: var(--brand-deep-navy);
            font-weight: 600;
            font-size: 0.9rem;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--brand-primary-orange);
            background-color: rgba(240, 85, 0, 0.08);
        }

        .btn-cta-nav {
            background-color: var(--brand-primary-orange);
            color: var(--brand-pure-white) !important;
            padding: 0.5rem 1.1rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.88rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            transition: all 0.2s ease;
            box-shadow: 0 2px 8px rgba(240, 85, 0, 0.35);
        }

        .btn-cta-nav:hover {
            background-color: #d84d00;
            transform: translateY(-1px);
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            border-left: 1px solid var(--border-subtle);
            padding-left: 1.25rem;
        }

        .user-name {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--brand-deep-navy);
        }

        .btn-logout {
            background: none;
            border: 1px solid #cbd5e1;
            padding: 0.35rem 0.75rem;
            border-radius: 6px;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            color: #ef4444;
            border-color: #ef4444;
            background-color: #fef2f2;
        }

        /* Contenido principal */
        .main-container {
            flex: 1;
            max-width: 1280px;
            width: 100%;
            margin: 0 auto;
            padding: 2rem 1.5rem 4rem 1.5rem;
        }

        /* Alertas y Mensajes Flash */
        .alert {
            padding: 1rem 1.25rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.92rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success {
            background-color: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
        }

        .alert-error {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            color: #991b1b;
        }

        /* Tarjetas y Contenedores */
        .card {
            background: var(--brand-pure-white);
            border: 1px solid var(--border-subtle);
            border-radius: 14px;
            padding: 1.75rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
            margin-bottom: 1.5rem;
        }

        .card-header-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.25rem;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--brand-deep-navy);
            letter-spacing: -0.02em;
        }

        .page-subtitle {
            color: #64748b;
            font-size: 0.95rem;
            margin-top: 0.2rem;
        }

        /* Formularios */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--brand-deep-navy);
            margin-bottom: 0.4rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            color: #1e293b;
            background-color: var(--brand-pure-white);
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--brand-primary-orange);
            box-shadow: 0 0 0 3px rgba(240, 85, 0, 0.15);
        }

        .form-error {
            color: #dc2626;
            font-size: 0.82rem;
            font-weight: 600;
            margin-top: 0.35rem;
        }

        .form-hint {
            color: #64748b;
            font-size: 0.8rem;
            margin-top: 0.35rem;
        }

        /* Botones generales */
        .btn-primary {
            background-color: var(--brand-primary-orange);
            color: var(--brand-pure-white);
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background-color: #d84d00;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: #f1f5f9;
            color: var(--brand-deep-navy);
            border: 1px solid #cbd5e1;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .btn-secondary:hover {
            background-color: #e2e8f0;
        }

        /* Badges de Estado */
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .badge-draft {
            background-color: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        .badge-queued {
            background-color: #fff7ed;
            color: #c2410c;
            border: 1px solid #ffedd5;
        }

        .badge-active {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-completed {
            background-color: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #dbeafe;
        }
    </style>
    @yield('styles')
</head>
<body>

    <!-- Navegación Superior -->
    <nav class="navbar">
        <a href="{{ route('viewer.home') }}" class="brand-link">
            <img src="{{ asset('images/60Logo.png') }}" alt="Logo" class="brand-logo">
            <span class="brand-text">Un Minuto Para <span>Vender</span></span>
        </a>

        <div class="nav-links">
            <a href="{{ route('viewer.home') }}" class="nav-link" target="_blank">
                📺 Visor en Vivo
            </a>
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    📊 Mis Anuncios
                </a>
                @can('view-pre-registrations')
                    <a href="{{ route('dashboard.pre-registrations') }}" class="nav-link {{ request()->routeIs('dashboard.pre-registrations*') ? 'active' : '' }}">
                        🚀 Pre-registros
                    </a>
                @endcan
                <a href="{{ route('ads.create') }}" class="btn-cta-nav">
                    <span>+</span> Nuevo Anuncio
                </a>
                <div class="user-dropdown">
                    <span class="user-name">👤 {{ Auth::user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                        @csrf
                        <button type="submit" class="btn-logout">Salir</button>
                    </form>
                </div>
            @else
                <a href="{{ route('login') }}" class="nav-link">Iniciar Sesión</a>
                <a href="{{ route('register') }}" class="btn-cta-nav">Registrar Empresa</a>
            @endauth
        </div>
    </nav>

    <!-- Contenido -->
    <main class="main-container">
        @if (session('status'))
            <div class="alert alert-success">
                <span>✅</span>
                <span>{{ session('status') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <span>⚠️</span>
                <div>
                    <strong>Por favor verifica los siguientes campos:</strong>
                    <ul style="margin-top: 0.3rem; padding-left: 1.2rem;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    @yield('scripts')
</body>
</html>
