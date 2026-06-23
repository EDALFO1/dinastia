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
        Schema::create('recibos', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
        ->constrained('empresas')
        ->cascadeOnDelete();

    $table->unsignedInteger('numero');

    $table->date('fecha');

    $table->foreignId('afiliado_id')
        ->constrained('afiliados')
        ->cascadeOnDelete();

    $table->unsignedTinyInteger('dias_liquidar');

    $table->decimal('ibc',12,2);

    $table->decimal('valor_eps',12,2)->default(0);
    $table->decimal('valor_arl',12,2)->default(0);
    $table->decimal('valor_pension',12,2)->default(0);
    $table->decimal('valor_caja',12,2)->default(0);

    $table->decimal('valor_admon',12,2)->default(0);
    $table->decimal('valor_servicios',12,2)->default(0);

    $table->decimal('total',14,2);

    $table->enum('novedad',['Ingreso','Retiro'])->nullable();

    $table->date('fecha_retiro')->nullable();

    $table->foreignId('export_batch_id')
        ->nullable()
        ->constrained('export_batches')
        ->nullOnDelete();

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
        Schema::dropIfExists('recibos');
    }
};
