@extends('layouts.main')
@section('titulo', $titulo)
@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-person-vcard me-2"></i>Afiliados</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Afiliados</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('afiliados.create') }}" class="btn btn-primary">
        <i class="bi bi-person-plus me-1"></i>Nuevo
    </a>
</div>

<section class="section mt-3">

{{-- TOOLBAR: importar / exportar / plantilla --}}
<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-3">
        <div class="row g-2 align-items-center">

            {{-- Importar --}}
            <div class="col-lg-5">
                <form action="{{ route('afiliados.importar') }}" method="POST" enctype="multipart/form-data" class="d-flex gap-2 align-items-center">
                    @csrf
                    <input type="file" name="archivo" class="form-control form-control-sm" style="max-width: 220px;" required>
                    <button class="btn btn-success btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-upload"></i>Importar
                    </button>
                </form>
            </div>

            {{-- Exportar con filtro de estado --}}
            <div class="col-lg-4">
                <form method="GET" action="{{ route('export.afiliados.exportar') }}" class="d-flex gap-2">
                    <select name="estado" class="form-select form-select-sm" style="max-width: 140px;">
                        <option value="">Todos</option>
                        <option value="1" {{ request('estado') == '1' ? 'selected' : '' }}>Activos</option>
                        <option value="0" {{ request('estado') == '0' ? 'selected' : '' }}>Inactivos</option>
                    </select>
                    <button class="btn btn-success btn-sm d-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-excel"></i>Exportar
                    </button>
                </form>
            </div>

            {{-- Plantilla --}}
            <div class="col-lg-3 d-flex justify-content-lg-end">
                <a href="{{ route('afiliados.plantilla') }}" class="btn btn-outline-success btn-sm d-flex align-items-center gap-1">
                    <i class="bi bi-download"></i>Plantilla
                </a>
            </div>

        </div>
    </div>
</div>

{{-- Buscador en tiempo real --}}
<div class="mb-3">
    <div class="input-group">
        <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
        <input type="text" id="buscador" class="form-control" placeholder="Buscar afiliado..." onkeyup="buscarEnTiempoReal()">
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="px-3 pt-3 text-muted small">
            Mostrando {{ $afiliados->firstItem() }} a {{ $afiliados->lastItem() }} de {{ $afiliados->total() }} registros
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Documento</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Empresa Laboral</th>
                        <th>Estado</th>
                        <th class="text-center" style="width:150px">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-afiliados">
                    @forelse($afiliados as $a)
                    <tr>
                        <td class="ps-3">{{ $a->documento?->nombre ?? '' }} {{ $a->numero_documento }}</td>
                        <td>{{ $a->primer_nombre }} {{ $a->segundo_nombre }} {{ $a->primer_apellido }} {{ $a->segundo_apellido }}</td>
                        <td>{{ $a->telefono }}</td>
                        <td>{{ $a->empresaLaboral?->nombre ?? '' }}</td>
                        <td>
                            @if($a->estado)
                            <span class="badge bg-success">Activo</span>
                            @else
                            <span class="badge bg-danger">Inactivo</span>
                            @endif
                        </td>
                        <td class="text-center">
                            {{-- Drive --}}
                            @if($a->google_drive_folder_id)
                                <a href="{{ $a->google_drive_folder_id }}"
                                   target="_blank"
                                   rel="noopener noreferrer"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Abrir carpeta en Google Drive">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 87.3 78" style="vertical-align:-1px">
                                        <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                                        <path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47"/>
                                        <path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z" fill="#ea4335"/>
                                        <path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d"/>
                                        <path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc"/>
                                        <path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 27h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
                                    </svg>
                                </a>
                            @else
                                <span class="text-muted small" title="Sin carpeta Drive">
                                    <i class="bi bi-folder2 opacity-25"></i>
                                </span>
                            @endif
                            {{-- Editar --}}
                            <a href="{{ route('afiliados.edit', $a) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            {{-- Eliminar --}}
                            <form action="{{ route('afiliados.destroy', $a) }}" method="POST" class="d-inline form-delete" data-nombre="{{ $a->primer_nombre }} {{ $a->primer_apellido }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm" title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-2 d-block mb-2 opacity-50"></i>
                            No hay afiliados registrados.
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
        let input = document.getElementById('buscador');
        let query = input.value.trim();

        if (query === '') {
            location.reload();
            return;
        }

        fetch("{{ route('afiliados.buscar') }}?buscar=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                let html = '';

                if (data.length === 0) {
                    html = `<tr><td colspan="6" class="text-center text-muted py-4">Sin resultados</td></tr>`;
                }

                data.forEach(a => {
                    html += `
                        <tr>
                            <td class="ps-3">${a.documento?.nombre ?? ''} ${a.numero_documento}</td>
                            <td>${a.primer_nombre} ${a.primer_apellido}</td>
                            <td>${a.telefono ?? ''}</td>
                            <td>${a.empresa_laboral?.nombre ?? ''}</td>
                            <td>${a.estado
                                    ? '<span class="badge bg-success">Activo</span>'
                                    : '<span class="badge bg-danger">Inactivo</span>'}</td>
                            <td class="text-center">
                                <a href="/afiliados/${a.id}/edit" class="btn btn-outline-warning btn-sm" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                            </td>
                        </tr>
                    `;
                });

                document.getElementById('tabla-afiliados').innerHTML = html;
            });
    }, 400);
}

$(function () {
    $('#tabla-afiliados').on('submit', '.form-delete', function (e) {
        e.preventDefault();
        const form = this;
        const nombre = $(this).data('nombre') || 'este registro';
        Swal.fire({
            title: '¿Eliminar afiliado?',
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
