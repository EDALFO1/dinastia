<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('journal_entry_id')->constrained('journal_entries')->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('restrict');

            $table->text('descripcion')->nullable()->comment('Descripción de la línea');
            $table->enum('tipo_movimiento', ['debito', 'credito'])->comment('Tipo de movimiento');
            $table->decimal('valor', 14, 2)->comment('Valor del movimiento');

            $table->foreignId('centro_costo_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->string('referencia_documento')->nullable()->comment('Referencia documento');

            $table->timestamps();

            // Índices
            $table->index(['empresa_id', 'journal_entry_id']);
            $table->index(['account_id']);
            $table->index(['tipo_movimiento']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_lines');
    }
};
