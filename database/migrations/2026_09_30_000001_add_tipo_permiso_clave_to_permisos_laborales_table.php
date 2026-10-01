<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna tipo_permiso_clave a permisos_laborales para
     * registrar de forma estructurada qué TipoPermiso específico se aplicó
     * (bm, cv, salud, etc.) y así la planilla de refrigerio puede leerlo
     * directamente sin adivinar por el texto del motivo.
     */
    public function up(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->string('tipo_permiso_clave')->nullable()->after('tipo')
                  ->comment('Clave del TipoPermiso (ej: bm, cv, salud). Solo aplica cuando tipo=permiso.');
        });
    }

    public function down(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->dropColumn('tipo_permiso_clave');
        });
    }
};
