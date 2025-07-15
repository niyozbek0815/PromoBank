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

        $default = null;

        return [
            'id'         => $this->id,
            'region'     => [
                'id'   => $this->region->id,
                'name' => $this->region->name,
            ],
            'district'   => [
                'id'   => $this->district->id,
                'name' => $this->district->name,
            ],
            'name'       => $this->name,
            'phone'      => $this->phone,
            'phone2'     => $this->phone2 ?? null,
            'gender'     => $this->gender,
            'avatar'     => $this->getMedia('user_avatar') ?: $default,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
        ];
    }
}