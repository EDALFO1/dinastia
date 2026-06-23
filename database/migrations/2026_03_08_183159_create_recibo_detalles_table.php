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
        Schema::create('recibo_detalles', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->foreignId('recibo_id')
        ->constrained('recibos')
        ->cascadeOnDelete();

    $table->string('concepto');

    $table->decimal('valor',12,2);

    $table->timestamps();

    $table->index('empresa_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recibo_detalles');
    }
};
