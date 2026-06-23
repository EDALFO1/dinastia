<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo Documento <span class="text-danger">*</span></label>
        <select name="documento_id" class="form-select @error('documento_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($documentos as $doc)
                <option value="{{ $doc->id }}" {{ old('documento_id', $empresa_laboral->documento_id ?? '') == $doc->id ? 'selected' : '' }}>{{ $doc->nombre }}</option>
            @endforeach
        </select>
        @error('documento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Número Documento <span class="text-danger">*</span></label>
        <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" value="{{ old('numero_documento', $empresa_laboral->numero_documento ?? '') }}" required>
        @error('numero_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-6">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="nombre" class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $empresa_laboral->nombre ?? '') }}" required>
        @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $empresa_laboral->telefono ?? '') }}">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Contacto <span class="text-danger">*</span></label>
        <input type="text" name="contacto" class="form-control @error('contacto') is-invalid @enderror" value="{{ old('contacto', $empresa_laboral->contacto ?? '') }}" required>
        @error('contacto') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $empresa_laboral->email ?? '') }}">
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $empresa_laboral->estado ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $empresa_laboral->estado ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-9">
        <label class="form-label fw-semibold">Dirección</label>
        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $empresa_laboral->direccion ?? '') }}">
        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
