<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Rol;
use App\Models\Modulo;

class RolModulosSeeder extends Seeder
{
    public function run(): void
    {
        // Módulos accesibles por todos los roles autenticados
        $todosLosRoles = [
            'dashboard', 'remisiones', 'afiliados', 'afiliaciones',
            'afiliado_servicios', 'empresas_laborales', 'incapacidades',
            'notas', 'planes', 'empresa_claves',
        ];

        // Módulos adicionales para admin, asesor e invitado (no operador)
        $adminAsesorInvitado = [
            'recibos', 'asesores', 'servicios',
            'empresas_externas', 'exportaciones',
        ];

        // Módulos adicionales para admin, asesor, invitado y operador
        $masOperador = ['servicios_externos', 'recibos_afiliacion'];

        // Módulos exclusivos del admin
        $soloAdmin = [
            'arls', 'eps', 'pensions', 'cajas', 'documentos',
            'subtipo_cotizantes', 'parametros_anuales',
            'empresas', 'usuarios', 'roles',
            'modulos_empresa', 'modulos_rol',
        ];

        $permisosPorRol = [
            'admin'    => array_merge($todosLosRoles, $adminAsesorInvitado, $masOperador, $soloAdmin),
            'asesor'   => array_merge($todosLosRoles, $adminAsesorInvitado, $masOperador),
            'invitado' => array_merge($todosLosRoles, $adminAsesorInvitado, $masOperador),
            'operador' => array_merge($todosLosRoles, $masOperador),
        ];

        foreach ($permisosPorRol as $nombreRol => $slugs) {
            $rol = Rol::where('nombre', $nombreRol)->first();
            if (! $rol) continue;

            $moduloIds = Modulo::whereIn('slug', $slugs)->pluck('id');
            $rol->modulos()->sync($moduloIds);
        }
    }
}
