<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boleta_codigos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->unique()->constrained('empleados')->cascadeOnDelete();
            $table->string('session_hash', 64);
            $table->string('email');
            $table->string('codigo_hash');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->timestamp('expires_at');
            $table->timestamp('sent_at');
            $table->timestamp('used_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boleta_codigos');
    }
};
