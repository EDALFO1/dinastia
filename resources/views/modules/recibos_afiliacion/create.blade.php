@extends('layouts.main')

@section('titulo','Nuevo Recibo de Afiliación')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nuevo Recibo de Afiliación</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos-afiliacion.index') }}">Recibos de Afiliación</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('recibos-afiliacion.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form action="{{ route('recibos-afiliacion.store') }}" method="POST">
@csrf

<div class="row g-3">

<div class="col-xl-8">
<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Datos del Recibo</h5>
    </div>
    <div class="card-body pt-4">

        <div class="row g-3">

            {{-- FECHA --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="fecha" class="form-control @error('fecha') is-invalid @enderror"
                       value="{{ old('fecha', date('Y-m-d')) }}" required>
                @error('fecha')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- AFILIADO --}}
            <div class="col-md-8 position-relative">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Afiliado
                </label>
                <input type="text" id="buscar_afiliado"
                       class="form-control @error('afiliado_id') is-invalid @enderror"
                       placeholder="Buscar por documento o nombre…"
                       autocomplete="off">
                <input type="hidden" name="afiliado_id" id="afiliado_id"
                       value="{{ old('afiliado_id') }}" required>
                <div id="resultados_afiliado" class="list-group shadow-sm"
                     style="position:absolute; z-index:20; width:calc(100% - 24px);"></div>
                @error('afiliado_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- CONCEPTO --}}
            <div class="col-12">
                <label class="form-label fw-semibold">Concepto</label>
                <textarea name="concepto" rows="3"
                          class="form-control @error('concepto') is-invalid @enderror"
                          placeholder="Descripción del servicio de afiliación cobrado…"
                          required>{{ old('concepto', 'Servicio de afiliación a seguridad social (EPS, Pensión, ARL, Caja)') }}</textarea>
                @error('concepto')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- VALOR --}}
            <div class="col-md-4">
                <label class="form-label fw-semibold">Valor a Cobrar</label>
                <div class="input-group">
                    <span class="input-group-text fw-semibold">$</span>
                    <input type="number" name="valor" id="valor" step="1" min="0"
                           class="form-control fw-bold @error('valor') is-invalid @enderror"
                           value="{{ old('valor') }}"
                           placeholder="0" required>
                    @error('valor')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- NOTAS --}}
            <div class="col-12">
                <label class="form-label fw-semibold">Notas internas <span class="text-muted fw-normal">(opcional)</span></label>
                <textarea name="notas" rows="2"
                          class="form-control @error('notas') is-invalid @enderror"
                          placeholder="Observaciones adicionales…">{{ old('notas') }}</textarea>
                @error('notas')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

        </div>

        <div class="d-flex gap-2 mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Guardar Recibo
            </button>
            <a href="{{ route('recibos-afiliacion.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>

    </div>
</div>
</div>

{{-- PANEL LATERAL --}}
<div class="col-xl-4">
    <div class="card shadow-sm border-0" style="position:sticky; top:80px;">
        <div class="card-header py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-person-check me-1"></i>Afiliado seleccionado
            </h5>
        </div>
        <div class="card-body" id="panel_afiliado">
            <p class="text-muted small mb-0 text-center py-3">
                Busca y selecciona un afiliado para ver su información.
            </p>
        </div>
    </div>
</div>

</div>

</form>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const input      = document.getElementById('buscar_afiliado');
    const resultados = document.getElementById('resultados_afiliado');
    const hidden     = document.getElementById('afiliado_id');
    const panel      = document.getElementById('panel_afiliado');

    let timeout = null;

    input.addEventListener('keyup', function () {
        clearTimeout(timeout);
        const texto = this.value.trim();

        if (texto.length < 2) {
            resultados.innerHTML = '';
            return;
        }

        timeout = setTimeout(() => {
            fetch(`/afiliados/buscar?q=${encodeURIComponent(texto)}`)
                .then(res => res.json())
                .then(data => {
                    resultados.innerHTML = '';

                    if (!data.length) {
                        resultados.innerHTML = '<div class="list-group-item text-muted small">Sin resultados</div>';
                        return;
                    }

                    if (data.length === 1) {
                        seleccionar(data[0]);
                        return;
                    }

                    data.forEach(a => {
                        const item = document.createElement('a');
                        item.classList.add('list-group-item', 'list-group-item-action');
                        item.innerHTML = `<strong>${a.primer_nombre} ${a.primer_apellido}</strong>
                            <span class="text-muted small ms-2">${a.numero_documento}</span>`;
                        item.onclick = () => seleccionar(a);
                        resultados.appendChild(item);
                    });
                });
        }, 300);
    });

    function seleccionar(a) {
        input.value  = `${a.primer_nombre} ${a.primer_apellido} — ${a.numero_documento}`;
        hidden.value = a.id;
        resultados.innerHTML = '';

        panel.innerHTML = `
            <div class="mb-2">
                <div class="text-muted small text-uppercase" style="font-size:0.72rem;letter-spacing:.05em">Nombre</div>
                <div class="fw-semibold">${a.primer_nombre} ${a.primer_apellido}</div>
            </div>
            <div class="mb-2">
                <div class="text-muted small text-uppercase" style="font-size:0.72rem;letter-spacing:.05em">Documento</div>
                <div class="fw-semibold">${a.numero_documento}</div>
            </div>
            ${a.tiene_afiliacion_activa
                ? '<span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Afiliación activa</span>'
                : '<span class="badge bg-secondary">Sin afiliación activa</span>'}
        `;
    }

    document.addEventListener('click', function (e) {
        if (!resultados.contains(e.target) && e.target !== input) {
            resultados.innerHTML = '';
        }
    });
});
</script>

@endsection
