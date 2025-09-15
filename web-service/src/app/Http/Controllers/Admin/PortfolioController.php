<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Str;

class PortfolioController extends Controller
{
    /**
     * DataTables uchun ma’lumotlar
     */
    public function data(Request $request)
    {
        $query = Portfolio::query()
            ->orderBy('position')
            ->orderByDesc('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('featured')) {
            $query->where('is_featured', (bool)$request->featured);
        }
        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('title', fn($item) => $item->getTranslation('title', 'uz') ?? '-')
            ->addColumn('subtitle', fn($item) => $item->getTranslation('subtitle', 'uz') ?? '-')
            ->addColumn('body', fn($item) => Str::limit($item->getTranslation('body', 'uz') ?? '-', 60))
            ->addColumn('position', fn($item) => $item->position)
            ->addColumn(
                'featured',
                fn($item) =>
                $item->is_featured
                    ? '<span class="badge bg-primary">Tanlangan</span>'
                    : '<span class="badge bg-secondary">Oddiy</span>'
            )
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
                    : config('services.urls.api_getaway').'/'.$item->image;

                return '<img src="' . $url. '" alt="portfolio" style="max-width:60px;max-height:60px;">';
            })
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/portfolio/{$row->id}/edit",
                        'delete' => "/admin/portfolio/{$row->id}/delete",
                        'status' => "/admin/portfolio/{$row->id}/status",
                        'feature' => "/admin/portfolio/{$row->id}/feature",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'featured', 'image', 'actions'])
            ->make(true);
    }

    /**
     * Yaratish
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title.uz'    => 'required|string|max:255',
            'title.ru'    => 'required|string|max:255',
            'title.kr'    => 'required|string|max:255',
            'subtitle.uz' => 'required|string|max:255',
            'subtitle.ru' => 'required|string|max:255',
            'subtitle.kr' => 'required|string|max:255',
            'body.uz'     => 'nullable|string',
            'body.ru'     => 'nullable|string',
            'body.kr'     => 'nullable|string',
            'position'    => 'required|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'status'      => 'nullable|boolean',
            'image'       => 'required|image|mimes:jpg,jpeg,png,svg|max:512', // 2MB
        ]);

        $portfolio = Portfolio::create([
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? [],
            'body'        => $validated['body'] ?? [],
            'position'    => $validated['position'],
            'is_featured' => $validated['is_featured'] ?? 0,
            'status'      => $validated['status'] ?? 1,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 portfolio image yuklanmoqda... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'portfolio', $portfolio->id));
        }

        return response()->json([
            'message' => 'Portfolio muvaffaqiyatli qo‘shildi ✅',
            'data'    => $portfolio,
        ]);
    }

    /**
     * Edit
     */
    public function edit($id)
    {
        $portfolio = Portfolio::findOrFail($id);

        return response()->json([
            'portfolio' => [
                'id'          => $portfolio->id,
                'title'       => $portfolio->getTranslations('title'),
                'subtitle'    => $portfolio->getTranslations('subtitle'),
                'body'        => $portfolio->getTranslations('body'),
                'position'    => $portfolio->position,
                'is_featured' => $portfolio->is_featured,
                'status'      => $portfolio->status,
                'image'       => $portfolio->image,
            ],
        ]);
    }

    /**
     * Yangilash
     */
    public function update(Request $request, $id)
    {
        $portfolio = Portfolio::findOrFail($id);

        $validated = $request->validate([
            'title.uz'    => 'required|string|max:255',
            'title.ru'    => 'required|string|max:255',
            'title.kr'    => 'required|string|max:255',
            'subtitle.uz' => 'required|string|max:255',
            'subtitle.ru' => 'required|string|max:255',
            'subtitle.kr' => 'required|string|max:255',
            'body.uz'     => 'nullable|string',
            'body.ru'     => 'nullable|string',
            'body.kr'     => 'nullable|string',
            'position'    => 'required|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'status'      => 'required|boolean',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,svg|max:512',
        ]);

        $portfolio->update([
            'title'       => $validated['title'],
            'subtitle'    => $validated['subtitle'] ?? $portfolio->subtitle,
            'body'        => $validated['body'] ?? $portfolio->body,
            'position'    => $validated['position'],
            'is_featured' => $validated['is_featured'] ?? $portfolio->is_featured,
            'status'      => $validated['status'] ?? $portfolio->status,
        ]);

        if ($request->hasFile('image')) {
            $file     = $request->file('image');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 portfolio image yangilandi... " . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'portfolio', $portfolio->id));
        }

        return response()->json([
            'success' => true,
            'message' => 'Portfolio muvaffaqiyatli yangilandi ✅',
            'data'    => $portfolio->fresh(),
        ]);
    }

    /**
     * Statusni almashtirish
     */
    public function changeStatus($id)
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->status = !$portfolio->status;
        $portfolio->save();

        return response()->json(['success' => true, 'status' => $portfolio->status]);
    }



    public function destroy($id)
    {
        $portfolio = Portfolio::findOrFail($id);
        $portfolio->delete();

        return response()->json(['success' => "Portfolio muvaffaqiyatli o‘chirildi."]);
    }
}
