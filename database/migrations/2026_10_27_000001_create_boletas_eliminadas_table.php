<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletas_eliminadas', function (Blueprint $table) {
            $table->id();
            // Sin claves foráneas: el respaldo debe sobrevivir a sus registros originales.
            $table->unsignedBigInteger('permiso_laboral_id')->unique();
            $table->unsignedBigInteger('empleado_id')->index();
            $table->unsignedBigInteger('eliminado_por')->index();
            $table->string('eliminado_por_nombre');
            $table->string('eliminado_por_email');
            $table->timestamp('eliminado_en')->index();
            $table->ipAddress('ip')->nullable();
            $table->json('datos_boleta');
            $table->json('datos_empleado')->nullable();
            $table->json('comprobantes');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletas_eliminadas');
    }
};
