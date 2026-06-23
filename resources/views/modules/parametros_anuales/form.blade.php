<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label fw-semibold">Año <span class="text-danger">*</span></label>
        <input type="number" name="anio" class="form-control @error('anio') is-invalid @enderror" value="{{ old('anio', $parametro_anual->anio ?? date('Y')) }}" required>
        @error('anio') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Salario Mínimo <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="salario_minimo" class="form-control @error('salario_minimo') is-invalid @enderror" value="{{ old('salario_minimo', $parametro_anual->salario_minimo ?? '') }}" required>
        @error('salario_minimo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Administración <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="administracion" class="form-control @error('administracion') is-invalid @enderror" value="{{ old('administracion', $parametro_anual->administracion ?? '') }}" required>
        @error('administracion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
