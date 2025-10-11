<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
   public function toArray(Request $request): array
    {
        // Eager loaded relation faqat bitta yozuv qaytaradi (limit(1) qo‘yilgan)
        $userRelation = $this->users->first();

        return [
            'id'         => $this->id,
            'title'      => $this->getTranslations('title'),
            'text'       => $this->getTranslations('text'),
            'link_type'  => $this->link_type,
            'link'       => $this->link,
            'image'      => $this->image,
            'is_viewed'  => $userRelation?->status === 'viewed',
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
