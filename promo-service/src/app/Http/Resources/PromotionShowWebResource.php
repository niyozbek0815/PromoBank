<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionShowWebResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $this->additional['lang'] ?? $request->get('lang', 'uz');

        $defaultimage = collect([
            ['url' => 'https://promobank.io/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
        ]);
        $defaultMedia = collect([
            ['url' => 'https://promobank.io/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://promobank.io/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://promobank.io/namuna/video1.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://promobank.io/namuna/video2.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://promobank.io/namuna/video3.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://promobank.io/namuna/video4.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://promobank.io/namuna/video5.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://promobank.io/namuna/video6.mp4', 'mime_type' => 'video/mp4'],
        ]);


        return [
            'id'                 => $this->id,
            'name' =>           $this->getTranslation('name', $lang),
            'title'              => $this->getTranslation('title',$lang),
            'description'        => $this->getTranslation('description', $lang),
            'start_date'         => $this->start_date,
            'end_date'           => $this->end_date,

            'offer'              => is_array($this->offer) && isset($this->offer['url']) ? $this->offer['url'] : 'https://promobank.io/namuna/php.docx',
            'banner'             => is_array($this->banner) && isset($this->banner['url']) ? $this->banner['url'] : $defaultimage->random()['url'],
            'gallery'            => ! empty($this->gallery) && count($this->gallery) >= 1
                ? $this->gallery
                : $defaultMedia->shuffle()->take(4)->values()->all(),
            'participation_type' => ParticipationTypeResource::collection($this->participationTypes),
            'company'            => new CompanyResource(resource: $this->company),
            'platforms' => $this->platforms->map(function ($platform) {
                return [
                    'name'  => $platform->name,
                    'phone' => $platform->pivot->phone,
                ];
            }),
         ];
    }
}
