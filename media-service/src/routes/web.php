<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
// routes/web.php

use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

Route::get('/uploads/{context}/{fileName}', function ($context, $fileName) {
    $path = "uploads/{$context}/{$fileName}";

    if (!Storage::disk('public')->exists($path)) {
        abort(404);
    }

    // Faylni olish
    $file = Storage::disk('public')->get($path);

    // MIME turini aniqlash
    $imageDetails = getimagesize(storage_path("app/public/{$path}"));
    $mimeType = $imageDetails['mime'];

    // Faylni qaytarish va Content-Type ni belgilash
    return response($file, 200)
        ->header('Content-Type', $mimeType);
});
