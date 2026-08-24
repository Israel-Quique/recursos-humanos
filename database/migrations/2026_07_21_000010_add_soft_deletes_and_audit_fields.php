<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (! Schema::hasColumn('empleados', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (! Schema::hasColumn('empleados', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('created_by');
            }
        });

        Schema::table('registros_asistencia', function (Blueprint $table) {
            if (! Schema::hasColumn('registros_asistencia', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
            if (! Schema::hasColumn('registros_asistencia', 'updated_by')) {
                $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
            }
            if (! Schema::hasColumn('registros_asistencia', 'deleted_by')) {
                $table->unsignedBigInteger('deleted_by')->nullable()->after('updated_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            if (Schema::hasColumn('empleados', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('empleados', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
        });

        Schema::table('registros_asistencia', function (Blueprint $table) {
            if (Schema::hasColumn('registros_asistencia', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
            if (Schema::hasColumn('registros_asistencia', 'updated_by')) {
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('registros_asistencia', 'deleted_by')) {
                $table->dropColumn('deleted_by');
            }
        });
    }
};
