<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('novedades_nomina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('afiliado_id')->constrained('afiliados')->onDelete('cascade');
            $table->string('tipo_novedad'); // salario_ordinario, bonificacion, incapacidad, etc
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_final')->nullable();
            $table->decimal('cantidad', 10, 2)->default(1); // Para incapacidades, permisos, etc
            $table->decimal('valor_unitario', 12, 2)->nullable(); // Para bonificaciones especiales
            $table->decimal('valor_total', 12, 2)->nullable(); // Calculado automáticamente
            $table->string('documento_soporte')->nullable(); // Ruta a archivo adjunto
            $table->enum('estado', ['pendiente', 'aprobada', 'rechazada'])->default('pendiente');
            $table->timestamps();

            $table->index(['empresa_id', 'afiliado_id']);
            $table->index(['tipo_novedad']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('novedades_nomina');
    }
};
