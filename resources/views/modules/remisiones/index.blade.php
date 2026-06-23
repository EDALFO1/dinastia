@extends('layouts.main')

@section('titulo',$titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center flex-wrap gap-2">
    <div>
        <h1 class="mb-0"><i class="bi bi-receipt me-2"></i>Remisiones</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item active">Remisiones</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('remisiones.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i>Crear Remisión
    </a>
</div>

<section class="section mt-3">

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Número</th>
                        <th>Fecha</th>
                        <th>Afiliado</th>
                        <th class="text-center">Días</th>
                        <th class="text-end">Total</th>
                        <th class="text-center" style="width:160px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($remisiones as $r)
                        <tr>
                            <td class="ps-3">
                                <span class="fw-semibold">{{ $r->numero }}</span>
                            </td>

                            <td>{{ $r->fecha }}</td>

                            <td>
                                <div class="fw-semibold">
                                    {{ $r->afiliado?->primer_nombre }} {{ $r->afiliado?->primer_apellido }}
                                </div>
                                <div class="text-muted small">
                                    CC {{ $r->afiliado?->numero_documento ?? 'N/A' }}
                                </div>
                            </td>

                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $r->dias_liquidar }}</span>
                            </td>

                            <td class="text-end">
                                <span class="fw-bold text-success">
                                    ${{ number_format($r->total, 0, ',', '.') }}
                                </span>
                            </td>

                            <td class="text-center">
                                <a href="{{ route('remisiones.imprimir',$r->id) }}"
                                   class="btn btn-outline-secondary btn-sm"
                                   title="Imprimir"
                                   target="_blank">
                                    <i class="bi bi-printer"></i>
                                </a>
                                <a href="{{ route('remisiones.edit',$r->id) }}"
                                   class="btn btn-outline-warning btn-sm"
                                   title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('remisiones.destroy',$r->id) }}"
                                      method="POST"
                                      class="d-inline form-delete"
                                      data-nombre="{{ $r->numero }}">
                                    @csrf
                                    @method('DELETE')
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
                                No hay remisiones registradas aún.
                                <a href="{{ route('remisiones.create') }}" class="d-block mt-1">
                                    Crea la primera remisión →
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($remisiones->hasPages())
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $remisiones->links() }}
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
            title: '¿Eliminar remisión?',
            text: `La remisión «${nombre}» será eliminada permanentemente.`,
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
