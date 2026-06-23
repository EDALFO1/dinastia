<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('export_batches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('codigo')->nullable();

            $table->string('periodo',7)->nullable();

            $table->unsignedInteger('recibos_count')->default(0);

            $table->decimal('total',14,2)->default(0);

            $table->timestamps();

            $table->index(['empresa_id','periodo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('export_batches');
    }
};
