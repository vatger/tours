<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = User::with(['roles' => function ($query) {
                $query->select('roles.id', 'roles.name', 'roles.slug');
            }])
            ->withCount('roles');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($role = $request->get('role')) {
            $query->whereHas('roles', function ($q) use ($role) {
                $q->where('slug', $role);
            });
        }

        $users = $query->paginate(15);

        return Inertia::render('Admin/Users/Index', [
            'users' => $users,
            'filters' => $request->only('search', 'role'),
            'roles' => Role::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Users/Create', [
            'roles' => Role::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign default 'user' role
        $userRole = Role::where('slug', 'user')->first();
        if ($userRole) {
            $user->roles()->attach($userRole);
        }

        return redirect()->route('admin.users.index')
            ->with('flash.message', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): Response
    {
        $user->load(['roles.permissions', 'ownedTeams', 'teams']);

        $userWithPermissions = $user->toArray();
        $userWithPermissions['permissions'] = $user->roles->flatMap->permissions->unique('id')->values();

        return Inertia::render('Admin/Users/Show', [
            'user' => $userWithPermissions,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): Response
    {
        $user->load('roles');

        return Inertia::render('Admin/Users/Edit', [
            'user' => $user,
            'roles' => Role::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->update($request->only('name', 'email'));

        return redirect()->route('admin.users.index')
            ->with('flash.message', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Check if this is the superadmin user (ID: 1)
        if ($user->id === 1) {
            return response()->json([
                'message' => 'Superadmin user cannot be deleted.'
            ], 403);
        }

        // Check if user is trying to delete themselves
        if ($user->id === Auth::id()) {
            return response()->json([
                'message' => 'You cannot delete yourself.'
            ], 403);
        }

        $user->delete();

        return response()->json([
            'message' => 'User deleted successfully.'
        ]);
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        $role = Role::findOrFail($request->role_id);
        $user->assignRole($role);

        return response()->json([
            'message' => 'Role assigned successfully.'
        ]);
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(User $user, Role $role)
    {
        $user->removeRole($role);

        return response()->json([
            'message' => 'Role removed successfully.'
        ]);
    }
}
