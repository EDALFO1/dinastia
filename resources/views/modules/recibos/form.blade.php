<form action="{{ route('recibos.store') }}" method="POST">
@csrf

<div class="row">

    {{-- 🔥 EMPRESA (SOLO VISUAL) --}}
    <div class="col-md-3 mb-3">
        <label class="form-label">Empresa</label>
        <input type="text"
               class="form-control bg-light"
               value="{{ session('empresa_nombre') }}"
               disabled>
    </div>

    {{-- 🔹 AFILIADO --}}
    <div class="col-md-4 mb-3">
        <label class="form-label">Afiliado</label>

        <select name="afiliado_id"
                class="form-control @error('afiliado_id') is-invalid @enderror"
                required>

            <option value="">Seleccione</option>

            @foreach($afiliados as $a)
            <option value="{{ $a->id }}"
            {{ old('afiliado_id', request('afiliado_id')) == $a->id ? 'selected' : '' }}>
                {{ $a->primer_nombre }} {{ $a->primer_apellido }}
                ({{ $a->numero_documento }})
            </option>
            @endforeach

        </select>

        @error('afiliado_id')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 🔹 FECHA --}}
    <div class="col-md-2 mb-3">
        <label class="form-label">Fecha</label>

        <input type="date"
        name="fecha"
        class="form-control @error('fecha') is-invalid @enderror"
        value="{{ old('fecha', date('Y-m-d')) }}"
        required>

        @error('fecha')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 🔹 NOVEDAD --}}
    <div class="col-md-2 mb-3">
        <label class="form-label">Novedad</label>

        <select name="novedad"
                id="novedad"
                class="form-control @error('novedad') is-invalid @enderror">

            <option value="">Sin novedad</option>

            <option value="Ingreso"
            {{ old('novedad')=='Ingreso'?'selected':'' }}>
            Ingreso
            </option>

            <option value="Retiro"
            {{ old('novedad')=='Retiro'?'selected':'' }}>
            Retiro
            </option>

        </select>

        @error('novedad')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- 🔹 FECHA RETIRO --}}
    <div class="col-md-3 mb-3" id="campo_retiro" style="display:none;">
        <label class="form-label">Fecha Retiro</label>

        <input type="date"
        name="fecha_retiro"
        class="form-control @error('fecha_retiro') is-invalid @enderror"
        value="{{ old('fecha_retiro') }}">

        @error('fecha_retiro')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

</div>

<button class="btn btn-primary">Guardar</button>

<a href="{{ route('recibos.index') }}" class="btn btn-secondary">
Cancelar
</a>

</form>