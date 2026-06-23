@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-grid-3x3-gap me-2"></i>Planes de Servicio</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Planes</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('planes.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Nuevo Plan
    </a>
</div>

<section class="section mt-3">

{{-- ── Selector de año ─────────────────────────────────────────────────── --}}
<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body py-3">
        <form method="GET" action="{{ route('planes.index') }}"
              class="d-flex flex-wrap align-items-center gap-3">

            <div class="d-flex align-items-center gap-2">
                <label class="fw-semibold mb-0 text-nowrap">
                    <i class="bi bi-calendar3 me-1"></i>Año de referencia:
                </label>
                <select name="anio" class="form-select form-select-sm w-auto"
                        onchange="this.form.submit()">
                    @forelse($aniosDisponibles as $a)
                        <option value="{{ $a }}" {{ $a == $anio ? 'selected' : '' }}>
                            {{ $a }}
                        </option>
                    @empty
                        <option value="{{ $anio }}">{{ $anio }}</option>
                    @endforelse
                </select>
            </div>

            @if($parametro)
                <div class="vr d-none d-md-block"></div>
                <div class="d-flex flex-wrap gap-3">
                    <div>
                        <span class="text-muted small">SMMLV {{ $anio }}</span><br>
                        <span class="fw-bold text-primary">
                            ${{ number_format($parametro->salario_minimo, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted small">Administración</span><br>
                        <span class="fw-bold text-success">
                            ${{ number_format($parametro->administracion, 0, ',', '.') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-muted small">Planes activos</span><br>
                        <span class="fw-bold">
                            {{ $planes->filter(fn($p) => $p['plan']->estado)->count() }}
                        </span>
                    </div>
                </div>
            @endif

            @if($aniosDisponibles->isEmpty())
                <a href="{{ route('parametros_anuales.create') }}"
                   class="btn btn-warning btn-sm">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Registrar SMMLV {{ $anio }}
                </a>
            @endif

        </form>
    </div>
</div>

{{-- ── Alerta sin parámetro ───────────────────────────────────────────── --}}
@if(!$parametro)
<div class="alert alert-warning d-flex align-items-center gap-2 mb-4">
    <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
    <div>
        No hay parámetros registrados para <strong>{{ $anio }}</strong>.
        Los valores no pueden calcularse hasta que registres el SMMLV de ese año.
        <a href="{{ route('parametros_anuales.create') }}" class="alert-link ms-1">
            Registrar ahora →
        </a>
    </div>
</div>
@endif

{{-- ── Tabla de planes ────────────────────────────────────────────────── --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="min-width:180px">Plan</th>
                        <th>Cobertura</th>
                        @if($parametro)
                            <th class="text-end" style="min-width:130px">
                                Valor {{ $anio }}
                            </th>
                            <th style="min-width:260px">Componentes</th>
                        @endif
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:110px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($planes as $item)
                        @php $plan = $item['plan']; $calculo = $item['calculo']; @endphp
                        <tr class="{{ !$plan->estado ? 'text-muted' : '' }}">
                            <td class="ps-3">
                                <div class="fw-semibold">{{ $plan->nombre }}</div>
                                @if($plan->descripcion)
                                    <div class="text-muted small">{{ $plan->descripcion }}</div>
                                @endif
                            </td>

                            <td>
                                @if($plan->incluye_eps)
                                    <span class="badge bg-primary me-1"
                                          title="EPS {{ number_format($plan->porcentaje_eps, 4) }}%">
                                        EPS
                                    </span>
                                @endif
                                @if($plan->incluye_pension)
                                    <span class="badge bg-info text-dark me-1"
                                          title="Pensión {{ number_format($plan->porcentaje_pension, 4) }}%">
                                        PEN
                                    </span>
                                @endif
                                @if($plan->incluye_caja)
                                    <span class="badge bg-warning text-dark me-1"
                                          title="Caja {{ number_format($plan->porcentaje_caja, 4) }}%">
                                        CAJA
                                    </span>
                                @endif
                                @if($plan->incluye_arl)
                                    <span class="badge bg-danger me-1"
                                          title="ARL {{ number_format($plan->porcentaje_arl, 4) }}%">
                                        ARL {{ $plan->nivel_arl }}
                                    </span>
                                @endif
                            </td>

                            @if($parametro)
                                <td class="text-end">
                                    @if($calculo)
                                        <span class="fw-bold fs-6 text-dark">
                                            ${{ number_format($calculo['total'], 0, ',', '.') }}
                                        </span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($calculo)
                                        @foreach($calculo['componentes'] as $k => $v)
                                            <span class="badge bg-light text-dark border me-1 mb-1">
                                                {{ $k }}: ${{ number_format($v, 0, ',', '.') }}
                                            </span>
                                        @endforeach
                                    @endif
                                </td>
                            @endif

                            <td class="text-center">
                                @if($plan->estado)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-secondary">Inactivo</span>
                                @endif
                            </td>

                            <td class="text-center">
                                <a href="{{ route('planes.edit', $plan) }}"
                                   class="btn btn-outline-warning btn-sm"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('planes.destroy', $plan) }}"
                                      method="POST"
                                      class="d-inline form-delete"
                                      data-nombre="{{ $plan->nombre }}">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-outline-danger btn-sm"
                                            title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $parametro ? 6 : 4 }}"
                                class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                No hay planes configurados aún.
                                <a href="{{ route('planes.create') }}" class="d-block mt-1">
                                    Crea el primer plan →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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
            title: '¿Eliminar plan?',
            text: `«${nombre}» será eliminado permanentemente.`,
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
