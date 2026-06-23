@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-shield-check me-2"></i>Afiliados ARL</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Afiliados ARL</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('arl-afiliados.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

    {{-- Buscador + Exportar --}}
    <div class="mb-3 d-flex gap-2 align-items-center">
        <div class="input-group">
            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
            <input type="text" id="buscador" class="form-control"
                   placeholder="Buscar por nombre o número de documento..."
                   onkeyup="buscarEnTiempoReal()">
        </div>
        @if(isset($modulosPermitidos) && $modulosPermitidos->contains('exportaciones'))
        <a href="{{ route('export.arl-afiliados.exportar') }}"
           class="btn btn-outline-success btn-sm text-nowrap">
            <i class="bi bi-file-earmark-excel me-1"></i>Exportar Excel
        </a>
        @endif
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="px-3 pt-3 text-muted small">
                Mostrando {{ $afiliados->firstItem() ?? 0 }} a {{ $afiliados->lastItem() ?? 0 }}
                de {{ $afiliados->total() }} registros
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Documento</th>
                            <th>Nombre</th>
                            <th>ARL / Riesgo</th>
                            <th>Empresa Empleadora</th>
                            <th class="text-end">Base Cotiz.</th>
                            <th class="text-end">Valor ARL</th>
                            <th class="text-end">Admin</th>
                            <th class="text-end">Total Mens.</th>
                            <th>Ingreso</th>
                            <th>Estado</th>
                            <th class="text-center" style="width:120px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-arl-afiliados">
                        @forelse($afiliados as $a)
                        <tr>
                            <td class="ps-3 text-nowrap">
                                {{ $a->documento?->nombre ?? '' }} {{ $a->numero }}
                            </td>
                            <td>{{ $a->nombre }}</td>
                            <td class="text-nowrap">
                                {{ $a->arl?->nombre ?? '—' }}
                                @if($a->arl)
                                    <span class="badge bg-secondary ms-1">R{{ $a->arl->nivel }}</span>
                                @endif
                            </td>
                            <td>{{ $a->empresaLaboral?->nombre ?? '—' }}</td>
                            <td class="text-end">
                                ${{ number_format($a->base_cotizacion, 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                ${{ number_format($a->valorArl(), 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                ${{ number_format($a->administracion, 0, ',', '.') }}
                            </td>
                            <td class="text-end fw-semibold">
                                ${{ number_format($a->totalMensual(), 0, ',', '.') }}
                            </td>
                            <td class="text-nowrap">
                                {{ $a->fecha_ingreso?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td>
                                @if($a->estado)
                                    <span class="badge bg-success">Activo</span>
                                @else
                                    <span class="badge bg-danger">Inactivo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('arl-afiliados.edit', $a) }}"
                                   class="btn btn-outline-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('arl-afiliados.destroy', $a) }}" method="POST"
                                      class="d-inline form-delete" data-nombre="{{ $a->nombre }}">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                                No hay afiliados ARL registrados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3">
                {{ $afiliados->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>

</section>

@push('scripts')
<script>
let delayTimer;

function buscarEnTiempoReal() {
    clearTimeout(delayTimer);
    delayTimer = setTimeout(() => {
        const query = document.getElementById('buscador').value.trim();

        if (query === '') {
            location.reload();
            return;
        }

        fetch("{{ route('arl-afiliados.buscar') }}?buscar=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                let html = '';

                if (data.length === 0) {
                    html = `<tr><td colspan="11" class="text-center text-muted py-4">Sin resultados</td></tr>`;
                }

                data.forEach(a => {
                    const estado = a.estado
                        ? '<span class="badge bg-success">Activo</span>'
                        : '<span class="badge bg-danger">Inactivo</span>';
                    html += `
                        <tr>
                            <td class="ps-3">${a.documento?.nombre ?? ''} ${a.numero}</td>
                            <td>${a.nombre}</td>
                            <td>${a.arl?.nombre ?? '—'}</td>
                            <td>—</td>
                            <td class="text-end">$${Number(a.base_cotizacion).toLocaleString('es-CO')}</td>
                            <td class="text-end">—</td>
                            <td class="text-end">$${Number(a.administracion).toLocaleString('es-CO')}</td>
                            <td class="text-end">—</td>
                            <td>${a.fecha_ingreso ?? '—'}</td>
                            <td>${estado}</td>
                            <td class="text-center">
                                <a href="/arl-afiliados/${a.id}/edit" class="btn btn-outline-warning btn-sm">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>`;
                });

                document.getElementById('tabla-arl-afiliados').innerHTML = html;
            });
    }, 400);
}

$(function () {
    $('#tabla-arl-afiliados').on('submit', '.form-delete', function (e) {
        e.preventDefault();
        const form   = this;
        const nombre = $(this).data('nombre') || 'este registro';
        Swal.fire({
            title: '¿Eliminar afiliado ARL?',
            text: `«${nombre}» será eliminado permanentemente.`,
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
