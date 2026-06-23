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
        Schema::create('periodo_afiliados', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->foreignId('afiliado_id')
        ->constrained('afiliados')
        ->cascadeOnDelete();

    $table->char('periodo',7);

    $table->enum('estado',['Activo','Retirado']);

    $table->foreignId('recibo_id')
        ->nullable()
        ->constrained('recibos')
        ->nullOnDelete();

    $table->timestamps();

    $table->unique(['empresa_id','afiliado_id','periodo']);
    $table->index('empresa_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periodo_afiliados');
    }
};
