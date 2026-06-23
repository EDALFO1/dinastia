@php
$isEdit = isset($afiliacion) && $afiliacion->id;
@endphp

<div class="row g-3">

    {{-- AFILIADO --}}
    @if(!$isEdit)

    <div class="col-md-6">
        <label class="form-label fw-semibold">Buscar Afiliado</label>
        <div class="input-group">
            <input type="text" id="buscar_afiliado" class="form-control" placeholder="Documento o nombre">
            <button type="button" class="btn btn-primary" id="btnBuscarAfiliado">
                <i class="bi bi-search me-1"></i>Buscar
            </button>
        </div>
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Resultados</label>
        <div id="lista_afiliados" class="list-group"></div>
    </div>

    <div class="col-12">
        <div id="info_afiliado" class="alert alert-info d-none mb-0"></div>
    </div>

    <input type="hidden" name="afiliado_id" id="afiliado_id">
    @error('afiliado_id') <small class="text-danger">{{ $message }}</small> @enderror

    @else

    <div class="col-md-6">
        <label class="form-label fw-semibold">Afiliado</label>
        <div class="alert alert-info mb-0">
            <strong>{{ $afiliacion->afiliado?->primer_nombre }} {{ $afiliacion->afiliado?->primer_apellido }}</strong><br>
            Documento: {{ $afiliacion->afiliado?->numero_documento ?? 'N/A' }}
        </div>
        <input type="hidden" name="afiliado_id" value="{{ $afiliacion->afiliado_id }}">
    </div>

    @endif

    {{-- EPS --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">EPS</label>
        <select name="eps_id" class="form-select campo-form @error('eps_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($eps as $e)
                <option value="{{ $e->id }}" {{ old('eps_id', $afiliacion->eps_id ?? '') == $e->id ? 'selected' : '' }}>{{ $e->nombre }}</option>
            @endforeach
        </select>
        @error('eps_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ARL --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">ARL</label>
        <select name="arl_id" class="form-select campo-form @error('arl_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($arls as $a)
                <option value="{{ $a->id }}" {{ old('arl_id', $afiliacion->arl_id ?? '') == $a->id ? 'selected' : '' }}>{{ $a->nombre }}</option>
            @endforeach
        </select>
        @error('arl_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- NIVEL ARL --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Nivel ARL</label>
        <select name="nivel_arl" class="form-select campo-form @error('nivel_arl') is-invalid @enderror">
            <option value="">Seleccione</option>
            @for($i=1; $i<=5; $i++)
                <option value="{{ $i }}" {{ old('nivel_arl', $afiliacion->nivel_arl ?? '') == $i ? 'selected' : '' }}>Nivel {{ $i }}</option>
            @endfor
        </select>
        @error('nivel_arl') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- PENSIÓN --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Pensión</label>
        <select name="pension_id" class="form-select campo-form @error('pension_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($pensions as $p)
                <option value="{{ $p->id }}" {{ old('pension_id', $afiliacion->pension_id ?? '') == $p->id ? 'selected' : '' }}>{{ $p->nombre }}</option>
            @endforeach
        </select>
        @error('pension_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CAJA --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Caja</label>
        <select name="caja_id" class="form-select campo-form @error('caja_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($cajas as $c)
                <option value="{{ $c->id }}" {{ old('caja_id', $afiliacion->caja_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->nombre }}</option>
            @endforeach
        </select>
        @error('caja_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- TIPO IBC --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tipo IBC</label>
        <select name="tipo_ibc" id="tipo_ibc" class="form-select campo-form @error('tipo_ibc') is-invalid @enderror">
            <option value="SMMLV" {{ old('tipo_ibc', $afiliacion->tipo_ibc ?? '') == 'SMMLV' ? 'selected' : '' }}>SMMLV</option>
            <option value="FIJO" {{ old('tipo_ibc', $afiliacion->tipo_ibc ?? '') == 'FIJO' ? 'selected' : '' }}>FIJO</option>
        </select>
        @error('tipo_ibc') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- IBC --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">IBC</label>
        <input type="number" name="ibc" id="ibc" class="form-control campo-form @error('ibc') is-invalid @enderror" value="{{ old('ibc', $afiliacion->ibc ?? '') }}">
        @error('ibc') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- FECHA --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha Afiliación</label>
        <input type="date" name="fecha_afiliacion" id="fecha_afiliacion" class="form-control campo-form @error('fecha_afiliacion') is-invalid @enderror" value="{{ old('fecha_afiliacion', $afiliacion->fecha_afiliacion ?? '') }}">
        @error('fecha_afiliacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- RETIRO --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha Retiro</label>
        <input type="date" name="fecha_retiro" class="form-control campo-form @error('fecha_retiro') is-invalid @enderror" value="{{ old('fecha_retiro', $afiliacion->fecha_retiro ?? '') }}">
        @error('fecha_retiro') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>
