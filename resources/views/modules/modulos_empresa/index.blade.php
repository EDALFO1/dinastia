@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-building-gear me-2"></i>Módulos por Empresa</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Módulos por Empresa</li>
            </ol>
        </nav>
    </div>
</div>

<section class="section mt-3">

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 datatable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Empresa</th>
                        <th>NIT</th>
                        <th class="text-center">Módulos activos</th>
                        <th class="text-center" style="width:120px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($empresas as $empresa)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $empresa->nombre }}</td>
                        <td class="text-muted">{{ $empresa->nit }}</td>
                        <td class="text-center">
                            <span class="badge bg-primary rounded-pill">{{ $empresa->modulos()->count() }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('modulos-empresa.edit', $empresa) }}"
                               class="btn btn-outline-primary btn-sm" title="Configurar módulos">
                                <i class="bi bi-toggles"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay empresas registradas.
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
