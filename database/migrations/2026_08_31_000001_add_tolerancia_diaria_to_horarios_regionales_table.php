<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_regionales', function (Blueprint $table) {
            $table->integer('tolerancia_minutos')->default(5)->after('hora_salida');
            $table->time('hora_tolerancia')->nullable()->after('tolerancia_minutos');
        });
    }

    public function down(): void
    {
        Schema::table('horarios_regionales', function (Blueprint $table) {
            $table->dropColumn(['tolerancia_minutos', 'hora_tolerancia']);
        });
    }
};
