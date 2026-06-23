<div class="row g-3">
    <div class="col-md-5">
        <label class="form-label fw-semibold">Empresas</label>
        <select name="empresa_id[]" class="form-select @error('empresa_id') is-invalid @enderror" multiple>
            @foreach($empresas as $empresa)
                <option value="{{ $empresa->id }}" {{ isset($usuario) && $usuario->empresas->pluck('id')->contains($empresa->id) ? 'selected' : '' }}>{{ $empresa->nombre }}</option>
            @endforeach
        </select>
        @error('empresa_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
        <small class="text-muted">Puedes seleccionar varias empresas</small>
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Rol</label>
        <select name="rol_id" class="form-select @error('rol_id') is-invalid @enderror">
            @foreach($roles as $rol)
                <option value="{{ $rol->id }}" {{ old('rol_id', $usuario->rol_id ?? '') == $rol->id ? 'selected' : '' }}>{{ $rol->nombre }}</option>
            @endforeach
        </select>
        @error('rol_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Estado</label>
        <select name="estado" class="form-select @error('estado') is-invalid @enderror">
            <option value="1" {{ old('estado', $usuario->estado ?? 1) == 1 ? 'selected' : '' }}>Activo</option>
            <option value="0" {{ old('estado', $usuario->estado ?? 1) == 0 ? 'selected' : '' }}>Inactivo</option>
        </select>
        @error('estado') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-5">
        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $usuario->name ?? '') }}" required>
        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $usuario->email ?? '') }}" required>
        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
