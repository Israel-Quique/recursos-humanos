<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->json('sedes_biometricas')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropColumn('sedes_biometricas');
        });
    }
};
