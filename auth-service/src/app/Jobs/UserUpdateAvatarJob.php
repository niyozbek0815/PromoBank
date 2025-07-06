<?php
namespace App\Jobs;

use App\Models\Media;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserUpdateAvatarJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $id, $base64image, $user;
    public function __construct($id, User $user, $base64Image)
    {
        $this->id          = $id;
        $this->user        = $user;
        $this->base64image = $base64Image;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        if (! preg_match("/^data:image\/(\w+);base64,/", $this->base64image, $type)) {
            throw new \InvalidArgumentException('Rasm formati noto‘g‘ri');
        }

        $image_urls = $this->user->media->pluck('url')->filter()->values()->toArray();

        // Rasm turini aniqlash
        $imageType = strtolower($type[1]);
        $fileName  = Str::uuid() . '.' . $imageType;
        $base64Str = preg_replace('/^data:image\/\w+;base64,/', '', $this->base64image);
        $base64Str = str_replace(' ', '+', $base64Str);

        // Vaqtinchalik faylni saqlash
        $tempDir      = storage_path('app/temp');
        $tempFilePath = "{$tempDir}/{$fileName}";

        try {
            if (! file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            // Base64 kodini dekodlash va faylga yozish
            $decoded = base64_decode($base64Str, true);
            if ($decoded === false) {
                throw new \InvalidArgumentException('Base64 dekodlashda xatolik.');
            }

            file_put_contents($tempFilePath, $decoded);

            if (! file_exists($tempFilePath) || ! is_readable($tempFilePath)) {
                throw new \Exception('Vaqtinchalik fayl mavjud emas yoki o‘qib bo‘lmaydi: ' . $tempFilePath);
            }

            // Faylni o‘qishx
            $fileContents = file_get_contents($tempFilePath);
            if ($fileContents === false) {
                throw new \Exception('Faylni o‘qishda xatolik yuz berdi: ' . $tempFilePath);
            }

            // So‘rov uchun multipart tuzilmasi
            $multipart = [
                [
                    'name'     => 'context',
                    'contents' => 'user_avatar',
                ],
            ];

            // `image_urls[]` ni kiritish
            foreach ($image_urls as $url) {
                $multipart[] = [
                    'name'     => 'image_urls[]',
                    'contents' => $url,
                ];
            }

            // Faylni biriktirish va so‘rovni yuborish
            $response = Http::asMultipart()
                ->attach('file', $fileContents, $fileName)
                ->post(config('services.urls.media_service') . '/api/media/upload', $multipart);

            // Media service tomonidan yuborilgan xatolikni tekshirish
            if (! $response->successful()) {
                throw new \Exception('Media-service xato: ' . $response->body());
            }
            foreach ($this->user->media as $media) {
                // Agar oldin yuklangan media fayl bo'lsa, uni o'chiring
                $media->delete();
            }
            $mediaResponse = $response->json();

            Media::create([
                'model_type'      => User::class, // model class nomi (to'liq namespace bilan)
                'model_id'        => $this->id,   // bog'lanadigan modelning IDsi
                'uuid'            => $mediaResponse['uuid'],
                'collection_name' => $mediaResponse['collection_name'],
                'file_name'       => $mediaResponse['file_name'],
                'name'            => $mediaResponse['name'],
                'mime_type'       => $mediaResponse['mime_type'],
                'path'            => $mediaResponse['path'],
                'url'             => $mediaResponse['url'],
            ]);
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Vaqtinchalik faylni o‘chirish
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
}