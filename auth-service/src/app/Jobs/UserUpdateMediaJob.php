<?php
namespace App\Jobs;

use App\Models\Media;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserUpdateMediaJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $filePath;

    public function __construct($userId, $filePath)
    {
        $this->userId   = $userId;
        $this->filePath = $filePath;
    }

    public function handle()
    {
        $extension = pathinfo($this->filePath, PATHINFO_EXTENSION);
        $fileName  = Str::uuid() . '.' . $extension;
        Log::info('UserUpdateMediaJob started', ['user_id' => $this->userId, 'file_path' => $this->filePath]);
        $absolutePath = storage_path('app/public/' . $this->filePath);
        // yoki:
        // $absolutePath = Storage::disk('public')->path($this->filePath);
        if (! file_exists($absolutePath)) {
            Log::error('1File not found for media upload', ['path' => $absolutePath]);
            return;
        }

        $oldMedias = Media::where('model_type', \App\Models\User::class)
            ->where('model_id', $this->userId)
            ->get(['id', 'url']);

        $mediaUrls   = $oldMedias->pluck('url')->toArray();
        $oldMediaIds = $oldMedias->pluck('id')->toArray();
        Log::info('Old media urls', ['media_urls' => $mediaUrls]);
        Log::info('Old media ids', ['media_ids' => $oldMediaIds]);

        $response = Http::attach(
            'file',
            file_get_contents($absolutePath),
            $fileName
        )->post(config('services.urls.media_service') . '/api/media/upload', [
            'context'    => 'user_profile',
            'user_id'    => $this->userId,
            'image_urls' => $mediaUrls,
        ]);

        if (! $response->successful()) {
            throw new \Exception('Media-service xato: ' . $response->body());
        }

        $mediaResponse = $response->json();
        Log::info('1Image_urls', ['1url' => $mediaResponse['url']]);

        if (! empty($oldMediaIds)) {
            Media::whereIn('id', $oldMediaIds)->delete();
        }

        Media::create([
            'model_type'      => \App\Models\User::class,
            'model_id'        => $this->userId,
            'uuid'            => $mediaResponse['uuid'],
            'collection_name' => $mediaResponse['collection_name'],
            'file_name'       => $mediaResponse['file_name'],
            'name'            => $mediaResponse['name'],
            'mime_type'       => $mediaResponse['mime_type'],
            'path'            => $mediaResponse['path'],
            'url'             => $mediaResponse['url'],
        ]);
    }
}
