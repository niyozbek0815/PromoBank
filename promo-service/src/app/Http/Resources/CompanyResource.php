<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->getTranslations('name'),
            "title" => $this->getTranslations('title'),
            // 'description' => $this->getTranslations('description'),
            'region' => $this->region,
            'address' => $this->address,
            'logo' => $this->logo ??             ['url' => 'https://promobank.io/namuna/logo.jpg', 'mime_type' => 'image/jpeg'],
            'social_media' => CompanyLinkResource::collection($this->socialMedia)
        ];
    }
}
