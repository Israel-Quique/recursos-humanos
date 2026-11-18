<?php

use App\Services\UnificacionPersonalLaPazService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            $table->foreignId('unificado_en_id')->nullable()->constrained('empleados')->nullOnDelete();
        });
        app(UnificacionPersonalLaPazService::class)->ejecutar();
    }

    public function down(): void
    {
        // Las fusiones y sus originales quedan registrados en auditoría.
        Schema::table('empleados', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unificado_en_id');
        });
    }
};
