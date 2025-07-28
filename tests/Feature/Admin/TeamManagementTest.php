<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Team;
use App\Models\TeamMember;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class TeamManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $teamOwner;
    protected User $teamMember;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Enable teams feature
        Config::set('app.is_team_active', true);

        // Create permissions
        $permissions = [
            ['name' => 'view.teams', 'group' => 'Teams', 'description' => 'View teams'],
            ['name' => 'create.teams', 'group' => 'Teams', 'description' => 'Create teams'],
            ['name' => 'edit.teams', 'group' => 'Teams', 'description' => 'Edit teams'],
            ['name' => 'delete.teams', 'group' => 'Teams', 'description' => 'Delete teams'],
            ['name' => 'manage.team_members', 'group' => 'Teams', 'description' => 'Manage team members'],
            ['name' => 'assign.team_roles', 'group' => 'Teams', 'description' => 'Assign team roles'],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }

        // Create roles
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

        $teamLeaderRole = Role::create([
            'name' => 'team-leader',
            'slug' => 'team-leader',
            'description' => 'Team leader role',
            'is_system_role' => false,
        ]);

        // Assign permissions
        $adminRole->permissions()->sync(Permission::all()->pluck('id'));

        // Create users
        $this->admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);
        $this->admin->roles()->attach($adminRole);

        $this->teamOwner = User::factory()->create([
            'name' => 'Team Owner',
            'email' => 'owner@example.com',
        ]);
        $this->teamOwner->roles()->attach($userRole);

        $this->teamMember = User::factory()->create([
            'name' => 'Team Member',
            'email' => 'member@example.com',
        ]);
        $this->teamMember->roles()->attach($userRole);

        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);
        $this->regularUser->roles()->attach($userRole);
    }

    /** @test */
    public function admin_can_view_teams_when_feature_is_active()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.teams.index'));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Teams/Index')
                    ->has('teams')
                    ->where('is_team_active', true)
            );
    }

    /** @test */
    public function teams_feature_is_disabled_when_config_is_false()
    {
        Config::set('app.is_team_active', false);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teams.index'));

        $response->assertStatus(404);
    }

    /** @test */
    public function admin_can_create_team()
    {
        $teamData = [
            'name' => 'Development Team',
            'slug' => 'development-team',
            'description' => 'Main development team',
            'owner_id' => $this->teamOwner->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teams.store'), $teamData);

        $response->assertRedirect(route('admin.teams.index'))
            ->assertSessionHas('flash.message', 'Team created successfully.');

        $team = Team::where('slug', 'development-team')->first();
        $this->assertNotNull($team);
        $this->assertEquals($this->teamOwner->id, $team->owner_id);
    }

    /** @test */
    public function team_owner_is_automatically_added_as_member()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $this->assertTrue($team->hasMember($this->teamOwner));
        $this->assertTrue($team->isOwner($this->teamOwner));
    }

    /** @test */
    public function admin_can_add_member_to_team()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teams.add-member', $team), [
                'user_id' => $this->teamMember->id,
                'role' => 'member',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Member added successfully.']);

        $this->assertTrue($team->fresh()->hasMember($this->teamMember));
    }

    /** @test */
    public function admin_can_remove_member_from_team()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $team->addMember($this->teamMember, 'member');

        $teamMemberInstance = $team->teamMembers()->where('user_id', $this->teamMember->id)->first();

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.teams.remove-member', [$team, $teamMemberInstance]));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Member removed successfully.']);

        $this->assertFalse($team->fresh()->hasMember($this->teamMember));
    }

    /** @test */
    public function team_owner_cannot_be_removed_from_team()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.teams.remove-member', [$team, $team->teamMembers()->where('user_id', $this->teamOwner->id)->first()]));

        $response->assertStatus(403)
            ->assertJson(['message' => 'Team owner cannot be removed from the team.']);
    }

    /** @test */
    public function admin_can_assign_role_to_team_member()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $team->addMember($this->teamMember, 'member');

        $teamMemberInstance = $team->teamMembers()->where('user_id', $this->teamMember->id)->first();
        $leaderRole = Role::where('slug', 'team-leader')->first();

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teams.assign-role', [$team, $teamMemberInstance]), [
                'role_id' => $leaderRole->id,
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role assigned successfully.']);

        $teamMember = $team->fresh()->teamMembers()
            ->where('user_id', $this->teamMember->id)
            ->first();

        $this->assertTrue($teamMember->roles->contains($leaderRole));
    }

    /** @test */
    public function admin_can_update_team()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $updateData = [
            'name' => 'Updated Team',
            'description' => 'Updated description',
        ];

        $response = $this->actingAs($this->admin)
            ->put(route('admin.teams.update', $team), $updateData);

        $response->assertRedirect(route('admin.teams.index'))
            ->assertSessionHas('flash.message', 'Team updated successfully.');

        $team->refresh();
        $this->assertEquals('Updated Team', $team->name);
        $this->assertEquals('Updated description', $team->description);
    }

    /** @test */
    public function admin_can_delete_team()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.teams.destroy', $team));

        $response->assertStatus(200)
            ->assertJson(['message' => 'Team deleted successfully.']);

        $this->assertSoftDeleted('teams', ['id' => $team->id]);
    }

    /** @test */
    public function team_members_are_automatically_removed_when_team_is_deleted()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $team->addMember($this->teamMember, 'member');

        $team->delete();

        $this->assertDatabaseMissing('team_members', [
            'team_id' => $team->id,
            'user_id' => $this->teamMember->id,
        ]);
    }

    /** @test */
    public function unauthorized_user_cannot_manage_teams()
    {
        $response = $this->actingAs($this->regularUser)
            ->get(route('admin.teams.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function team_validation_requires_all_fields()
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.teams.store'), []);

        $response->assertSessionHasErrors(['name', 'slug', 'owner_id']);
    }

    /** @test */
    public function team_slug_must_be_unique()
    {
        Team::create([
            'name' => 'Existing Team',
            'slug' => 'existing-team',
            'description' => 'Existing team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $teamData = [
            'name' => 'New Team',
            'slug' => 'existing-team',
            'description' => 'New team with duplicate slug',
            'owner_id' => $this->teamMember->id,
        ];

        $response = $this->actingAs($this->admin)
            ->post(route('admin.teams.store'), $teamData);

        $response->assertSessionHasErrors(['slug']);
    }

    /** @test */
    public function team_can_be_viewed_with_members()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'slug' => 'test-team',
            'description' => 'Test team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $team->addMember($this->teamMember, 'member');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teams.show', $team));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->component('Admin/Teams/Show')
                    ->where('team.id', $team->id)
                    ->has('team.members')
                    ->has('team.owner')
            );
    }

    /** @test */
    public function teams_can_be_searched()
    {
        Team::create([
            'name' => 'Development Team',
            'slug' => 'development-team',
            'description' => 'Dev team',
            'owner_id' => $this->teamOwner->id,
        ]);

        Team::create([
            'name' => 'Marketing Team',
            'slug' => 'marketing-team',
            'description' => 'Marketing team',
            'owner_id' => $this->teamMember->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.teams.index', ['search' => 'Development']));

        $response->assertStatus(200)
            ->assertInertia(fn ($page) => 
                $page->has('teams.data', 1)
                    ->where('teams.data.0.name', 'Development Team')
            );
    }

    /** @test */
    public function team_member_roles_are_scoped_to_team()
    {
        $team1 = Team::create([
            'name' => 'Team 1',
            'slug' => 'team-1',
            'description' => 'First team',
            'owner_id' => $this->teamOwner->id,
        ]);

        $team2 = Team::create([
            'name' => 'Team 2',
            'slug' => 'team-2',
            'description' => 'Second team',
            'owner_id' => $this->teamMember->id,
        ]);

        $leaderRole = Role::where('slug', 'team-leader')->first();

        $team1->addMember($this->regularUser, 'member');
        $team1Member = $team1->teamMembers()->where('user_id', $this->regularUser->id)->first();
        $team1Member->roles()->attach($leaderRole);

        // User should have team-leader role in team1 but not in team2
        $this->assertTrue($team1->memberHasRole($this->regularUser, 'team-leader'));
        $this->assertFalse($team2->memberHasRole($this->regularUser, 'team-leader'));
    }
}
