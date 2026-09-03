<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permiso_comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('permiso_laboral_id')->constrained('permisos_laborales')->cascadeOnDelete();
            $table->string('ruta_archivo');
            $table->string('nombre_original');
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('tamano_bytes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permiso_comprobantes');
    }
};
