<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->string('boleta_cargo', 150)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('permisos_laborales', function (Blueprint $table) {
            $table->dropColumn('boleta_cargo');
        });
    }
};
