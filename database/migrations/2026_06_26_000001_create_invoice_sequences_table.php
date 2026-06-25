<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->string('numero_resolucion')->unique();
            $table->enum('tipo_factura', ['factura', 'nota_credito', 'nota_debito']);
            $table->bigInteger('rango_inicio');
            $table->bigInteger('rango_fin');
            $table->bigInteger('proximo_numero');
            $table->date('fecha_vigencia_inicio');
            $table->date('fecha_vigencia_fin');
            $table->enum('estado', ['activa', 'vencida', 'suspendida'])->default('activa');
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['numero_resolucion']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_sequences');
    }
};
