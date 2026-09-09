<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('horarios_regionales', function (Blueprint $table) {
            $table->unsignedInteger('tolerancia_mensual_minutos')->nullable()->after('hora_tolerancia');
        });
    }

    public function down(): void
    {
        Schema::table('horarios_regionales', function (Blueprint $table) {
            $table->dropColumn('tolerancia_mensual_minutos');
        });
    }
};
