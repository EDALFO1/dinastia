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
        Schema::create('remisiones', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->unsignedInteger('numero');

    $table->decimal('mensajeria', 14, 2)->default(0);
    $table->decimal('intereses', 14, 2)->default(0);


    $table->date('fecha');

    $table->foreignId('afiliado_id')
        ->constrained('afiliados')
        ->cascadeOnDelete();    

    $table->unsignedTinyInteger('dias_liquidar');

    $table->decimal('total',14,2);

    $table->timestamps();

    $table->unique(['empresa_id','numero']);
    $table->index('empresa_id');
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remisiones');
    }
};
