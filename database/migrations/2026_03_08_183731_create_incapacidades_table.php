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
        Schema::create('incapacidades', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();

    $table->foreignId('afiliado_id')->nullable()->constrained()->nullOnDelete();

    $table->string('documento');
    $table->string('nombre');

    $table->foreignId('empresa_externa_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('empresa_laboral_id')->nullable()->constrained()->nullOnDelete();

    $table->enum('entidad_tipo',['EPS','ARL']);

    $table->foreignId('eps_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('arl_id')->nullable()->constrained()->nullOnDelete();

    $table->string('entidad_nombre');

    $table->date('fecha_inicio');
    $table->date('fecha_fin');

    $table->unsignedInteger('dias_solicitados');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incapacidades');
    }
};
