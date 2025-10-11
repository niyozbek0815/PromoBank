<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipationTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'   => $this->id,
            'type' => $this->slug,
            'name' => $this->getTranslatedTypeName($this->slug),
        ];
    }

    private function getTranslatedTypeName(string $slug): array
    {
        return match ($slug) {
            'qr_code' => [
                'uz' => 'QR orqali',
                'ru' => 'Через QR',
                'kr' => 'ҚР орқали',
            ],
            'text_code' => [
                'uz' => 'Kod kiritish',
                'ru' => 'Ввод кода',
                'kr' => 'Код киритиш',
            ],
            'receipt_scan' => [
                'uz' => 'Checkni skanerlash',
                'ru' => 'Сканировать чек',
                'kr' => 'Чекни сканерлаш',
            ],
            default => [
                'uz' => 'Nomaʼlum',
                'ru' => 'Неизвестно',
                'kr' => 'Номаълум',
            ]
        };
    }
}
