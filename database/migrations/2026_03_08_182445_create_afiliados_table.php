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
        Schema::create('afiliados', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->foreignId('empresa_laboral_id')
        ->constrained('empresas_laborales')
        ->cascadeOnDelete();

    $table->foreignId('asesor_id')
        ->nullable()
        ->constrained('asesores')
        ->nullOnDelete();

    $table->foreignId('documento_id')
        ->constrained('documentos')
        ->restrictOnDelete();

    $table->foreignId('subtipo_cotizante_id')
        ->constrained('subtipo_cotizantes')
        ->restrictOnDelete();

    $table->string('numero_documento');

    $table->string('primer_nombre');
    $table->string('segundo_nombre')->nullable();
    $table->string('primer_apellido');
    $table->string('segundo_apellido')->nullable();

    $table->date('fecha_nacimiento');

    $table->enum('sexo', ['M','F','Otro'])->default('M');

    $table->string('correo')->nullable();
    $table->string('telefono')->nullable();
    $table->string('direccion')->nullable();
    $table->string('ciudad')->nullable();

    $table->string('google_drive_folder_id')->nullable();

    $table->boolean('estado')->default(true);

    $table->timestamps();

    $table->unique(['empresa_id','numero_documento']);
    $table->index(['empresa_id','estado']);
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliados');
    }
};
