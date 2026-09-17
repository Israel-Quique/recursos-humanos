<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->text('foto')->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->text('foto')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->string('foto', 255)->nullable()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('foto', 255)->nullable()->change();
        });
    }
};
