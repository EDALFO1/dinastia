@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-person-plus me-2"></i>{{ $titulo }}</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('arl-afiliados.index') }}">Afiliados ARL</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section mt-3">
    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form action="{{ route('arl-afiliados.store') }}" method="POST">
                @csrf
                @include('modules.arl-afiliados.form')
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Guardar
                    </button>
                    <a href="{{ route('arl-afiliados.index') }}" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</section>

@endsection
