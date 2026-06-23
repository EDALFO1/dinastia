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
        Schema::create('empresas_laborales', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
          ->constrained('empresas')
          ->cascadeOnDelete();

    $table->foreignId('documento_id')
          ->constrained('documentos')
          ->restrictOnDelete();

    $table->string('numero_documento');

    $table->string('nombre');
    $table->string('direccion')->nullable();
    $table->string('telefono')->nullable();
    $table->string('contacto')->nullable();

    $table->boolean('estado')->default(true);

    $table->timestamps();

    // Índice único compuesto
    $table->unique(['empresa_id', 'numero_documento']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresas_laborales');
    }
};
