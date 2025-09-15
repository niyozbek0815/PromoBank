<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Benefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class BenefitController extends Controller
{
    /**
     * DataTables uchun ma’lumotlar
     */
    public function data(Request $request)
    {
        $query = Benefit::query()
            ->orderBy('position')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('title', fn($item) => $item->getTranslation('title', 'uz') ?? '-')
            ->addColumn('description', fn($item) => \Str::limit($item->getTranslation('description', 'uz') ?? '-', 50))
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
                        'edit'   => "/admin/benefits/{$row->id}/edit",
                        'delete' => "/admin/benefits/{$row->id}/delete",
                        'status' => "/admin/benefits/{$row->id}/status",
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
            'image'          => 'required|image|mimes:jpg,jpeg,png,svg|max:1024', // 1 MB
        ]);

        $benefit = Benefit::create([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? [],
            'position'    => $validated['position'],
            'status'      => $validated['status'] ?? 1,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 benefit image yuklanmoqda... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'benefit', $benefit->id));
        }

        return response()->json([
            'message' => 'Benefit muvaffaqiyatli qo‘shildi ✅',
            'data'    => $benefit,
        ]);
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $benefit = Benefit::findOrFail($id);
        return response()->json([
            'benefit' => [
                'id'          => $benefit->id,
                'title'       => $benefit->getTranslations('title'),
                'description' => $benefit->getTranslations('description'),
                'position'    => $benefit->position,
                'status'      => $benefit->status,
                'image'       => $benefit->image,
            ],
        ]);
    }

    /**
     * Yangilash
     */
    public function update(Request $request, $id)
    {
        $benefit = Benefit::findOrFail($id);
        $validated = $request->validate([
            'title.uz'       => 'required|string|max:255',
            'title.ru'       => 'required|string|max:255',
            'title.kr'       => 'required|string|max:255',
            'description.uz' => 'required|string',
            'description.ru' => 'required|string',
            'description.kr' => 'required|string',
            'position'       => 'required|integer|min:0',
            'status'         => 'required|boolean',
            'image'          => 'nullable|image|mimes:jpg,jpeg,png,svg|max:1024',
        ]);

        $benefit->update([
            'title'       => $validated['title'],
            'description' => $validated['description'] ?? $benefit->description,
            'position'    => $validated['position'],
            'status'      => $validated['status'] ?? $benefit->status,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 benefit image yangilandi... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'benefit', $benefit['id']));
        }

        return response()->json([
            'success' => true,
            'message' => 'Benefit muvaffaqiyatli yangilandi ✅',
            'data'    => $benefit->fresh(),
        ]);
    }

    /**
     * Statusni almashtirish
     */
    public function changeStatus($id)
    {
        $benefit = Benefit::findOrFail($id);
        $benefit->status = !$benefit->status;
        $benefit->save();

        return response()->json(['success' => true, 'status' => $benefit->status]);
    }

    /**
     * O‘chirish
     */
    public function destroy($id)
    {
        $benefit = Benefit::findOrFail($id);
        $benefit->delete();

        return response()->json(['success' => "Benefit muvaffaqiyatli o‘chirildi."]);
    }
}
