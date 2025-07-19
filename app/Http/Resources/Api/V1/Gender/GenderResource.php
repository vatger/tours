<?php

namespace App\Http\Resources\Api\V1\Gender;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;
use App\Http\Resources\Api\V1\User\UserResource; 
use App\Http\Resources\Api\V1\ActiveMotive\ActiveMotiveResource; 
use App\Http\Resources\Api\V1\ActivityLog\ActivityLogResource; 


/**
 * @version V1
 */
class GenderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_translated' => $this->name_translated,
            'icon' => $this->icon,
            'icon_url' => $this->icon_url,
            'icon_file_name' => $this->icon_file_name,
            'icon_file_extension' => $this->icon_file_extension,
            'icon_file_size' => $this->icon_file_size,
            'original_locale' => $this->original_locale,
            'is_active' => $this->is_active,
            'is_active_text' => $this->is_active_text,
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
            'users' => UserResource::collection($this->whenLoaded('users')),
            'active_motives' => ActiveMotiveResource::collection($this->whenLoaded('activeMotives')),
            'activities' => ActivityLogResource::collection($this->whenLoaded('activities'))
        ];
    }
}