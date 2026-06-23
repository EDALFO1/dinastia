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
        Schema::table('arl_afiliados', function (Blueprint $table) {
            $table->dropForeign(['empresa_externa_id']);
            $table->dropColumn('empresa_externa_id');

            $table->foreignId('empresa_laboral_id')
                ->nullable()
                ->after('arl_id')
                ->constrained('empresas_laborales')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('arl_afiliados', function (Blueprint $table) {
            $table->dropForeign(['empresa_laboral_id']);
            $table->dropColumn('empresa_laboral_id');

            $table->foreignId('empresa_externa_id')
                ->nullable()
                ->after('arl_id')
                ->constrained('empresa_externas')
                ->nullOnDelete();
        });
    }
};
