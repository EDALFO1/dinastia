<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recibos_afiliacion', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedInteger('numero');
            $table->unsignedBigInteger('afiliado_id');
            $table->date('fecha');
            $table->string('concepto', 500);
            $table->decimal('valor', 12, 2);
            $table->enum('estado_pago', ['pendiente', 'pagado'])->default('pendiente');
            $table->date('fecha_pago')->nullable();
            $table->text('notas')->nullable();
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('afiliado_id')->references('id')->on('afiliados')->onDelete('cascade');
            $table->unique(['empresa_id', 'numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recibos_afiliacion');
    }
};
