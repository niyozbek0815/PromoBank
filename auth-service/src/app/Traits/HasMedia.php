<?php
namespace App\Traits;

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

        $media = $this->media
            ->when($collectionName, fn($collection) => $collection->where('collection_name', $collectionName))
            ->sortByDesc('created_at')
            ->first();

        // Agar media topilsa, faqat URL ni qaytarish
        // Agar media topilsa, faqat URL ni qaytarish
        if ($media) {
            return $media->full_url; // faqat to'liq URL
        }
        $baseUrl = config('services.urls.global_url');
        return $baseUrl . '/media/upload/user_avate/default-avatar.png';
    }
    /**
     * Hamma media'ni olish va har biri uchun faqat full_url qaytarish
     */
    public function getAllMedia(?string $collectionName = null)
    {
        // Agar media aloqasi allaqachon yuklangan bo'lsa
        if (! $this->relationLoaded('media')) {
            $this->load('media'); // Agar yuklanmagan bo'lsa, uni yuklab olish
        }

        $media = $this->media
            ->when($collectionName, fn($collection) => $collection->where('collection_name', $collectionName))
            ->sortByDesc('created_at');

        // Har bir media uchun faqat full_url ni qaytarish
        return $media->map(function ($item) {
            return $item->full_url;
        });
    }
}