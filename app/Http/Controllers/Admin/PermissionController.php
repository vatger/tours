<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class PermissionController extends Controller
{
    /**
     * Display a listing of the permissions grouped by category.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $group = $request->get('group');
        
        // Get permissions with optional filtering
        $query = Permission::query();
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%");
            });
        }
        
        if ($group) {
            $query->where('group', $group);
        }
        
        $permissions = $query->orderBy('group')->orderBy('name')->get();
        
        // Group permissions by category
        $groupedPermissions = $permissions->groupBy('group')->map(function ($groupPermissions, $groupName) {
            return [
                'name' => $groupName,
                'permissions' => $groupPermissions->toArray()
            ];
        })->values();
        
        // Get available groups for filter
        $availableGroups = Permission::distinct('group')->pluck('group')->sort()->values();
        
        return Inertia::render('Admin/Permissions/Index', [
            'grouped_permissions' => $groupedPermissions->keyBy('name'),
            'availableGroups' => $availableGroups,
            'filters' => [
                'search' => $search,
                'group' => $group,
            ],
        ]);
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load('roles.users');
        
        return Inertia::render('Admin/Permissions/Show', [
            'permission' => $permission,
            'roles' => $permission->roles->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'slug' => $role->slug,
                    'users_count' => $role->users_count ?? $role->users->count(),
                ];
            }),
        ]);
    }

    /**
     * Get permissions grouped for role assignment.
     */
    public function grouped()
    {
        $cacheKey = 'permissions_grouped';
        
        $grouped = Cache::remember($cacheKey, 3600, function () {
            return Permission::orderBy('group')->orderBy('name')->get()
                ->groupBy('group')
                ->map(function ($permissions, $group) {
                    return [
                        'name' => $group,
                        'permissions' => $permissions->map(function ($permission) {
                            return [
                                'id' => $permission->id,
                                'name' => $permission->name,
                                'slug' => $permission->slug,
                                'description' => $permission->description,
                            ];
                        })->toArray()
                    ];
                })
                ->values();
        });
        
        return response()->json(['groups' => $grouped]);
    }

    /**
     * Get all permissions for API use.
     */
    public function all()
    {
        $permissions = Permission::orderBy('group')->orderBy('name')->get([
            'id', 'name', 'slug', 'description', 'group'
        ]);
        
        return response()->json(['permissions' => $permissions]);
    }

    /**
     * Sync permissions cache.
     */
    public function syncCache()
    {
        // Clear permission-related cache
        Cache::forget('permissions_grouped');
        Cache::tags(['permissions'])->flush();
        
        // Warm up the cache
        $this->grouped();
        
        return response()->json([
            'message' => 'Permission cache synchronized successfully'
        ]);
    }
}
