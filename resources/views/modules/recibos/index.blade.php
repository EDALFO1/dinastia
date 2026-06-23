@extends('layouts.main')

@section('titulo','Recibos')

@php(\Carbon\Carbon::setLocale('es'))

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i>Recibos</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Recibos</li>
            </ol>
        </nav>
    </div>
    <span class="badge bg-light text-dark border px-3 py-2">
        <i class="bi bi-calendar3 me-1"></i>
        Periodo {{ ucfirst(now()->subMonth()->translatedFormat('F Y')) }}
    </span>
</div>

<section class="section mt-3">

{{-- 🔔 MENSAJES --}}
@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>{!! session('error') !!}</div>
</div>
@endif

{{-- 🔘 BARRA DE ACCIONES --}}
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex gap-2 flex-wrap align-items-center">

        <a href="{{ route('recibos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Crear Recibo
        </a>

        <a href="{{ route('recibos.sin_recibo') }}" class="btn btn-outline-warning">
            <i class="bi bi-person-dash me-1"></i>Usuarios sin recibo
        </a>

        <div class="vr d-none d-md-block"></div>

        <a href="{{ route('export.pila.excel') }}"
           class="btn btn-outline-success {{ $pendientes == 0 ? 'disabled' : '' }}"
           {{ $pendientes == 0 ? 'onclick=return false;' : '' }}>
            <i class="bi bi-file-earmark-excel me-1"></i>
            Exportar PILA Excel
            <span class="badge bg-dark ms-1">{{ $pendientes }}</span>
        </a>

        <a href="{{ route('export.index') }}" class="btn btn-outline-info">
            <i class="bi bi-archive me-1"></i>Ver Lotes
        </a>

        <a href="{{ route('recibos.exportar.vigentes') }}" class="btn btn-outline-success">
            <i class="bi bi-download me-1"></i>Exportar afiliados vigentes
        </a>

        <div class="vr d-none d-md-block"></div>

        <a href="{{ route('recibos.activos') }}" class="btn btn-outline-dark">
            <i class="bi bi-calendar-check me-1"></i>Activos siguiente periodo
        </a>

        <form action="{{ route('recibos.cerrar_periodo') }}" method="POST" class="ms-md-auto form-cerrar-periodo">
            @csrf
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-lock-fill me-1"></i>Cerrar periodo
            </button>
        </form>

    </div>
</div>

{{-- 📊 TABLA --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Fecha</th>
                        <th>Afiliado</th>
                        <th class="text-end">IBC</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Novedad</th>
                        <th class="text-center" style="width:170px">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recibos as $r)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $r->numero }}</td>

                        <td>{{ $r->fecha }}</td>

                        <td>
                            <div class="fw-semibold">
                                {{ $r->afiliado?->primer_nombre }} {{ $r->afiliado?->primer_apellido }}
                            </div>
                        </td>

                        <td class="text-end">${{ number_format($r->ibc,0,',','.') }}</td>

                        <td class="text-end">
                            <span class="fw-bold text-success">${{ number_format($r->total,0,',','.') }}</span>
                        </td>

                        <td class="text-center">
                            @if($r->novedad)
                                <span class="badge bg-warning text-dark">{{ $r->novedad }}</span>
                            @else
                                <span class="badge bg-success">Normal</span>
                            @endif
                        </td>

                        <td class="text-center">
                            @if(!$r->export_batch_id)
                                <a href="{{ route('recibos.edit',$r) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('recibos.destroy',$r) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $r->numero }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-lock-fill me-1"></i>Bloqueado
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay recibos registrados en este periodo.
                            <a href="{{ route('recibos.create') }}" class="d-block mt-1">
                                Crea el primer recibo →
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($recibos->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $recibos->links() }}
        </div>
    @endif
</div>

</section>

@push('scripts')
<script>
$(function () {

    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form   = this;
        const nombre = $(this).data('nombre');
        Swal.fire({
            title: '¿Eliminar recibo?',
            text: `El recibo «${nombre}» será eliminado permanentemente.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, eliminar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    $('.form-cerrar-periodo').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Cerrar el periodo?',
            text: 'Se validará que todos los afiliados activos tengan recibo generado.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, cerrar periodo',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

});
</script>
@endpush

@endsection
