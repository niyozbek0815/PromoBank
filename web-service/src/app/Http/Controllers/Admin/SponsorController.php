<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Sponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class SponsorController extends Controller
{


    public function data(Request $request)
    {
        $query = Sponsor::query()
            ->orderBy('weight')
            ->orderByDesc('id');

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            // === Asosiy ustunlar ===
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('name', fn($item) => $item->getTranslation('name', 'uz') ?? '-')
            ->addColumn('url', fn($item) => $item->url ?? '-')
            ->addColumn('weight', fn($item) => $item->weight)

            // === Status ===
            ->addColumn('status', function ($item) {
                return $item->status
                    ? '<span class="badge bg-success">Faol</span>'
                    : '<span class="badge bg-secondary">Nofaol</span>';
            })

            ->addColumn('image', function ($item) {
                if (!$item->image) {
                    return '-';
                }
                $url = Str::startsWith($item->image, ['http://', 'https://'])
                    ? $item->image
                    : config('services.urls.api_getaway') . '/' . $item->image;

                return '<img src="' . $url . '" alt="portfolio" style="max-width:60px;max-height:60px;">';
            })

            // === Actions ===
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/sponsors/{$row->id}/edit",
                        'delete' => "/admin/sponsors/{$row->id}/delete",
                        'status' => "/admin/sponsors/{$row->id}/status",
                    ],
                ])->render();
            })

            ->rawColumns(['status', 'image', 'actions'])
            ->make(true);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'name.uz' => 'nullable|string|max:255',
            'name.ru' => 'nullable|string|max:255',
            'name.kr' => 'nullable|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'url'     => 'required|url|max:1024',
            'weight'  => 'required|integer|min:0',
            'status'  => 'nullable|boolean',
            'logo'    => 'required|image|mimes:jpg,jpeg,png,svg|max:512', // 512 KB
        ]);
        // Sponsor yaratish
        $sponsor = Sponsor::create([
            'name'   => $validated['name'],
            'url'    => $validated['url'],
            'weight' => $validated['weight'],
            'status' => $validated['status'] ?? 1,
        ]);
        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 logo mavjud. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'sponsor', $sponsor['id']));
        }

        return response()->json([
            'message' => 'Homiy muvaffaqiyatli qo‘shildi ✅',
            'data'    => $sponsor,
        ]);
    }

    public function edit($id)
    {
        $sponsor = Sponsor::findOrFail($id);
        return response()->json([
            'sponsor' => [
                'id' => $sponsor['id'],
                'name' => $sponsor->getTranslations('name'),
                'url' => $sponsor->url,
                'weight' => $sponsor->weight,
                'status' => $sponsor->status,
                'logo' => $sponsor->image,
            ],
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name.uz' => 'nullable|string|max:255',
            'name.ru' => 'nullable|string|max:255',
            'name.kr' => 'nullable|string|max:255',
            'name.en' => 'nullable|string|max:255',
            'url'     => 'required|url|max:1024',
            'weight'  => 'required|integer|min:0',
            'status'  => 'nullable|boolean',
            'logo'    => 'nullable|image|mimes:jpg,jpeg,png,svg|max:512', // 512 KB
        ]);
        $sponsor = Sponsor::findOrFail($id);

        // Homiyni yangilash
        $sponsor->update([
            'name'   => $validated['name'],
            'url'    => $validated['url'],
            'weight' => $validated['weight'],
            'status' => $validated['status'] ?? $sponsor->status,
        ]);
        Log::info("📎 logo yangilandi. Yuklanmoqda...");

        // Agar logo yuklangan bo‘lsa — mediaga yuboramiz
        if ($request->hasFile('logo')) {
            $file     = $request->file('logo');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 logo yangilandi. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'sponsor', $sponsor['id']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Homiy muvaffaqiyatli yangilandi ✅',
            'data'    => $sponsor->fresh(), // yangilangan ma’lumot qaytadi
        ]);
    }

    public function changeStatus($id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $sponsor->status = !$sponsor->status;
        $sponsor->save();

        return response()->json(['success' => true, 'status' => $sponsor->status]);
    }

    public function destroy($id)
    {
        $sponsor = Sponsor::findOrFail($id);

        $sponsor->delete();

        return response()->json(['success' => "Homiy muvaffaqiyatli o'chirildi."]);
    }
}
