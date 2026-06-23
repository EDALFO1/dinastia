<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('empresa_id')
                ->constrained('empresas')
                ->cascadeOnDelete();

            $table->string('nombre', 120);
            $table->string('descripcion', 255)->nullable();

            $table->boolean('incluye_eps')->default(false);
            $table->decimal('porcentaje_eps', 7, 4)->default(0);

            $table->boolean('incluye_pension')->default(false);
            $table->decimal('porcentaje_pension', 7, 4)->default(0);

            $table->boolean('incluye_caja')->default(false);
            $table->decimal('porcentaje_caja', 7, 4)->default(0);

            $table->boolean('incluye_arl')->default(false);
            $table->string('nivel_arl', 5)->nullable();
            $table->decimal('porcentaje_arl', 7, 4)->default(0);

            // false = usar administracion de parametros_anuales
            $table->boolean('usa_admin_fijo')->default(false);
            $table->decimal('valor_admin_fijo', 12, 2)->default(0);

            $table->unsignedSmallInteger('orden')->default(0);
            $table->boolean('estado')->default(true);

            $table->timestamps();

            $table->index('empresa_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes');
    }
};
