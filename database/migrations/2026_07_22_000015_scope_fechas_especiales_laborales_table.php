<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fechas_especiales_laborales_tmp', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('sucursal', 120)->default('TODAS');
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['fecha', 'sucursal']);
        });

        DB::table('fechas_especiales_laborales')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('fechas_especiales_laborales_tmp')->insert([
                    'id' => $row->id,
                    'fecha' => $row->fecha,
                    'sucursal' => 'TODAS',
                    'nombre' => $row->nombre,
                    'descripcion' => $row->descripcion,
                    'tipo' => $row->tipo,
                    'hora_entrada' => $row->hora_entrada,
                    'hora_salida' => $row->hora_salida,
                    'created_by' => $row->created_by,
                    'updated_by' => $row->updated_by,
                    'deleted_by' => $row->deleted_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'deleted_at' => $row->deleted_at,
                ]);
            });

        Schema::drop('fechas_especiales_laborales');
        Schema::rename('fechas_especiales_laborales_tmp', 'fechas_especiales_laborales');
    }

    public function down(): void
    {
        Schema::create('fechas_especiales_laborales_tmp', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->unique();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();
            $table->string('tipo', 30);
            $table->time('hora_entrada')->nullable();
            $table->time('hora_salida')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('fechas_especiales_laborales')
            ->where('sucursal', 'TODAS')
            ->orderBy('id')
            ->get()
            ->each(function ($row) {
                DB::table('fechas_especiales_laborales_tmp')->insert([
                    'id' => $row->id,
                    'fecha' => $row->fecha,
                    'nombre' => $row->nombre,
                    'descripcion' => $row->descripcion,
                    'tipo' => $row->tipo,
                    'hora_entrada' => $row->hora_entrada,
                    'hora_salida' => $row->hora_salida,
                    'created_by' => $row->created_by,
                    'updated_by' => $row->updated_by,
                    'deleted_by' => $row->deleted_by,
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                    'deleted_at' => $row->deleted_at,
                ]);
            });

        Schema::drop('fechas_especiales_laborales');
        Schema::rename('fechas_especiales_laborales_tmp', 'fechas_especiales_laborales');
    }
};
