<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'role',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The team this member belongs to.
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * The user this team member represents.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The roles assigned to this team member.
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'team_member_roles', 'team_member_id', 'role_id')->withTimestamps();
    }

    /**
     * Assign a role to this team member.
     */
    public function assignRole(Role $role): void
    {
        $this->roles()->syncWithoutDetaching([$role->id]);
    }

    /**
     * Remove a role from this team member.
     */
    public function removeRole(Role $role): void
    {
        $this->roles()->detach($role->id);
    }

    /**
     * Check if team member has a specific role.
     */
    public function hasRole(string|Role $role): bool
    {
        if ($role instanceof Role) {
            return $this->roles()->where('roles.id', $role->id)->exists();
        }
        
        return $this->roles()->where('slug', $role)->exists();
    }
}
