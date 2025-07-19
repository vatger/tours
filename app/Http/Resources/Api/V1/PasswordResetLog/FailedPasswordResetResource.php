<?php

namespace App\Http\Resources\Api\V1\PasswordResetLog;


use Illuminate\Http\Request;
use App\Http\Resources\Api\V1\User\UserResource;
use Illuminate\Http\Resources\Json\JsonResource;

class FailedPasswordResetResource extends JsonResource
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
            'username' => $this->username,
            'email' => $this->email,
            'device' => $this->device,
            'ip_address' => $this->ip_address,
            'user' => new UserResource($this->whenLoaded('user')),
            'attempted_at' => $this->attempted_at,
        ];
    }
}
