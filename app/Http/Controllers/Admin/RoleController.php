<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $query = Role::withCount('users', 'permissions');

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(15);

        return Inertia::render('Admin/Roles/Index', [
            'roles' => $roles,
            'permissions' => Permission::getGrouped(),
            'filters' => $request->only('search'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Roles/Create', [
            'permissions' => Permission::getGrouped(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles',
            'description' => 'required|string|max:500',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create($request->only('name', 'slug', 'description'));

        if ($request->has('permissions')) {
            $role->permissions()->sync($request->permissions);
        }

        return redirect()->route('admin.roles.index')
            ->with('flash.message', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role): Response
    {
        $role->load('permissions', 'users');

        return Inertia::render('Admin/Roles/Show', [
            'role' => $role,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role): Response
    {
        $role->load('permissions');

        return Inertia::render('Admin/Roles/Edit', [
            'role' => $role,
            'permissions' => Permission::getGrouped(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:roles,slug,' . $role->id,
            'description' => 'required|string|max:500',
        ]);

        $role->update($request->only('name', 'slug', 'description'));

        return redirect()->route('admin.roles.index')
            ->with('flash.message', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Check if this is a system role
        if ($role->isSystemRole()) {
            return response()->json([
                'message' => 'System roles cannot be deleted.'
            ], 403);
        }

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.'
        ]);
    }

    /**
     * Sync permissions to the role.
     */
    public function syncPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permission_groups' => 'required|array',
        ]);

        $permissionIds = collect($request->permission_groups)
            ->flatten()
            ->toArray();

        $role->syncPermissions($permissionIds);

        return response()->json([
            'message' => 'Permissions synchronized successfully.'
        ]);
    }
}
