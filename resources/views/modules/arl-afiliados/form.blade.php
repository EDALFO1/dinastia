<div class="row g-3">

    {{-- TIPO DOCUMENTO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo Documento <span class="text-danger">*</span></label>
        <select name="documento_id" class="form-select @error('documento_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($documentos as $doc)
                <option value="{{ $doc->id }}"
                    {{ old('documento_id', $arl_afiliado->documento_id ?? '') == $doc->id ? 'selected' : '' }}>
                    {{ $doc->nombre }}
                </option>
            @endforeach
        </select>
        @error('documento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- NÚMERO DOCUMENTO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Número Documento <span class="text-danger">*</span></label>
        <input type="text" name="numero"
               class="form-control @error('numero') is-invalid @enderror"
               value="{{ old('numero', $arl_afiliado->numero ?? '') }}"
               maxlength="50" required>
        @error('numero') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- NOMBRE COMPLETO --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre Completo <span class="text-danger">*</span></label>
        <input type="text" name="nombre"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $arl_afiliado->nombre ?? '') }}"
               maxlength="255" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ARL --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">ARL <span class="text-danger">*</span></label>
        <select name="arl_id" id="sel_arl"
                class="form-select @error('arl_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($arls as $arl)
                <option value="{{ $arl->id }}"
                        data-porcentaje="{{ $arl->porcentaje }}"
                        data-nivel="{{ $arl->nivel }}"
                    {{ old('arl_id', $arl_afiliado->arl_id ?? '') == $arl->id ? 'selected' : '' }}>
                    {{ $arl->nombre }} — Riesgo {{ $arl->nivel }} ({{ number_format($arl->porcentaje, 4) }}%)
                </option>
            @endforeach
        </select>
        @error('arl_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- EMPRESA LABORAL --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Empresa Empleadora</label>
        <select name="empresa_laboral_id"
                class="form-select @error('empresa_laboral_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($empresas as $emp)
                <option value="{{ $emp->id }}"
                    {{ old('empresa_laboral_id', $arl_afiliado->empresa_laboral_id ?? '') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->nombre }}
                </option>
            @endforeach
        </select>
        @error('empresa_laboral_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- FECHA INGRESO --}}
    <div class="col-md-2">
        <label class="form-label fw-semibold">Fecha Ingreso <span class="text-danger">*</span></label>
        <input type="date" name="fecha_ingreso"
               class="form-control @error('fecha_ingreso') is-invalid @enderror"
               value="{{ old('fecha_ingreso', isset($arl_afiliado) ? $arl_afiliado->fecha_ingreso?->format('Y-m-d') : '') }}"
               required>
        @error('fecha_ingreso') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- FECHA RETIRO --}}
    <div class="col-md-2">
        <label class="form-label fw-semibold">Fecha Retiro</label>
        <input type="date" name="fecha_retiro"
               class="form-control @error('fecha_retiro') is-invalid @enderror"
               value="{{ old('fecha_retiro', isset($arl_afiliado) ? $arl_afiliado->fecha_retiro?->format('Y-m-d') : '') }}">
        @error('fecha_retiro') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12"><hr class="my-1"></div>

    {{-- BASE COTIZACIÓN --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Base Cotización (IBC) <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="base_cotizacion" id="inp_base"
                   class="form-control @error('base_cotizacion') is-invalid @enderror"
                   value="{{ old('base_cotizacion', $arl_afiliado->base_cotizacion ?? '') }}"
                   min="0" step="100" required>
        </div>
        @error('base_cotizacion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- VALOR ARL (readonly, calculado) --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Valor ARL (calculado)</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" id="out_arl" class="form-control bg-light" readonly placeholder="0">
        </div>
        <div class="form-text" id="txt_porcentaje">Seleccione una ARL</div>
    </div>

    {{-- ADMINISTRACIÓN --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Administración <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" name="administracion" id="inp_admin"
                   class="form-control @error('administracion') is-invalid @enderror"
                   value="{{ old('administracion', $arl_afiliado->administracion ?? '') }}"
                   min="0" step="100" required>
        </div>
        @error('administracion') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    {{-- TOTAL MENSUAL (readonly) --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Total Mensual</label>
        <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="text" id="out_total" class="form-control fw-bold bg-light" readonly placeholder="0">
        </div>
    </div>

    {{-- ESTADO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $arl_afiliado->estado ?? 1) == '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $arl_afiliado->estado ?? 1) == '0' ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

@push('scripts')
<script>
(function () {
    const selArl   = document.getElementById('sel_arl');
    const inpBase  = document.getElementById('inp_base');
    const inpAdmin = document.getElementById('inp_admin');
    const outArl   = document.getElementById('out_arl');
    const outTotal = document.getElementById('out_total');
    const txtPct   = document.getElementById('txt_porcentaje');

    function fmt(n) {
        return Math.round(n).toLocaleString('es-CO');
    }

    function recalcular() {
        const base = parseFloat(inpBase.value) || 0;
        const opt  = selArl.options[selArl.selectedIndex];
        const pct  = opt && opt.dataset.porcentaje ? parseFloat(opt.dataset.porcentaje) : 0;
        const nivel = opt && opt.dataset.nivel ? opt.dataset.nivel : null;
        const admin = parseFloat(inpAdmin.value) || 0;

        const valorArl = pct > 0 ? Math.ceil((base * pct / 100) / 100) * 100 : 0;
        const total    = valorArl + admin;

        outArl.value   = fmt(valorArl);
        outTotal.value = fmt(total);

        if (nivel) {
            txtPct.textContent = `Riesgo ${nivel} — ${pct}%`;
        } else {
            txtPct.textContent = 'Seleccione una ARL';
        }
    }

    selArl.addEventListener('change', recalcular);
    inpBase.addEventListener('input', recalcular);
    inpAdmin.addEventListener('input', recalcular);

    recalcular();
})();
</script>
@endpush
