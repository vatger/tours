<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $superadmin;
    protected User $admin;
    protected User $regularUser;
    protected Role $adminRole;
    protected Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create permissions
        $permissions = [
            ['name' => 'view.users', 'group' => 'Users', 'description' => 'View users'],
            ['name' => 'create.users', 'group' => 'Users', 'description' => 'Create users'],
            ['name' => 'edit.users', 'group' => 'Users', 'description' => 'Edit users'],
            ['name' => 'delete.users', 'group' => 'Users', 'description' => 'Delete users'],
            ['name' => 'manage.roles', 'group' => 'Roles', 'description' => 'Manage roles'],
            ['name' => 'view.dashboard', 'group' => 'Dashboard', 'description' => 'View dashboard'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
        $this->userRole = Role::create([
            'name' => 'user',
            'slug' => 'user',
            'description' => 'Regular user role',
            'is_system_role' => true,
        ]);

        $this->adminRole = Role::create([
            'name' => 'admin',
            'slug' => 'admin',
            'description' => 'Administrator role',
            'is_system_role' => true,
        ]);

        $superadminRole = Role::create([
            'name' => 'superadmin',
            'slug' => 'superadmin',
            'description' => 'Super administrator role',
            'is_system_role' => true,
        ]);

        // Assign all permissions to admin and superadmin
        $allPermissions = Permission::all();
        $this->adminRole->permissions()->sync($allPermissions->pluck('id'));
        $superadminRole->permissions()->sync($allPermissions->pluck('id'));

        // Create users
        $this->superadmin = User::factory()->create([
            'id' => 1,
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
        ]);
        $this->superadmin->roles()->attach($superadminRole);

        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $this->admin->roles()->attach($this->adminRole);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        $this->regularUser->roles()->attach($this->userRole);
    }

    /** @test */
    public function superadmin_can_view_all_users()
    {
        $response = $this->actingAs($this->superadmin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Users/Index')
                    ->has('users.data', 3)
                    ->where('users.data.0.name', 'Super Admin')
            );
    }

    /** @test */
    public function admin_can_view_users_list()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthorized_user_cannot_view_users()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.users.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function guest_cannot_view_users()
    {
        $response = $this->get(route('admin.users.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function admin_can_create_user_with_default_role()
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('flash.message', 'User created successfully.');

        $newUser = User::where('email', 'newuser@example.com')->first();
        $this->assertNotNull($newUser);
        $this->assertTrue($newUser->hasRole('user'));
    }

    /** @test */
    public function admin_can_update_user()
    {
        $updateData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.users.update', $this->regularUser), $updateData);

        $response->assertRedirect(route('admin.users.index'))
            ->assertSessionHas('flash.message', 'User updated successfully.');

        $this->regularUser->refresh();
        $this->assertEquals('Updated Name', $this->regularUser->name);
        $this->assertEquals('updated@example.com', $this->regularUser->email);
    }

    /** @test */
    public function superadmin_cannot_be_deleted()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->superadmin));

        $response->assertStatus(403)
            ->assertJson(['message' => 'Superadmin user cannot be deleted.']);

        $this->assertDatabaseHas('users', ['id' => 1]);
    }

    /** @test */
    public function user_cannot_delete_themselves()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->admin));

        $response->assertStatus(403)
            ->assertJson(['message' => 'You cannot delete yourself.']);

        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    /** @test */
    public function admin_can_delete_regular_user()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.destroy', $this->regularUser));

        $response->assertStatus(200)
            ->assertJson(['message' => 'User deleted successfully.']);

        $this->assertSoftDeleted('users', ['id' => $this->regularUser->id]);
    }

    /** @test */
    public function admin_can_assign_role_to_user()
    {
        $editorRole = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Editor role',
            'is_system_role' => false,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.assign-role', $this->regularUser), [
                'role_id' => $editorRole->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role assigned successfully.']);

        $this->assertTrue($this->regularUser->fresh()->hasRole('editor'));
    }

    /** @test */
    public function admin_can_remove_role_from_user()
    {
        $response = $this->actingAs($this->admin)
            ->delete(route('admin.users.remove-role', [$this->regularUser, $this->userRole]));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role removed successfully.']);

        $this->assertFalse($this->regularUser->fresh()->hasRole('user'));
    }

    /** @test */
    public function users_index_prevents_n_plus_one_queries()
    {
        // Create additional users
        User::factory()->count(10)->create()->each(function ($user) {
            $user->roles()->attach($this->userRole);
        });

        // Enable query log
        DB::enableQueryLog();

        $this->actingAs($this->admin)
            ->get(route('admin.users.index'));

        $queries = DB::getQueryLog();
        
        // Should have minimal queries due to eager loading
        // 1 for pagination count, 1 for users with roles, 1 for roles count, 1 for roles list
        $this->assertLessThanOrEqual(6, count($queries),
            'Too many queries executed. Check for N+1 query problems.');
    }

    /** @test */
    public function user_permission_check_uses_cache()
    {
        Cache::shouldReceive('remember')
            ->once()
            ->with(
                "user_permissions_{$this->admin->id}",
                3600,
                \Closure::class
            )
            ->andReturn(['view.users', 'create.users']);

        $hasPermission = $this->admin->hasPermission('view.users');
        
        $this->assertTrue($hasPermission);
    }

    /** @test */
    public function cache_is_invalidated_when_user_roles_change()
    {
        $editorRole = Role::create([
            'name' => 'editor',
            'slug' => 'editor',
            'description' => 'Editor role',
        ]);

        // Cache should be cleared when role is assigned
        Cache::shouldReceive('forget')
            ->once()
            ->with("user_permissions_{$this->regularUser->id}");

        $this->regularUser->assignRole($editorRole);
    }

    /** @test */
    public function user_validation_requires_all_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), []);

        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function user_email_must_be_unique()
    {
        $userData = [
            'name' => 'Duplicate User',
            'email' => $this->regularUser->email,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.users.store'), $userData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function users_can_be_searched_and_filtered()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['search' => 'Super']));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->has('users.data', 1)
                    ->where('users.data.0.name', 'Super Admin')
            );
    }

    /** @test */
    public function users_can_be_filtered_by_role()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.index', ['role' => 'admin']));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->has('users.data', 1)
                    ->where('users.data.0.email', 'admin@example.com')
            );
    }

    /** @test */
    public function user_can_be_shown_with_roles_and_permissions()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.users.show', $this->regularUser));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Users/Show')
                    ->where('user.id', $this->regularUser->id)
                    ->has('user.roles')
                    ->has('user.permissions')
            );
    }
}
