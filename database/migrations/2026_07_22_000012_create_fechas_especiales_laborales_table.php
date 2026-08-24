<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fechas_especiales_laborales', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fechas_especiales_laborales');
    }
};
