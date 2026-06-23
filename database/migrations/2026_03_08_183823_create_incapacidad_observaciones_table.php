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
        Schema::create('incapacidad_observaciones', function (Blueprint $table) {
    $table->id();

    $table->foreignId('empresa_id')
          ->constrained()
          ->cascadeOnDelete();

    $table->foreignId('incapacidad_id')
          ->constrained('incapacidades')
          ->cascadeOnDelete();

    $table->text('nota');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incapacidad_observaciones');
    }
};
