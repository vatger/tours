<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'owner_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The owner of the team.
     */
    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * The members of the team.
     */
    public function members()
    {
        return $this->belongsToMany(User::class, 'team_members')
                    ->withPivot('role', 'metadata')
                    ->withTimestamps();
    }

    /**
     * The team member pivot records with roles.
     */
    public function teamMembers()
    {
        return $this->hasMany(TeamMember::class);
    }

    /**
     * Check if user is a member of the team.
     */
    public function hasMember(User $user): bool
    {
        return $this->teamMembers()->where('user_id', $user->id)->exists();
    }

    /**
     * Check if user is the owner of the team.
     */
    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /**
     * Add a member to the team.
     */
    public function addMember(User $user, string $role = 'member'): void
    {
        if (!$this->hasMember($user)) {
            $this->teamMembers()->create([
                'user_id' => $user->id,
                'role' => $role,
            ]);
        }
    }

    /**
     * Remove a member from the team.
     */
    public function removeMember(User $user): void
    {
        $this->teamMembers()->where('user_id', $user->id)->delete();
    }

    /**
     * Check if a member has a specific role in this team.
     */
    public function memberHasRole(User $user, string $role): bool
    {
        $teamMember = $this->teamMembers()
            ->where('user_id', $user->id)
            ->first();

        if (!$teamMember) {
            return false;
        }

        return $teamMember->roles()->where('slug', $role)->exists();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($team) {
            // Automatically add owner as a member
            $team->addMember($team->owner, 'owner');
        });

        static::deleting(function ($team) {
            // Remove all team members when team is deleted
            $team->teamMembers()->delete();
        });
    }
}