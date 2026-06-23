@extends('layouts.main')

@section('titulo','Crear Recibo')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Crear Recibo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos.index') }}">Recibos</a></li>
                <li class="breadcrumb-item active">Nuevo</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('recibos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

<form action="{{ route('recibos.store') }}" method="POST">
@csrf

<div class="row g-3">

<div class="col-xl-8">

<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Datos del recibo</h5>
        <p class="text-muted small mb-0 mt-1">
            Los valores se calculan automáticamente según el afiliado seleccionado.
        </p>
    </div>
    <div class="card-body pt-4">

        <div class="row g-3">

            {{-- FECHA --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="fecha" id="fecha"
                       class="form-control"
                       value="{{ date('Y-m-d') }}">
            </div>

            {{-- BUSCADOR --}}
            <div class="col-md-6 position-relative">
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

            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha afiliación</label>
                <input type="date" id="fecha_afiliacion" class="form-control bg-light" readonly>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- DIAS --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Días</label>
                <input type="number" id="dias_liquidar" class="form-control bg-light" readonly>
            </div>

            {{-- IBC --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">IBC</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="ibc" class="form-control bg-light" readonly>
                </div>
            </div>

            {{-- TOTAL --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Total</label>
                <div class="input-group">
                    <span class="input-group-text">$</span>
                    <input type="number" id="total" name="total" class="form-control fw-bold" readonly>
                </div>
            </div>

            <div class="col-12"><hr class="my-1"></div>

            {{-- NOVEDAD --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Novedad</label>
                <select name="novedad" id="novedad" class="form-select">
                    <option value="">NINGUNA</option>
                    <option value="Retiro">Retiro</option>
                </select>
            </div>

            <div class="col-md-3" id="div_fecha_retiro" style="display:none;">
                <label class="form-label fw-semibold">Fecha retiro</label>
                <input type="date" name="fecha_retiro" id="fecha_retiro" class="form-control">
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
                <i class="bi bi-check-lg me-1"></i>Guardar Recibo
            </button>
            <a href="{{ route('recibos.index') }}" class="btn btn-outline-secondary">
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
                <i class="bi bi-receipt-cutoff me-1"></i>Detalle del Recibo
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
                <tbody id="detalle_recibo"></tbody>
                <tfoot>
                    <tr class="table-light">
                        <th class="ps-3">Total</th>
                        <th class="text-end pe-3" id="total_recibo"></th>
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

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById("buscar_afiliado");
    const resultados = document.getElementById("resultados_afiliado");
    const afiliado_id = document.getElementById("afiliado_id");

    const fecha = document.getElementById('fecha');
    const novedad = document.getElementById('novedad');
    const fechaRetiro = document.getElementById('fecha_retiro');
    const divFechaRetiro = document.getElementById('div_fecha_retiro');

    let timeout = null;

    // =========================
    // 🔥 BUSCADOR AFILIADOS
    // =========================
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
        input.value = `${a.primer_nombre} ${a.primer_apellido} - ${a.numero_documento}`;
        afiliado_id.value = a.id;
        resultados.innerHTML = "";
        calcularPreview();
    }

    // =========================
    // 🔥 NOVEDAD RETIRO
    // =========================
    novedad.addEventListener('change', function () {

        if (this.value === 'Retiro') {
            divFechaRetiro.style.display = 'block';
        } else {
            divFechaRetiro.style.display = 'none';
            fechaRetiro.value = '';
        }

        calcularPreview();
    });

    // =========================
    // 🔥 EVENTOS
    // =========================
    fecha.addEventListener('change', calcularPreview);
    fechaRetiro.addEventListener('change', calcularPreview);

    // =========================
    // 🔥 VALIDAR RETIRO
    // =========================
    function validarFechaRetiro(){

        if (novedad.value !== 'Retiro' || !fechaRetiro.value) return true;

        let [y, m, d] = fechaRetiro.value.split('-');
        let [yr, mr, dr] = fecha.value.split('-');

        let fechaSeleccionada = new Date(y, m-1, d);
        let fechaRecibo = new Date(yr, mr-1, dr);

        let mesAnterior = new Date(
            fechaRecibo.getFullYear(),
            fechaRecibo.getMonth() - 1,
            1
        );

        if (
            fechaSeleccionada.getMonth() !== mesAnterior.getMonth() ||
            fechaSeleccionada.getFullYear() !== mesAnterior.getFullYear()
        ) {
            alert("⚠ La fecha de retiro debe ser del mes anterior al recibo");
            fechaRetiro.value = '';
            return false;
        }

        return true;
    }

    // =========================
    // 🔥 PREVIEW
    // =========================
    function calcularPreview(){

        if(!afiliado_id.value || !fecha.value) return;

        if(!validarFechaRetiro()) return;

        fetch("{{ route('recibos.preview') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                afiliado_id: afiliado_id.value,
                fecha: fecha.value,
                novedad: novedad.value,
                fecha_retiro: fechaRetiro.value
            })
        })
        .then(res => res.json())
        .then(data => {

            if(!data) return;

            document.getElementById('dias_liquidar').value = data.dias || 0;
            document.getElementById('ibc').value = data.ibc || 0;
            document.getElementById('fecha_afiliacion').value = data.fecha_afiliacion ?? '';

            TOTAL_BASE = Number(data.total || 0);

            let tbody = document.getElementById('detalle_recibo');
            tbody.innerHTML = "";

            data.detalles.forEach(d => {
                tbody.innerHTML += `<tr><td class="ps-3">${d.concepto}</td><td class="text-end pe-3">${Number(d.valor).toLocaleString()}</td></tr>`;
            });

            recalcularTotal();
        });
    }

    // expone calcularPreview para los handlers de arriba
    window.calcularPreview = calcularPreview;

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
    let tbody = document.getElementById("detalle_recibo");

    document.querySelectorAll(".cargo-row").forEach(el => el.remove());

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

    let totalRecibo = document.getElementById("total_recibo");
    if(totalRecibo){
        totalRecibo.innerHTML = totalFinal.toLocaleString();
    }
}

document.addEventListener("input", function(e){
    if(e.target.classList.contains("valor-cargo")){
        recalcularTotal();
    }
});

</script>
@endsection
