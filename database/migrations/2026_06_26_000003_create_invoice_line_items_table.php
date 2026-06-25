<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->integer('linea_numero');
            $table->string('descripcion', 500);
            $table->decimal('cantidad', 10, 4);
            $table->enum('unidad', [
                'unidad', 'kilogramo', 'gramo', 'metro', 'centimetro',
                'hora', 'minuto', 'litro', 'mililitro'
            ]);
            $table->decimal('valor_unitario', 12, 2);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('valor_linea', 14, 2);
            $table->timestamps();

            $table->index(['empresa_id', 'id']);
            $table->index(['invoice_id']);
            $table->unique(['invoice_id', 'linea_numero']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_line_items');
    }
};
