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

class StoreUploadedMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $context,
        public string $correlationId,
    ) {}

    public function handle()
    {
        $absolutePath = storage_path('app/public/' . $this->filePath);
        $extension    = pathinfo($this->filePath, PATHINFO_EXTENSION);
        $fileName     = Str::uuid() . '.' . $extension;

        Log::info('UserUpdateMediaJob started', [
            'user_id'   => $this->correlationId,
            'file_path' => $this->filePath,
        ]);

        try {
            if (! file_exists($absolutePath)) {
                Log::error('File not found for media upload', ['path' => $absolutePath]);
                return;
            }

            $oldMedias = Media::where('collection_name', $this->context)
                ->where('model_id', $this->correlationId)
                ->get(['id', 'url']);

            $mediaUrls   = $oldMedias->pluck('url')->toArray();
            $oldMediaIds = $oldMedias->pluck('id')->toArray();

            Log::info('Old media', [
                'urls' => $mediaUrls,
                'ids'  => $oldMediaIds,
            ]);

            $multipart = [
                [
                    'name'     => 'file',
                    'contents' => file_get_contents($absolutePath),
                    'filename' => $fileName,
                ],
                [
                    'name'     => 'context',
                    'contents' => $this->context,
                ],
                [
                    'name'     => 'user_id',
                    'contents' => $this->correlationId,
                ],
            ];

            foreach ($mediaUrls as $url) {
                $multipart[] = [
                    'name'     => 'image_urls[]',
                    'contents' => $url,
                ];
            }

            $response = Http::asMultipart()->post(config('services.urls.media_service') . '/api/media/upload', $multipart);

            if (! $response->successful()) {
                throw new \Exception('Media-service xato: ' . $response->body());
            }

            $mediaResponse = $response->json();

            Log::info('Media-service javobi', ['response' => $mediaResponse]);

            if (! empty($oldMediaIds)) {
                Media::whereIn('id', $oldMediaIds)->delete();
            }

            Media::create([
                'model_type'      => $this->getModelTypeByContext($this->context),
                'model_id'        => $this->correlationId,
                'uuid'            => $mediaResponse['uuid'],
                'collection_name' => $mediaResponse['collection_name'],
                'file_name'       => $mediaResponse['file_name'],
                'name'            => $mediaResponse['name'],
                'mime_type'       => $mediaResponse['mime_type'],
                'path'            => $mediaResponse['path'],
                'url'             => $mediaResponse['url'],
            ]);

        } catch (\Throwable $e) {
            Log::error('UserUpdateMediaJob: Exception', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            if (Storage::disk('public')->exists($this->filePath)) {
                Storage::disk('public')->delete($this->filePath);
                Log::info('Tmp fayl o‘chirildi: ' . $this->filePath);
            }
        }
    }
    protected function getModelTypeByContext(string $context): string
    {
        return match ($context) {
            'user_avatar' => \App\Models\User::class,
            'notification-image' => \App\Models\Notification::class,
            'notification-excel' => \App\Models\NotificationExcel::class,
        // kerak bo‘lsa yana qo‘sh
            default => throw new \InvalidArgumentException("Unknown context: {$context}"),
        };
    }

}
