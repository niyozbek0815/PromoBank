<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
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
            'game_name_for_url' => "card",
            'name' => $this->resource->getTranslations('name'),
            'title' => $this->resource->getTranslations('title'),
            'about' => $this->resource->getTranslations('about'),
            'banner' => "https://www.pinterest.com/pin/854558098082722274/",
        ];
    }
}
