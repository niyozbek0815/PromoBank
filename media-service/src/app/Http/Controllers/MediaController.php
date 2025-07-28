<?php
namespace App\Http\Controllers;

use App\Http\Requests\MediaRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function upload(MediaRequest $request)
    {
        $file      = $request->file('file');
        $imageUrls = $request->input('image_urls');
        if ($imageUrls !== null) {
          $this->deleteImages($imageUrls);
        }
        $context = $request->input('context'); // ex: 'user_avatar'
        $saved   = $this->store($file, $context);
        [
    [
        'url'       => 'https://qadarun.com/namuna/1.gif',
        'mime_type' => 'image/gif',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/2.gif',
        'mime_type' => 'image/gif',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/3.gif',
        'mime_type' => 'image/gif',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/4.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/5.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/6.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/7.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/8.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/9.jpeg',
        'mime_type' => 'image/jpeg',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/promo-default.xlsx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/php.docx',
        'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video1.mp4',
        'mime_type' => 'video/mp4',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video2.mp4',
        'mime_type' => 'video/mp4',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video3.mp4',
        'mime_type' => 'video/mp4',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video4.mp4',
        'mime_type' => 'video/mp4',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video5.mp4',
        'mime_type' => 'video/mp4',
    ],
    [
        'url'       => 'https://qadarun.com/namuna/video6.mp4',
        'mime_type' => 'video/mp4',
    ],
];

        return response()->json($saved);
    }

    public function store($file, string $context): array
    {
        $originalName = $file->getClientOriginalName();
        $mimeType     = $file->getClientMimeType();
        $extension    = $file->getClientOriginalExtension();
        $uuid     = (string) Str::uuid();
        $fileName = $uuid . '.' . $extension;
        $path = "uploads/{$context}";
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
        return $fileData;
    }

    public function deleteImages(array $imageUrls)
    {
        $allDeleted = true;
        foreach ($imageUrls as $image) {
            $relativePath = str_starts_with($image, '/media')
            ? ltrim(substr($image, strlen('/media')), '/')
            : ltrim($image, '/');
            if (Storage::disk('public')->exists($relativePath)) {
                $deleted = Storage::disk('public')->delete($relativePath);
                if (! $deleted) {
                    $allDeleted = false;
                }
            } else {
                // Fayl mavjud emas, o‘tkazib yuborish
                $allDeleted = false;
            }
        }
        return $allDeleted;
    }
    public function uploadBatch(Request $request)
    {
        Log::info('📥 [upload-batch] So‘rov kelib tushdi', [
            'context' => $request->input('context'),
            'user_id' => $request->input('user_id'),
        ]);

        $files     = $request->file('files');
        $context   = $request->input('context');
        $userId    = $request->input('user_id');
        $responses = [];
        $imageUrls = $request->input('image_urls');

        if ($imageUrls !== null) {
            $this->deleteImages($imageUrls);
        }

        if (! $files || ! is_array($files)) {
            return response()->json(['message' => 'No files received.'], 422);
        }
        foreach ($files as $file) {
            try {
                $originalName = $file->getClientOriginalName();
                $mimeType     = $file->getClientMimeType();
                $extension    = $file->getClientOriginalExtension();
                $uuid         = (string) Str::uuid();
                $fileName     = $uuid . '.' . $extension;
                $path         = "uploads/{$context}";
                $file->storeAs($path, $fileName, 'public');
                $fileData = [
                    'file_name'       => $fileName,
                    'name'            => $originalName,
                    'mime_type'       => $mimeType,
                    'collection_name' => $context,
                    'uuid'            => $uuid,
                    'path'            => $path,
                    'url'             => "/media/{$path}/{$fileName}",
                ];
                $responses[] = $fileData;
            } catch (\Throwable $e) {
                Log::error('❌ Faylni saqlashda xatolik', [
                    'file'    => $file?->getClientOriginalName(),
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
            }
        }
        return response()->json($responses);
    }
}
