@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

@php $estadoInfo = $estados[$incapacidad->estado] ?? ['label' => $incapacidad->estado, 'color' => 'secondary']; @endphp

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-file-medical me-2"></i>{{ $incapacidad->nombre }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('incapacidades.index') }}">Incapacidades</a></li>
                <li class="breadcrumb-item active">Detalle</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge bg-{{ $estadoInfo['color'] }} fs-6 px-3 py-2">
            {{ $estadoInfo['label'] }}
        </span>
        <a href="{{ route('incapacidades.edit', $incapacidad) }}"
           class="btn btn-warning btn-sm">
            <i class="bi bi-pencil me-1"></i>Editar
        </a>
        <a href="{{ route('incapacidades.index') }}"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<section class="section mt-3">
<div class="row g-4">

    {{-- ── Panel izquierdo: datos ────────────────────────────────────── --}}
    <div class="col-lg-5">

        {{-- Afiliado --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold">
                <i class="bi bi-person me-1"></i>Afiliado
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Documento</dt>
                    <dd class="col-7 fw-semibold">{{ $incapacidad->documento }}</dd>

                    <dt class="col-5 text-muted">Nombre</dt>
                    <dd class="col-7 fw-semibold">{{ $incapacidad->nombre }}</dd>

                    <dt class="col-5 text-muted">Empresa laboral</dt>
                    <dd class="col-7">{{ $incapacidad->empresaLaboral?->nombre ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Entidad --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold">
                <i class="bi bi-building-check me-1"></i>Entidad
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted">Tipo</dt>
                    <dd class="col-7">
                        <span class="badge {{ $incapacidad->entidad_tipo === 'EPS' ? 'bg-primary' : 'bg-danger' }}">
                            {{ $incapacidad->entidad_tipo }}
                        </span>
                    </dd>

                    <dt class="col-5 text-muted">Nombre</dt>
                    <dd class="col-7 fw-semibold">{{ $incapacidad->entidad_nombre }}</dd>
                </dl>
            </div>
        </div>

        {{-- Fechas y días --}}
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header py-2 bg-transparent fw-semibold">
                <i class="bi bi-calendar-range me-1"></i>Fechas y días
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-6 text-muted">Fecha inicio</dt>
                    <dd class="col-6">{{ $incapacidad->fecha_inicio->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted">Fecha fin</dt>
                    <dd class="col-6">{{ $incapacidad->fecha_fin->format('d/m/Y') }}</dd>

                    <dt class="col-6 text-muted">Total días</dt>
                    <dd class="col-6">
                        <span class="badge bg-dark fs-6 px-2">{{ $incapacidad->dias_solicitados }} días</span>
                    </dd>

                    <dt class="col-6 text-muted">Fecha radicación</dt>
                    <dd class="col-6">
                        {{ $incapacidad->fecha_radicacion?->format('d/m/Y') ?? '—' }}
                    </dd>

                    @if($incapacidad->fecha_pago)
                        <dt class="col-6 text-muted">Fecha pago</dt>
                        <dd class="col-6 text-success fw-semibold">
                            {{ $incapacidad->fecha_pago->format('d/m/Y') }}
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Estado --}}
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <div class="text-muted small mb-1">Estado actual</div>
                <span class="badge bg-{{ $estadoInfo['color'] }} fs-5 px-3 py-2">
                    {{ $estadoInfo['label'] }}
                </span>
                <div class="mt-3 d-flex flex-wrap gap-2">
                    @foreach($estados as $key => $info)
                        @if($key !== $incapacidad->estado)
                            <form action="{{ route('incapacidades.update', $incapacidad) }}"
                                  method="POST"
                                  class="d-inline">
                                @csrf @method('PUT')
                                {{-- campos ocultos para mantener datos --}}
                                <input type="hidden" name="afiliado_id"        value="{{ $incapacidad->afiliado_id }}">
                                <input type="hidden" name="documento"           value="{{ $incapacidad->documento }}">
                                <input type="hidden" name="nombre"              value="{{ $incapacidad->nombre }}">
                                <input type="hidden" name="empresa_laboral_id"  value="{{ $incapacidad->empresa_laboral_id }}">
                                <input type="hidden" name="entidad_tipo"        value="{{ $incapacidad->entidad_tipo }}">
                                <input type="hidden" name="eps_id"              value="{{ $incapacidad->eps_id }}">
                                <input type="hidden" name="arl_id"              value="{{ $incapacidad->arl_id }}">
                                <input type="hidden" name="fecha_inicio"        value="{{ $incapacidad->fecha_inicio->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_fin"           value="{{ $incapacidad->fecha_fin->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_radicacion"    value="{{ $incapacidad->fecha_radicacion?->format('Y-m-d') }}">
                                <input type="hidden" name="fecha_pago"          value="{{ $incapacidad->fecha_pago?->format('Y-m-d') }}">
                                <input type="hidden" name="estado"              value="{{ $key }}">
                                <button type="submit"
                                        class="btn btn-outline-{{ $info['color'] }} btn-sm"
                                        title="Cambiar a {{ $info['label'] }}">
                                    → {{ $info['label'] }}
                                </button>
                            </form>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- ── Panel derecho: observaciones ──────────────────────────────── --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-transparent py-3 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">
                    <i class="bi bi-journal-text me-1"></i>
                    Seguimiento y Observaciones
                    <span class="badge bg-secondary ms-1">{{ $incapacidad->observaciones->count() }}</span>
                </span>
            </div>

            {{-- Form nueva observación --}}
            <div class="card-body border-bottom">
                <form action="{{ route('incapacidades.observacion', $incapacidad) }}"
                      method="POST">
                    @csrf
                    <label class="form-label small fw-semibold">Agregar gestión / observación</label>
                    <textarea name="nota"
                              class="form-control @error('nota') is-invalid @enderror"
                              rows="3"
                              placeholder="Describe la gestión realizada, contacto con la entidad, documentos solicitados..."
                              required></textarea>
                    @error('nota') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="mt-2 text-end">
                        <button type="submit" class="btn btn-primary btn-sm">
                            <i class="bi bi-send me-1"></i>Registrar
                        </button>
                    </div>
                </form>
            </div>

            {{-- Timeline de observaciones --}}
            <div class="card-body" style="max-height:550px; overflow-y:auto">
                @forelse($incapacidad->observaciones as $obs)
                    <div class="d-flex gap-3 mb-4">
                        <div class="flex-shrink-0">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center
                                        justify-content-center"
                                 style="width:36px;height:36px;font-size:0.8rem">
                                {{ strtoupper(substr($obs->usuario?->name ?? 'U', 0, 1)) }}
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start">
                                <span class="fw-semibold small">{{ $obs->usuario?->name ?? 'Usuario' }}</span>
                                <span class="text-muted" style="font-size:0.75rem">
                                    {{ $obs->created_at->format('d/m/Y H:i') }}
                                </span>
                            </div>
                            <div class="bg-light rounded p-2 mt-1 small" style="white-space:pre-wrap">{{ $obs->nota }}</div>

                            <form action="{{ route('incapacidad_observaciones.destroy', $obs) }}"
                                  method="POST"
                                  class="d-inline form-delete-obs"
                                  data-nombre="esta observación">
                                @csrf @method('DELETE')
                                <button type="submit"
                                        class="btn btn-link btn-sm text-danger p-0 mt-1"
                                        style="font-size:0.75rem">
                                    <i class="bi bi-trash me-1"></i>Eliminar
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-chat-square-text fs-3 d-block mb-2 opacity-50"></i>
                        Aún no hay observaciones. Usa el formulario de arriba para agregar la primera.
                    </div>
                @endforelse
            </div>

        </div>
    </div>

</div>
</section>

@push('scripts')
<script>
$(function () {
    $('.form-delete-obs').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Eliminar observación?',
            text: 'Esta acción no se puede deshacer.',
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
