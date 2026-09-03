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
        Schema::table('permiso_comprobantes', function (Blueprint $table) {
            $table->string('ruta_archivo')->nullable()->change();
            $table->binary('archivo_binario')->nullable()->after('ruta_archivo');
            $table->longText('archivo_base64')->nullable()->after('archivo_binario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permiso_comprobantes', function (Blueprint $table) {
            $table->dropColumn(['archivo_binario', 'archivo_base64']);
        });
    }
};
