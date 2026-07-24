<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['importaciones', 'marcaciones', 'asistencias', 'asistencias_procesadas', 'empleados'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $t) use ($table) {
                    if (! Schema::hasColumn($table, 'created_by')) {
                        $t->unsignedBigInteger('created_by')->nullable()->after('id');
                        $t->index('created_by');
                    }
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['importaciones', 'marcaciones', 'asistencias', 'asistencias_procesadas', 'empleados'];
        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'created_by')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropIndex(['created_by']);
                    $t->dropColumn('created_by');
                });
            }
        }
    }
};
