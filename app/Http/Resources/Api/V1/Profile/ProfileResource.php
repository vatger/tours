<?php

namespace App\Http\Resources\Api\V1\Profile;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Resources\Api\V1\Acl\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ActivityLog\ActivityLogResource;
use App\Http\Resources\Api\V1\ActiveMotive\ActiveMotiveResource;
use App\Http\Resources\Api\V1\ApprovedStatusMotive\ApprovedStatusMotiveResource;


class ProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'username' => $this->username,
            'locale' => $this->locale,
            'genre' => $this->genre,
            'phone' => $this->phone,
            'group_id' => $this->group_id,
            'sub_group_id' => $this->sub_group_id,

            'send_notifications' => $this->send_notifications,
            'use_term' => $this->use_term,
            'is_active' => $this->is_active,
            'is_active_text' => $this->is_active_text,
            'approved_status' => $this->approved_status,
            'approved_status_text' => $this->approved_status_text,
            'avatar_url' => $this->avatar_url,
            'created_at' => [
                'default' => $this->created_at,
                'short' => $this->created_at ? Carbon::parse($this->created_at)->isoFormat('L') : null,
                'short_with_time' => $this->created_at ? Carbon::parse($this->created_at)->isoFormat('L LT') : null,
                'human' => $this->created_at ? Carbon::parse($this->created_at)->diffForHumans() : null,
            ],
            'updated_at' => [
                'default' => $this->updated_at,
                'short' => $this->updated_at ? Carbon::parse($this->updated_at)->isoFormat('L') : null,
                'short_with_time' => $this->updated_at ? Carbon::parse($this->updated_at)->isoFormat('L LT') : null,
                'human' => $this->updated_at ? Carbon::parse($this->updated_at)->diffForHumans() : null,
            ],
            'active_motives' => ActiveMotiveResource::collection($this->whenLoaded('activeMotives')),
            'approved_status_motives' => ApprovedStatusMotiveResource::collection($this->whenLoaded('approvedMotives')),

            'roles' => RoleResource::collection($this->whenLoaded('roles')),
            'activities' => ActivityLogResource::collection($this->whenLoaded('activities')),

            // 'tokens' => ActivityResource::collection($this->whenLoaded('tokens')),

        ];
    }
}
