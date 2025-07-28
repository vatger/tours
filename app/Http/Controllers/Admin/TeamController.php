<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 15);
        
        $query = Team::query()
            ->withCount(['teamMembers', 'teamMembers as active_members_count' => function ($query) {
                $query->whereNull('deleted_at');
            }])
            ->with(['owner:id,name,email']);
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('owner', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                               ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }
        
        $teams = $query->orderBy('created_at', 'desc')->paginate($perPage);
        
        return Inertia::render('Admin/Teams/Index', [
            'teams' => $teams,
            'filters' => [
                'search' => $search,
                'per_page' => $perPage,
            ],
            'is_team_active' => config('app.is_team_active', true),
        ]);
    }

    /**
     * Show the form for creating a new team.
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
            
        return Inertia::render('Admin/Teams/Create', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created team in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'unique:teams,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'owner_id' => ['required', 'exists:users,id'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        $team = DB::transaction(function () use ($validated) {
            $team = Team::create($validated);
            
            // Owner is automatically added as team member via model boot method
            
            return $team;
        });

        return redirect()->route('admin.teams.index')
            ->with('flash.message', 'Team created successfully.');
    }

    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        $team->load([
            'owner',
            'teamMembers.user',
            'teamMembers.roles'
        ]);

        return Inertia::render('Admin/Teams/Show', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'slug' => $team->slug,
                'description' => $team->description,
                'created_at' => $team->created_at,
                'updated_at' => $team->updated_at,
                'owner' => $team->owner,
                'members' => $team->teamMembers->map(function ($member) {
                    return [
                        'id' => $member->id,
                        'user' => $member->user,
                        'roles' => $member->roles,
                        'joined_at' => $member->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified team.
     */
    public function edit(Team $team)
    {
        $users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
            
        return Inertia::render('Admin/Teams/Edit', [
            'team' => $team,
            'users' => $users,
        ]);
    }

    /**
     * Update the specified team in storage.
     */
    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('teams', 'slug')->ignore($team->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'owner_id' => ['sometimes', 'exists:users,id'],
        ]);

        // Keep existing values if not provided
        if (!isset($validated['slug'])) {
            $validated['slug'] = $team->slug;
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }
        
        if (!isset($validated['owner_id'])) {
            $validated['owner_id'] = $team->owner_id;
        }

        DB::transaction(function () use ($team, $validated) {
            // If owner is changing, update team member records
            if ($team->owner_id != $validated['owner_id']) {
                // Remove owner role from current owner
                $team->teamMembers()->where('user_id', $team->owner_id)->update(['role' => 'member']);
                
                // Add new owner as member if not already a member
                $existingMember = $team->teamMembers()->where('user_id', $validated['owner_id'])->first();
                if (!$existingMember) {
                    $team->teamMembers()->create([
                        'user_id' => $validated['owner_id'],
                        'role' => 'owner',
                    ]);
                } else {
                    $existingMember->update(['role' => 'owner']);
                }
            }
            
            $team->update($validated);
        });

        return redirect()->route('admin.teams.index')
            ->with('flash.message', 'Team updated successfully.');
    }

    /**
     * Remove the specified team from storage.
     */
    public function destroy(Team $team)
    {
        DB::transaction(function () use ($team) {
            // Soft delete team members first
            $team->teamMembers()->delete();
            
            // Delete the team
            $team->delete();
        });

        return response()->json(['message' => 'Team deleted successfully.']);
    }

    /**
     * Add a member to the team.
     */
    public function addMember(Request $request, Team $team)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role' => ['required', 'string', 'in:member,admin'],
        ]);

        // Check if user is already a member
        $existingMember = $team->teamMembers()->where('user_id', $validated['user_id'])->first();
        if ($existingMember) {
            return response()->json(['message' => 'User is already a member of this team.'], 422);
        }

        $team->teamMembers()->create([
            'user_id' => $validated['user_id'],
            'role' => $validated['role'],
        ]);

        return response()->json(['message' => 'Member added successfully.']);
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(Team $team, TeamMember $member)
    {
        // Prevent removing the team owner
        if ($member->user_id === $team->owner_id) {
            return response()->json(['message' => 'Team owner cannot be removed from the team.'], 403);
        }

        $member->delete();

        return response()->json(['message' => 'Member removed successfully.']);
    }

    /**
     * Assign a role to a team member.
     */
    public function assignRole(Request $request, Team $team, TeamMember $member)
    {
        $validated = $request->validate([
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $role = Role::findOrFail($validated['role_id']);

        // Check if role assignment already exists
        if ($member->hasRole($role)) {
            return response()->json(['message' => 'Role already assigned to this team member.'], 422);
        }

        // Use the TeamMember model method for clean role assignment
        $member->assignRole($role);

        return response()->json(['message' => 'Role assigned successfully.']);
    }

    /**
     * Remove a role from a team member.
     */
    public function removeRole(Team $team, TeamMember $member, $roleId)
    {
        $role = Role::findOrFail($roleId);
        
        // Use the TeamMember model method for clean role removal
        $member->removeRole($role);

        return response()->json(['message' => 'Role removed successfully.']);
    }
}
