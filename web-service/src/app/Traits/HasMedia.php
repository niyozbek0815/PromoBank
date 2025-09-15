<?php
namespace App\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

trait HasMedia
{
    public function media()
    {
        return $this->morphMany(\App\Models\Media::class, 'model');
    }
    /**
     * Bitta media'ni olish va faqat full_url qaytarish
     */
   public function getMedia(?string $collectionName = null, ?string $fallback = null)
    {
        // Agar media aloqasi allaqachon yuklangan bo'lsa
        if (! $this->relationLoaded('media')) {
            $this->load('media'); // Agar yuklanmagan bo'lsa, uni yuklab olish
        }
        $media = $this->media()
            ->when($collectionName, function ($query) use ($collectionName) {
            $query->where('collection_name', $collectionName);
            })
            ->orderByDesc('created_at')
            ->first();

        if ($media) {
            return [
                'url'       => $media->full_url,
                'mime_type' => $media->mime_type,
            ];
        }

        return $fallback ? [
            'url'       => $fallback,
            'mime_type' => null,
        ] : null;

    }
    /**
     * Hamma media'ni olish va har biri uchun faqat full_url qaytarish
     */
    public function getAllMedia(?string $collectionName = null): Collection
    {
        if (! $this->relationLoaded('media')) {
            $this->load('media');
        }
        $mediaCollection = $this->media ?? collect();
        return $mediaCollection
            ->when($collectionName, fn($collection) => $collection->where('collection_name', $collectionName))
            ->sortByDesc('created_at')
            ->map(fn($media) => [
                'url'       => $media->full_url,
                'mime_type' => $media->mime_type,
            ])
            ->values();
    }
    public function getMediaCollection(?string $collectionName = null): Collection
    {
        return $this->getAllMedia($collectionName);
    }
}
