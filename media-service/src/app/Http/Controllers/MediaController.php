<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\MediaRequest;

class MediaController extends Controller
{
    public function upload(MediaRequest $request)
    {
        $file = $request->file('file');

        $context = $request->input('context'); // ex: 'user_avatar'
        $userId = $request->input('user_id');
        $saved = $this->store($file, $context, $userId);
        return response()->json($saved);
    }

    public function store($file, string $context, $userId = null): array
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
            'url' => config('services.urls.api_getaway') . "/media/uploads/{$context}/{$fileName}",
        ];
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
