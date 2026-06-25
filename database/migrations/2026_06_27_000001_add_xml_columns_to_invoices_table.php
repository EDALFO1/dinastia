<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'xml_status')) {
                $table->enum('xml_status', ['pending', 'generated', 'signed'])->default('pending')->after('uuid_dian');
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'xml_status')) {
                $table->dropColumn('xml_status');
            }
        });
    }
};
