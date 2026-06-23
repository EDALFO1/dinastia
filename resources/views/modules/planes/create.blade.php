@extends('layouts.main')

@section('titulo', $titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo Plan</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('planes.index') }}">Planes</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('planes.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">
<div class="row justify-content-center">
<div class="col-xl-10">

<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Configuración del plan</h5>
        <p class="text-muted small mb-0 mt-1">
            Define qué servicios incluye y los porcentajes que se aplicarán sobre el SMMLV del año vigente.
        </p>
    </div>
    <div class="card-body pt-4">
        <form action="{{ route('planes.store') }}" method="POST">
            @csrf
            @include('modules.planes.form')
            <div class="d-flex gap-2 mt-4 pt-3 border-top">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i>Guardar Plan
                </button>
                <a href="{{ route('planes.index') }}" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

</div>
</div>
</section>

@endsection
