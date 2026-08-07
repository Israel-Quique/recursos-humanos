<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'ver panel',
            'importar biometria',
            'ver calendario',
            'ver reportes',
            'gestionar personal',
            'gestionar accesos',
            'ver auditoria',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $administrador = Role::findOrCreate('administrador', 'web');
        $gestor = Role::findOrCreate('gestor', 'web');

        $administrador->syncPermissions($permissions);
        $gestor->syncPermissions([
            'ver panel',
            'importar biometria',
            'ver calendario',
            'ver reportes',
            'gestionar personal',
        ]);

        $legacyRoleMap = [
            'admin' => 'administrador',
            'usuario' => 'gestor',
        ];

        foreach ($legacyRoleMap as $legacyRole => $newRole) {
            $legacyUsers = User::query()->role($legacyRole)->get();

            foreach ($legacyUsers as $user) {
                $user->syncRoles([$newRole]);
            }
        }
    }
}
