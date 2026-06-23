@extends('layouts.main')

@section('titulo','Recibos de Afiliación')

@php(\Carbon\Carbon::setLocale('es'))

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-person-check me-2"></i>Recibos de Afiliación</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Recibos de Afiliación</li>
            </ol>
        </nav>
    </div>
    @if($pendientes > 0)
    <span class="badge bg-warning text-dark px-3 py-2">
        <i class="bi bi-clock me-1"></i>{{ $pendientes }} pendiente{{ $pendientes > 1 ? 's' : '' }}
    </span>
    @endif
</div>

<section class="section mt-3">

@if(session('success'))
<div class="alert alert-success d-flex align-items-center gap-2">
    <i class="bi bi-check-circle-fill"></i>
    <div>{{ session('success') }}</div>
</div>
@endif

@if(session('error'))
<div class="alert alert-danger d-flex align-items-center gap-2">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <div>{{ session('error') }}</div>
</div>
@endif

{{-- BARRA DE ACCIONES Y FILTROS --}}
<div class="card shadow-sm border-0 mb-3">
    <div class="card-body d-flex gap-2 flex-wrap align-items-center">
        <a href="{{ route('recibos-afiliacion.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Nuevo Recibo
        </a>

        <div class="vr d-none d-md-block"></div>

        <form method="GET" action="{{ route('recibos-afiliacion.index') }}" class="d-flex gap-2 flex-wrap align-items-center ms-md-auto">
            <input type="text" name="buscar" value="{{ request('buscar') }}"
                   class="form-control form-control-sm" placeholder="Buscar afiliado…" style="width:200px">

            <select name="estado" class="form-select form-select-sm" style="width:160px">
                <option value="">Todos los estados</option>
                <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="pagado"    {{ request('estado') === 'pagado'    ? 'selected' : '' }}>Pagado</option>
            </select>

            <button type="submit" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-search"></i>
            </button>
            @if(request('buscar') || request('estado'))
            <a href="{{ route('recibos-afiliacion.index') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-x-lg"></i>
            </a>
            @endif
        </form>
    </div>
</div>

{{-- TABLA --}}
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">#</th>
                        <th>Fecha</th>
                        <th>Afiliado</th>
                        <th>Concepto</th>
                        <th class="text-end">Valor</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:160px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recibos as $r)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $r->numero }}</td>

                        <td>
                            <div>{{ \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') }}</div>
                        </td>

                        <td>
                            <div class="fw-semibold">
                                {{ $r->afiliado?->primer_nombre }} {{ $r->afiliado?->primer_apellido }}
                            </div>
                            <div class="small text-muted">{{ $r->afiliado?->numero_documento }}</div>
                        </td>

                        <td>
                            <span class="text-truncate d-inline-block" style="max-width:260px" title="{{ $r->concepto }}">
                                {{ $r->concepto }}
                            </span>
                        </td>

                        <td class="text-end fw-bold text-success">
                            ${{ number_format($r->valor, 0, ',', '.') }}
                        </td>

                        <td class="text-center">
                            @if($r->estado_pago === 'pagado')
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle me-1"></i>Pagado
                                </span>
                                @if($r->fecha_pago)
                                <div class="small text-muted mt-1">{{ \Carbon\Carbon::parse($r->fecha_pago)->format('d/m/Y') }}</div>
                                @endif
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="bi bi-clock me-1"></i>Pendiente
                                </span>
                            @endif
                        </td>

                        <td class="text-center">
                            <a href="{{ route('recibos-afiliacion.imprimir', $r) }}"
                               class="btn btn-outline-primary btn-sm" title="Ver / Imprimir" target="_blank">
                                <i class="bi bi-printer"></i>
                            </a>

                            @if($r->estado_pago === 'pendiente')
                            <form action="{{ route('recibos-afiliacion.pagar', $r) }}" method="POST" class="d-inline form-pagar">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm" title="Marcar como pagado">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>

                            <form action="{{ route('recibos-afiliacion.destroy', $r) }}" method="POST"
                                  class="d-inline form-delete" data-numero="{{ $r->numero }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay recibos de afiliación registrados.
                            <a href="{{ route('recibos-afiliacion.create') }}" class="d-block mt-1">
                                Crea el primero →
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
    $('.form-pagar').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Marcar como pagado?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#16a34a',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, marcar pagado',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });

    $('.form-delete').on('submit', function (e) {
        e.preventDefault();
        const form   = this;
        const numero = $(this).data('numero');
        Swal.fire({
            title: '¿Eliminar recibo?',
            text: `El recibo #${numero} será eliminado permanentemente.`,
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
