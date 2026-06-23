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
        Schema::create('arls', function (Blueprint $table) {
            $table->id();

            $table->string('nombre');
            $table->string('codigo');

            $table->unsignedTinyInteger('nivel'); // 1 a 5

            $table->decimal('porcentaje',6,4);

            $table->string('actividad_economica',7)->nullable();

            $table->timestamps();

            $table->unique(['codigo','nivel']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arls');
    }
};
