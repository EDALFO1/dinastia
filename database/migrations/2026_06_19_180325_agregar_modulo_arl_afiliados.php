<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insertar módulo arl_afiliados
        $moduloId = DB::table('modulos')->insertGetId([
            'slug'        => 'arl_afiliados',
            'nombre'      => 'Afiliados ARL',
            'descripcion' => 'Gestión de afiliados que solo pagan ARL',
            'grupo'       => 'gestion',
            'orden'       => 7,
            'activo'      => true,
        ]);

        // Asignarlo a todos los roles existentes que ya tienen 'afiliados'
        $rolesConAfiliados = DB::table('rol_modulo as rm')
            ->join('modulos as m', 'm.id', '=', 'rm.modulo_id')
            ->where('m.slug', 'afiliados')
            ->pluck('rm.rol_id');

        foreach ($rolesConAfiliados as $rolId) {
            DB::table('rol_modulo')->insertOrIgnore([
                'rol_id'    => $rolId,
                'modulo_id' => $moduloId,
            ]);
        }

        // Asignarlo a empresas que ya tienen 'afiliados'
        $empresasConAfiliados = DB::table('empresa_modulo as em')
            ->join('modulos as m', 'm.id', '=', 'em.modulo_id')
            ->where('m.slug', 'afiliados')
            ->pluck('em.empresa_id');

        foreach ($empresasConAfiliados as $empresaId) {
            DB::table('empresa_modulo')->insertOrIgnore([
                'empresa_id' => $empresaId,
                'modulo_id'  => $moduloId,
            ]);
        }
    }

    public function down(): void
    {
        $modulo = DB::table('modulos')->where('slug', 'arl_afiliados')->first();
        if ($modulo) {
            DB::table('rol_modulo')->where('modulo_id', $modulo->id)->delete();
            DB::table('empresa_modulo')->where('modulo_id', $modulo->id)->delete();
            DB::table('modulos')->where('id', $modulo->id)->delete();
        }
    }
};
