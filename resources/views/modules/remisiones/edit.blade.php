@extends('layouts.main')

@section('titulo',$titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Remisión</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('remisiones.index') }}">Remisiones</a></li>
                <li class="breadcrumb-item active">{{ $remision->numero }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('remisiones.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

<form action="{{ route('remisiones.update',$remision->id) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-3">

<div class="col-xl-8">

<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Datos de la remisión</h5>
        <p class="text-muted small mb-0 mt-1">
            Remisión N° <strong>{{ $remision->numero }}</strong>
        </p>
    </div>
    <div class="card-body pt-4">

        <div class="row g-3">

            {{-- EMPRESA --}}
            <div class="col-md-6">
                <label class="form-label fw-semibold">Empresa</label>
                <input type="text" class="form-control bg-light"
                       value="{{ session('empresa_nombre') }}" readonly>
            </div>

            {{-- NIT --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">NIT</label>
                <input type="text" class="form-control bg-light"
                       value="{{ session('empresa_nit') }}" readonly>
            </div>

            {{-- FECHA --}}
            <div class="col-md-2">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="fecha" id="fecha"
                       class="form-control"
                       value="{{ $remision->fecha }}">
            </div>

            {{-- PERIODO --}}
            <div class="col-md-1">
                <label class="form-label fw-semibold">Periodo</label>
                <input type="text" id="periodo" class="form-control bg-light" readonly>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- AFILIADO --}}
            <div class="col-md-7">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person me-1"></i>Afiliado
                </label>
                <input type="text" class="form-control bg-light"
                       value="{{ $remision->afiliado?->primer_nombre }} {{ $remision->afiliado?->primer_apellido }} - {{ $remision->afiliado?->numero_documento ?? 'N/A' }}"
                       readonly>
            </div>

            {{-- FECHA AFILIACION --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha afiliación</label>
                <input type="text" id="fecha_afiliacion"
                       class="form-control bg-light"
                       value="{{ $remision->afiliado->afiliacion->fecha_afiliacion ?? '' }}"
                       readonly>
            </div>

            {{-- DIAS --}}
            <div class="col-md-2">
                <label class="form-label fw-semibold">Días</label>
                <input type="number" id="dias_liquidar"
                       class="form-control bg-light"
                       value="{{ $remision->dias_liquidar }}"
                       readonly>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- MENSAJERIA --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Mensajería</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="mensajeria" name="mensajeria"
                           class="form-control"
                           value="{{ $remision->mensajeria }}">
                </div>
            </div>

            {{-- INTERESES --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Intereses</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="intereses" name="intereses"
                           class="form-control"
                           value="{{ $remision->intereses }}">
                </div>
            </div>

            {{-- TOTAL --}}
            <div class="col-md-3 ms-auto">
                <label class="form-label fw-semibold">Total</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="total" name="total"
                           class="form-control fw-bold"
                           value="{{ $remision->total }}">
                </div>
            </div>

            {{-- CARGOS --}}
            <div class="col-12 mt-2">
                <label class="form-label fw-semibold">Cargos adicionales</label>
                <div id="contenedor_cargos"></div>
                <button type="button" class="btn btn-outline-success btn-sm mt-2"
                        onclick="agregarCargo()">
                    <i class="bi bi-plus-lg me-1"></i>Agregar cargo
                </button>
            </div>

        </div>

        <div class="d-flex gap-2 mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Actualizar Remisión
            </button>
            <a href="{{ route('remisiones.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>

    </div>
</div>

</div>

{{-- DETALLE --}}
<div class="col-xl-4">
    <div class="card shadow-sm border-0" style="position:sticky; top:80px;">
        <div class="card-header py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="bi bi-receipt-cutoff me-1"></i>Detalle de Remisión
            </h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Concepto</th>
                        <th class="text-end pe-3">Valor</th>
                    </tr>
                </thead>
                <tbody id="detalle_remision"></tbody>
                <tfoot>
                    <tr class="table-light">
                        <th class="ps-3">Total</th>
                        <th class="text-end pe-3" id="total_remision"></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

</div>

</form>

</section>

<script>

// 🔥 SOLO BASE (SIN manuales)
let DETALLES = @json(
    $remision->detalles->whereNotIn('concepto',['Mensajería','Intereses'])
);

let contadorCargos = 0;

document.addEventListener("DOMContentLoaded", function(){

    recalcularTotal();

    ["mensajeria","intereses"].forEach(id => {
        document.getElementById(id)
        ?.addEventListener("input", recalcularTotal);
    });

});

// =========================
// 🔥 CARGOS
// =========================

function agregarCargo(nombre = '', valor = 0){

    contadorCargos++;

    let html = `
    <div class="row g-2 mt-1 cargo-item align-items-center" data-id="${contadorCargos}">
        <div class="col-7">
            <input type="text" name="cargos[${contadorCargos}][concepto]"
                class="form-control form-control-sm"
                value="${nombre}">
        </div>

        <div class="col-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01"
                    name="cargos[${contadorCargos}][valor]"
                    class="form-control valor-cargo"
                    value="${valor}">
            </div>
        </div>

        <div class="col-1 text-end">
            <button type="button" class="btn btn-outline-danger btn-sm"
                onclick="eliminarCargo(${contadorCargos})">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>`;

    document.getElementById("contenedor_cargos")
        .insertAdjacentHTML('beforeend', html);

    recalcularTotal();
}

function eliminarCargo(id){
    document.querySelector(`[data-id='${id}']`)?.remove();
    recalcularTotal();
}

// =========================
// 🔥 TOTAL
// =========================

function recalcularTotal(){

    let totalFinal = 0;
    let tbody = document.getElementById("detalle_remision");

    tbody.innerHTML = "";

    DETALLES.forEach(d => {

        tbody.innerHTML += `
        <tr>
            <td class="ps-3">${d.concepto}</td>
            <td class="text-end pe-3">${Number(d.valor).toLocaleString()}</td>
        </tr>`;

        totalFinal += Number(d.valor);
    });

    let mensajeria = Number(document.getElementById("mensajeria").value || 0);
    let intereses = Number(document.getElementById("intereses").value || 0);

    if(mensajeria > 0){
        tbody.innerHTML += `<tr><td class="ps-3">Mensajería</td><td class="text-end pe-3">${mensajeria.toLocaleString()}</td></tr>`;
        totalFinal += mensajeria;
    }

    if(intereses > 0){
        tbody.innerHTML += `<tr><td class="ps-3">Intereses</td><td class="text-end pe-3">${intereses.toLocaleString()}</td></tr>`;
        totalFinal += intereses;
    }

    document.querySelectorAll(".valor-cargo").forEach(input => {

        let valor = Number(input.value || 0);
        let concepto = input.closest('.cargo-item')
            .querySelector('input[type="text"]').value || 'Cargo';

        if(valor > 0){
            tbody.innerHTML += `<tr><td class="ps-3">${concepto}</td><td class="text-end pe-3">${valor.toLocaleString()}</td></tr>`;
            totalFinal += valor;
        }
    });

    document.getElementById("total").value = totalFinal;
    document.getElementById("total_remision").innerHTML = totalFinal.toLocaleString();
}

document.addEventListener("input", function(e){
    if(e.target.classList.contains("valor-cargo")){
        recalcularTotal();
    }
});

</script>

@endsection
