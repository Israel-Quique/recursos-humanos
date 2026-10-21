<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->nullable()->unique();
            $table->boolean('acceso_personal')->default(false);
            $table->boolean('must_change_password')->default(false);
            $table->string('email')->nullable()->change();
        });
        Schema::create('acceso_personal_reglas', function (Blueprint $table) {
            $table->id();
            $table->string('tipo');
            $table->string('referencia');
            $table->boolean('habilitado');
            $table->unique(['tipo', 'referencia']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acceso_personal_reglas');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'acceso_personal', 'must_change_password']);
        });
    }
};
