<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionResource extends JsonResource
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
            'name' => $this->getTranslations('name'),
            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'banner' => $this->getMedia('promo_banner'),
            'video' => $this->getMedia('promo_video'),
            'participation_type' => ParticipationTypeResource::collection($this->participationTypes),
            'company' => new CompanyResource(resource: $this->company),

        ];
    }
}
