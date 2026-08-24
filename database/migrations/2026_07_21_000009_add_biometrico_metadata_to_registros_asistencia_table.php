<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->string('tipo_verificacion')->nullable()->after('hora_salida');
            $table->string('estado_marcacion')->nullable()->after('tipo_verificacion');
            $table->string('evento_biometrico')->nullable()->after('estado_marcacion');
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropColumn(['tipo_verificacion', 'estado_marcacion', 'evento_biometrico']);
        });
    }
};
