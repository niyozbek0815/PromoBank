<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\About;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class AboutsController extends Controller
{
    public function index()
    {
        $about = About::first();
        return response()->json(['about' => $about]);
    }

    public function edit()
    {
        $about = About::first();

        if (!$about) {
            return response()->json(['message' => 'About not found'], 404);
        }

        return response()->json([
            'about' => [
                'id'          => $about->id,
                'subtitle'    => $about->subtitle,
                'title'       => $about->title,
                'description' => $about->description,
                'list'        => $about->list,
                'image'       => $about->image,
                'status'      => $about->status,
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

            'list_uz'        => 'required|array',
            'list_ru'        => 'required|array',
            'list_kr'        => 'required|array',
            'list_en' => 'required|array',

            'list_uz.*'      => 'string|max:255',
            'list_ru.*'      => 'string|max:255',
            'list_kr.*'      => 'string|max:255',
            'list_en.*' => 'string|max:255',

            'about_image'    => 'nullable|image|max:512', // FilePond input nomi
        ]);

        // 2️⃣ Modelni olish yoki yaratish
        $about = About::firstOrNew([]);

        // 3️⃣ JSON maydonlarni yozish
        $about->fill([
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? [],
            'description' => $validated['description'] ?? [],
            'list'        => [
                'uz' => $validated['list_uz'],
                'ru' => $validated['list_ru'],
                'kr' => $validated['list_kr'],
                'en' => $validated['list_en'],

            ],
        ]);

        $about->save();

        // 4️⃣ Fayl yuklash (queue orqali)
        if ($request->hasFile('about_image')) {
            $file = $request->file('about_image');
            if (is_array($file)) {
                $file = $file[0];
            }
            $tempPath = $file->store('tmp', 'public');
            Queue::connection('rabbitmq')->push(
                new StoreUploadedMediaJob($tempPath, 'about', $about->id)
            );
        }

        return response()->json(['message' => 'About updated successfully']);
    }
}
