<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('invoice_sequence_id')->constrained('invoice_sequences')->onDelete('restrict');
            $table->bigInteger('numero');
            $table->string('razon_ajuste'); // Válida según DIAN (ej: "Por devolución")
            $table->text('descripcion_ajuste')->nullable();
            $table->date('fecha_emision');
            $table->decimal('porcentaje_descuento', 5, 2)->default(0);
            $table->decimal('valor_descuento', 12, 2)->default(0);
            $table->decimal('valor_impuesto_descuento', 12, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['borrador', 'enviada', 'aceptada', 'rechazada'])->default('borrador');
            $table->longText('xml_factura')->nullable();
            $table->json('firma_digital')->nullable();
            $table->string('uuid_dian')->nullable()->unique();
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['invoice_id']);
            $table->index(['uuid_dian']);
            $table->index(['estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
