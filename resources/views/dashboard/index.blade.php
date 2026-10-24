@extends('layouts.app')

@section('title', 'Mis Anuncios — Panel Empresarial')

@section('content')
<div class="card-header-flex">
    <div>
        <h1 class="page-title">Gestión de Anuncios</h1>
        <p class="page-subtitle">Crea borradores, asocia etiquetas regionales y monitorea tus 60 segundos en vivo.</p>
    </div>
    <div>
        <a href="{{ route('ads.create') }}" class="btn-primary">
            <span>+</span> Redactar Nuevo Anuncio
        </a>
    </div>
</div>

<!-- Tarjetas de Métricas Resumidas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--brand-deep-navy);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total de Anuncios</div>
        <div style="font-size: 2rem; font-weight: 900; color: var(--brand-deep-navy); margin-top: 0.25rem;">{{ $totalAds }}</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--brand-slate-blue);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Borradores Listos</div>
        <div style="font-size: 2rem; font-weight: 900; color: var(--brand-slate-blue); margin-top: 0.25rem;">{{ $draftAds }}</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--brand-primary-orange);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">En Cola / Activos</div>
        <div style="font-size: 2rem; font-weight: 900; color: var(--brand-primary-orange); margin-top: 0.25rem;">{{ $queuedAds }}</div>
    </div>

    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid #10b981;">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Transmitidos</div>
        <div style="font-size: 2rem; font-weight: 900; color: #10b981; margin-top: 0.25rem;">{{ $completedAds }}</div>
    </div>
</div>

<!-- Filtros y Tabla de Anuncios -->
<div class="card">
    <form method="GET" action="{{ route('dashboard') }}" style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 0.8rem;">Filtrar por Región</label>
            <select name="region_id" class="form-control" onchange="this.form.submit()">
                <option value="">Todas las regiones</option>
                @foreach ($regions as $r)
                    <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>
                        {{ $r->name }} ({{ $r->state }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="width: 200px;">
            <label class="form-label" style="font-size: 0.8rem;">Filtrar por Estado</label>
            <select name="status" class="form-control" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Borrador</option>
                <option value="queued" {{ request('status') === 'queued' ? 'selected' : '' }}>En Cola</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activo en Vivo</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Transmitido</option>
            </select>
        </div>

        @if (request()->hasAny(['region_id', 'status']))
            <div>
                <a href="{{ route('dashboard') }}" class="btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.85rem;">
                    Limpiar Filtros
                </a>
            </div>
        @endif
    </form>

    @if ($advertisements->isEmpty())
        <div style="text-align: center; padding: 4rem 1rem; color: #64748b;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">📢</div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--brand-deep-navy); margin-bottom: 0.5rem;">
                No se encontraron anuncios
            </h3>
            <p style="margin-bottom: 1.5rem; font-size: 0.95rem;">
                Comienza redactando tu primer borrador de anuncio de 60 segundos.
            </p>
            <a href="{{ route('ads.create') }}" class="btn-primary">
                <span>+</span> Crear Mi Primer Anuncio
            </a>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-subtle); color: #64748b; font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">Anuncio / Título</th>
                        <th style="padding: 0.75rem 1rem;">Empresa & Región</th>
                        <th style="padding: 0.75rem 1rem;">Etiquetas</th>
                        <th style="padding: 0.75rem 1rem;">Estado</th>
                        <th style="padding: 0.75rem 1rem; text-align: center;">Métricas (Imp / Clics)</th>
                        <th style="padding: 0.75rem 1rem; text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($advertisements as $ad)
                        <tr style="border-bottom: 1px solid var(--border-subtle); transition: background-color 0.15s ease;">
                            <td style="padding: 1rem;">
                                <div style="font-weight: 800; color: var(--brand-deep-navy); font-size: 1rem;">
                                    {{ $ad->title }}
                                </div>
                                <div style="font-size: 0.8rem; color: #64748b; margin-top: 0.2rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>🔗 {{ Str::limit($ad->destination_url, 40) }}</span>
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="font-weight: 700; color: #334155;">{{ $ad->business->name }}</div>
                                <div style="font-size: 0.8rem; color: var(--brand-slate-blue);">📍 {{ $ad->region->name }}</div>
                            </td>
                            <td style="padding: 1rem;">
                                <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                                    @forelse ($ad->tags as $tag)
                                        <span style="background: rgba(76, 97, 137, 0.12); color: var(--brand-slate-blue); font-size: 0.75rem; font-weight: 700; padding: 0.15rem 0.5rem; border-radius: 9999px;">
                                            #{{ $tag->name }}
                                        </span>
                                    @empty
                                        <span style="color: #94a3b8; font-size: 0.8rem;">Sin tags</span>
                                    @endforelse
                                </div>
                            </td>
                            <td style="padding: 1rem;">
                                @if ($ad->status === 'draft')
                                    <span class="badge badge-draft">📝 Borrador</span>
                                @elseif ($ad->status === 'queued' || $ad->status === 'scheduled')
                                    <span class="badge badge-queued">📅 Programado</span>
                                @elseif ($ad->status === 'active')
                                    <span class="badge badge-active">🔴 En Vivo</span>
                                @elseif ($ad->status === 'completed')
                                    <span class="badge badge-completed">✅ Emitido</span>
                                @else
                                    <span class="badge badge-draft">{{ $ad->status }}</span>
                                @endif
                            </td>
                            <td style="padding: 1rem; text-align: center;">
                                <span style="font-weight: 700; color: var(--brand-deep-navy);">{{ $ad->impressions_count }}</span>
                                <span style="color: #94a3b8;">/</span>
                                <span style="font-weight: 700; color: var(--brand-primary-orange);">{{ $ad->clicks_count }}</span>
                            </td>
                            <td style="padding: 1rem; text-align: right;">
                                <div style="display: inline-flex; gap: 0.5rem; align-items: center;">
                                    @if ($ad->status === 'draft')
                                        <a href="{{ route('ads.checkout', $ad) }}" class="btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.82rem; background: #009EE3; border-color: #009EE3; box-shadow: 0 2px 8px rgba(0, 158, 227, 0.3);" title="Comprar slot de emisión">
                                            ⚡ Comprar Slot
                                        </a>

                                        <a href="{{ route('ads.edit', $ad) }}" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.82rem;" title="Editar y previsualizar borrador">
                                            ✏️ Editar
                                        </a>

                                        <form action="{{ route('ads.destroy', $ad) }}" method="POST" onsubmit="return confirm('¿Seguro que deseas eliminar este borrador?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-secondary" style="padding: 0.4rem 0.75rem; font-size: 0.82rem; color: #dc2626;" title="Eliminar borrador">
                                                🗑️
                                            </button>
                                        </form>
                                    @elseif ($ad->status === 'active' || $ad->status === 'scheduled' || $ad->status === 'queued')
                                        <a href="{{ route('viewer.region', $ad->region->slug) }}" target="_blank" class="btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.82rem; background: var(--brand-primary-orange);" title="Ver emisión en directo">
                                            🔴 Ver en Vivo
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $advertisements->links() }}
        </div>
    @endif
</div>
@endsection
