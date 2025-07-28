<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_system_role',
        'metadata',
    ];

    protected $casts = [
        'is_system_role' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role')->withTimestamps();
    }

    /**
     * The permissions that belong to the role.
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permission')->withTimestamps();
    }

    /**
     * Check if role has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->permissions()->where('name', $permission)->exists();
    }

    /**
     * Sync permissions to the role.
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
        $this->clearUserCaches();
    }

    /**
     * Check if this is a system role.
     */
    public function isSystemRole(): bool
    {
        return $this->is_system_role;
    }

    /**
     * Clear cache for all users with this role.
     */
    protected function clearUserCaches(): void
    {
        $this->users()->each(function ($user) {
            Cache::forget("user_permissions_{$user->id}");
        });
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($role) {
            $role->clearUserCaches();
        });

        static::deleted(function ($role) {
            $role->clearUserCaches();
        });
    }
}
