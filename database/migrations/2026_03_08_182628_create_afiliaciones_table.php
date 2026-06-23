<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
    Schema::create('afiliaciones', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();

    $table->foreignId('afiliado_id')->constrained()->cascadeOnDelete();

    $table->foreignId('eps_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('arl_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('pension_id')->nullable()->constrained()->nullOnDelete();
    $table->foreignId('caja_id')->nullable()->constrained()->nullOnDelete();

    $table->unsignedTinyInteger('nivel_arl')->nullable();

    $table->enum('tipo_ibc', ['SMMLV','FIJO'])->default('SMMLV');

    $table->decimal('ibc',12,2)->nullable();

    $table->date('fecha_afiliacion');
    $table->date('fecha_retiro')->nullable();

    $table->boolean('estado')->default(true);

    $table->timestamps();
});
    }

    public function down(): void
    {
        Schema::dropIfExists('afiliaciones');
    }
};