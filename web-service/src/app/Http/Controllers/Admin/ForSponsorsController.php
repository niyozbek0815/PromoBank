<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\ForSponsor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class ForSponsorsController extends Controller
{
    /**
     * DataTables uchun ma’lumotlar
     */
    public function data(Request $request)
    {
        $query = ForSponsor::query()
            ->orderBy('position')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('title', fn($item) => $item->getTranslation('title', 'uz') ?? '-')
            ->addColumn('description', fn($item) => Str::limit($item->getTranslation('description', 'uz') ?? '-', 60))
            ->addColumn('position', fn($item) => $item->position)
            ->addColumn(
                'status',
                fn($item) =>
                $item->status
                    ? '<span class="badge bg-success">Faol</span>'
                    : '<span class="badge bg-secondary">Nofaol</span>'
            )
            ->addColumn('image', function ($item) {
                if (!$item->image) {
                    return '-';
                }
                $url = Str::startsWith($item->image, ['http://', 'https://'])
                    ? $item->image
                    : config('services.urls.api_getaway') . '/' . $item->image;

                return '<img src="' . $url . '" alt="portfolio" style="max-width:60px;max-height:60px;">';
            })

            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/forsponsor/{$row->id}/edit",
                        'delete' => "/admin/forsponsor/{$row->id}/delete",
                        'status' => "/admin/forsponsor/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'image', 'actions'])
            ->make(true);
    }

    /**
     * Yaratish
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.uz'       => 'required|string|max:255',
            'title.ru'       => 'required|string|max:255',
            'title.kr'       => 'required|string|max:255',
            'description.uz' => 'required|string',
            'description.ru' => 'required|string',
            'description.kr' => 'required|string',
            'position'       => 'required|integer|min:0',
            'status'         => 'nullable|boolean',
            'image'          => 'required|image|mimes:jpg,jpeg,png,svg|max:512',
        ]);

        $forSponsor = ForSponsor::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? [],
            'position'    => $validated['position'],
            'status'      => $validated['status'] ?? 1,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 for_sponsor image yuklanmoqda... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'for_sponsor', $forSponsor->id));
        }

        return response()->json([
            'message' => 'ForSponsor muvaffaqiyatli qo‘shildi ✅',
            'data'    => $forSponsor,
        ]);
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $forSponsor = ForSponsor::findOrFail($id);

        return response()->json([
            'forSponsor' => [
                'id'          => $forSponsor->id,
                'title'       => $forSponsor->getTranslations('title'),
                'description' => $forSponsor->getTranslations('description'),
                'position'    => $forSponsor->position,
                'status'      => $forSponsor->status,
                'image'       => $forSponsor->image,
            ],
        ]);
    }

    /**
     * Yangilash
     */
    public function update(Request $request, $id)
    {
        $forSponsor = ForSponsor::findOrFail($id);

        $validated = $request->validate([
            'title.uz'       => 'required|string|max:255',
            'title.ru'       => 'required|string|max:255',
            'title.kr'       => 'required|string|max:255',
            'description.uz' => 'required|string',
            'description.ru' => 'required|string',
            'description.kr' => 'required|string',
            'position'       => 'required|integer|min:0',
            'status'         => 'required|boolean',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,svg|max:512',
        ]);

        $forSponsor->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? $forSponsor->description,
            'position'    => $validated['position'],
            'status'      => $validated['status'] ?? $forSponsor->status,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 for_sponsor image yangilandi... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'for_sponsor', $forSponsor->id));
        }

        return response()->json([
            'success' => true,
            'message' => 'ForSponsor muvaffaqiyatli yangilandi ✅',
            'data'    => $forSponsor->fresh(),
        ]);
    }

    /**
     * Statusni almashtirish
     */
    public function changeStatus($id)
    {
        $forSponsor = ForSponsor::findOrFail($id);
        $forSponsor->status = !$forSponsor->status;
        $forSponsor->save();

        return response()->json(['success' => true, 'status' => $forSponsor->status]);
    }

    /**
     * O‘chirish
     */
    public function destroy($id)
    {
        $forSponsor = ForSponsor::findOrFail($id);
        $forSponsor->delete();

        return response()->json(['success' => "ForSponsor muvaffaqiyatli o‘chirildi."]);
    }
}
