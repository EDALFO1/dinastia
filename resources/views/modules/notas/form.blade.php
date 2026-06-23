<div class="row g-3">

    {{-- Título --}}
    <div class="col-12">
        <label class="form-label fw-semibold">
            Título <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="titulo"
               class="form-control @error('titulo') is-invalid @enderror"
               value="{{ old('titulo', $nota->titulo ?? '') }}"
               maxlength="200"
               placeholder="Describe brevemente la tarea o evento"
               required>
        @error('titulo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Tipo y Estado --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Tipo <span class="text-danger">*</span>
        </label>
        <select name="tipo"
                class="form-select @error('tipo') is-invalid @enderror"
                required>
            <option value="">Seleccionar tipo...</option>
            @foreach($tipos as $key => $label)
                <option value="{{ $key }}"
                    {{ old('tipo', $nota->tipo ?? '') === $key ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        @error('tipo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">
            Estado <span class="text-danger">*</span>
        </label>
        <select name="estado"
                class="form-select @error('estado') is-invalid @enderror"
                required>
            @foreach($estados as $key => $info)
                <option value="{{ $key }}"
                    {{ old('estado', $nota->estado ?? 'pendiente') === $key ? 'selected' : '' }}>
                    {{ $info['label'] }}
                </option>
            @endforeach
        </select>
        @error('estado')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Fecha de vencimiento</label>
        <input type="date"
               name="fecha_vencimiento"
               class="form-control @error('fecha_vencimiento') is-invalid @enderror"
               value="{{ old('fecha_vencimiento', isset($nota->fecha_vencimiento) ? $nota->fecha_vencimiento->format('Y-m-d') : '') }}">
        <div class="form-text">Opcional — para tareas con plazo.</div>
        @error('fecha_vencimiento')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- Descripción --}}
    <div class="col-12">
        <label class="form-label fw-semibold">Descripción / Detalle</label>
        <textarea name="descripcion"
                  class="form-control"
                  rows="5"
                  placeholder="Agrega todos los detalles necesarios para hacer seguimiento...">{{ old('descripcion', $nota->descripcion ?? '') }}</textarea>
    </div>

</div>
