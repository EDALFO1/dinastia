@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Nota</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('notas.index') }}">Notas</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('notas.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">
<div class="row justify-content-center">
<div class="col-xl-8">

<div class="card shadow-sm border-0">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0 fw-semibold">{{ $nota->titulo }}</h5>
            <span class="small text-muted">
                Creada por {{ $nota->creadoPor?->name ?? '—' }}
                el {{ $nota->created_at->format('d/m/Y') }}
            </span>
        </div>
        @php $estadoInfo = $estados[$nota->estado] ?? ['label' => $nota->estado, 'color' => 'secondary']; @endphp
        <span class="badge bg-{{ $estadoInfo['color'] }} fs-6 px-3">
            {{ $estadoInfo['label'] }}
        </span>
    </div>
    <div class="card-body pt-4">
        <form action="{{ route('notas.update', $nota) }}" method="POST">
            @csrf @method('PUT')
            @include('modules.notas.form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Actualizar
                </button>
                <a href="{{ route('notas.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
                <button type="button" class="btn btn-outline-danger btn-sm ms-auto btn-delete">
                    <i class="bi bi-trash me-1"></i>Eliminar
                </button>
            </div>
        </form>

        <form action="{{ route('notas.destroy', $nota) }}"
              method="POST"
              class="d-none form-delete"
              data-nombre="{{ $nota->titulo }}">
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
