@extends('layouts.main')

@section('titulo','Activos siguiente periodo')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-calendar-check me-2"></i>Activos siguiente periodo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos.index') }}">Recibos</a></li>
                <li class="breadcrumb-item active">Activos siguiente periodo</li>
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
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Nombre</th>
                        <th>Documento</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($afiliados as $a)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $a->primer_nombre }} {{ $a->primer_apellido }}</td>
                        <td>{{ $a->numero_documento }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay afiliados activos sin recibo para el siguiente periodo.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

</section>

@endsection
