<div class="row g-3">

    {{-- EMPRESA LABORAL --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Empresa Laboral <span class="text-danger">*</span></label>
        <select name="empresa_laboral_id" class="form-select @error('empresa_laboral_id') is-invalid @enderror" required>
            <option value="">Seleccione</option>
            @foreach($empresasLaborales as $el)
                <option value="{{ $el->id }}" {{ old('empresa_laboral_id', $afiliado->empresa_laboral_id ?? '') == $el->id ? 'selected' : '' }}>{{ $el->nombre }}</option>
            @endforeach
        </select>
        @error('empresa_laboral_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ASESOR --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Asesor</label>
        <select name="asesor_id" class="form-select @error('asesor_id') is-invalid @enderror">
            <option value="">Seleccione</option>
            @foreach($asesores as $a)
                <option value="{{ $a->id }}" {{ old('asesor_id', $afiliado->asesor_id ?? '') == $a->id ? 'selected' : '' }}>{{ $a->nombre }}</option>
            @endforeach
        </select>
        @error('asesor_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- DOCUMENTO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Tipo Documento <span class="text-danger">*</span></label>
        <select name="documento_id" class="form-select @error('documento_id') is-invalid @enderror" required>
            @foreach($documentos as $doc)
                <option value="{{ $doc->id }}" {{ old('documento_id', $afiliado->documento_id ?? '') == $doc->id ? 'selected' : '' }}>{{ $doc->nombre }}</option>
            @endforeach
        </select>
        @error('documento_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- SUBTIPO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Subtipo Cotizante <span class="text-danger">*</span></label>
        <select name="subtipo_cotizante_id" class="form-select @error('subtipo_cotizante_id') is-invalid @enderror" required>
            @foreach($subtipos as $s)
                <option value="{{ $s->id }}" {{ old('subtipo_cotizante_id', $afiliado->subtipo_cotizante_id ?? '') == $s->id ? 'selected' : '' }}>{{ $s->nombre }}</option>
            @endforeach
        </select>
        @error('subtipo_cotizante_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- NUMERO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Número Documento <span class="text-danger">*</span></label>
        <input type="text" name="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" value="{{ old('numero_documento', $afiliado->numero_documento ?? '') }}" required>
        @error('numero_documento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- NOMBRES --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Primer Nombre <span class="text-danger">*</span></label>
        <input type="text" name="primer_nombre" class="form-control @error('primer_nombre') is-invalid @enderror" value="{{ old('primer_nombre', $afiliado->primer_nombre ?? '') }}" required>
        @error('primer_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Segundo Nombre</label>
        <input type="text" name="segundo_nombre" class="form-control @error('segundo_nombre') is-invalid @enderror" value="{{ old('segundo_nombre', $afiliado->segundo_nombre ?? '') }}">
        @error('segundo_nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- APELLIDOS --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Primer Apellido <span class="text-danger">*</span></label>
        <input type="text" name="primer_apellido" class="form-control @error('primer_apellido') is-invalid @enderror" value="{{ old('primer_apellido', $afiliado->primer_apellido ?? '') }}" required>
        @error('primer_apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Segundo Apellido</label>
        <input type="text" name="segundo_apellido" class="form-control @error('segundo_apellido') is-invalid @enderror" value="{{ old('segundo_apellido', $afiliado->segundo_apellido ?? '') }}">
        @error('segundo_apellido') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- FECHA --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Fecha Nacimiento <span class="text-danger">*</span></label>
        <input type="date" name="fecha_nacimiento" class="form-control @error('fecha_nacimiento') is-invalid @enderror" value="{{ old('fecha_nacimiento', $afiliado->fecha_nacimiento?->format('Y-m-d')) }}" required>
        @error('fecha_nacimiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- SEXO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Sexo <span class="text-danger">*</span></label>
        <select name="sexo" class="form-select @error('sexo') is-invalid @enderror" required>
            <option value="M" {{ old('sexo', $afiliado->sexo ?? '') == 'M' ? 'selected' : '' }}>Masculino</option>
            <option value="F" {{ old('sexo', $afiliado->sexo ?? '') == 'F' ? 'selected' : '' }}>Femenino</option>
            <option value="Otro" {{ old('sexo', $afiliado->sexo ?? '') == 'Otro' ? 'selected' : '' }}>Otro</option>
        </select>
        @error('sexo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CONTACTO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Correo</label>
        <input type="email" name="correo" class="form-control @error('correo') is-invalid @enderror" value="{{ old('correo', $afiliado->correo ?? '') }}">
        @error('correo') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Teléfono</label>
        <input type="text" name="telefono" class="form-control @error('telefono') is-invalid @enderror" value="{{ old('telefono', $afiliado->telefono ?? '') }}">
        @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Dirección</label>
        <input type="text" name="direccion" class="form-control @error('direccion') is-invalid @enderror" value="{{ old('direccion', $afiliado->direccion ?? '') }}">
        @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Ciudad</label>
        <input type="text" name="ciudad" class="form-control @error('ciudad') is-invalid @enderror" value="{{ old('ciudad', $afiliado->ciudad ?? '') }}">
        @error('ciudad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- ESTADO --}}
    <div class="col-md-3">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $afiliado->estado ?? '') == '1' ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $afiliado->estado ?? '') == '0' ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    {{-- CARPETA GOOGLE DRIVE --}}
    <div class="col-12">
        <hr class="my-1">
    </div>

    <div class="col-md-9">
        <label class="form-label fw-semibold">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 87.3 78" class="me-1" style="vertical-align:-2px">
                <path d="m6.6 66.85 3.85 6.65c.8 1.4 1.95 2.5 3.3 3.3l13.75-23.8h-27.5c0 1.55.4 3.1 1.2 4.5z" fill="#0066da"/>
                <path d="m43.65 25-13.75-23.8c-1.35.8-2.5 1.9-3.3 3.3l-25.4 44a9.06 9.06 0 0 0 -1.2 4.5h27.5z" fill="#00ac47"/>
                <path d="m73.55 76.8c1.35-.8 2.5-1.9 3.3-3.3l1.6-2.75 7.65-13.25c.8-1.4 1.2-2.95 1.2-4.5h-27.502l5.852 11.5z" fill="#ea4335"/>
                <path d="m43.65 25 13.75-23.8c-1.35-.8-2.9-1.2-4.5-1.2h-18.5c-1.6 0-3.15.45-4.5 1.2z" fill="#00832d"/>
                <path d="m59.8 53h-32.3l-13.75 23.8c1.35.8 2.9 1.2 4.5 1.2h50.8c1.6 0 3.15-.45 4.5-1.2z" fill="#2684fc"/>
                <path d="m73.4 26.5-12.7-22c-.8-1.4-1.95-2.5-3.3-3.3l-13.75 23.8 16.15 27h27.45c0-1.55-.4-3.1-1.2-4.5z" fill="#ffba00"/>
            </svg>
            Carpeta Google Drive
        </label>
        @php $driveUrl = old('google_drive_folder_id', $afiliado->google_drive_folder_id ?? ''); @endphp
        <div class="input-group">
            <input type="url"
                   name="google_drive_folder_id"
                   id="inp_drive"
                   class="form-control @error('google_drive_folder_id') is-invalid @enderror"
                   value="{{ $driveUrl }}"
                   placeholder="https://drive.google.com/drive/folders/..."
                   maxlength="500">
            <a id="btn_drive_open"
               href="{{ $driveUrl ?: '#' }}"
               target="_blank"
               rel="noopener noreferrer"
               class="btn btn-outline-secondary {{ $driveUrl ? '' : 'disabled' }}"
               title="Abrir carpeta en Google Drive">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
        </div>
        <div class="form-text">Pega aquí la URL de la carpeta de Drive donde están los soportes de este afiliado.</div>
        @error('google_drive_folder_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

</div>

@push('scripts')
<script>
(function () {
    const inp = document.getElementById('inp_drive');
    const btn = document.getElementById('btn_drive_open');
    if (!inp || !btn) return;

    inp.addEventListener('input', function () {
        const val = this.value.trim();
        if (val) {
            btn.href = val;
            btn.classList.remove('disabled');
        } else {
            btn.href = '#';
            btn.classList.add('disabled');
        }
    });
})();
</script>
@endpush
