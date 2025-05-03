<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Http\Requests\MediaRequest;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function upload(MediaRequest $request)
    {
        $file = $request->file('file');
        $imageUrls = $request->input('image_urls');

        if ($imageUrls !== null) {
            $this->deleteImages($imageUrls);
        }
        $context = $request->input('context'); // ex: 'user_avatar'
        $saved = $this->store($file, $context);
        return response()->json($saved);
    }

    public function store($file, string $context): array
    {
        // Faylning ma'lumotlarini olish
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();

        // UUID yaratish va fayl nomini tayyorlash
        $uuid = (string) Str::uuid();
        $fileName = $uuid . '.' . $extension;

        // Fayl saqlanish joyini belgilash
        $path = "uploads/{$context}";

        // Faylni saqlash
        $file->storeAs($path, $fileName, 'public');

        // Fayl haqida ma'lumotlarni qaytarish
        return [
            'file_name' => $fileName,
            'name' => $originalName,
            'mime_type' => $mimeType,
            'collection_name' => $context,
            'uuid' => $uuid,
            'path' => $path,
            'url' => "/media/uploads/{$context}/{$fileName}",
        ];
    }


    public function deleteImages(array $imageUrls)
    {
        $allDeleted = true;
        foreach ($imageUrls as $image) {

            // '/media' prefiksini olib tashlash
            $relativePath = str_starts_with($image, '/media')
                ? ltrim(substr($image, strlen('/media')), '/')
                : ltrim($image, '/');

            // $relativePath = 'uploads/user_avatar/7c764d43-760c-41a-801e-3f85230f53fa.png';
            // return Storage::disk('public')->exists($relativePath);
            // Faylni storage orqali o‘chirish
            if (Storage::disk('public')->exists($relativePath)) {
                $deleted = Storage::disk('public')->delete($relativePath);

                // Agar o'chirish muvaffaqiyatsiz bo'lsa, xato haqida xabar berish
                if (!$deleted) {
                    $allDeleted = false;
                }
            } else {
                // Fayl mavjud emas, o‘tkazib yuborish
                $allDeleted = false;
            }
        }

        return $allDeleted;
    }
    // public function destroy($collection, $filename)
    // {
    //     $path = "uploads/{$collection}/{$filename}";

    //     if (Storage::exists($path)) {
    //         Storage::delete($path);
    //         return response()->json(['message' => 'Deleted successfully']);
    //     }

    //     return response()->json(['message' => 'File not found'], 404);
    // }
}
