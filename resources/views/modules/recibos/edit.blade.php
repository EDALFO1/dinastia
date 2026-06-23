@extends('layouts.main')

@section('titulo','Editar Recibo')

@section('contenido')

<div class="pagetitle d-flex justify-content-between align-items-center">
    <div>
        <h1 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editar Recibo</h1>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
                <li class="breadcrumb-item"><a href="{{ route('recibos.index') }}">Recibos</a></li>
                <li class="breadcrumb-item active">{{ $recibo->numero }}</li>
            </ol>
        </nav>
    </div>
    <a href="{{ route('recibos.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i>Volver
    </a>
</div>

<section class="section mt-3">

<form action="{{ route('recibos.update',$recibo->id) }}" method="POST">
@csrf
@method('PUT')

<div class="row g-3">

<div class="col-xl-8">

<div class="card shadow-sm border-0">
    <div class="card-header py-3">
        <h5 class="mb-0 fw-semibold">Datos del recibo</h5>
        <p class="text-muted small mb-0 mt-1">
            Recibo N° <strong>{{ $recibo->numero }}</strong>
        </p>
    </div>
    <div class="card-body pt-4">

        <div class="row g-3">

            {{-- FECHA --}}
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha</label>
                <input type="date" name="fecha" id="fecha"
                       class="form-control"
                       value="{{ $recibo->fecha }}">
            </div>

            {{-- PERIODO --}}
            <div class="col-md-2">
                <label class="form-label fw-semibold">Periodo</label>
                <input type="text" id="periodo" class="form-control bg-light" readonly>
            </div>

            {{-- AFILIADO (BLOQUEADO) --}}
            <div class="col-md-7">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person me-1"></i>Afiliado
                </label>
                <input type="text" class="form-control bg-light"
                       value="{{ optional($recibo->afiliado)->primer_nombre }} {{ optional($recibo->afiliado)->primer_apellido }} - {{ optional($recibo->afiliado)->numero_documento }}"
                       readonly>
                <input type="hidden" id="afiliado_id" name="afiliado_id" value="{{ $recibo->afiliado_id }}">
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
                    <option value="">Sin novedad</option>
                    <option value="Ingreso" {{ $recibo->novedad=='Ingreso'?'selected':'' }}>Ingreso</option>
                    <option value="Retiro" {{ $recibo->novedad=='Retiro'?'selected':'' }}>Retiro</option>
                </select>
            </div>

            {{-- FECHA RETIRO --}}
            <div class="col-md-3" id="campo_retiro"
                 style="{{ $recibo->novedad=='Retiro'?'':'display:none;' }}">
                <label class="form-label fw-semibold">Fecha Retiro</label>
                <input type="date" name="fecha_retiro"
                       class="form-control"
                       value="{{ $recibo->fecha_retiro }}">
            </div>

        </div>

        <div class="d-flex gap-2 mt-4 pt-3 border-top">
            <button type="submit" class="btn btn-primary">
                <i class="bi bi-check-lg me-1"></i>Actualizar Recibo
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

let RECIBO_ID = {{ $recibo->id }};
let TOTAL_BASE = 0;

document.addEventListener("DOMContentLoaded", function(){

    calcularPeriodo();
    cargarPreview();

    document.getElementById("fecha").addEventListener("change", function(){
        calcularPeriodo();
        cargarPreview();
    });

    // 🔥 manejar novedad
    const novedad = document.getElementById('novedad');
    const retiro = document.getElementById('campo_retiro');

    function toggleRetiro(){
        retiro.style.display = (novedad.value === 'Retiro') ? 'block' : 'none';
    }

    novedad.addEventListener('change', toggleRetiro);
    toggleRetiro();

});

// 🔥 PERIODO
function calcularPeriodo(){
    let fecha = document.getElementById("fecha").value;
    if(!fecha) return;

    let f = new Date(fecha);
    document.getElementById("periodo").value =
        f.getFullYear() + "-" + (f.getMonth()+1).toString().padStart(2,'0');
}

// 🔥 PREVIEW
function cargarPreview(){

    let afiliado = document.getElementById("afiliado_id").value;
    let fecha = document.getElementById("fecha").value;

    if(!afiliado || !fecha) return;

    fetch("{{ route('recibos.preview') }}",{
        method:'POST',
        headers:{
            'Content-Type':'application/json',
            'X-CSRF-TOKEN':'{{ csrf_token() }}'
        },
        body: JSON.stringify({
            afiliado_id: afiliado,
            fecha: fecha,
            recibo_id: RECIBO_ID
        })
    })
    .then(res => res.json())
    .then(data => {

        if(!data){
            alert("No se puede recalcular el recibo");
            return;
        }

        TOTAL_BASE = Number(data.total || 0);

        document.getElementById("dias_liquidar").value = data.dias || 0;
        document.getElementById("ibc").value = data.ibc || 0;

        let tbody = document.getElementById("detalle_recibo");
        tbody.innerHTML = "";

        data.detalles.forEach(d => {
            tbody.innerHTML += `<tr><td class="ps-3">${d.concepto}</td><td class="text-end pe-3">${Number(d.valor).toLocaleString()}</td></tr>`;
        });

        document.getElementById("total").value = TOTAL_BASE;
        document.getElementById("total_recibo").innerHTML = TOTAL_BASE.toLocaleString();
    });

}

</script>

@endsection
