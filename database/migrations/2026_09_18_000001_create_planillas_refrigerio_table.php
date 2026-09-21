<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planillas_refrigerio', function (Blueprint $table) {
            $table->id();
            $table->string('periodo', 7); // YYYY-MM
            $table->string('sucursal')->nullable();
            $table->decimal('tarifa_diaria', 8, 2)->default(20.00);
            $table->json('datos'); // array of employees and calculated/edited days
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['periodo', 'sucursal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planillas_refrigerio');
    }
};
