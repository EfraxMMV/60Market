@extends('layouts.app')

@section('title', 'Registro de Anunciante')

@section('content')
<div style="max-width: 580px; margin: 2rem auto;">
    <div class="card" style="box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08); padding: 2.25rem;">
        <div style="text-align: center; margin-bottom: 2rem;">
            <img src="{{ asset('images/60Logo.png') }}" alt="Logo" style="height: 54px; margin-bottom: 1rem;">
            <h1 class="page-title" style="font-size: 1.5rem;">Crea tu Cuenta Empresarial</h1>
            <p class="page-subtitle">Emite ofertas exclusivas en vivo directo a tu audiencia local</p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="name" class="form-label">Nombre del responsable *</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required autofocus placeholder="Juan Pérez">
                    @error('name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="business_name" class="form-label">Nombre del negocio / marca *</label>
                    <input type="text" id="business_name" name="business_name" class="form-control" value="{{ old('business_name') }}" required placeholder="Café La Estación">
                    @error('business_name')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-group">
                <label for="region_id" class="form-label">Región principal de emisión *</label>
                <select id="region_id" name="region_id" class="form-control" required>
                    <option value="">-- Selecciona tu región local --</option>
                    @foreach ($regions as $region)
                        <option value="{{ $region->id }}" {{ old('region_id') == $region->id ? 'selected' : '' }}>
                            {{ $region->name }} ({{ $region->state }})
                        </option>
                    @endforeach
                </select>
                <div class="form-hint">Tus anuncios se emitirán prioritariamente en la cola local de esta región.</div>
                @error('region_id')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Correo electrónico corporativo *</label>
                <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="contacto@minegocio.com">
                @error('email')
                    <div class="form-error">{{ $message }}</div>
                @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div class="form-group">
                    <label for="password" class="form-label">Contraseña *</label>
                    <input type="password" id="password" name="password" class="form-control" required placeholder="Mínimo 8 caracteres">
                    @error('password')
                        <div class="form-error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña *</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required placeholder="Repite la contraseña">
                </div>
            </div>

            <div style="margin-top: 1.75rem;">
                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 0.85rem;">
                    Crear Cuenta Empresarial
                </button>
            </div>
        </form>

        <div style="text-align: center; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 1px solid var(--border-subtle); font-size: 0.88rem; color: #64748b;">
            ¿Ya tienes una cuenta registrada?
            <a href="{{ route('login') }}" style="color: var(--brand-primary-orange); font-weight: 700; text-decoration: none; margin-left: 0.25rem;">
                Inicia sesión aquí
            </a>
        </div>
    </div>
</div>
@endsection
