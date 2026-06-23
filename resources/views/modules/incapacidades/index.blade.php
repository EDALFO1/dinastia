@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-file-medical me-2"></i>Control de Incapacidades</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Incapacidades</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('incapacidades.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Registrar Incapacidad
    </a>
</div>

<section class="section mt-3">

{{-- ── Stats por estado ────────────────────────────────────────────────── --}}
<div class="row g-2 mb-4">
    @foreach($estados as $key => $info)
        <div class="col-6 col-sm-4 col-xl">
            <a href="{{ route('incapacidades.index', ['estado' => $key]) }}"
               class="card border-0 shadow-sm text-decoration-none h-100
                      {{ request('estado') === $key ? 'border border-2 border-'.$info['color'] : '' }}">
                <div class="card-body py-2 px-3 d-flex align-items-center gap-2">
                    <span class="badge bg-{{ $info['color'] }} fs-5 px-2">{{ $stats[$key] }}</span>
                    <span class="small text-muted">{{ $info['label'] }}</span>
                </div>
            </a>
        </div>
    @endforeach
</div>

{{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('incapacidades.index') }}"
              class="d-flex flex-wrap gap-3 align-items-end">

            <div>
                <label class="form-label small fw-semibold mb-1">Buscar</label>
                <input type="text"
                       name="buscar"
                       value="{{ request('buscar') }}"
                       class="form-control form-control-sm"
                       style="min-width:200px"
                       placeholder="Nombre o documento...">
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm" style="min-width:160px">
                    <option value="">Todos</option>
                    @foreach($estados as $key => $info)
                        <option value="{{ $key }}" {{ request('estado') === $key ? 'selected' : '' }}>
                            {{ $info['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Entidad</label>
                <select name="entidad_tipo" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <option value="EPS" {{ request('entidad_tipo') === 'EPS' ? 'selected' : '' }}>EPS</option>
                    <option value="ARL" {{ request('entidad_tipo') === 'ARL' ? 'selected' : '' }}>ARL</option>
                </select>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-secondary btn-sm">
                    <i class="bi bi-search me-1"></i>Filtrar
                </button>
                @if(request('estado') || request('entidad_tipo') || request('buscar'))
                    <a href="{{ route('incapacidades.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-x-circle me-1"></i>Limpiar
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>

{{-- ── Tabla ───────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="min-width:200px">Afiliado</th>
                        <th>Empresa laboral</th>
                        <th>Entidad</th>
                        <th class="text-center">Días</th>
                        <th>Inicio — Fin</th>
                        <th>Radicación</th>
                        <th>Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($incapacidades as $inc)
                        @php $estadoInfo = $estados[$inc->estado] ?? ['label' => $inc->estado, 'color' => 'secondary']; @endphp
                        <tr>
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $inc->nombre }}</div>
                                <div class="text-muted small">{{ $inc->documento }}</div>
                            </td>

                            <td>
                                <span class="small">{{ $inc->empresaLaboral?->nombre ?? '—' }}</span>
                            </td>

                            <td>
                                <span class="badge {{ $inc->entidad_tipo === 'EPS' ? 'bg-primary' : 'bg-danger' }} me-1">
                                    {{ $inc->entidad_tipo }}
                                </span>
                                <span class="small">{{ $inc->entidad_nombre }}</span>
                            </td>

                            <td class="text-center fw-bold">{{ $inc->dias_solicitados }}</td>

                            <td>
                                <span class="small text-nowrap">
                                    {{ $inc->fecha_inicio->format('d/m/Y') }}
                                    <i class="bi bi-arrow-right mx-1 text-muted"></i>
                                    {{ $inc->fecha_fin->format('d/m/Y') }}
                                </span>
                            </td>

                            <td>
                                @if($inc->fecha_radicacion)
                                    <span class="small">{{ $inc->fecha_radicacion->format('d/m/Y') }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-{{ $estadoInfo['color'] }}">
                                    {{ $estadoInfo['label'] }}
                                </span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('incapacidades.show', $inc) }}"
                                   class="btn btn-outline-primary btn-sm"
                                   title="Ver detalle y seguimiento">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('incapacidades.edit', $inc) }}"
                                   class="btn btn-outline-warning btn-sm"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                No se encontraron incapacidades.
                                <a href="{{ route('incapacidades.create') }}" class="d-block mt-1">
                                    Registrar la primera →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($incapacidades->hasPages())
        <div class="card-footer border-0 bg-transparent">
            {{ $incapacidades->links() }}
        </div>
    @endif
</div>

</section>

@endsection
