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

    if (! Storage::disk('public')->exists($path)) {
        abort(404);
    }

    $absolutePath = storage_path("app/public/{$path}");
    $mimeType     = \Illuminate\Support\Facades\File::mimeType($absolutePath);
    $file         = Storage::disk('public')->get($path);

    return Response::make($file, 200, [
        'Content-Type'                => $mimeType,
        'Content-Disposition'         => 'inline; filename="' . $fileName . '"',
        'Access-Control-Allow-Origin' => '*', // faqat development uchun
    ]);
});

