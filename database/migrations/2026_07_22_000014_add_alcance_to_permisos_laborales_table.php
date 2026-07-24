<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->string('alcance')->default('dia_completo');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->unsignedInteger('minutos_contabilizados')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->dropColumn(['alcance', 'hora_inicio', 'hora_fin', 'minutos_contabilizados']);
        });
    }
};
