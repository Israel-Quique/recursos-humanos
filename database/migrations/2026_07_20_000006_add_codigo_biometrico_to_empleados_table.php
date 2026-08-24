<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('codigo_biometrico')->nullable()->after('apellido');
            $table->unique(['codigo_biometrico']);
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropUnique(['codigo_biometrico']);
            $table->dropColumn('codigo_biometrico');
        });
    }
};
