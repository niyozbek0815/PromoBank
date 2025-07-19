<?php
namespace App\Jobs;

use App\Jobs\MediaUploadedJob;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StoreBase64MediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $base64,
        public string $context,
        public string $correlationId,
        public string $callbackQueue,
        public ?array $deleteMediaUrls = null
    ) {}

    public function handle(): void
    {
        try {
            if ($this->deleteMediaUrls) {
                $this->deleteImages($this->deleteMediaUrls);
            }
            $fileData = $this->storeBase64($this->base64, $this->context);
            MediaUploadedJob::dispatch($fileData, $this->context, $this->correlationId)->onQueue($this->callbackQueue);
        } catch (Exception $e) {
            Log::error('StoreBase64MediaJob: Exception occurred', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
        }
    }

    protected function storeBase64(string $base64, string $context): array
    {
        if (! preg_match('/^data:(.*?);base64,(.*)$/', $base64, $matches)) {
            throw new Exception('Invalid base64 format');
        }

        $mimeType   = $matches[1];
        $base64Data = $matches[2];
        $extension  = explode('/', $mimeType)[1] ?? 'bin';

        $uuid     = (string) Str::uuid();
        $fileName = $uuid . '.' . $extension;
        $path     = "uploads/{$context}/{$fileName}";

        Storage::disk('public')->put($path, base64_decode($base64Data));

        return [
            'file_name'       => $fileName,
            'name'            => $fileName,
            'mime_type'       => $mimeType,
            'collection_name' => $context,
            'uuid'            => $uuid,
            'path'            => "uploads/{$context}",
            'url'             => "/media/uploads/{$context}/{$fileName}",
        ];
    }

    protected function deleteImages(array $imageUrls): void
    {
        foreach ($imageUrls as $image) {
            $relativePath = str_starts_with($image, '/media')
            ? ltrim(substr($image, strlen('/media')), '/')
            : ltrim($image, '/');

            if (Storage::disk('public')->exists($relativePath)) {
                Storage::disk('public')->delete($relativePath);
            } else {
                Log::warning('StoreBase64MediaJob: File not found for deletion', [
                    'path' => $relativePath,
                ]);
            }
        }
    }
}
