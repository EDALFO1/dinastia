<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModulosSeeder extends Seeder
{
    public function run(): void
    {
        $modulos = [
            // grupo: principal
            ['slug' => 'dashboard',          'nombre' => 'Dashboard',               'grupo' => 'principal',     'orden' => 1],

            // grupo: operativo
            ['slug' => 'remisiones',         'nombre' => 'Remisiones',              'grupo' => 'operativo',     'orden' => 1],
            ['slug' => 'recibos',            'nombre' => 'Recibos',                 'grupo' => 'operativo',     'orden' => 2],
            ['slug' => 'recibos_afiliacion', 'nombre' => 'Recibos de Afiliación',   'grupo' => 'operativo',     'orden' => 3],

            // grupo: gestion
            ['slug' => 'afiliados',          'nombre' => 'Afiliados',               'grupo' => 'gestion',       'orden' => 1],
            ['slug' => 'afiliaciones',       'nombre' => 'Afiliaciones',            'grupo' => 'gestion',       'orden' => 2],
            ['slug' => 'afiliado_servicios', 'nombre' => 'Servicios por Afiliado',  'grupo' => 'gestion',       'orden' => 3],
            ['slug' => 'empresas_laborales', 'nombre' => 'Empresas Laborales',      'grupo' => 'gestion',       'orden' => 4],
            ['slug' => 'asesores',           'nombre' => 'Asesores',                'grupo' => 'gestion',       'orden' => 5],
            ['slug' => 'servicios',          'nombre' => 'Servicios',               'grupo' => 'gestion',       'orden' => 6],
            ['slug' => 'incapacidades',      'nombre' => 'Incapacidades',           'grupo' => 'gestion',       'orden' => 7],
            ['slug' => 'servicios_externos', 'nombre' => 'Servicios Externos',      'grupo' => 'gestion',       'orden' => 8],
            ['slug' => 'exportaciones',      'nombre' => 'Exportaciones',           'grupo' => 'gestion',       'orden' => 9],
            ['slug' => 'empresas_externas',  'nombre' => 'Empresas Externas',       'grupo' => 'gestion',       'orden' => 10],
            ['slug' => 'notas',              'nombre' => 'Notas',                   'grupo' => 'gestion',       'orden' => 11],
            ['slug' => 'planes',             'nombre' => 'Planes',                  'grupo' => 'gestion',       'orden' => 12],
            ['slug' => 'empresa_claves',     'nombre' => 'Claves de Empresa',       'grupo' => 'gestion',       'orden' => 13],

            // grupo: configuracion (librería)
            ['slug' => 'arls',               'nombre' => 'ARL',                     'grupo' => 'configuracion', 'orden' => 1],
            ['slug' => 'eps',                'nombre' => 'EPS',                     'grupo' => 'configuracion', 'orden' => 2],
            ['slug' => 'pensions',           'nombre' => 'Pensión',                 'grupo' => 'configuracion', 'orden' => 3],
            ['slug' => 'cajas',              'nombre' => 'Caja',                    'grupo' => 'configuracion', 'orden' => 4],
            ['slug' => 'documentos',         'nombre' => 'Documentos',              'grupo' => 'configuracion', 'orden' => 5],
            ['slug' => 'subtipo_cotizantes', 'nombre' => 'Subtipos Cotizantes',     'grupo' => 'configuracion', 'orden' => 6],
            ['slug' => 'parametros_anuales', 'nombre' => 'Valor Anual (SMMLV)',     'grupo' => 'configuracion', 'orden' => 7],

            // grupo: sistema
            ['slug' => 'empresas',           'nombre' => 'Empresas',                'grupo' => 'sistema',       'orden' => 1],
            ['slug' => 'usuarios',           'nombre' => 'Usuarios',                'grupo' => 'sistema',       'orden' => 2],
            ['slug' => 'roles',              'nombre' => 'Roles',                   'grupo' => 'sistema',       'orden' => 3],
            ['slug' => 'modulos_empresa',    'nombre' => 'Módulos por Empresa',     'grupo' => 'sistema',       'orden' => 4],
            ['slug' => 'modulos_rol',        'nombre' => 'Módulos por Rol',         'grupo' => 'sistema',       'orden' => 5],
        ];

        foreach ($modulos as $m) {
            DB::table('modulos')->insertOrIgnore(array_merge($m, [
                'activo'     => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
