<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create permission groups
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
            ],
            'Roles' => [
                'view.roles' => 'View roles',
                'create.roles' => 'Create roles',
                'edit.roles' => 'Edit roles',
                'delete.roles' => 'Delete roles',
                'assign.roles' => 'Assign roles',
            ],
            'Posts' => [
                'view.posts' => 'View posts',
                'create.posts' => 'Create posts',
                'edit.posts' => 'Edit posts',
                'delete.posts' => 'Delete posts',
                'publish.posts' => 'Publish posts',
            ],
            'Teams' => [
                'view.teams' => 'View teams',
                'create.teams' => 'Create teams',
                'edit.teams' => 'Edit teams',
                'delete.teams' => 'Delete teams',
                'manage.team_members' => 'Manage team members',
            ],
        ];

        foreach ($permissionGroups as $group => $permissions) {
            foreach ($permissions as $name => $description) {
                Permission::create([
                    'name' => $name,
                    'group' => $group,
                    'description' => $description,
                ]);
            }
        }

        // Create system roles
        $adminRole = Role::create([
            'name' => 'admin',
            'slug' => 'admin',
            'description' => 'Administrator role',
            'is_system_role' => true,
        ]);

        $userRole = Role::create([
            'name' => 'user',
            'slug' => 'user',
            'description' => 'Regular user role',
            'is_system_role' => true,
        ]);

        // Assign all permissions to admin
        $allPermissions = Permission::all();
        $adminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create users
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        $this->regularUser->roles()->attach($userRole);
    }

    /** @test */
    public function admin_can_view_roles_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Roles/Index')
                    ->has('roles.data', 2)
                    ->has('permissions')
            );
    }

    /** @test */
    public function admin_can_create_role_with_permissions()
    {
        $permissions = Permission::where('group', 'Posts')->pluck('id')->toArray();
        
        $roleData = [
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Content editor role',
            'permissions' => $permissions,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), $roleData);

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('flash.message', 'Role created successfully.');

        $role = Role::where('slug', 'editor')->first();
        $this->assertNotNull($role);
        $this->assertCount(count($permissions), $role->permissions);
    }

    /** @test */
    public function admin_can_update_role()
    {
        $role = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Editor role',
            'is_system_role' => false,
        ]);

        $updateData = [
            'name' => 'content-editor',
            'slug' => 'content-editor',
            'description' => 'Updated content editor role',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.roles.update', $role), $updateData);

        $response->assertRedirect(route('admin.roles.index'))
            ->assertSessionHas('flash.message', 'Role updated successfully.');

        $role->refresh();
        $this->assertEquals('content-editor', $role->name);
    }

    /** @test */
    public function system_roles_cannot_be_deleted()
    {
        $systemRole = Role::where('is_system_role', true)->first();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $systemRole));

        $response->assertStatus(403)
            ->assertJson(['message' => 'System roles cannot be deleted.']);

        $this->assertDatabaseHas('roles', ['id' => $systemRole->id]);
    }

    /** @test */
    public function non_system_roles_can_be_deleted()
    {
        $customRole = Role::create([
            'name' => 'custom',
            'slug' => 'custom',
            'description' => 'Custom role',
            'is_system_role' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.roles.destroy', $customRole));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role deleted successfully.']);

        $this->assertSoftDeleted('roles', ['id' => $customRole->id]);
    }

    /** @test */
    public function admin_can_view_permissions_grouped()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.permissions.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Permissions/Index')
                    ->has('grouped_permissions.Dashboard')
                    ->has('grouped_permissions.Users')
                    ->has('grouped_permissions.Roles')
                    ->has('grouped_permissions.Posts')
                    ->has('grouped_permissions.Teams')
            );
    }

    /** @test */
    public function admin_can_bulk_assign_permissions_to_role()
    {
        $role = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Editor role',
        ]);

        $permissionGroups = [
            'Posts' => Permission::where('group', 'Posts')->pluck('id')->toArray(),
            'Dashboard' => Permission::where('group', 'Dashboard')->pluck('id')->toArray(),
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.roles.sync-permissions', $role), [
                'permission_groups' => $permissionGroups,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Permissions synchronized successfully.']);

        $expectedPermissions = array_merge(
            $permissionGroups['Posts'],
            $permissionGroups['Dashboard']
        );

        $this->assertCount(count($expectedPermissions), $role->fresh()->permissions);
    }

    /** @test */
    public function permissions_can_be_selected_by_group()
    {
        $dashboardPermissions = Permission::where('group', 'Dashboard')->get();
        
        $this->assertCount(2, $dashboardPermissions);
        $this->assertTrue($dashboardPermissions->contains('name', 'view.dashboard'));
        $this->assertTrue($dashboardPermissions->contains('name', 'manage.dashboard'));
    }

    /** @test */
    public function role_permission_sync_clears_cache()
    {
        $role = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Editor role',
        ]);

        $user = User::factory()->create();
        $user->roles()->attach($role);

        // Mock cache clearing
        Cache::shouldReceive('forget')
            ->once()
            ->with("user_permissions_{$user->id}");

        $permissions = Permission::where('group', 'Posts')->pluck('id')->toArray();
        $role->syncPermissions($permissions);
    }

    /** @test */
    public function unauthorized_user_cannot_manage_roles()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.roles.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function role_validation_requires_all_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), []);

        $response->assertSessionHasErrors(['name', 'slug', 'description']);
    }

    /** @test */
    public function role_slug_must_be_unique()
    {
        $existingRole = Role::first();

        $roleData = [
            'name' => 'new-role',
            'slug' => $existingRole->slug,
            'description' => 'New role with duplicate slug',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.roles.store'), $roleData);

        $response->assertSessionHasErrors(['slug']);
    }

    /** @test */
    public function permissions_are_cached_for_performance()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with('grouped_permissions', 3600, \Closure::class)
            ->andReturn([
                'Dashboard' => [
                    ['id' => 1, 'name' => 'view.dashboard', 'description' => 'View dashboard'],
                ],
            ]);

        $groupedPermissions = Permission::getGrouped();
        
        $this->assertArrayHasKey('Dashboard', $groupedPermissions);
    }

    /** @test */
    public function role_can_check_if_it_has_specific_permission()
    {
        $permission = Permission::first();
        
        // Create a new role without any permissions
        $role = Role::create([
            'name' => 'Test Role',
            'slug' => 'test-role',
            'description' => 'Test role for permission check',
            'is_system_role' => false,
        ]);

        // Initially, role should not have the permission
        $this->assertFalse($role->hasPermission($permission->name));
        
        // Attach the permission
        $role->permissions()->attach($permission);

        $this->assertTrue($role->hasPermission($permission->name));
        $this->assertFalse($role->hasPermission('non.existent.permission'));
    }

    /** @test */
    public function role_permissions_can_be_viewed()
    {
        $role = Role::first();

        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.show', $role));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Roles/Show')
                    ->where('role.id', $role->id)
                    ->has('role.permissions')
            );
    }

    /** @test */
    public function roles_can_be_searched()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.roles.index', ['search' => 'admin']));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->has('roles.data', 1)
                    ->where('roles.data.0.name', 'admin')
            );
    }

    /** @test */
    public function permission_groups_are_properly_structured()
    {
        $groupedPermissions = Permission::select('group')
            ->distinct()
            ->pluck('group')
            ->toArray();

        $expectedGroups = ['Dashboard', 'Users', 'Roles', 'Posts', 'Teams'];
        
        foreach ($expectedGroups as $group) {
            $this->assertContains($group, $groupedPermissions);
        }
    }
}
