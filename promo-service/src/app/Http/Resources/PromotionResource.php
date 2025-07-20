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
            'offer'=>$this->offer,
            'banner' => $this->banner,
            'gallery' => $this->gallery,
            'participation_type' => ParticipationTypeResource::collection($this->participationTypes),
            'company' => new CompanyResource(resource: $this->company),
        ];
    }
}
