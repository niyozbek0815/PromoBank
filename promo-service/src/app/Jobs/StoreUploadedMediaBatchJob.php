<?php
namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreUploadedMediaBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $filePaths,      // ['tmp/abc.jpg', 'tmp/xyz.jpg']
        public string $context,       // e.g., 'promotion-gallery'
        public string $correlationId, // e.g., promotion ID
    ) {
    }

    public function handle()
    {
        $multipart = [
            [
                'name' => 'context',
                'contents' => $this->context,
            ],
            [
                'name' => 'user_id',
                'contents' => $this->correlationId,
            ],
        ];

        // Fayllarni multipart massivi sifatida tayyorlash
        foreach ($this->filePaths as $filePath) {
            $absolutePath = storage_path('app/public/' . $filePath);
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $fileName = Str::uuid() . '.' . $extension;
            if (!file_exists($absolutePath)) {
                Log::warning('⛔ Fayl topilmadi: ' . $absolutePath);
                continue;
            }
            $multipart[] = [
                'name' => 'files[]',
                'contents' => file_get_contents($absolutePath),
                'filename' => $fileName,
            ];
        }

        try {
            $oldMedias = Media::where('collection_name', $this->context)
                ->where('model_id', $this->correlationId)
                ->get(['id', 'url']);
            $mediaUrls = $oldMedias->pluck('url')->toArray();
            $oldMediaIds = $oldMedias->pluck('id')->toArray();

            foreach ($mediaUrls as $url) {
                $multipart[] = [
                    'name' => 'image_urls[]',
                    'contents' => $url,
                ];
            }

            $response = Http::asMultipart()
                ->post(config('services.urls.media_service') . '/api/media/upload-batch', $multipart);

            if (!$response->successful()) {
                Log::error('❌ Media-service xatolik (non-200)', [
                    'status' => $response->status(),
                    'body' => $response->body(), // bu yerda HTML bo‘lishi mumkin
                ]);

                throw new \Exception('Media-service xatolik: ' . $response->body());
            }

            // JSON tekshiruvi: JSON bo‘lmagan bo‘lsa logga yozib to‘xtatamiz
            $body = $response->body();
            if (!$this->isJson($body)) {
                Log::error('❌ Media-service noto‘g‘ri formatda javob berdi (JSON emas)', ['raw_response' => $body]);
                throw new \Exception('Media-service JSON formatda javob qaytarmadi.');
            }

            $mediaResponses = $response->json();
            if (!empty($oldMediaIds)) {
                Media::whereIn('id', $oldMediaIds)->delete();
            }

            foreach ($mediaResponses as $mediaResponse) {
                Media::create([
                    'model_type' => $this->getModelTypeByContext($this->context),
                    'model_id' => $this->correlationId,
                    'uuid' => $mediaResponse['uuid'],
                    'collection_name' => $mediaResponse['collection_name'],
                    'file_name' => $mediaResponse['file_name'],
                    'name' => $mediaResponse['name'],
                    'mime_type' => $mediaResponse['mime_type'],
                    'path' => $mediaResponse['path'],
                    'url' => $mediaResponse['url'],
                ]);
            }

        } catch (\Throwable $e) {
            Log::error('🔥 StoreUploadedMediaBatchJob: Exception', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            // Hammasini o‘chirish
            foreach ($this->filePaths as $filePath) {
                if (Storage::disk('public')->exists($filePath)) {
                    Storage::disk('public')->delete($filePath);
                    Log::info('🧹 Tmp fayl o‘chirildi: ' . $filePath);
                }
            }
        }
    }
    protected function isJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }
    protected function getModelTypeByContext(string $context): string
    {
        return match ($context) {
            'promotion-gallery' => \App\Models\Promotions::class,
            'user_avatar' => \App\Models\User::class,
            'banners' => \App\Models\Banner::class,
            default => throw new \InvalidArgumentException("Noma'lum context: {$context}"),
        };
    }
}
