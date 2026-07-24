<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('empleados', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('apellido');
            $table->string('area');
            $table->string('sucursal');
            $table->time('hora_entrada_programada');
            $table->time('hora_salida_programada')->nullable();
            $table->date('fecha_contratacion');
            $table->date('fecha_despido')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['sucursal', 'area']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
