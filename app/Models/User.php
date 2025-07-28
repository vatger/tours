<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_role')->withTimestamps();
    }

    /**
     * Get all permissions for the user through roles.
     */
    public function permissions()
    {
        return Permission::whereIn('id', function ($query) {
            $query->select('permission_id')
                ->from('role_permission')
                ->whereIn('role_id', $this->roles()->pluck('id'));
        });
    }

    /**
     * The teams owned by the user.
     */
    public function ownedTeams()
    {
        return $this->hasMany(Team::class, 'owner_id');
    }

    /**
     * The teams that the user belongs to.
     */
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_members')->withTimestamps();
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        $permissions = Cache::remember("user_permissions_{$this->id}", 3600, function () {
            return $this->roles()->with('permissions')->get()
                ->pluck('permissions')
                ->flatten()
                ->pluck('name')
                ->toArray();
        });
        
        return in_array($permission, $permissions);
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return $this->roles()->where('slug', $role)->exists();
    }

    /**
     * Assign a role to the user.
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
        $this->clearPermissionCache();
    }

    /**
     * Remove a role from the user.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
        $this->clearPermissionCache();
    }

    /**
     * Check if the user can be deleted.
     */
    public function canBeDeleted(): bool
    {
        // Superadmin (ID: 1) cannot be deleted
        return $this->id !== 1;
    }

    /**
     * Clear the user's permission cache.
     */
    protected function clearPermissionCache(): void
    {
        Cache::forget("user_permissions_{$this->id}");
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($user) {
            $user->clearPermissionCache();
        });

        static::deleted(function ($user) {
            $user->clearPermissionCache();
        });
    }
}
