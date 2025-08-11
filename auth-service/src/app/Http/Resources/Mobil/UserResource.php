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
    return [
        'id'         => $this->id ?? null,
        'region'     => [
            'id'   => $this->region->id ?? null,
            'name' => $this->region->name ?? null,
        ],
        'district'   => [
            'id'   => $this->district->id ?? null,
            'name' => $this->district->name ?? null,
        ],
        'name'       => $this->name ?? null,
        'phone'      => $this->phone ?? null,
        'phone2'     => $this->phone2 ?? null,
        'gender'     => $this->gender ?? null,
        'birthdate'  => $this->birthdate?->toDateString() ?? null,
        'avatar'     => $this->avatar ?? null,
        'created_at' => $this->created_at?->toDateTimeString() ?? null,
        'updated_at' => $this->updated_at?->toDateTimeString() ?? null,
    ];
}
}
