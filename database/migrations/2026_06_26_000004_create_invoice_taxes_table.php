<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->foreignId('invoice_line_item_id')->nullable()->constrained('invoice_line_items')->onDelete('cascade');
            $table->enum('tipo_impuesto', ['iva', 'impuesto_consumo', 'impuesto_nacional']);
            $table->decimal('porcentaje', 5, 2);
            $table->decimal('base', 14, 2);
            $table->decimal('valor', 14, 2);
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['invoice_id']);
            $table->index(['tipo_impuesto']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_taxes');
    }
};
