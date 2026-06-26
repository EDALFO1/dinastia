<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('periodos_nomina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->integer('numero_periodo')->comment('1-12 para mensual');
            $table->year('anio');
            $table->tinyInteger('mes');
            $table->date('fecha_inicio');
            $table->date('fecha_final');
            $table->date('fecha_pago');
            $table->integer('dias_habiles')->comment('Excluyendo domingos');
            $table->enum('estado', ['abierto', 'cerrado', 'procesado'])->default('abierto');
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'numero_periodo', 'anio']);
            $table->index(['anio', 'mes']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('periodos_nomina');
    }
};
