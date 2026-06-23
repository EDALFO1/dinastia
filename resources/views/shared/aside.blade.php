<aside id="sidebar" class="sidebar">

<ul class="sidebar-nav" id="sidebar-nav">

    <li class="nav-heading">Principal</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('dashboard') ? '' : 'collapsed' }}"
           href="{{ route('dashboard') }}">
            <i class="bi bi-grid-1x2-fill"></i>
            <span>Dashboard</span>
        </a>
    </li>

    {{-- OPERATIVO --}}
    @php
        $tieneRemisiones        = $modulosPermitidos->contains('remisiones');
        $tieneRecibos           = $modulosPermitidos->contains('recibos');
        $tieneRecibosAfiliacion = $modulosPermitidos->contains('recibos_afiliacion');
    @endphp

    @if($tieneRemisiones || $tieneRecibos || $tieneRecibosAfiliacion)
    <li class="nav-heading">Operativo</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('remisiones.*','recibos.*','recibos-afiliacion.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-facturacion"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-receipt"></i>
            <span>Facturación</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-facturacion"
            class="nav-content collapse {{ request()->routeIs('remisiones.*','recibos.*','recibos-afiliacion.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tieneRemisiones)
            <li>
                <a href="{{ route('remisiones.index') }}"
                   class="{{ request()->routeIs('remisiones.*') ? 'active' : '' }}">
                    <span>Crear Remisiones</span>
                </a>
            </li>
            @endif
            @if($tieneRecibos)
            <li>
                <a href="{{ route('recibos.index') }}"
                   class="{{ request()->routeIs('recibos.*') ? 'active' : '' }}">
                    <span>Crear Recibos</span>
                </a>
            </li>
            @endif
            @if($tieneRecibosAfiliacion)
            <li>
                <a href="{{ route('recibos-afiliacion.index') }}"
                   class="{{ request()->routeIs('recibos-afiliacion.*') ? 'active' : '' }}">
                    <span>Recibos de Afiliación</span>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    {{-- GESTIÓN --}}
    @php
        $tieneAfiliados       = $modulosPermitidos->contains('afiliados');
        $tieneAfiliaciones    = $modulosPermitidos->contains('afiliaciones');
        $tieneAfiliadoServ    = $modulosPermitidos->contains('afiliado_servicios');
        $tieneArlAfiliados    = $modulosPermitidos->contains('arl_afiliados');
        $tieneEmpresasLab     = $modulosPermitidos->contains('empresas_laborales');
        $tieneAsesores        = $modulosPermitidos->contains('asesores');
        $tieneServicios       = $modulosPermitidos->contains('servicios');
        $tieneIncapacidades   = $modulosPermitidos->contains('incapacidades');
        $tieneServExt         = $modulosPermitidos->contains('servicios_externos');

        $tieneAlgunAfiliado   = $tieneAfiliados || $tieneAfiliaciones || $tieneAfiliadoServ || $tieneArlAfiliados;
        $tieneAlgunEmpresa    = $tieneEmpresasLab || $tieneAsesores || $tieneServicios;
        $hayGestion           = $tieneAlgunAfiliado || $tieneAlgunEmpresa || $tieneIncapacidades || $tieneServExt;
    @endphp

    @if($hayGestion)
    <li class="nav-heading">Gestión</li>
    @endif

    @if($tieneAlgunAfiliado)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('afiliados.*','afiliaciones.*','afiliado_servicios.*','arl-afiliados.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-afiliados"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-people-fill"></i>
            <span>Afiliados</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-afiliados"
            class="nav-content collapse {{ request()->routeIs('afiliados.*','afiliaciones.*','afiliado_servicios.*','arl-afiliados.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tieneAfiliados)
            <li>
                <a href="{{ route('afiliados.index') }}"
                   class="{{ request()->routeIs('afiliados.*') ? 'active' : '' }}">
                    <span>Afiliados</span>
                </a>
            </li>
            @endif
            @if($tieneAfiliaciones)
            <li>
                <a href="{{ route('afiliaciones.index') }}"
                   class="{{ request()->routeIs('afiliaciones.*') ? 'active' : '' }}">
                    <span>Afiliaciones</span>
                </a>
            </li>
            @endif
            @if($tieneAfiliadoServ)
            <li>
                <a href="{{ route('afiliado_servicios.index') }}"
                   class="{{ request()->routeIs('afiliado_servicios.*') ? 'active' : '' }}">
                    <span>Servicios por Afiliado</span>
                </a>
            </li>
            @endif
            @if($tieneArlAfiliados)
            <li>
                <a href="{{ route('arl-afiliados.index') }}"
                   class="{{ request()->routeIs('arl-afiliados.*') ? 'active' : '' }}">
                    <span>Afiliados ARL</span>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    @if($tieneAlgunEmpresa)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('empresas_laborales.*','asesores.*','servicios.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-empresas"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-building-fill"></i>
            <span>Empresas Laborales</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-empresas"
            class="nav-content collapse {{ request()->routeIs('empresas_laborales.*','asesores.*','servicios.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($tieneEmpresasLab)
            <li>
                <a href="{{ route('empresas_laborales.index') }}"
                   class="{{ request()->routeIs('empresas_laborales.*') ? 'active' : '' }}">
                    <span>Empresas Laborales</span>
                </a>
            </li>
            @endif
            @if($tieneAsesores)
            <li>
                <a href="{{ route('asesores.index') }}"
                   class="{{ request()->routeIs('asesores.*') ? 'active' : '' }}">
                    <span>Asesores</span>
                </a>
            </li>
            @endif
            @if($tieneServicios)
            <li>
                <a href="{{ route('servicios.index') }}"
                   class="{{ request()->routeIs('servicios.*') ? 'active' : '' }}">
                    <span>Servicios</span>
                </a>
            </li>
            @endif
        </ul>
    </li>
    @endif

    @if($tieneIncapacidades)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('incapacidades.*') ? '' : 'collapsed' }}"
           href="{{ route('incapacidades.index') }}">
            <i class="bi bi-file-medical-fill"></i>
            <span>Incapacidades</span>
        </a>
    </li>
    @endif

    @if($tieneServExt)
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('servicios-externos.*') ? '' : 'collapsed' }}"
           href="{{ route('servicios-externos.index') }}">
            <i class="bi bi-globe2"></i>
            <span>Servicios Externos</span>
        </a>
    </li>
    @endif

    {{-- EXPORTACIONES --}}
    @if($modulosPermitidos->contains('exportaciones'))
    <li class="nav-divider"></li>
    <li class="nav-heading">Exportaciones</li>

    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('export.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-exportaciones"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-box-arrow-up-right"></i>
            <span>Exportaciones</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-exportaciones"
            class="nav-content collapse {{ request()->routeIs('export.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            <li>
                <a href="{{ route('export.index') }}"
                   class="{{ request()->routeIs('export.index') ? 'active' : '' }}">
                    <span>Lotes PILA</span>
                </a>
            </li>
            <li>
                <a href="{{ route('export.afiliados.exportar') }}"
                   class="{{ request()->routeIs('export.afiliados.exportar') ? 'active' : '' }}">
                    <span>Afiliados Excel</span>
                </a>
            </li>
            <li>
                <a href="{{ route('export.arl-afiliados.exportar') }}"
                   class="{{ request()->routeIs('export.arl-afiliados.exportar') ? 'active' : '' }}">
                    <span>Afiliados ARL Excel</span>
                </a>
            </li>
        </ul>
    </li>
    @endif

    {{-- CONFIGURACIÓN --}}
    @if($modulosPermitidos->contains('arls') || $modulosPermitidos->contains('usuarios'))
    <li class="nav-divider"></li>
    <li class="nav-heading">Configuración</li>
    @endif

    @if($modulosPermitidos->contains('arls'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('arls.*','eps.*','pensions.*','cajas.*','documentos.*','subtipo_cotizantes.*','parametros_anuales.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-libreria"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-collection-fill"></i>
            <span>Librería</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-libreria"
            class="nav-content collapse {{ request()->routeIs('arls.*','eps.*','pensions.*','cajas.*','documentos.*','subtipo_cotizantes.*','parametros_anuales.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            <li><a href="{{ route('arls.index') }}"               class="{{ request()->routeIs('arls.*') ? 'active' : '' }}"><span>ARL</span></a></li>
            <li><a href="{{ route('eps.index') }}"                class="{{ request()->routeIs('eps.*') ? 'active' : '' }}"><span>EPS</span></a></li>
            <li><a href="{{ route('pensions.index') }}"           class="{{ request()->routeIs('pensions.*') ? 'active' : '' }}"><span>Pensión</span></a></li>
            <li><a href="{{ route('cajas.index') }}"              class="{{ request()->routeIs('cajas.*') ? 'active' : '' }}"><span>Caja</span></a></li>
            <li><a href="{{ route('documentos.index') }}"         class="{{ request()->routeIs('documentos.*') ? 'active' : '' }}"><span>Documentos</span></a></li>
            <li><a href="{{ route('subtipo_cotizantes.index') }}" class="{{ request()->routeIs('subtipo_cotizantes.*') ? 'active' : '' }}"><span>Subtipos</span></a></li>
            <li><a href="{{ route('parametros_anuales.index') }}" class="{{ request()->routeIs('parametros_anuales.*') ? 'active' : '' }}"><span>Valor Anual</span></a></li>
        </ul>
    </li>
    @endif

    @if($modulosPermitidos->contains('usuarios'))
    <li class="nav-item">
        <a class="nav-link {{ request()->routeIs('empresas.*','usuarios.*','roles.*','modulos-empresa.*','modulos-rol.*') ? '' : 'collapsed' }}"
           data-bs-target="#nav-sistema"
           data-bs-toggle="collapse"
           href="#">
            <i class="bi bi-shield-lock-fill"></i>
            <span>Sistema</span>
            <i class="bi bi-chevron-down ms-auto"></i>
        </a>
        <ul id="nav-sistema"
            class="nav-content collapse {{ request()->routeIs('empresas.*','usuarios.*','roles.*','modulos-empresa.*','modulos-rol.*') ? 'show' : '' }}"
            data-bs-parent="#sidebar-nav">
            @if($modulosPermitidos->contains('empresas'))
            <li><a href="{{ route('empresas.index') }}" class="{{ request()->routeIs('empresas.*') ? 'active' : '' }}"><span>Empresas</span></a></li>
            @endif
            <li><a href="{{ route('usuarios.index') }}" class="{{ request()->routeIs('usuarios.*') ? 'active' : '' }}"><span>Usuarios</span></a></li>
            <li><a href="{{ route('roles.index') }}"    class="{{ request()->routeIs('roles.*') ? 'active' : '' }}"><span>Roles</span></a></li>
            @if($modulosPermitidos->contains('modulos_empresa'))
            <li><a href="{{ route('modulos-empresa.index') }}" class="{{ request()->routeIs('modulos-empresa.*') ? 'active' : '' }}"><span>Módulos por Empresa</span></a></li>
            @endif
            @if($modulosPermitidos->contains('modulos_rol'))
            <li><a href="{{ route('modulos-rol.index') }}" class="{{ request()->routeIs('modulos-rol.*') ? 'active' : '' }}"><span>Módulos por Rol</span></a></li>
            @endif
        </ul>
    </li>
    @endif

</ul>

</aside>
