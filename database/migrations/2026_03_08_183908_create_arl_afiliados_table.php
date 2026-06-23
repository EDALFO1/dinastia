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
        Schema::create('arl_afiliados', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->foreignId('documento_id')
        ->constrained('documentos');

    $table->string('numero'); // quitamos unique aquí
    $table->string('nombre');

    $table->date('fecha_ingreso');

    $table->foreignId('arl_id')
        ->constrained('arls');

    $table->foreignId('empresa_externa_id')
        ->constrained('empresa_externas');

    $table->decimal('base_cotizacion', 12, 2)->default(0);
    $table->decimal('administracion', 12, 2)->default(0);

    $table->boolean('estado')->default(true);

    $table->date('fecha_retiro')->nullable();

    $table->boolean('override_parametros')->default(false);

    $table->timestamps();

    // Índice único compuesto
    $table->unique(['empresa_id', 'numero']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arl_afiliados');
    }
};
