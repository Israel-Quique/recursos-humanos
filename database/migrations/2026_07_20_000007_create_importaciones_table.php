<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('importaciones', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_archivo');
            $table->string('ruta_archivo');
            $table->date('fecha_operativa')->nullable();
            $table->unsignedInteger('registros_total')->default(0);
            $table->unsignedInteger('registros_generados')->default(0);
            $table->unsignedInteger('empleados_detectados')->default(0);
            $table->string('estado')->default('procesando');
            $table->text('mensaje_error')->nullable();
            $table->json('resumen_json')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('fecha_operativa');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('importaciones');
    }
};
