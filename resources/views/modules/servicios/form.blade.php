<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $servicio->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo</label>
        <input type="text" name="tipo" class="form-control @error('tipo') is-invalid @enderror" value="{{ old('tipo', $servicio->tipo ?? '') }}">
        @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Valor Base</label>
        <input type="number" step="0.01" name="valor_base" class="form-control @error('valor_base') is-invalid @enderror" value="{{ old('valor_base', $servicio->valor_base ?? 0) }}">
        @error('valor_base') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-2">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $servicio->estado ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $servicio->estado ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
