<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            if (! Schema::hasColumn('registros_asistencia', 'importacion_id')) {
                $table->foreignId('importacion_id')
                    ->nullable()
                    ->constrained('importaciones')
                    ->nullOnDelete()
                    ->after('empleado_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('registros_asistencia', function (Blueprint $table) {
            if (Schema::hasColumn('registros_asistencia', 'importacion_id')) {
                $table->dropConstrainedForeignId('importacion_id');
            }
        });
    }
};
