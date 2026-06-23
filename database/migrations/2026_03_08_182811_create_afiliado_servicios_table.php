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
        Schema::create('afiliado_servicios', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('afiliado_id')
          ->constrained('afiliados')
          ->cascadeOnDelete();

    $table->foreignId('servicio_id')
          ->constrained('servicios')
          ->cascadeOnDelete();

    $table->decimal('valor', 12, 2)->default(0);

    $table->boolean('estado')->default(true);

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('afiliado_servicios');
    }
};
