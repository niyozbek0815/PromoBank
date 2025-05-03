<?php

namespace App\Jobs;

use App\Models\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UploadUserAvatarJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $base64Image;

    public function __construct($userId, $base64Image)
    {
        $this->userId = $userId;
        $this->base64Image = $base64Image;
    }

    public function handle()
    {
        if (!preg_match("/^data:image\/(\w+);base64,/", $this->base64Image, $type)) {
            throw new \InvalidArgumentException('Rasm formati noto‘g‘ri');
        }

        $imageType = strtolower($type[1]);
        $fileName = Str::uuid() . '.' . $imageType;
        $base64Str = preg_replace('/^data:image\/\w+;base64,/', '', $this->base64Image);
        $base64Str = str_replace(' ', '+', $base64Str);

        $tempDir = storage_path('app/temp');
        $tempFilePath = "{$tempDir}/{$fileName}";

        try {
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            file_put_contents($tempFilePath, base64_decode($base64Str));

            $response = Http::attach(
                'file',
                file_get_contents($tempFilePath),
                $fileName
            )->post(config('services.urls.media_service') . '/api/media/upload', [
                'context' => 'company_logo',
                'user_id' => $this->userId,
            ]);

            if (!$response->successful()) {
                throw new \Exception('Media-service xato: ' . $response->body());
            }

            $mediaResponse = $response->json();

            // Metadata saqlaymiz
            Media::create([
                'model_type' => \App\Models\User::class,
                'model_id' => $this->userId,
                'uuid' => $mediaResponse['uuid'],
                'collection_name' => $mediaResponse['collection_name'],
                'file_name' => $mediaResponse['file_name'],
                'name' => $mediaResponse['name'],
                'mime_type' => $mediaResponse['mime_type'],
                'path' => $mediaResponse['path'],
                'url' => $mediaResponse['url'],
            ]);
        } finally {
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}
