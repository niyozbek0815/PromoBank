<?php
namespace App\Http\Controllers;

use App\Http\Requests\MediaRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(MediaRequest $request)
    {
        \Log::info('Upload request received', [
            'request_data' => $request->all(),
            'files'        => $request->file('file'),
        ]);

        $file      = $request->file('file');
        $imageUrls = $request->input('image_urls');

        if ($imageUrls !== null) {
            \Log::info('Deleting images', ['image_urls' => $imageUrls]);
            $this->deleteImages($imageUrls);
        }
        $context = $request->input('context'); // ex: 'user_avatar'
        $saved   = $this->store($file, $context);

        \Log::info('File saved', ['saved_data' => $saved]);

        return response()->json($saved);
    }

    public function store($file, string $context): array
    {
        // Faylning ma'lumotlarini olish
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType();
        $extension    = $file->getClientOriginalExtension();

        // UUID yaratish va fayl nomini tayyorlash
        $uuid     = (string) Str::uuid();
        $fileName = $uuid . '.' . $extension;

        // Fayl saqlanish joyini belgilash
        $path = "uploads/{$context}";

        // Faylni saqlash
        $file->storeAs($path, $fileName, 'public');

        $fileData = [
            'file_name'       => $fileName,
            'name'            => $originalName,
            'mime_type'       => $mimeType,
            'collection_name' => $context,
            'uuid'            => $uuid,
            'path'            => $path,
            'url'             => "/media/uploads/{$context}/{$fileName}",
        ];

        \Log::info('File stored', ['file_data' => $fileData]);

        // Fayl haqida ma'lumotlarni qaytarish
        return $fileData;
    }

    public function deleteImages(array $imageUrls)
    {
        $allDeleted = true;
        foreach ($imageUrls as $image) {

            // '/media' prefiksini olib tashlash
            $relativePath = str_starts_with($image, '/media')
            ? ltrim(substr($image, strlen('/media')), '/')
            : ltrim($image, '/');

            \Log::info('Attempting to delete image', ['relative_path' => $relativePath]);

            // Faylni storage orqali o‘chirish
            if (Storage::disk('public')->exists($relativePath)) {
                $deleted = Storage::disk('public')->delete($relativePath);

                \Log::info('Delete result', [
                    'relative_path' => $relativePath,
                    'deleted'       => $deleted,
                ]);

                // Agar o'chirish muvaffaqiyatsiz bo'lsa, xato haqida xabar berish
                if (! $deleted) {
                    $allDeleted = false;
                }
            } else {
                // Fayl mavjud emas, o‘tkazib yuborish
                \Log::warning('File not found for deletion', ['relative_path' => $relativePath]);
                $allDeleted = false;
            }
        }

        \Log::info('All images deleted status', ['all_deleted' => $allDeleted]);

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