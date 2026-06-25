<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('invoice_sequence_id')->constrained('invoice_sequences')->onDelete('restrict');
            $table->bigInteger('numero')->unique();
            $table->enum('tipo_documento', ['factura', 'nota_credito', 'nota_debito']);
            $table->string('cliente_nit', 20);
            $table->string('cliente_nombre', 255);
            $table->date('fecha_emision');
            $table->date('fecha_vencimiento');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('descuento', 14, 2)->default(0);
            $table->decimal('total_impuestos', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->text('observaciones')->nullable();
            $table->enum('estado', ['borrador', 'enviada', 'aceptada', 'rechazada', 'anulada'])->default('borrador');
            $table->longText('xml_factura')->nullable();
            $table->text('firma_digital')->nullable();
            $table->string('uuid_dian')->nullable()->unique();
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['numero']);
            $table->index(['cliente_nit']);
            $table->index(['estado']);
            $table->index(['fecha_emision']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
