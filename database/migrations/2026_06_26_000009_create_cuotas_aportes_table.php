<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cuotas_aportes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('recibo_id')->constrained('recibos')->onDelete('cascade');
            $table->string('tipo_aporte'); // afp, eps, arl, caja_compensacion
            $table->decimal('base_calculo', 12, 2);
            $table->decimal('porcentaje_empleado', 5, 4)->default(0);
            $table->decimal('aporte_empleado', 12, 2)->default(0);
            $table->decimal('porcentaje_empleador', 5, 4)->default(0);
            $table->decimal('aporte_empleador', 12, 2)->default(0);
            $table->decimal('total_aporte', 12, 2)->default(0);
            $table->timestamps();

            $table->index(['empresa_id', 'recibo_id']);
            $table->index(['tipo_aporte']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cuotas_aportes');
    }
};
