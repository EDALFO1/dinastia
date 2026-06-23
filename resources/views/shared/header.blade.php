<header id="header" class="header fixed-top d-flex align-items-center gap-3">

    @php
        $hayEmpresa    = session()->has('empresa_id');
        $rolActual     = (int) auth()->user()->rol_id;
        $iniciales     = strtoupper(substr(auth()->user()->name, 0, 2));
        $empresaActiva = auth()->user()->empresas->where('id', session('empresa_id'))->first();
        $rolNombre     = match($rolActual) { 1 => 'Admin', 3 => 'Asesor', 4 => 'Invitado', 5 => 'Operador', default => 'Usuario' };
        $notasPend     = $hayEmpresa
            ? \App\Models\Nota::whereIn('estado', ['pendiente','en_proceso'])->count()
            : 0;
    @endphp

    {{-- LOGO + TOGGLE --}}
    <div class="d-flex align-items-center flex-shrink-0">
        <a href="{{ route('dashboard') }}" class="logo">
            <div class="logo-icon">ST</div>
            <div>
                <div class="logo-text">SeguraTech</div>
                <div class="logo-sub"></div>
            </div>
        </a>
        <i class="bi bi-list toggle-sidebar-btn"></i>
    </div>

    {{-- ACCIONES + PERFIL --}}
    <nav class="header-nav ms-auto">
        <ul class="d-flex align-items-center gap-2 mb-0 list-unstyled flex-wrap">

            {{-- NOTAS --}}
            @if($modulosPermitidos->contains('notas'))
            <li>
                <a href="{{ $hayEmpresa ? route('notas.index') : '#' }}"
                   class="st-pill notas {{ !$hayEmpresa ? 'disabled' : '' }}"
                   @if(!$hayEmpresa) title="Selecciona una empresa primero" @endif>
                    <i class="bi bi-stickies-fill"></i>
                    <span class="d-none d-md-inline">Notas</span>
                    @if($notasPend > 0)
                        <span class="badge-count">{{ $notasPend > 99 ? '99+' : $notasPend }}</span>
                    @endif
                </a>
            </li>
            @endif

            {{-- PLANES --}}
            @if($modulosPermitidos->contains('planes'))
            <li>
                <a href="{{ $hayEmpresa ? route('planes.index') : '#' }}"
                   class="st-pill planes {{ !$hayEmpresa ? 'disabled' : '' }}"
                   @if(!$hayEmpresa) title="Selecciona una empresa primero" @endif>
                    <i class="bi bi-layers-fill"></i>
                    <span class="d-none d-md-inline">Planes</span>
                </a>
            </li>
            @endif

            {{-- CLAVES --}}
            @if($modulosPermitidos->contains('empresa_claves'))
            <li>
                <a href="{{ route('empresa-claves.index') }}" class="st-pill claves">
                    <i class="bi bi-key-fill"></i>
                    <span class="d-none d-lg-inline">Claves</span>
                </a>
            </li>
            @endif

            {{-- SEPARADOR --}}
            <li class="d-none d-lg-flex"><div class="st-sep mx-1"></div></li>

            {{-- EMPRESA ACTIVA --}}
            @if($empresaActiva)
            <li class="d-none d-lg-flex align-items-center gap-2">
                <span class="st-empresa-chip">
                    <i class="bi bi-building-fill"></i>
                    {{ $empresaActiva->nombre }}
                </span>
                <a href="{{ route('seleccionar.empresa') }}" class="st-cambiar">
                    <i class="bi bi-arrow-left-right me-1"></i>Cambiar
                </a>
            </li>
            @endif

            {{-- SEPARADOR --}}
            <li class="d-none d-lg-flex"><div class="st-sep mx-1"></div></li>

            {{-- PERFIL --}}
            <li class="nav-item dropdown">
                <a href="#"
                   data-bs-toggle="dropdown"
                   class="d-flex align-items-center gap-2 text-decoration-none">
                    <div class="st-avatar">{{ $iniciales }}</div>
                    <div class="st-user-info d-none d-lg-block">
                        <div class="uname">{{ auth()->user()->name }}</div>
                        <div class="urole">{{ $rolNombre }}</div>
                    </div>
                    <i class="bi bi-chevron-down st-chevron d-none d-lg-inline"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end st-profile mt-2">
                    <li class="dh">
                        <div class="dh-name">{{ auth()->user()->name }}</div>
                        <div class="dh-email">{{ auth()->user()->email }}</div>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>

                    @if($empresaActiva)
                    <li>
                        <a class="dropdown-item" href="{{ route('seleccionar.empresa') }}">
                            <i class="bi bi-arrow-left-right text-warning"></i>
                            Cambiar empresa
                        </a>
                    </li>
                    <li><hr class="dropdown-divider my-1"></li>
                    @endif

                    <li>
                        <a class="dropdown-item text-danger" href="#"
                           onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right"></i>
                            Cerrar sesión
                        </a>
                    </li>
                </ul>

                <form id="logout-form"
                      action="{{ route('logout') }}"
                      method="POST"
                      class="d-none">
                    @csrf
                </form>
            </li>

        </ul>
    </nav>

</header>
