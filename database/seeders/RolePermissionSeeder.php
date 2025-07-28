<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create permissions grouped by functionality
        $permissionGroups = [
            'Dashboard' => [
                'view.dashboard' => 'View dashboard',
                'manage.dashboard' => 'Manage dashboard settings',
            ],
            'Users' => [
                'view.users' => 'View users',
                'create.users' => 'Create users',
                'edit.users' => 'Edit users',
                'delete.users' => 'Delete users',
                'assign.roles' => 'Assign roles to users',
            ],
            'Roles' => [
                'view.roles' => 'View roles',
                'create.roles' => 'Create roles',
                'edit.roles' => 'Edit roles',
                'delete.roles' => 'Delete roles',
                'manage.permissions' => 'Manage role permissions',
            ],
            'Posts' => [
                'view.posts' => 'View posts',
                'create.posts' => 'Create posts',
                'edit.posts' => 'Edit posts',
                'delete.posts' => 'Delete posts',
                'publish.posts' => 'Publish posts',
                'manage.posts' => 'Manage all posts',
            ],
            'Teams' => [
                'view.teams' => 'View teams',
                'create.teams' => 'Create teams',
                'edit.teams' => 'Edit teams',
                'delete.teams' => 'Delete teams',
                'manage.team_members' => 'Manage team members',
                'assign.team_roles' => 'Assign team roles',
            ],
        ];

        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::firstOrCreate([
                    'name' => $name,
                ], [
                    'group' => $group,
                    'description' => $description,
                ]);
            }
        }

        // Create system roles
        $roles = [
            [
                'name' => 'superadmin',
                'slug' => 'superadmin',
                'description' => 'Super administrator with all permissions',
                'is_system_role' => true,
                'permissions' => Permission::all()->pluck('name')->toArray(),
            ],
            [
                'name' => 'admin',
                'slug' => 'admin',
                'description' => 'Administrator role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                    'view.users', 'create.users', 'edit.users', 'delete.users', 'assign.roles',
                    'view.roles', 'create.roles', 'edit.roles', 'delete.roles', 'manage.permissions',
                    'view.posts', 'create.posts', 'edit.posts', 'delete.posts', 'publish.posts', 'manage.posts',
                    'view.teams', 'create.teams', 'edit.teams', 'delete.teams', 'manage.team_members', 'assign.team_roles',
                ],
            ],
            [
                'name' => 'editor',
                'slug' => 'editor',
                'description' => 'Content editor role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                    'view.posts', 'create.posts', 'edit.posts', 'publish.posts',
                ],
            ],
            [
                'name' => 'author',
                'slug' => 'author',
                'description' => 'Content author role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                    'view.posts', 'create.posts', 'edit.posts',
                ],
            ],
            [
                'name' => 'user',
                'slug' => 'user',
                'description' => 'Regular user role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                ],
            ],
            [
                'name' => 'customer',
                'slug' => 'customer',
                'description' => 'Customer role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                ],
            ],
            [
                'name' => 'deliveryboy',
                'slug' => 'deliveryboy',
                'description' => 'Delivery person role',
                'is_system_role' => true,
                'permissions' => [
                    'view.dashboard',
                ],
            ],
        ];

        foreach ($roles as $roleData) {
            $permissions = $roleData['permissions'];
            unset($roleData['permissions']);

            $role = Role::firstOrCreate([
                'slug' => $roleData['slug'],
            ], $roleData);

            // Assign permissions to role
            $permissionIds = Permission::whereIn('name', $permissions)->pluck('id');
            $role->permissions()->sync($permissionIds);
        }

        // Create superadmin user
        $superadmin = User::firstOrCreate([
            'email' => 'superadmin@example.com',
        ], [
            'id' => 1,
            'name' => 'Super Admin',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Assign superadmin role
        $superadminRole = Role::where('slug', 'superadmin')->first();
        if ($superadminRole) {
            $superadmin->roles()->syncWithoutDetaching([$superadminRole->id]);
        }
    }
}
