@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-stickies me-2"></i>Notas y Tareas</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Notas</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('notas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nueva Nota
    </a>
</div>

<section class="section mt-3">

{{-- ── Stats ──────────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 bg-warning bg-opacity-15 p-3">
                    <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small">Pendientes</div>
                    <div class="fs-4 fw-bold">{{ $stats['pendientes'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 bg-primary bg-opacity-15 p-3">
                    <i class="bi bi-arrow-repeat fs-4 text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small">En proceso</div>
                    <div class="fs-4 fw-bold">{{ $stats['en_proceso'] }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 bg-danger bg-opacity-15 p-3">
                    <i class="bi bi-exclamation-circle fs-4 text-danger"></i>
                </div>
                <div>
                    <div class="text-muted small">Vencidas</div>
                    <div class="fs-4 fw-bold {{ $stats['vencidas'] > 0 ? 'text-danger' : '' }}">
                        {{ $stats['vencidas'] }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3 py-3">
                <div class="rounded-3 bg-success bg-opacity-15 p-3">
                    <i class="bi bi-check-circle fs-4 text-success"></i>
                </div>
                <div>
                    <div class="text-muted small">Resueltas esta semana</div>
                    <div class="fs-4 fw-bold text-success">{{ $stats['resueltas'] }}</div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ── Filtros ─────────────────────────────────────────────────────────── --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('notas.index') }}"
              class="d-flex flex-wrap gap-3 align-items-end">

            <div>
                <label class="form-label small fw-semibold mb-1">Estado</label>
                <select name="estado" class="form-select form-select-sm"
                        style="min-width:140px" onchange="this.form.submit()">
                    <option value="">Todos los estados</option>
                    @foreach($estados as $key => $info)
                        <option value="{{ $key }}"
                            {{ request('estado') === $key ? 'selected' : '' }}>
                            {{ $info['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="form-label small fw-semibold mb-1">Tipo</label>
                <select name="tipo" class="form-select form-select-sm"
                        style="min-width:160px" onchange="this.form.submit()">
                    <option value="">Todos los tipos</option>
                    @foreach($tipos as $key => $label)
                        <option value="{{ $key }}"
                            {{ request('tipo') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            @if(request('estado') || request('tipo'))
                <a href="{{ route('notas.index') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-x-circle me-1"></i>Limpiar filtros
                </a>
            @endif

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
                        <th class="ps-3" style="min-width:220px">Tarea / Nota</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Vencimiento</th>
                        <th>Creada por</th>
                        <th class="text-center" style="width:150px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($notas as $nota)
                        @php
                            $estadoInfo = $estados[$nota->estado] ?? ['label' => $nota->estado, 'color' => 'secondary'];
                            $vencida    = $nota->estaVencida();
                        @endphp
                        <tr class="{{ $vencida ? 'table-danger' : '' }}">

                            <td class="ps-3">
                                <div class="fw-semibold">{{ $nota->titulo }}</div>
                                @if($nota->descripcion)
                                    <div class="text-muted small text-truncate" style="max-width:300px">
                                        {{ $nota->descripcion }}
                                    </div>
                                @endif
                                @if($nota->estado === 'resuelto' && $nota->resueltoPor)
                                    <div class="text-success small">
                                        <i class="bi bi-check2 me-1"></i>
                                        Resuelto por {{ $nota->resueltoPor->name }}
                                        {{ $nota->fecha_resuelto?->format('d/m/Y H:i') }}
                                    </div>
                                @endif
                            </td>

                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ $tipos[$nota->tipo] ?? $nota->tipo }}
                                </span>
                            </td>

                            <td>
                                <span class="badge bg-{{ $estadoInfo['color'] }}">
                                    {{ $estadoInfo['label'] }}
                                </span>
                            </td>

                            <td>
                                @if($nota->fecha_vencimiento)
                                    <span class="{{ $vencida ? 'text-danger fw-semibold' : '' }}">
                                        @if($vencida)
                                            <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                        @endif
                                        {{ $nota->fecha_vencimiento->format('d/m/Y') }}
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>

                            <td>
                                <span class="small">{{ $nota->creadoPor?->name ?? '—' }}</span>
                                <div class="text-muted" style="font-size:0.75rem">
                                    {{ $nota->created_at->format('d/m/Y') }}
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="d-flex gap-1 justify-content-center flex-wrap">

                                    {{-- Resolver --}}
                                    @if(!in_array($nota->estado, ['resuelto', 'cancelado']))
                                        <form action="{{ route('notas.resolver', $nota) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-success btn-sm"
                                                    title="Marcar como resuelta">
                                                <i class="bi bi-check-lg"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Reabrir --}}
                                    @if(in_array($nota->estado, ['resuelto', 'cancelado']))
                                        <form action="{{ route('notas.reabrir', $nota) }}"
                                              method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-warning btn-sm"
                                                    title="Reabrir">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Editar --}}
                                    <a href="{{ route('notas.edit', $nota) }}"
                                       class="btn btn-outline-primary btn-sm"
                                       title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>

                                    {{-- Eliminar --}}
                                    <form action="{{ route('notas.destroy', $nota) }}"
                                          method="POST"
                                          class="d-inline form-delete"
                                          data-nombre="{{ $nota->titulo }}">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="btn btn-outline-danger btn-sm"
                                                title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                No hay notas registradas.
                                <a href="{{ route('notas.create') }}" class="d-block mt-1">
                                    Crear la primera nota →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($notas->hasPages())
        <div class="card-footer border-0 bg-transparent">
            {{ $notas->links() }}
        </div>
    @endif
</div>

</section>

@push('scripts')
<script>
$(function () {
    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form   = this;
        const nombre = $(this).data('nombre');
        Swal.fire({
            title: '¿Eliminar nota?',
            text: `«${nombre}» será eliminada permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
