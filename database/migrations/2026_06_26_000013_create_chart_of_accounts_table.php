<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('parent_id')->nullable()->constrained('chart_of_accounts')->onDelete('cascade');

            // Código de cuenta (2-10 dígitos según nivel)
            $table->string('codigo', 10)->comment('Código único de cuenta');
            $table->string('nombre')->comment('Nombre de la cuenta');
            $table->text('descripcion')->nullable();

            // Clasificación
            $table->enum('tipo_cuenta', ['activo', 'pasivo', 'patrimonio', 'ingresos', 'gastos', 'costo', 'eliminacion']);
            $table->tinyInteger('nivel')->comment('Nivel jerárquico 1-5');

            // Valores iniciales
            $table->decimal('saldo_inicial', 14, 2)->default(0);

            // Vigencia
            $table->date('fecha_vigencia_inicio')->nullable();
            $table->date('fecha_vigencia_fin')->nullable();

            // Estado
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // Configuración
            $table->boolean('permite_movimiento')->default(false)->comment('Permite asientos contables');
            $table->integer('orden')->nullable()->comment('Orden de visualización');

            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'tipo_cuenta']);
            $table->index(['empresa_id', 'nivel']);
            $table->index(['empresa_id', 'estado']);
            $table->index(['parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
