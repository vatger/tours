<?php

namespace App\Http\Resources\Api\V1\User;

use Carbon\Carbon;
use App\Http\Resources\Api\V1\Acl\RoleResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ActivityLog\ActivityLogResource;
use App\Http\Resources\Api\V1\ApprovedStatusMotive\ApprovedStatusMotiveResource;

/**
 * @version V1
 */
class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'username' => $this->username,
            'pin_code' => $this->pin_code,
            'locale' => $this->locale,
            'nickname' => $this->nickname,
            'is_active' => $this->is_active,
            'is_active_text' => $this->is_active_text,
            'approved_status' => $this->approved_status,
            'approved_status_text' => $this->approved_status_text,
            'email_verified_at' => [
                'default' => $this->email_verified_at,
                'short' => $this->email_verified_at ? Carbon::parse($this->email_verified_at)->isoFormat('L') : null,
                'short_with_time' => $this->email_verified_at ? Carbon::parse($this->email_verified_at)->isoFormat('L LT') : null,
                'human' => $this->email_verified_at ? Carbon::parse($this->email_verified_at)->diffForHumans() : null,
            ],
            'avatar' => $this->avatar,
            'avatar_url' => $this->avatar_url,
            'original_file_name' => $this->original_file_name,
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
            'deleted_at' => [
                'default' => $this->deleted_at,
                'short' => $this->deleted_at ? Carbon::parse($this->deleted_at)->isoFormat('L') : null,
                'short_with_time' => $this->deleted_at ? Carbon::parse($this->deleted_at)->isoFormat('L LT') : null,
                'human' => $this->deleted_at ? Carbon::parse($this->deleted_at)->diffForHumans() : null,
            ],
            'registeredBy' => new UserResource($this->whenLoaded('registeredBy')),

            'activities' => ActivityLogResource::collection($this->whenLoaded('activities')),
            'actions' => ActivityLogResource::collection($this->whenLoaded('actions')),
            'approvedMotives' => ApprovedStatusMotiveResource::collection($this->whenLoaded('approvedMotives')),
            'roles' => RoleResource::collection($this->whenLoaded('roles')),
        ];
    }
}
