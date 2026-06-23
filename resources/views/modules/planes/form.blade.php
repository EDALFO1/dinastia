{{--
  Variables requeridas: $plan (Plan|new Plan), $niveles (array)
  Los porcentajes se almacenan como valores porcentuales (4.0 = 4%).
--}}

<div class="row g-3">

    {{-- ── Nombre ───────────────────────────────────────────────────── --}}
    <div class="col-md-6">
        <label class="form-label fw-semibold">
            Nombre del plan <span class="text-danger">*</span>
        </label>
        <input type="text"
               name="nombre"
               class="form-control @error('nombre') is-invalid @enderror"
               value="{{ old('nombre', $plan->nombre ?? '') }}"
               maxlength="120"
               placeholder="Ej: EPS + ARL I + Pensión"
               required>
        @error('nombre')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    {{-- ── Descripción ──────────────────────────────────────────────── --}}
    <div class="col-md-4">
        <label class="form-label fw-semibold">Descripción</label>
        <input type="text"
               name="descripcion"
               class="form-control"
               value="{{ old('descripcion', $plan->descripcion ?? '') }}"
               maxlength="255"
               placeholder="Descripción opcional">
    </div>

    {{-- ── Orden / Estado ───────────────────────────────────────────── --}}
    <div class="col-md-1">
        <label class="form-label fw-semibold">Orden</label>
        <input type="number"
               name="orden"
               class="form-control"
               value="{{ old('orden', $plan->orden ?? 0) }}"
               min="0">
    </div>

    <div class="col-md-1">
        <label class="form-label fw-semibold">Estado</label>
        <input type="hidden" name="estado" value="0">
        <div class="form-check form-switch mt-2">
            <input class="form-check-input"
                   type="checkbox"
                   name="estado"
                   value="1"
                   id="chk_estado"
                   {{ old('estado', $plan->estado ?? true) ? 'checked' : '' }}>
            <label class="form-check-label" for="chk_estado">Activo</label>
        </div>
    </div>

    <div class="col-12">
        <hr class="my-1">
        <p class="text-muted small mb-2">
            Selecciona los servicios que incluye este plan y sus porcentajes
            sobre el SMMLV vigente.
        </p>
    </div>

    {{-- ── EPS ──────────────────────────────────────────────────────── --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-2 {{ old('incluye_eps', $plan->incluye_eps ?? false) ? 'border-primary' : 'border' }}"
             id="card_eps">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="hidden" name="incluye_eps" value="0">
                    <input class="form-check-input service-toggle"
                           type="checkbox"
                           name="incluye_eps"
                           value="1"
                           id="chk_eps"
                           data-target="grp_eps"
                           data-card="card_eps"
                           data-color="primary"
                           {{ old('incluye_eps', $plan->incluye_eps ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold fs-6" for="chk_eps">
                        <span class="badge bg-primary me-1">EPS</span>
                        Salud
                    </label>
                </div>
                <div id="grp_eps"
                     class="{{ old('incluye_eps', $plan->incluye_eps ?? false) ? '' : 'd-none' }}">
                    <label class="form-label small fw-semibold">Porcentaje sobre SMMLV</label>
                    <div class="input-group input-group-sm">
                        <input type="number"
                               name="porcentaje_eps"
                               class="form-control @error('porcentaje_eps') is-invalid @enderror"
                               value="{{ old('porcentaje_eps', $plan->porcentaje_eps ?? 4.0) }}"
                               step="0.0001"
                               min="0"
                               max="100"
                               placeholder="4.0000">
                        <span class="input-group-text">%</span>
                        @error('porcentaje_eps')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Independientes: 12.5% · Empleador: 4%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Pensión ───────────────────────────────────────────────────── --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-2 {{ old('incluye_pension', $plan->incluye_pension ?? false) ? 'border-info' : 'border' }}"
             id="card_pension">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="hidden" name="incluye_pension" value="0">
                    <input class="form-check-input service-toggle"
                           type="checkbox"
                           name="incluye_pension"
                           value="1"
                           id="chk_pension"
                           data-target="grp_pension"
                           data-card="card_pension"
                           data-color="info"
                           {{ old('incluye_pension', $plan->incluye_pension ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold fs-6" for="chk_pension">
                        <span class="badge bg-info text-dark me-1">PEN</span>
                        Pensión
                    </label>
                </div>
                <div id="grp_pension"
                     class="{{ old('incluye_pension', $plan->incluye_pension ?? false) ? '' : 'd-none' }}">
                    <label class="form-label small fw-semibold">Porcentaje sobre SMMLV</label>
                    <div class="input-group input-group-sm">
                        <input type="number"
                               name="porcentaje_pension"
                               class="form-control @error('porcentaje_pension') is-invalid @enderror"
                               value="{{ old('porcentaje_pension', $plan->porcentaje_pension ?? 16.0) }}"
                               step="0.0001"
                               min="0"
                               max="100"
                               placeholder="16.0000">
                        <span class="input-group-text">%</span>
                        @error('porcentaje_pension')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Estándar: 16%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Caja ──────────────────────────────────────────────────────── --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-2 {{ old('incluye_caja', $plan->incluye_caja ?? false) ? 'border-warning' : 'border' }}"
             id="card_caja">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="hidden" name="incluye_caja" value="0">
                    <input class="form-check-input service-toggle"
                           type="checkbox"
                           name="incluye_caja"
                           value="1"
                           id="chk_caja"
                           data-target="grp_caja"
                           data-card="card_caja"
                           data-color="warning"
                           {{ old('incluye_caja', $plan->incluye_caja ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold fs-6" for="chk_caja">
                        <span class="badge bg-warning text-dark me-1">CAJA</span>
                        Caja de Compensación
                    </label>
                </div>
                <div id="grp_caja"
                     class="{{ old('incluye_caja', $plan->incluye_caja ?? false) ? '' : 'd-none' }}">
                    <label class="form-label small fw-semibold">Porcentaje sobre SMMLV</label>
                    <div class="input-group input-group-sm">
                        <input type="number"
                               name="porcentaje_caja"
                               class="form-control @error('porcentaje_caja') is-invalid @enderror"
                               value="{{ old('porcentaje_caja', $plan->porcentaje_caja ?? 4.0) }}"
                               step="0.0001"
                               min="0"
                               max="100"
                               placeholder="4.0000">
                        <span class="input-group-text">%</span>
                        @error('porcentaje_caja')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text">Estándar: 4%</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── ARL ───────────────────────────────────────────────────────── --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card h-100 border-2 {{ old('incluye_arl', $plan->incluye_arl ?? false) ? 'border-danger' : 'border' }}"
             id="card_arl">
            <div class="card-body">
                <div class="form-check mb-3">
                    <input type="hidden" name="incluye_arl" value="0">
                    <input class="form-check-input service-toggle"
                           type="checkbox"
                           name="incluye_arl"
                           value="1"
                           id="chk_arl"
                           data-target="grp_arl"
                           data-card="card_arl"
                           data-color="danger"
                           {{ old('incluye_arl', $plan->incluye_arl ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold fs-6" for="chk_arl">
                        <span class="badge bg-danger me-1">ARL</span>
                        Riesgos Laborales
                    </label>
                </div>
                <div id="grp_arl"
                     class="{{ old('incluye_arl', $plan->incluye_arl ?? false) ? '' : 'd-none' }}">
                    <label class="form-label small fw-semibold">Nivel de riesgo</label>
                    <select name="nivel_arl"
                            id="sel_nivel_arl"
                            class="form-select form-select-sm mb-2 @error('nivel_arl') is-invalid @enderror">
                        <option value="">Seleccionar nivel...</option>
                        @foreach($niveles as $clave => $info)
                            <option value="{{ $clave }}"
                                    data-porc="{{ $info['porcentaje'] }}"
                                    {{ old('nivel_arl', $plan->nivel_arl ?? '') === $clave ? 'selected' : '' }}>
                                {{ $info['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @error('nivel_arl')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <label class="form-label small fw-semibold">Porcentaje sobre SMMLV</label>
                    <div class="input-group input-group-sm">
                        <input type="number"
                               name="porcentaje_arl"
                               id="inp_porc_arl"
                               class="form-control @error('porcentaje_arl') is-invalid @enderror"
                               value="{{ old('porcentaje_arl', $plan->porcentaje_arl ?? '') }}"
                               step="0.0001"
                               min="0"
                               max="100"
                               placeholder="0.5220">
                        <span class="input-group-text">%</span>
                        @error('porcentaje_arl')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-text text-nowrap">
                        I: 0.522% · II: 1.044% · III: 2.436% · IV: 4.35% · V: 6.96%
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Administración ───────────────────────────────────────────── --}}
    <div class="col-12">
        <hr class="my-1">
    </div>

    <div class="col-md-6">
        <div class="card border">
            <div class="card-header py-2 fw-semibold">
                <i class="bi bi-currency-dollar me-1"></i>Valor de Administración
            </div>
            <div class="card-body">
                <div class="form-check mb-2">
                    <input type="hidden" name="usa_admin_fijo" value="0">
                    <input class="form-check-input"
                           type="radio"
                           name="usa_admin_fijo"
                           value="0"
                           id="admin_param"
                           {{ !old('usa_admin_fijo', $plan->usa_admin_fijo ?? false) ? 'checked' : '' }}
                           onchange="toggleAdminFijo(false)">
                    <label class="form-check-label" for="admin_param">
                        Usar el valor del parámetro anual
                        <span class="text-muted small">(recomendado)</span>
                    </label>
                </div>
                <div class="form-check mb-3">
                    <input class="form-check-input"
                           type="radio"
                           name="usa_admin_fijo"
                           value="1"
                           id="admin_fijo"
                           {{ old('usa_admin_fijo', $plan->usa_admin_fijo ?? false) ? 'checked' : '' }}
                           onchange="toggleAdminFijo(true)">
                    <label class="form-check-label" for="admin_fijo">
                        Valor fijo para este plan
                    </label>
                </div>
                <div id="grp_admin_fijo"
                     class="{{ old('usa_admin_fijo', $plan->usa_admin_fijo ?? false) ? '' : 'd-none' }}">
                    <label class="form-label small fw-semibold">Monto fijo</label>
                    <div class="input-group input-group-sm" style="max-width:200px">
                        <span class="input-group-text">$</span>
                        <input type="number"
                               name="valor_admin_fijo"
                               class="form-control"
                               value="{{ old('valor_admin_fijo', $plan->valor_admin_fijo ?? 0) }}"
                               step="1"
                               min="0"
                               placeholder="56000">
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
(function () {
    // Toggle de cards de servicio
    document.querySelectorAll('.service-toggle').forEach(function (chk) {
        chk.addEventListener('change', function () {
            const target = document.getElementById(this.dataset.target);
            const card   = document.getElementById(this.dataset.card);
            const color  = this.dataset.color;

            if (this.checked) {
                target.classList.remove('d-none');
                card.classList.remove('border');
                card.classList.add('border-' + color);
            } else {
                target.classList.add('d-none');
                card.classList.add('border');
                card.classList.remove('border-' + color);
            }
        });
    });

    // Auto-completar porcentaje al seleccionar nivel ARL
    const selNivel = document.getElementById('sel_nivel_arl');
    const inpPorc  = document.getElementById('inp_porc_arl');
    if (selNivel && inpPorc) {
        selNivel.addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            const p   = opt.getAttribute('data-porc');
            if (p) inpPorc.value = p;
        });
    }
})();

function toggleAdminFijo(usarFijo) {
    const grp = document.getElementById('grp_admin_fijo');
    grp.classList.toggle('d-none', !usarFijo);
}
</script>
@endpush
