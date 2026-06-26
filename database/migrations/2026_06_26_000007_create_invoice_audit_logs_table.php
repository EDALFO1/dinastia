<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('invoice_id')->constrained('invoices')->onDelete('cascade');
            $table->enum('tipo_documento', ['factura', 'nota_credito', 'nota_debito'])->default('factura');
            $table->bigInteger('documento_id')->nullable(); // ID de nota si aplica
            $table->string('evento'); // 'created', 'updated', 'signed', 'sent_to_dian', 'accepted', 'rejected', 'revoked'
            $table->text('descripcion')->nullable();
            $table->foreignId('usuario_id')->nullable()->constrained('users')->onDelete('set null');
            $table->ipAddress('ip_address')->nullable();
            $table->json('datos_anteriores')->nullable(); // For tracking changes
            $table->json('datos_nuevos')->nullable(); // For tracking changes
            $table->timestamp('timestamp');

            $table->index(['empresa_id']);
            $table->index(['invoice_id']);
            $table->index(['evento']);
            $table->index(['timestamp']);
            $table->index(['usuario_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_audit_logs');
    }
};
