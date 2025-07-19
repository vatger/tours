<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info("==>> Iniciando Seeder de permissions e roles");
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        // Permissões para gerenciar desenvolvimento
        Permission::create(['name' => 'system.models.list']);
        Permission::create(['name' => 'system.models.create']);
        Permission::create(['name' => 'system.models.edit']);
        Permission::create(['name' => 'system.models.show']);
        Permission::create(['name' => 'system.models.delete']);
        Permission::create(['name' => 'system.models.trashed.show']);
        Permission::create(['name' => 'system.models.trashed.delete']);
        Permission::create(['name' => 'system.models.trashed.restore']);
        Permission::create(['name' => 'system.models.toggle.is.active']);
        Permission::create(['name' => 'system.models.toggle.approve.status']);
        Permission::create(['name' => 'system.models.activate']);
        Permission::create(['name' => 'system.models.deactivate']);
        $this->command->info("====>> Permissions para system adicionadas");
        // Permissões para gerenciar log activities
        Permission::create(['name' => 'activityLogs.list']);
        Permission::create(['name' => 'activityLogs.create']);
        Permission::create(['name' => 'activityLogs.edit']);
        Permission::create(['name' => 'activityLogs.show']);
        Permission::create(['name' => 'activityLogs.delete']);
        Permission::create(['name' => 'activityLogs.trashed.show']);
        Permission::create(['name' => 'activityLogs.trashed.delete']);
        Permission::create(['name' => 'activityLogs.trashed.restore']);
        Permission::create(['name' => 'activityLogs.toggle.is.active']);
        Permission::create(['name' => 'activityLogs.toggle.approve.status']);
        Permission::create(['name' => 'activityLogs.activate']);
        Permission::create(['name' => 'activityLogs.deactivate']);
        $this->command->info("====>> Permissions para activity logs adicionadas");
        // Permissões para gerenciar generos
        Permission::create(['name' => 'genders.list']);
        Permission::create(['name' => 'genders.create']);
        Permission::create(['name' => 'genders.edit']);
        Permission::create(['name' => 'genders.show']);
        Permission::create(['name' => 'genders.delete']);
        Permission::create(['name' => 'genders.trashed.show']);
        Permission::create(['name' => 'genders.trashed.delete']);
        Permission::create(['name' => 'genders.trashed.restore']);
        Permission::create(['name' => 'genders.toggle.is.active']);
        Permission::create(['name' => 'genders.toggle.approve.status']);
        Permission::create(['name' => 'genders.activate']);
        Permission::create(['name' => 'genders.deactivate']);
        $this->command->info("====>> Permissions para genders adicionadas");
        // Permissões para gerenciar usuários
        Permission::create(['name' => 'users.list']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.edit']);
        Permission::create(['name' => 'users.show']);
        Permission::create(['name' => 'users.delete']);
        Permission::create(['name' => 'users.trashed.show']);
        Permission::create(['name' => 'users.trashed.delete']);
        Permission::create(['name' => 'users.trashed.restore']);
        Permission::create(['name' => 'users.toggle.is.active']);
        Permission::create(['name' => 'users.toggle.approve.status']);
        Permission::create(['name' => 'users.activate']);
        Permission::create(['name' => 'users.deactivate']);
        $this->command->info("====>> Permissions para users adicionadas");
        $systemNames = Permission::where('name', 'like', 'system.%')
            ->pluck('name')
            ->toArray();
        $usersPermissionNames = Permission::where('name', 'like', 'users.%')
            ->pluck('name')
            ->toArray();
        $gendersPermissionNames = Permission::where('name', 'like', 'genders.%')
            ->pluck('name')
            ->toArray();
        $activityLogsNames = Permission::where('name', 'like', 'activityLogs.%')
            ->pluck('name')
            ->toArray();

        $system = Role::create(['name' => 'system-admin']);
        $system->givePermissionTo($systemNames);
        $system->givePermissionTo($activityLogsNames);
        $this->command->info("====>> Role para system-admin adicionada");
        $admin = Role::create(['name' => 'super-admin']);
        $admin->givePermissionTo($usersPermissionNames);
        $admin->givePermissionTo($gendersPermissionNames);
        $admin->givePermissionTo($activityLogsNames);
        $this->command->info("====>> Role para super-admin adicionada");
        $manager = Role::create(['name' => 'users-manager']);
        $manager->givePermissionTo([
            'genders.list',
            'genders.show',
            'users.list',
            'users.show',
            'users.toggle.is.active'
        ]);
        $manager->givePermissionTo($activityLogsNames);
        $this->command->info("====>> Role para users-manager adicionada");

        $guest = Role::create(['name' => 'guests']);
        $guest->givePermissionTo([
            'users.list',
            'users.show',
        ]);
        $this->command->info("====>> Role para guests adicionada");
        $this->command->info("==>> Encerrado Seeder de permissions e roles");
    }
}
