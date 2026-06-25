<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('afiliados', function (Blueprint $table) {
            $table->index(['empresa_id', 'id'], 'afiliados_empresa_id_id_index');
        });

        Schema::table('recibos', function (Blueprint $table) {
            $table->index(['empresa_id', 'id'], 'recibos_empresa_id_id_index');
        });

        Schema::table('remisiones', function (Blueprint $table) {
            $table->index(['empresa_id', 'id'], 'remisiones_empresa_id_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('afiliados', function (Blueprint $table) {
            $table->dropIndex('afiliados_empresa_id_id_index');
        });

        Schema::table('recibos', function (Blueprint $table) {
            $table->dropIndex('recibos_empresa_id_id_index');
        });

        Schema::table('remisiones', function (Blueprint $table) {
            $table->dropIndex('remisiones_empresa_id_id_index');
        });
    }
};
