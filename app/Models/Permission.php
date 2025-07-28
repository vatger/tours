<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group',
        'description',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permission')->withTimestamps();
    }

    /**
     * Scope permissions by group.
     */
    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }

    /**
     * Get permissions grouped by their group field.
     */
    public static function getGrouped(): array
    {
        return Cache::remember('grouped_permissions', 3600, function () {
            return self::all()
                ->groupBy('group')
                ->map(function ($permissions) {
                    return $permissions->map(function ($permission) {
                        return [
                            'id' => $permission->id,
                            'name' => $permission->name,
                            'description' => $permission->description,
                        ];
                    })->toArray();
                })
                ->toArray();
        });
    }

    /**
     * Clear the grouped permissions cache.
     */
    public static function clearGroupedCache(): void
    {
        Cache::forget('grouped_permissions');
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function () {
            self::clearGroupedCache();
        });

        static::deleted(function () {
            self::clearGroupedCache();
        });
    }
}