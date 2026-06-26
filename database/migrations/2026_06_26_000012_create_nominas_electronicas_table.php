<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nominas_electronicas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('recibo_id')->constrained('recibos')->onDelete('cascade');
            $table->string('numero_nomina')->unique()->comment('Número secuencial de nómina');
            $table->integer('consecutivo')->comment('Consecutivo DIAN');
            $table->date('fecha_emision');
            $table->date('periodo_pago_inicio');
            $table->date('periodo_pago_final');
            $table->decimal('salario_ordinario', 12, 2)->default(0);
            $table->decimal('salario_integral', 12, 2)->default(0);
            $table->decimal('total_devengado', 12, 2)->default(0);
            $table->decimal('total_descuentos', 12, 2)->default(0);
            $table->decimal('neto_pagar', 12, 2)->default(0);
            $table->enum('estado', ['borrador', 'enviada', 'aceptada', 'rechazada', 'error'])->default('borrador');
            $table->longText('xml_nomina')->nullable()->comment('XML firmado de la nómina');
            $table->json('firma_digital')->nullable()->comment('Información de la firma digital');
            $table->string('uuid_dian')->nullable()->unique()->comment('Identificador DIAN');
            $table->dateTime('fecha_envio_dian')->nullable();
            $table->json('respuesta_dian')->nullable()->comment('Respuesta completa de DIAN');
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['uuid_dian']);
            $table->index(['estado']);
            $table->index(['fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nominas_electronicas');
    }
};
