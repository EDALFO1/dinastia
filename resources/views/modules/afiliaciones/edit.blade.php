@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Afiliación</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('afiliaciones.index') }}">Afiliaciones</a></li>
                <li class="breadcrumb-item active">Editar</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('afiliaciones.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">
<div class="row justify-content-center">
<div class="col-xl-10">
<div class="card border-0 shadow-sm">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Afiliación</h5>
    </div>
    <div class="card-body pt-4">
        <form action="{{ route('afiliaciones.update', $afiliacion) }}" method="POST">
            @csrf @method('PUT')
            @include('modules.afiliaciones.form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Actualizar
                </button>
                <a href="{{ route('afiliaciones.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
</div>
</div>
</section>

@endsection

@push('scripts')
<script>
$(document).ready(function () {

    function manejarIBC() {
        let tipo = $('#tipo_ibc').val();

        if (tipo === 'SMMLV') {
            $('#ibc').prop('readonly', true);
        } else {
            $('#ibc').prop('readonly', false);
        }
    }

    manejarIBC();

    $('#tipo_ibc').on('change', function () {
        manejarIBC();
    });

});
</script>
@endpush
