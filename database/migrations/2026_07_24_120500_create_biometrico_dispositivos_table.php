<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometrico_dispositivos', function (Blueprint $table) {
            $table->id();
            $table->string('department', 120);
            $table->string('branch', 160);
            $table->string('ip', 80)->unique();
            $table->unsignedInteger('port')->default(4370);
            $table->string('connection_mode', 30)->default('TCP/IP');
            $table->string('communication_password', 80)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometrico_dispositivos');
    }
};
