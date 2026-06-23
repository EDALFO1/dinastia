@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Incapacidad</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('incapacidades.index') }}">Incapacidades</a></li>
                <li class="breadcrumb-item">
                    <a href="{{ route('incapacidades.show', $incapacidad) }}">{{ $incapacidad->nombre }}</a>
                </li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('incapacidades.show', $incapacidad) }}"
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye me-1"></i>Ver detalle
        </a>
        <a href="{{ route('incapacidades.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Volver
        </a>
    </div>
</div>

<section class="section mt-3">
<div class="row justify-content-center">
<div class="col-xl-10">

<div class="card shadow-sm border-0">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0 fw-semibold">{{ $incapacidad->nombre }}</h5>
        @php $ei = $estados[$incapacidad->estado] ?? ['label'=>$incapacidad->estado,'color'=>'secondary']; @endphp
        <span class="badge bg-{{ $ei['color'] }} px-3 py-2">{{ $ei['label'] }}</span>
    </div>
    <div class="card-body pt-4">
        <form action="{{ route('incapacidades.update', $incapacidad) }}" method="POST">
            @csrf @method('PUT')
            @include('modules.incapacidades.form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Actualizar
                </button>
                <a href="{{ route('incapacidades.show', $incapacidad) }}"
                   class="btn btn-outline-secondary">
                    Cancelar
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm ms-auto btn-delete">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </div>
        </form>

        <form action="{{ route('incapacidades.destroy', $incapacidad) }}"
              method="POST"
              class="d-none form-delete"
              data-nombre="{{ $incapacidad->nombre }}">
            @csrf @method('DELETE')
        </form>
    </div>
</div>

</div>
</div>
</section>

@push('scripts')
<script>
$(function () {
    $('.btn-delete').on('click', function () {
        const form   = document.querySelector('.form-delete');
        const nombre = $(form).data('nombre');
        Swal.fire({
            title: '¿Eliminar incapacidad?',
            text: `El registro de «${nombre}» y todas sus observaciones serán eliminados.`,
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
