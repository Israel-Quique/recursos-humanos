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
        Schema::create('reglas_sanciones', function (Blueprint $table) {
            $table->id();
            $table->string('categoria', 50)->default('atraso'); // atraso, inasistencia, omision, gravisima
            $table->string('causal', 255); // Ej: "1 a 30", "31 a 45", "121 o más por primera vez en la Gestión"
            $table->integer('rango_min')->nullable(); // Ej: 1, 31, 46, 61, 91, 121
            $table->integer('rango_max')->nullable(); // Ej: 30, 45, 60, 90, 120, null (sin límite superior)
            $table->decimal('dias_sancion', 5, 2)->default(0.00); // 0.00, 0.50, 1.00, 2.00, 3.00, 4.00
            $table->string('sancion_texto', 150); // Ej: "Sin sanción", "Medio (1/2) día", "Un (1) día", "Destitución con proceso interno"
            $table->string('unidad', 30)->default('minutos'); // minutos, dias, ocurrencias
            $table->boolean('es_destitucion')->default(false);
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->text('observaciones')->nullable();
            $table->timestamps();

            $table->index(['categoria', 'activo']);
            $table->index('orden');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reglas_sanciones');
    }
};
