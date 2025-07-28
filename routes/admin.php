<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\TeamController;
use Illuminate\Support\Facades\Route;

// Admin routes with authentication and permission middleware
Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified'])->group(function () {
    
    // User Management Routes
    Route::middleware(['permission:view.users'])->group(function () {
        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
    });
    
    Route::middleware(['permission:create.users'])->group(function () {
        Route::get('users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
    });
    
    Route::middleware(['permission:edit.users'])->group(function () {
        Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::post('users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
        Route::delete('users/{user}/remove-role/{role}', [UserController::class, 'removeRole'])->name('users.remove-role');
    });
    
    Route::middleware(['permission:delete.users'])->group(function () {
        Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    });
    
    // Role Management Routes
    Route::middleware(['permission:view.roles'])->group(function () {
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::get('roles/{role}', [RoleController::class, 'show'])->name('roles.show');
    });
    
    Route::middleware(['permission:create.roles'])->group(function () {
        Route::get('roles/create', [RoleController::class, 'create'])->name('roles.create');
        Route::post('roles', [RoleController::class, 'store'])->name('roles.store');
    });
    
    Route::middleware(['permission:edit.roles'])->group(function () {
        Route::get('roles/{role}/edit', [RoleController::class, 'edit'])->name('roles.edit');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('roles/{role}/sync-permissions', [RoleController::class, 'syncPermissions'])->name('roles.sync-permissions');
    });
    
    Route::middleware(['permission:delete.roles'])->group(function () {
        Route::delete('roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');
    });
    
    // Permission Management Routes
    Route::middleware(['permission:view.roles'])->group(function () {
        Route::get('permissions', [PermissionController::class, 'index'])->name('permissions.index');
        Route::get('permissions/{permission}', [PermissionController::class, 'show'])->name('permissions.show');
    });
    
    // Team Management Routes (conditional based on feature flag)
    Route::middleware('teams.enabled')->group(function () {
        Route::middleware(['permission:view.teams'])->group(function () {
            Route::get('teams', [TeamController::class, 'index'])->name('teams.index');
            Route::get('teams/{team}', [TeamController::class, 'show'])->name('teams.show');
        });
        
        Route::middleware(['permission:create.teams'])->group(function () {
            Route::get('teams/create', [TeamController::class, 'create'])->name('teams.create');
            Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
        });
        
        Route::middleware(['permission:edit.teams'])->group(function () {
            Route::get('teams/{team}/edit', [TeamController::class, 'edit'])->name('teams.edit');
            Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');
        });
        
        Route::middleware(['permission:manage.team_members'])->group(function () {
            Route::post('teams/{team}/add-member', [TeamController::class, 'addMember'])->name('teams.add-member');
            Route::delete('teams/{team}/remove-member/{member}', [TeamController::class, 'removeMember'])->name('teams.remove-member');
        });
        
        Route::middleware(['permission:assign.team_roles'])->group(function () {
            Route::post('teams/{team}/assign-role/{member}', [TeamController::class, 'assignRole'])->name('teams.assign-role');
        });
        
        Route::middleware(['permission:delete.teams'])->group(function () {
            Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');
        });
    });
});
