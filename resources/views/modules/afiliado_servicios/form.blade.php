<div class="row g-3">

    {{-- AFILIADO --}}
    <div class="col-md-5">
        <label class="form-label fw-semibold">Afiliado <span class="text-danger">*</span></label>
        <select name="afiliado_id" class="form-select @error('afiliado_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($afiliados as $af)
                <option value="{{ $af->id }}" {{ old('afiliado_id', $afiliado_servicio->afiliado_id ?? '') == $af->id ? 'selected' : '' }}>{{ $af->primer_nombre }} {{ $af->primer_apellido }} ({{ $af->numero_documento }})</option>
            @endforeach
        </select>
        @error('afiliado_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- SERVICIO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Servicio <span class="text-danger">*</span></label>
        <select name="servicio_id" class="form-select @error('servicio_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($servicios as $s)
                <option value="{{ $s->id }}" {{ old('servicio_id', $afiliado_servicio->servicio_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
            @endforeach
        </select>
        @error('servicio_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- VALOR --}}
    <div class="col-md-2">
        <label class="form-label fw-semibold">Valor</label>
        <input type="number" step="0.01" name="valor" class="form-control @error('valor') is-invalid @enderror" value="{{ old('valor', $afiliado_servicio->valor ?? 0) }}">
        @error('valor') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ESTADO --}}
    <div class="col-md-2">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $afiliado_servicio->estado ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $afiliado_servicio->estado ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

</div>
