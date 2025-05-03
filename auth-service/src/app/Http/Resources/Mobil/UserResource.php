<?php

namespace App\Http\Resources\Mobil;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);

        $default = config('services.urls.api_getaway') . '/media/upload/user_avate/default-avatar.png';

        return [
            'id' => $this->id,
            'region' => $this->region->name,
            'district' => $this->district->name,
            'name' => $this->name,
            'phone' => $this->phone,
            'phone2' => $this->phone2 ?? null,
            'gender' => $this->gender,
            'avatar_url' => $this->getMedia('user_avatar') ?: $default,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}
