<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incapacidades', function (Blueprint $table) {
            $table->date('fecha_radicacion')->nullable()->after('dias_solicitados');

            $table->enum('estado', [
                'transcrita',
                'pendiente_radicar',
                'radicada',
                'aprobada',
                'liquidada',
                'rechazada',
                'pagada',
            ])->default('transcrita')->after('fecha_radicacion');

            $table->date('fecha_pago')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('incapacidades', function (Blueprint $table) {
            $table->dropColumn(['fecha_radicacion', 'estado', 'fecha_pago']);
        });
    }
};
