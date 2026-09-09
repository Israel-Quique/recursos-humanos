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
        Schema::table('reglas_sanciones', function (Blueprint $table) {
            $table->string('tipo_vigencia', 30)->default('siempre')->after('activo'); // siempre, desde_fecha, solo_gestion, no_aplica
            $table->integer('aplica_desde_gestion')->nullable()->after('tipo_vigencia'); // ej: 2026
            $table->integer('aplica_desde_mes')->nullable()->after('aplica_desde_gestion'); // 1..12 ej: 9 para Septiembre
            $table->integer('aplica_hasta_gestion')->nullable()->after('aplica_desde_mes');
            $table->integer('aplica_hasta_mes')->nullable()->after('aplica_hasta_gestion');
            $table->string('explicacion_vigencia', 255)->nullable()->after('aplica_hasta_mes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reglas_sanciones', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_vigencia',
                'aplica_desde_gestion',
                'aplica_desde_mes',
                'aplica_hasta_gestion',
                'aplica_hasta_mes',
                'explicacion_vigencia',
            ]);
        });
    }
};
