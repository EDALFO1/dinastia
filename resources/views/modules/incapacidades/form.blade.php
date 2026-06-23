{{-- Variables requeridas: $incapacidad, $afiliados, $empresasLaboral, $epsList, $arlList, $estados --}}

<div class="row g-3">

    {{-- ── Búsqueda de afiliado ────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="card border bg-light">
            <div class="card-body py-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-person-search me-1"></i>Buscar afiliado
                    <span class="text-muted fw-normal small">(opcional — puede ingresar documento y nombre manualmente)</span>
                </label>
                <select id="sel_afiliado"
                        name="afiliado_id"
                        class="form-select select2-afiliado"
                        style="width:100%">
                    <option value="">Seleccionar afiliado...</option>
                    @foreach($afiliados as $af)
                        <option value="{{ $af->id }}"
                                data-doc="{{ $af->numero_documento }}"
                                data-nombre="{{ trim("{$af->primer_nombre} {$af->segundo_nombre} {$af->primer_apellido} {$af->segundo_apellido}") }}"
                                {{ old('afiliado_id', $incapacidad->afiliado_id ?? '') == $af->id ? 'selected' : '' }}>
                            {{ $af->primer_apellido }} {{ $af->segundo_apellido }}
                            {{ $af->primer_nombre }} — {{ $af->numero_documento }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ── Documento y Nombre ──────────────────────────────────────────── --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">
            Documento <span class="text-danger">*</span>
        </label>
        <input type="text"
               id="inp_documento"
               name="documento"
               class="form-control @error('documento') is-invalid @enderror"
               value="{{ old('documento', $incapacidad->documento ?? '') }}"
               maxlength="50"
               required>
        @error('documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label fw-semibold">
            Nombre completo <span class="text-danger">*</span>
        </label>
        <input type="text"
               id="inp_nombre"
               name="nombre"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $incapacidad->nombre ?? '') }}"
               maxlength="255"
               required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ── Empresa Laboral ─────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">Empresa laboral</label>
        <select name="empresa_laboral_id" class="form-select">
            <option value="">Sin empresa laboral</option>
            @foreach($empresasLaboral as $emp)
                <option value="{{ $emp->id }}"
                    {{ old('empresa_laboral_id', $incapacidad->empresa_laboral_id ?? '') == $emp->id ? 'selected' : '' }}>
                    {{ $emp->nombre }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- ── Entidad ─────────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Entidad que reporta <span class="text-danger">*</span>
        </label>
        <div class="d-flex gap-3 mt-1 mb-2">
            <div class="form-check">
                <input class="form-check-input"
                       type="radio"
                       name="entidad_tipo"
                       value="EPS"
                       id="tipo_eps"
                       {{ old('entidad_tipo', $incapacidad->entidad_tipo ?? 'EPS') === 'EPS' ? 'checked' : '' }}
                       onchange="toggleEntidad()">
                <label class="form-check-label fw-semibold" for="tipo_eps">EPS</label>
            </div>
            <div class="form-check">
                <input class="form-check-input"
                       type="radio"
                       name="entidad_tipo"
                       value="ARL"
                       id="tipo_arl"
                       {{ old('entidad_tipo', $incapacidad->entidad_tipo ?? '') === 'ARL' ? 'checked' : '' }}
                       onchange="toggleEntidad()">
                <label class="form-check-label fw-semibold" for="tipo_arl">ARL</label>
            </div>
        </div>

        <div id="grp_eps" class="{{ old('entidad_tipo', $incapacidad->entidad_tipo ?? 'EPS') === 'EPS' ? '' : 'd-none' }}">
            <select name="eps_id" class="form-select @error('eps_id') is-invalid @enderror">
                <option value="">Seleccionar EPS...</option>
                @foreach($epsList as $ep)
                    <option value="{{ $ep->id }}"
                        {{ old('eps_id', $incapacidad->eps_id ?? '') == $ep->id ? 'selected' : '' }}>
                        {{ $ep->nombre }}
                    </option>
                @endforeach
            </select>
            @error('eps_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div id="grp_arl" class="{{ old('entidad_tipo', $incapacidad->entidad_tipo ?? '') === 'ARL' ? '' : 'd-none' }}">
            <select name="arl_id" class="form-select @error('arl_id') is-invalid @enderror">
                <option value="">Seleccionar ARL...</option>
                @foreach($arlList as $ar)
                    <option value="{{ $ar->id }}"
                        {{ old('arl_id', $incapacidad->arl_id ?? '') == $ar->id ? 'selected' : '' }}>
                        {{ $ar->nombre }}
                    </option>
                @endforeach
            </select>
            @error('arl_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>
    </div>

    <div class="col-12"><hr class="my-1"></div>

    {{-- ── Fechas ───────────────────────────────────────────────────────── --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">
            Fecha inicio <span class="text-danger">*</span>
        </label>
        <input type="date"
               name="fecha_inicio"
               id="inp_fecha_inicio"
               class="form-control @error('fecha_inicio') is-invalid @enderror"
               value="{{ old('fecha_inicio', isset($incapacidad->fecha_inicio) ? $incapacidad->fecha_inicio->format('Y-m-d') : '') }}"
               required
               onchange="calcularDias()">
        @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">
            Fecha fin <span class="text-danger">*</span>
        </label>
        <input type="date"
               name="fecha_fin"
               id="inp_fecha_fin"
               class="form-control @error('fecha_fin') is-invalid @enderror"
               value="{{ old('fecha_fin', isset($incapacidad->fecha_fin) ? $incapacidad->fecha_fin->format('Y-m-d') : '') }}"
               required
               onchange="calcularDias()">
        @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Total días</label>
        <div class="input-group">
            <input type="text"
                   id="inp_dias"
                   class="form-control bg-light fw-bold text-center"
                   value="{{ old('dias_calc', $incapacidad->dias_solicitados ?? '') }}"
                   readonly>
            <span class="input-group-text">días</span>
        </div>
        <div class="form-text">Se calcula automáticamente.</div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha de radicación</label>
        <input type="date"
               name="fecha_radicacion"
               class="form-control @error('fecha_radicacion') is-invalid @enderror"
               value="{{ old('fecha_radicacion', isset($incapacidad->fecha_radicacion) ? $incapacidad->fecha_radicacion->format('Y-m-d') : '') }}">
        @error('fecha_radicacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-12"><hr class="my-1"></div>

    {{-- ── Estado y Fecha pago ─────────────────────────────────────────── --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Estado <span class="text-danger">*</span>
        </label>
        <select name="estado"
                id="sel_estado"
                class="form-select @error('estado') is-invalid @enderror"
                required
                onchange="toggleFechaPago()">
            @foreach($estados as $key => $info)
                <option value="{{ $key }}"
                    {{ old('estado', $incapacidad->estado ?? 'transcrita') === $key ? 'selected' : '' }}>
                    {{ $info['label'] }}
                </option>
            @endforeach
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3"
         id="grp_fecha_pago"
         style="{{ old('estado', $incapacidad->estado ?? '') === 'pagada' ? '' : 'display:none' }}">
        <label class="form-label fw-semibold">Fecha de pago</label>
        <input type="date"
               name="fecha_pago"
               class="form-control @error('fecha_pago') is-invalid @enderror"
               value="{{ old('fecha_pago', isset($incapacidad->fecha_pago) ? $incapacidad->fecha_pago->format('Y-m-d') : '') }}">
        @error('fecha_pago') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>

@push('scripts')
<script>
// ── Select2 para afiliado ────────────────────────────────────────────────
$(function () {
    $('.select2-afiliado').select2({
        placeholder: 'Buscar por nombre o documento...',
        allowClear: true,
    }).on('change', function () {
        const opt = $(this).find(':selected');
        const doc    = opt.data('doc')    || '';
        const nombre = opt.data('nombre') || '';
        if (doc)    $('#inp_documento').val(doc);
        if (nombre) $('#inp_nombre').val(nombre);
    });
});

// ── Toggle EPS / ARL ────────────────────────────────────────────────────
function toggleEntidad() {
    const tipo = document.querySelector('input[name="entidad_tipo"]:checked')?.value;
    document.getElementById('grp_eps').classList.toggle('d-none', tipo !== 'EPS');
    document.getElementById('grp_arl').classList.toggle('d-none', tipo !== 'ARL');
}

// ── Calcular días ────────────────────────────────────────────────────────
function calcularDias() {
    const inicio = document.getElementById('inp_fecha_inicio').value;
    const fin    = document.getElementById('inp_fecha_fin').value;
    if (inicio && fin) {
        const diff = Math.round((new Date(fin) - new Date(inicio)) / 86400000) + 1;
        document.getElementById('inp_dias').value = diff > 0 ? diff : '';
    }
}

// ── Mostrar fecha pago sólo en estado "pagada" ───────────────────────────
function toggleFechaPago() {
    const estado = document.getElementById('sel_estado').value;
    document.getElementById('grp_fecha_pago').style.display =
        estado === 'pagada' ? '' : 'none';
}

// Inicializar al cargar
document.addEventListener('DOMContentLoaded', function () {
    calcularDias();
    toggleFechaPago();
    toggleEntidad();
});
</script>
@endpush
