<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permisos_laborales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('empleados')->cascadeOnDelete();
            $table->string('tipo');
            $table->string('estado')->default('aprobado');
            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->text('motivo')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permisos_laborales');
    }
};
