<div class="mb-3">

    <label class="form-label fw-bold">
        Nombre
    </label>

    <input type="text"
           name="nombre"
           class="form-control"
           value="{{ old('nombre', $serviciosExterno->nombre ?? '') }}"
           required>

</div>

<div class="mb-3">

    <label class="form-label fw-bold">
        URL
    </label>

    <input type="url"
           name="url"
           class="form-control"
           placeholder="https://"
           value="{{ old('url', $serviciosExterno->url ?? '') }}">

</div>

<div class="form-check form-switch mb-4">

    <input class="form-check-input"
           type="checkbox"
           name="activo"
           value="1"
           id="activo"

           {{ old('activo',
           $serviciosExterno->activo ?? true)
           ? 'checked' : '' }}>

    <label class="form-check-label"
           for="activo">

        Servicio Activo

    </label>

</div>