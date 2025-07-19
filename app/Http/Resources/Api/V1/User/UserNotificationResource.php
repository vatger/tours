<?php

namespace App\Http\Resources\Api\V1\User;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\ActivityLog\ActivityLogResource;
use App\Http\Resources\Api\V1\ActiveMotive\ActiveMotiveResource;
use App\Http\Resources\Api\V1\ApprovedStatusMotive\ApprovedStatusMotiveResource;
use App\Http\Resources\Api\V1\ProfileType\ProfileTypeResource;
use App\Http\Resources\Api\V1\UserLogin\UserLoginResource;

/**
 * @version V1
 */
class UserNotificationResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'type' => $this->type,
            'notifiable_type' => $this->notifiable_type,
            'notifiable_id' => $this->notifiable_id,
            'data' => $this->data,
            'read_at' => [
                'default' => $this->read_at,
                'short' => $this->read_at ? Carbon::parse($this->read_at)->isoFormat('L') : null,
                'short_with_time' => $this->read_at ? Carbon::parse($this->read_at)->isoFormat('L LT') : null,
                'human' => $this->read_at ? Carbon::parse($this->read_at)->diffForHumans() : null,
            ],
            //            'read_at' => $this->read_at?->toDateTimeString(),
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
            // Relacionamentos (se necessário)
            'notifiable' => $this->whenLoaded('notifiable'),

            // Links
            // 'links' => [
            //     'self' => route('api.notifications.show', $this->id),
            //     'mark_as_read' => route('api.notifications.mark-as-read', $this->id),
            // ],
        ];
    }
}
