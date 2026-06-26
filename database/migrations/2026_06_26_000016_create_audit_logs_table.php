<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');

            // Información del modelo auditado
            $table->string('auditable_type')->comment('Clase del modelo');
            $table->unsignedBigInteger('auditable_id')->comment('ID del modelo');

            // Tipo de acción
            $table->enum('action', [
                'created',
                'updated',
                'deleted',
                'restored',
                'unauthorized_attempted_change',
            ])->comment('Tipo de acción');

            // Valores antes y después
            $table->json('old_values')->nullable()->comment('Valores anteriores');
            $table->json('new_values')->nullable()->comment('Valores nuevos');

            // Información de cliente
            $table->string('ip_address')->nullable()->comment('IP del cliente');
            $table->string('user_agent', 500)->nullable()->comment('User-Agent del navegador');

            // Descripción de cambios
            $table->text('changes_description')->nullable()->comment('Resumen de cambios');

            $table->softDeletes();
            $table->timestamps();

            // Índices
            $table->index(['empresa_id']);
            $table->index(['user_id']);
            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['action']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
