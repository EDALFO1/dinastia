<div class="row g-3">
    <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $arl->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
        <input type="text" name="codigo" class="form-control @error('codigo') is-invalid @enderror" value="{{ old('codigo', $arl->codigo ?? '') }}" required>
        @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Nivel <span class="text-danger">*</span></label>
        <select name="nivel" class="form-select @error('nivel') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @for($i=1; $i<=5; $i++)
                <option value="{{ $i }}" {{ old('nivel', $arl->nivel ?? '') == $i ? 'selected' : '' }}>Nivel {{ $i }}</option>
            @endfor
        </select>
        @error('nivel') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Porcentaje <span class="text-danger">*</span></label>
        <input type="number" step="0.0001" name="porcentaje" class="form-control @error('porcentaje') is-invalid @enderror" value="{{ old('porcentaje', $arl->porcentaje ?? '') }}" required>
        @error('porcentaje') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label fw-semibold">Actividad Económica</label>
        <input type="text" name="actividad_economica" class="form-control @error('actividad_economica') is-invalid @enderror" value="{{ old('actividad_economica', $arl->actividad_economica ?? '') }}">
        @error('actividad_economica') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
