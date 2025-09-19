<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoWebResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $this->additional['lang'] ?? 'uz';
        $defaultimage = collect([
            ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
        ]);
        $defaultMedia = collect([
            ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/video1.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video2.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video3.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video4.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video5.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video6.mp4', 'mime_type' => 'video/mp4'],
        ]);

        return [
            'id'                 => $this->id,
            'name' => $this->getTranslation('name', $lang),
            'banner'             => is_array($this->banner) && isset($this->banner['url']) ? $this->banner['url'] : $defaultimage->random()['url'],
        ];
    }
}
