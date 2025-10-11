<?php
namespace App\Jobs;

use App\Models\Media;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MediaUploadedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public array $fileData,
        public string $context,
        public string $correlationId, // e.g., user_id
    ) {}

    public function handle(): void
    {
        if ($this->context === 'user_avatar') {
            $this->attachAvatarToUser();
        }

        Log::info('MediaUploadedJob: Finished');
    }

    protected function attachAvatarToUser(): void
    {
        DB::beginTransaction();

        try {
            $userId = (int) $this->correlationId;
            $user   = User::findOrFail($userId);

            $existing = Media::where('model_type', User::class)
                ->where('model_id', $user->id)
                ->where('collection_name', $this->context)
                ->first();

            if ($existing) {
                $existing->delete();
            }
            Media::create([
                'model_type'      => User::class,
                'model_id'        => $user->id,
                'uuid'            => $this->fileData['uuid'],
                'collection_name' => $this->fileData['collection_name'],
                'file_name'       => $this->fileData['file_name'],
                'name'            => $this->fileData['name'],
                'mime_type'       => $this->fileData['mime_type'],
                'path'            => $this->fileData['path'],
                'url'             => $this->fileData['url'],
            ]);
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('MediaUploadedJob: Exception occurred', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}