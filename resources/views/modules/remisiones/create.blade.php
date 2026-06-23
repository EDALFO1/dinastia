@extends('layouts.main')

@section('titulo',$titulo)

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nueva Remisión</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('remisiones.index') }}">Remisiones</a></li>
                <li class="breadcrumb-item active">Nueva</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('remisiones.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

<form action="{{ route('remisiones.store') }}" method="POST">
@csrf

<div class="row g-3">

<div class="col-xl-8">

<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Datos de la remisión</h5>
        <p class="text-muted small mb-0 mt-1">
            El número de remisión se generará automáticamente.
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
                       value="{{ date('Y-m-d') }}">
            </div>

            {{-- PERIODO --}}
            <div class="col-md-1">
                <label class="form-label fw-semibold">Periodo</label>
                <input type="text" id="periodo" class="form-control bg-light" readonly>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- BUSCADOR --}}
            <div class="col-md-7 position-relative">
                <label class="form-label fw-semibold">
                    <i class="bi bi-search me-1"></i>Afiliado
                </label>
                <input type="text" id="buscar_afiliado"
                       class="form-control"
                       placeholder="Buscar por documento o nombre">
                <input type="hidden" name="afiliado_id" id="afiliado_id">
                <div id="resultados_afiliado" class="list-group shadow-sm"
                     style="position:absolute; z-index:20; width:100%;"></div>
            </div>

            {{-- FECHA AFILIACION --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha afiliación</label>
                <input type="text" id="fecha_afiliacion" class="form-control bg-light" readonly>
            </div>

            {{-- DIAS --}}
            <div class="col-md-2">
                <label class="form-label fw-semibold">Días</label>
                <input type="number" name="dias_liquidar" id="dias_liquidar"
                       class="form-control bg-light" readonly>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- MENSAJERIA --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Mensajería</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="mensajeria" id="mensajeria"
                           class="form-control" value="0">
                </div>
            </div>

            {{-- INTERESES --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Intereses</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" step="0.01" name="intereses" id="intereses"
                           class="form-control" value="0">
                </div>
            </div>

            {{-- TOTAL --}}
            <div class="col-md-3 ms-auto">
                <label class="form-label fw-semibold">Total</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" name="total" id="total"
                           class="form-control fw-bold" readonly>
                </div>
            </div>

            {{-- CARGOS DINAMICOS --}}
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
                <i class="bi bi-check-lg me-1"></i>Guardar Remisión
            </button>
            <a href="{{ route('remisiones.index') }}" class="btn btn-outline-secondary">
                Cancelar
            </a>
        </div>

    </div>
</div>

</div>

{{-- PREVIEW --}}
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
let TOTAL_BASE = 0;
let contadorCargos = 0;

document.addEventListener("DOMContentLoaded", function(){

    calcularPeriodo();

    document.getElementById("fecha").addEventListener("change", function(){
        calcularPeriodo();
        cargarPreview();
    });

    const input = document.getElementById("buscar_afiliado");
    const resultados = document.getElementById("resultados_afiliado");

    let timeout = null;

    input.addEventListener("keyup", function(){

        clearTimeout(timeout);

        let texto = this.value.trim();

        if(texto.length < 2){
            resultados.innerHTML = "";
            return;
        }

        timeout = setTimeout(() => {

            fetch(`/buscar-afiliados?q=${texto}`)
            .then(res => res.json())
            .then(data => {

                resultados.innerHTML = "";

                if(!data.length){
                    resultados.innerHTML = `<div class="list-group-item">Sin resultados</div>`;
                    return;
                }

                if(data.length === 1){
                    seleccionarAfiliado(data[0]);
                    return;
                }

                data.forEach(a => {

                    let item = document.createElement("a");
                    item.classList.add("list-group-item","list-group-item-action");

                    item.innerText = `${a.primer_nombre} ${a.primer_apellido} - ${a.numero_documento}`;

                    item.onclick = function(){
                        seleccionarAfiliado(a);
                    };

                    resultados.appendChild(item);

                });

            });

        }, 300);

    });

    function seleccionarAfiliado(a){
        document.getElementById("buscar_afiliado").value =
            `${a.primer_nombre} ${a.primer_apellido} - ${a.numero_documento}`;

        document.getElementById("afiliado_id").value = a.id;
        resultados.innerHTML = "";

        cargarPreview();
    }

    // eventos manuales
    ["mensajeria","intereses"].forEach(id => {
        let el = document.getElementById(id);
        if(el){
            el.addEventListener("input", recalcularTotal);
        }
    });

});

// =========================
// 🔥 CARGOS DINÁMICOS
// =========================

function agregarCargo(){

    contadorCargos++;

    let html = `
    <div class="row g-2 mt-1 cargo-item align-items-center" data-id="${contadorCargos}">
        <div class="col-7">
            <input type="text" name="cargos[${contadorCargos}][concepto]"
                class="form-control form-control-sm"
                placeholder="Concepto">
        </div>

        <div class="col-4">
            <div class="input-group input-group-sm">
                <span class="input-group-text">$</span>
                <input type="number" step="0.01"
                    name="cargos[${contadorCargos}][valor]"
                    class="form-control valor-cargo">
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
    document.querySelector(`.cargo-item[data-id='${id}']`)?.remove();
    recalcularTotal();
}

// =========================
// 🔥 RECALCULO
// =========================

function recalcularTotal(){

    let totalFinal = TOTAL_BASE;
    let tbody = document.getElementById("detalle_remision");

    document.querySelectorAll(".manual-row, .cargo-row")
        .forEach(el => el.remove());

    let mensajeria = Number(document.getElementById("mensajeria").value || 0);
    let intereses = Number(document.getElementById("intereses").value || 0);

    if(mensajeria > 0){
        tbody.innerHTML += `<tr class="manual-row"><td class="ps-3">Mensajería</td><td class="text-end pe-3">${mensajeria.toLocaleString()}</td></tr>`;
        totalFinal += mensajeria;
    }

    if(intereses > 0){
        tbody.innerHTML += `<tr class="manual-row"><td class="ps-3">Intereses</td><td class="text-end pe-3">${intereses.toLocaleString()}</td></tr>`;
        totalFinal += intereses;
    }

    document.querySelectorAll(".valor-cargo").forEach(input => {

        let valor = Number(input.value || 0);
        let concepto = input.closest('.cargo-item')
            .querySelector('input[type="text"]').value || 'Cargo';

        if(valor > 0){
            tbody.innerHTML += `<tr class="cargo-row"><td class="ps-3">${concepto}</td><td class="text-end pe-3">${valor.toLocaleString()}</td></tr>`;
            totalFinal += valor;
        }
    });

    document.getElementById("total").value = totalFinal;

    let totalRemision = document.getElementById("total_remision");
    if(totalRemision){
        totalRemision.innerHTML = totalFinal.toLocaleString();
    }
}

document.addEventListener("input", function(e){
    if(e.target.classList.contains("valor-cargo")){
        recalcularTotal();
    }
});

// =========================
// 🔥 OTROS
// =========================

function calcularPeriodo(){
    let fecha = document.getElementById("fecha").value;
    if(!fecha) return;

    let f = new Date(fecha);
    document.getElementById("periodo").value =
        f.getFullYear() + "-" + (f.getMonth()+1).toString().padStart(2,'0');
}

function cargarPreview(){

    let afiliado = document.getElementById("afiliado_id").value;
    let fecha = document.getElementById("fecha").value;

    if(!afiliado || !fecha) return;

    fetch("{{ route('remisiones.preview') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({ afiliado_id:afiliado, fecha:fecha })
    })
    .then(res => res.json())
    .then(data => {

        TOTAL_BASE = Number(data.total || 0);

        document.getElementById("fecha_afiliacion").value = data.fecha_afiliacion || '';
        document.getElementById("dias_liquidar").value = data.dias || 0;

        let tbody = document.getElementById("detalle_remision");
        tbody.innerHTML = "";

        data.detalles.forEach(d => {
            tbody.innerHTML += `<tr><td class="ps-3">${d.concepto}</td><td class="text-end pe-3">${Number(d.valor).toLocaleString()}</td></tr>`;
        });

        recalcularTotal();
    });

}
</script>

@endsection
