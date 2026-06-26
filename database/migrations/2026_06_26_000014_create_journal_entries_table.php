<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');

            $table->string('numero_asiento')->unique()->comment('Número secuencial del asiento');
            $table->date('fecha')->comment('Fecha del asiento');
            $table->text('descripcion')->nullable()->comment('Descripción del asiento');

            $table->string('referencia_documento')->nullable()->comment('Referencia a documento origen');
            $table->string('tipo_documento')->nullable()->comment('Tipo de documento (factura, recibo, etc)');

            // Referencia a usuarios
            $table->foreignId('usuario_creacion_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('usuario_aprobacion_id')->nullable()->constrained('users')->onDelete('set null');

            // Estado y auditoría
            $table->enum('estado', ['borrador', 'posteado', 'rechazado'])->default('borrador');
            $table->dateTime('fecha_aprobacion')->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index(['empresa_id', 'fecha']);
            $table->index(['estado']);
            $table->index(['numero_asiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
