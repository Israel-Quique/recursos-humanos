<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('biometrico_dispositivos', function (Blueprint $table) {
            if (! Schema::hasColumn('biometrico_dispositivos', 'mac')) {
                $table->string('mac', 17)->nullable()->after('ip');
            }
        });
    }

    public function down(): void
    {
        Schema::table('biometrico_dispositivos', function (Blueprint $table) {
            if (Schema::hasColumn('biometrico_dispositivos', 'mac')) {
                $table->dropColumn('mac');
            }
        });
    }
};
