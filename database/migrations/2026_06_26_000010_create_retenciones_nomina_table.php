<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retenciones_nomina', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('recibo_id')->constrained('recibos')->onDelete('cascade');
            $table->string('tipo_retencion'); // renta, salud, solidaridad, etc
            $table->decimal('base_calculo', 12, 2);
            $table->decimal('porcentaje', 5, 4)->default(0);
            $table->decimal('valor_retencion', 12, 2)->default(0);
            $table->string('concepto')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'recibo_id']);
            $table->index(['tipo_retencion']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retenciones_nomina');
    }
};
