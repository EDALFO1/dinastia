<form action="{{ route('remisiones.store') }}" method="POST">
@csrf

<div class="row">

    {{-- 🔥 EMPRESA (SOLO VISUAL) --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Empresa</label>
        <input type="text"
               class="form-control bg-light"
               value="{{ session('empresa_nombre') }}"
               readonly>
    </div>

    {{-- 🔹 NIT (OPCIONAL SI LO TIENES EN SESIÓN) --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">NIT</label>
        <input type="text"
               class="form-control bg-light"
               value="{{ session('empresa_nit') }}"
               readonly>
    </div>

    {{-- 🔹 FECHA --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Fecha</label>
        <input
        type="date"
        name="fecha"
        id="fecha"
        class="form-control @error('fecha') is-invalid @enderror"
        value="{{ old('fecha', date('Y-m-d')) }}"
        required>
    </div>

    {{-- 🔹 PERIODO --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Periodo</label>
        <input type="text" id="periodo" class="form-control" readonly>
    </div>

    {{-- 🔥 BUSCADOR AFILIADO --}}
    <div class="col-md-6 mb-3 position-relative">

        <label class="form-label">Afiliado</label>

        <input
        type="text"
        id="buscar_afiliado"
        class="form-control"
        placeholder="Buscar por documento o nombre">

        <input
        type="hidden"
        name="afiliado_id"
        id="afiliado_id">

        <div id="resultados_afiliado" class="list-group"></div>

    </div>

    {{-- 🔹 FECHA AFILIACION --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Fecha afiliación</label>
        <input type="text" id="fecha_afiliacion" class="form-control" readonly>
    </div>

    {{-- 🔹 DIAS --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Días</label>
        <input type="number" id="dias_liquidar" name="dias_liquidar"
               class="form-control" readonly>
    </div>

    {{-- 🔹 TOTAL --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Total</label>
        <input type="number" id="total" name="total"
               class="form-control" readonly>
    </div>

    <div class="col-12 mt-3">
    <label><strong>Cargos adicionales</strong></label>

    <div id="contenedor_cargos"></div>

    <button type="button" class="btn btn-sm btn-success mt-2" onclick="agregarCargo()">
        + Agregar cargo
    </button>
</div>

    <div class="col-md-3 mb-3">
    <label>Mensajería</label>
    <input type="number" id="mensajeria" name="mensajeria"
value="{{ $remision->mensajeria }}">
</div>

<div class="col-md-3 mb-3">
    <label>Intereses</label>
    <input type="number" id="intereses" name="intereses"
value="{{ $remision->intereses }}">
</div>

</div>

<button class="btn btn-primary">Guardar</button>

<a href="{{ route('remisiones.index') }}" class="btn btn-secondary">
Cancelar
</a>

</form>