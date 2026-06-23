@extends('layouts.main')

@section('titulo','Afiliados sin Recibo')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-person-dash me-2"></i>Afiliados sin Recibo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos.index') }}">Recibos</a></li>
                <li class="breadcrumb-item active">Sin recibo</li>
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

        <div class="p-3 border-bottom">
            <form action="{{ route('recibos.generar.todos') }}" method="POST" class="form-generar-todos">
                @csrf
                <button type="submit" class="btn btn-success {{ $afiliados->isEmpty() ? 'disabled' : '' }}">
                    <i class="bi bi-lightning-charge-fill me-1"></i>Generar todos los recibos
                </button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Documento</th>
                        <th>Nombre</th>
                        <th class="text-center" style="width:140px">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($afiliados as $a)
                    <tr>
                        <td class="ps-3">{{ $a->numero_documento }}</td>
                        <td class="fw-semibold">{{ $a->primer_nombre }} {{ $a->primer_apellido }}</td>
                        <td class="text-center">
                            <form action="{{ route('recibos.generar.uno', $a->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-lg me-1"></i>Generar
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle fs-2 d-block mb-2 text-success opacity-75"></i>
                            Todos los afiliados tienen recibo generado.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>

</section>

@push('scripts')
<script>
$(function () {
    $('.form-generar-todos').on('submit', function (e) {
        e.preventDefault();
        const form = this;
        Swal.fire({
            title: '¿Generar todos los recibos?',
            text: 'Se generarán los recibos pendientes para los afiliados activos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonText: 'Cancelar',
            confirmButtonText: 'Sí, generar',
        }).then(r => { if (r.isConfirmed) form.submit(); });
    });
});
</script>
@endpush

@endsection
