@extends('layouts.app')

@section('title', 'Acceso Empresarial')

@section('content')
    <div style="max-width: 460px; margin: 3rem auto;">
        <div class="card" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); padding: 2.25rem;">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="{{ asset('images/60Logo.png') }}" alt="Logo" style="height: 54px; margin-bottom: 1rem;">
                <h1 class="page-title" style="font-size: 1.5rem;">Acceso para Anunciantes</h1>
                <p class="page-subtitle">Gestiona tus anuncios locales en slots de 60 segundos</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">Correo electrónico corporativo</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required
                        autofocus placeholder="contacto@empresa.com">
                    @error('email')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <label for="password" class="form-label" style="margin-bottom: 0;">Contraseña</label>
                    </div>
                    <input type="password" id="password" name="password" class="form-control" required
                        placeholder="••••••••">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem;">
                    <input type="checkbox" name="remember" id="remember"
                        style="accent-color: var(--brand-primary-orange); cursor: pointer;">
                    <label for="remember" style="font-size: 0.85rem; color: #475569; cursor: pointer;">Recordar mi sesión en
                        este equipo</label>
                </div>

                <div style="margin-top: 1.75rem;">
                    <button type="submit" class="btn-primary"
                        style="width: 100%; justify-content: center; padding: 0.85rem;">
                        Ingresar a mi Panel
                    </button>
                </div>
            </form>

            <div
                style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border-subtle); font-size: 0.88rem; color: #64748b;">
                ¿Aún no tienes cuenta empresarial?
                <a href="{{ route('register') }}"
                    style="color: var(--brand-primary-orange); font-weight: 700; text-decoration: none; margin-left: 0.25rem;">
                    Regístrate aquí
                </a>
            </div>
        </div>
    </div>
@endsection