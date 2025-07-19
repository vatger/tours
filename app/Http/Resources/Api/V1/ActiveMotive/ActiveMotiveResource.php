<?php

namespace App\Http\Resources\Api\V1\ActiveMotive;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\LogSubjectType;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveMotiveResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subjectTypeAlias = LogSubjectType::where('type', $this->subject_type)->value('alias');
        $subject = $this->subject; // Acesso ao sujeito através do método existente
        $subjectName = $subject ? ($subject->name ?? $subject->title ?? 'Unknown') : 'Unknown';

        return [
            'id' => $this->id,
            'is_active' => $this->is_active,
            'is_active_text' => $this->is_active_text,
            'subject_type' => $this->subject_type,
            'subject_id' => $this->subject_id,
            'subject_name' => $subjectName,

            'causer_type' => $this->causer_type,
            'causer_id' => $this->causer_id,
            'causer_name' => optional($this->causer)->name, // Nome do causer se disponível

            'motive' => $this->motive,
            'created_at' => [
                'default' => $this->created_at,
                'short' => $this->created_at ? Carbon::parse($this->created_at)->isoFormat('L') : null,
                'short_with_time' => $this->created_at ? Carbon::parse($this->created_at)->isoFormat('L LT') : null,
                'human' => $this->created_at ? Carbon::parse($this->created_at)->diffForHumans() : null,
            ],
            // 'updated_at' => [
            //     'default' => $this->updated_at,
            //     'short' => $this->updated_at ? Carbon::parse($this->updated_at)->isoFormat('L') : null,
            //     'short_with_time' => $this->updated_at ? Carbon::parse($this->updated_at)->isoFormat('L LT') : null,
            //     'human' => $this->updated_at ? Carbon::parse($this->updated_at)->diffForHumans() : null,
            // ],
        ];
    }
}
