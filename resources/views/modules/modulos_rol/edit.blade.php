@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

@php
$grupoLabels = [
    'principal'     => ['label' => 'Principal',     'icon' => 'bi-grid-1x2-fill'],
    'operativo'     => ['label' => 'Operativo',     'icon' => 'bi-receipt'],
    'gestion'       => ['label' => 'Gestión',       'icon' => 'bi-people-fill'],
    'configuracion' => ['label' => 'Configuración', 'icon' => 'bi-collection-fill'],
    'sistema'       => ['label' => 'Sistema',       'icon' => 'bi-shield-lock-fill'],
];
@endphp

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0 text-capitalize"><i class="bi bi-person-gear me-2"></i>{{ $rol->nombre }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('modulos-rol.index') }}">Módulos por Rol</a></li>
                <li class="breadcrumb-item active text-capitalize">{{ $rol->nombre }}</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section mt-3">

<form action="{{ route('modulos-rol.update', $rol) }}" method="POST">
    @csrf @method('PUT')

    <div class="row g-3">
        @foreach($modulos as $grupo => $items)
        @php $info = $grupoLabels[$grupo] ?? ['label' => ucfirst($grupo), 'icon' => 'bi-grid']; @endphp
        <div class="col-12 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                    <span class="fw-semibold">
                        <i class="bi {{ $info['icon'] }} me-2 text-primary"></i>{{ $info['label'] }}
                    </span>
                    <button type="button" class="btn btn-link btn-sm p-0 text-muted toggle-grupo"
                            data-grupo="{{ $grupo }}">
                        Sel. todos
                    </button>
                </div>
                <div class="card-body py-2">
                    @foreach($items as $modulo)
                    <div class="form-check py-1 border-bottom">
                        <input class="form-check-input modulo-check grupo-{{ $grupo }}"
                               type="checkbox"
                               name="modulos[]"
                               value="{{ $modulo->id }}"
                               id="mod_{{ $modulo->id }}"
                               {{ in_array($modulo->id, $asignados) ? 'checked' : '' }}>
                        <label class="form-check-label w-100" for="mod_{{ $modulo->id }}">
                            <span class="fw-medium">{{ $modulo->nombre }}</span>
                            @if($modulo->descripcion)
                            <small class="d-block text-muted">{{ $modulo->descripcion }}</small>
                            @endif
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save me-1"></i>Guardar cambios
        </button>
        <a href="{{ route('modulos-rol.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>

</form>

</section>

@push('scripts')
<script>
$('.toggle-grupo').on('click', function () {
    const grupo   = $(this).data('grupo');
    const checks  = $('.grupo-' + grupo);
    const allOn   = checks.toArray().every(c => c.checked);
    checks.prop('checked', !allOn);
    $(this).text(allOn ? 'Sel. todos' : 'Desmarcar');
});
</script>
@endpush

@endsection
