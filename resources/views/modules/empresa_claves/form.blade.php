<div class="mb-3">

    <label class="form-label fw-bold">
        Servicio
    </label>

    <select name="servicio_externo_id"
            class="form-select"
            required>

        <option value="">
            Seleccione...
        </option>

        @foreach($servicios as $id => $nombre)

            <option value="{{ $id }}"
                @selected(old('servicio_externo_id',
                $empresaClave->servicio_externo_id ?? '') == $id)>

                {{ $nombre }}

            </option>

        @endforeach

    </select>

</div>

<div class="mb-3">

    <label class="form-label fw-bold">
        Usuario
    </label>

    <input type="text"
           name="usuario"
           class="form-control"
           value="{{ old('usuario', $empresaClave->usuario ?? '') }}">

</div>

<div class="mb-3">

    <label class="form-label fw-bold">
        Correo Registrado
    </label>

    <input type="email"
           name="correo_registrado"
           class="form-control"
           value="{{ old('correo_registrado',
           $empresaClave->correo_registrado ?? '') }}">

</div>

<div class="mb-3">

    <label class="form-label fw-bold">
        Contraseña
    </label>

    <input type="text"
           name="password"
           class="form-control"
           value="">

    <small class="text-muted">
        Dejar vacío para mantener la actual.
    </small>

</div>

