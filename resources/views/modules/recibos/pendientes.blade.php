@extends('layouts.main')

@section('titulo','No se puede cerrar periodo')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>No se puede cerrar periodo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos.index') }}">Recibos</a></li>
                <li class="breadcrumb-item active">Pendientes</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('recibos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

<div class="card shadow-sm border-0">
    <div class="card-body p-0">

        <div class="alert alert-danger d-flex align-items-center gap-2 m-3 mb-0">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>Hay afiliados sin recibo. Debes generarlos antes de cerrar el periodo.</div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Documento</th>
                        <th>Nombre</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($afiliados as $a)
                    <tr>
                        <td class="ps-3">{{ $a->numero_documento }}</td>
                        <td class="fw-semibold">{{ $a->primer_nombre }} {{ $a->primer_apellido }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3 border-top">
            <a href="{{ route('recibos.sin_recibo') }}" class="btn btn-primary">
                <i class="bi bi-arrow-right-circle me-1"></i>Ir a generar recibos
            </a>
        </div>

    </div>
</div>

</section>

@endsection
