<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->index('fecha', 'registros_asistencia_fecha_index');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->index('created_at', 'empleados_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            $table->dropIndex('registros_asistencia_fecha_index');
        });

        Schema::table('empleados', function (Blueprint $table) {
            $table->dropIndex('empleados_created_at_index');
        });
    }
};
