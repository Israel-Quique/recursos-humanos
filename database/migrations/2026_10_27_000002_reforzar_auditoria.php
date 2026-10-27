<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->unsignedBigInteger('actor_id_original')->nullable()->index();
            $table->string('actor_nombre')->nullable();
            $table->string('actor_email')->nullable();
        });

        DB::table('users')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                DB::table('auditorias')->where('actor_id', $user->id)->update([
                    'actor_id_original' => $user->id,
                    'actor_nombre' => $user->name,
                    'actor_email' => $user->email,
                ]);
            }
        });

        Schema::table('boletas_eliminadas', function (Blueprint $table) {
            $table->dropUnique(['permiso_laboral_id']);
            $table->index('permiso_laboral_id');
        });

        $permiso = Permission::findOrCreate('revertir auditoria', 'web');
        Role::findOrCreate('administrador', 'web')->givePermissionTo($permiso);
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down(): void
    {
        // El historial de eliminaciones repetidas se conserva incluso al revertir.
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropIndex(['actor_id_original']);
            $table->dropColumn(['actor_id_original', 'actor_nombre', 'actor_email']);
        });
        Permission::where('name', 'revertir auditoria')->where('guard_name', 'web')->delete();
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
