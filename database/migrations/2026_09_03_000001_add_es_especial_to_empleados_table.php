<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'es_especial')) {
                $table->boolean('es_especial')->default(false)->after('sucursal');
                $table->index('es_especial');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'es_especial')) {
                $table->dropIndex(['es_especial']);
                $table->dropColumn('es_especial');
            }
        });
    }
};
