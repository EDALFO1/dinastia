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
        Schema::create('empresa_claves', function (Blueprint $table) {
        $table->id();

        $table->foreignId('empresa_id')
              ->constrained('empresas')
              ->onDelete('cascade');

        $table->foreignId('servicio_externo_id')
              ->constrained('servicios_externos')
              ->onDelete('cascade');

        $table->string('usuario')->nullable();
        $table->string('correo_registrado')->nullable();
        $table->text('password')->nullable();

        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa_claves');
    }
};
