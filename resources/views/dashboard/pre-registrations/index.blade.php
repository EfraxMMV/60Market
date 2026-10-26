@extends('layouts.app')

@section('title', 'Pre-registros')

@section('content')
<div class="card-header-flex">
    <div>
        <h1 class="page-title">Pre-registros "Avisarme del lanzamiento"</h1>
        <p class="page-subtitle">Contactos capturados desde el visor público, por región y fecha.</p>
    </div>
    <div>
        {{-- Exporta respetando los filtros activos --}}
        <a href="{{ route('dashboard.pre-registrations.export', request()->query()) }}" class="btn-primary">
            ⬇️ Exportar CSV
        </a>
    </div>
</div>

<!-- Métricas -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--brand-deep-navy);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Total histórico</div>
        <div style="font-size: 2rem; font-weight: 900; color: var(--brand-deep-navy); margin-top: 0.25rem;">{{ $totalAll }}</div>
    </div>
    <div class="card" style="margin-bottom: 0; padding: 1.25rem; border-left: 4px solid var(--brand-primary-orange);">
        <div style="font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Con filtros actuales</div>
        <div style="font-size: 2rem; font-weight: 900; color: var(--brand-primary-orange); margin-top: 0.25rem;">{{ $registrations->total() }}</div>
    </div>
</div>

<div class="card">
    <!-- Filtros -->
    <form method="GET" action="{{ route('dashboard.pre-registrations') }}"
        style="display: flex; gap: 1rem; margin-bottom: 1.5rem; flex-wrap: wrap; align-items: flex-end;">
        <div style="flex: 1; min-width: 200px;">
            <label class="form-label" style="font-size: 0.8rem;" for="region_id">Región</label>
            <select name="region_id" id="region_id" class="form-control">
                <option value="">Todas las regiones</option>
                @foreach ($regions as $r)
                    <option value="{{ $r->id }}" @selected(($filters['region_id'] ?? null) == $r->id)>
                        {{ $r->name }} ({{ $r->state }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="width: 170px;">
            <label class="form-label" style="font-size: 0.8rem;" for="user_type">Tipo</label>
            <select name="user_type" id="user_type" class="form-control">
                <option value="">Todos</option>
                <option value="shopper" @selected(($filters['user_type'] ?? null) === 'shopper')>🛍️ Comprador</option>
                <option value="merchant" @selected(($filters['user_type'] ?? null) === 'merchant')>🏪 Comercio</option>
            </select>
        </div>

        <div style="width: 170px;">
            <label class="form-label" style="font-size: 0.8rem;" for="from">Desde</label>
            <input type="date" name="from" id="from" class="form-control" value="{{ $filters['from'] ?? '' }}">
        </div>

        <div style="width: 170px;">
            <label class="form-label" style="font-size: 0.8rem;" for="to">Hasta</label>
            <input type="date" name="to" id="to" class="form-control" value="{{ $filters['to'] ?? '' }}">
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn-primary" style="padding: 0.75rem 1rem; font-size: 0.85rem;">Filtrar</button>
            @if (array_filter($filters))
                <a href="{{ route('dashboard.pre-registrations') }}" class="btn-secondary" style="padding: 0.75rem 1rem; font-size: 0.85rem;">
                    Limpiar
                </a>
            @endif
        </div>
    </form>

    @if ($registrations->isEmpty())
        <div style="text-align: center; padding: 4rem 1rem; color: #64748b;">
            <div style="font-size: 3.5rem; margin-bottom: 1rem;">📭</div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: var(--brand-deep-navy); margin-bottom: 0.5rem;">
                Sin pre-registros
            </h3>
            <p>No hay contactos que coincidan con los filtros seleccionados.</p>
        </div>
    @else
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-subtle); color: #64748b; font-size: 0.8rem; text-transform: uppercase;">
                        <th style="padding: 0.75rem 1rem;">#</th>
                        <th style="padding: 0.75rem 1rem;">Contacto</th>
                        <th style="padding: 0.75rem 1rem;">Tipo</th>
                        <th style="padding: 0.75rem 1rem;">Región</th>
                        <th style="padding: 0.75rem 1rem;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($registrations as $reg)
                        <tr style="border-bottom: 1px solid var(--border-subtle);">
                            <td style="padding: 0.75rem 1rem; color: #94a3b8;">{{ $reg->id }}</td>
                            <td style="padding: 0.75rem 1rem; font-weight: 600; color: var(--brand-deep-navy);">
                                {{ $reg->isEmail() ? '✉️' : '📱' }} {{ $reg->contact }}
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                @if ($reg->user_type === 'merchant')
                                    <span class="badge badge-queued">🏪 Comercio</span>
                                @elseif ($reg->user_type === 'shopper')
                                    <span class="badge badge-draft">🛍️ Comprador</span>
                                @else
                                    <span style="color: #94a3b8;">—</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                {{ $reg->region?->name ?? 'Sin región' }}
                                @if ($reg->region?->state)
                                    <span style="color: #94a3b8;">({{ $reg->region->state }})</span>
                                @endif
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #64748b;">{{ $reg->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div style="margin-top: 1.5rem; display: flex; justify-content: space-between; align-items: center; color: #64748b; font-size: 0.85rem;">
            <span>Página {{ $registrations->currentPage() }} de {{ $registrations->lastPage() }}</span>
            <div style="display: flex; gap: 0.5rem;">
                @if ($registrations->previousPageUrl())
                    <a href="{{ $registrations->previousPageUrl() }}" class="btn-secondary" style="padding: 0.5rem 0.9rem; font-size: 0.85rem;">← Anterior</a>
                @endif
                @if ($registrations->nextPageUrl())
                    <a href="{{ $registrations->nextPageUrl() }}" class="btn-secondary" style="padding: 0.5rem 0.9rem; font-size: 0.85rem;">Siguiente →</a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
