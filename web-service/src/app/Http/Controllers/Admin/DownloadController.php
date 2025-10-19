<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Download;
use App\Models\DownloadLink;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class DownloadController extends Controller
{
    public function index()
    {
        $download = Download::with('links')->first();

        return response()->json(['download' => $download]);
    }

    public function edit()
    {
        $download = Download::with('links')->first();

        return response()->json([
            'download' => [
                'id'          => $download->id,
                'title'       => $download->title,
                'subtitle'    => $download->subtitle,
                'description' => $download->description,
                'image'       => $download->image,
                'links'       => $download->links->pluck('url', 'type')->toArray(),
            ]
        ]);
    }

    public function update(Request $request)
    {

        // 1️⃣ Validatsiya
        $validated = $request->validate([
            'title.uz'       => 'required|string|max:255',
            'title.ru'       => 'required|string|max:255',
            'title.kr'       => 'required|string|max:255',
            'title.en' => 'required|string|max:255',

            'subtitle.uz'    => 'nullable|string|max:255',
            'subtitle.ru'    => 'nullable|string|max:255',
            'subtitle.kr'    => 'nullable|string|max:255',
            'subtitle.en' => 'nullable|string|max:255',


            'description.uz' => 'nullable|string',
            'description.ru' => 'nullable|string',
            'description.kr' => 'nullable|string',
            'description.en' => 'nullable|string',

            'image'          => 'nullable|image|max:512',

            'links'          => 'required|array',
            'links.googleplay' => 'required|url|max:1024',
            'links.appstore'   => 'required|url|max:1024',
            'links.telegram'   => 'required|url|max:1024',
        ]);

        // 2️⃣ Download modelini olish yoki yaratish
        $download = Download::firstOrNew([]);

        // 3️⃣ JSON maydonlarni yozish (casts array sifatida saqlaydi)
        $download->fill([
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? [],
            'description' => $validated['description'] ?? [],
        ]);

        $download->save();

        // 4️⃣ Fayl yuklash (queue orqali)
        if ($request->hasFile('image')) {
            $tempPath = $request->file('image')->store('tmp', 'public');
            Log::info("📎 Download image yuklanmoqda: {$tempPath}");

            Queue::connection('rabbitmq')->push(
                new StoreUploadedMediaJob($tempPath, 'download', $download->id)
            );
        }

        // 5️⃣ Linklarni saqlash (bulk upsert bilan)
        foreach ($validated['links'] as $type => $url) {
            DownloadLink::updateOrCreate(
                ['download_id' => $download->id, 'type' => strtolower($type)],
                ['url' => $url, 'status' => 1]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Download malumotlari muvaffaqiyatli saqlandi!',
        ]);
    }
}
