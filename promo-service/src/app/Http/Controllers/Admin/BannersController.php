<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaBatchJob;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class BannersController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'       => 'required|array',
            'title.uz'    => 'required|string|max:255',
            'title.ru'    => 'nullable|string|max:255',
            'title.kr'    => 'nullable|string|max:255',

            'banner_type' => 'required|string|in:game,promotion,url', // kerakli type’larni belgilab ol
            'url'         => 'required|string',

            'media'       => 'required|array',
            'media.*'     => 'file|mimes:jpg,jpeg,png,gif,webp,mp4,webm|max:10240',
        ]);

        // 🔹 1. Banner yaratamiz
        $banner = Banner::create([
            'title'       => $validated['title'],
            'banner_type' => $validated['banner_type'],
            'url'         => $validated['url'] ?? null,
            'status'      => true, // default holatda active
        ]);

        // 🔹 2. Media fayllarni vaqtincha saqlash
        if ($request->hasFile('media')) {
            $tempPaths = [];
            foreach ($request->file('media') as $langKey => $file) {
                if ($file instanceof \Illuminate\Http\UploadedFile) {
                    $tempPath            = $file->store("tmp/banners/{$banner->id}", 'public');
                    $tempPaths[$langKey] = $tempPath;

                    Log::info("📎 Banner media yuklandi: {$langKey}", [
                        'path'     => $tempPath,
                        'filename' => $file->getClientOriginalName(),
                    ]);
                }
            }

            // 🔹 3. Media fayllarni RabbitMQ job orqali asosiy storage’ga yuborish
            if (! empty($tempPaths)) {
                Queue::connection('rabbitmq')->push(new StoreUploadedMediaBatchJob($tempPaths, 'banners', $banner->id));
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Banner muvaffaqiyatli saqlandi.',
            'id'      => $banner->id,
        ]);
    }
public function data(Request $request)
{
    $query = Banner::query();

    return DataTables::of($query)
        ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 20))
        ->addColumn('banner_type', fn($item) => ucfirst($item->banner_type))
        ->addColumn('url', fn($item) => Str::limit($item->url ?? '-', 30))
        ->addColumn('media', function ($item) {
            $uzMedia = $item->media['uz']['url'] ?? null;
            return $uzMedia
                ? "<img src='{$uzMedia}' alt='banner' style='max-height:40px'>"
                : '-';
        })
        ->addColumn('status', fn($item) =>
            $item->status
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
        )
        ->addColumn('created_at', fn($item) => optional($item->created_at)->format('d.m.Y H:i') ?? '-')
        ->addColumn('actions', function ($row) {
            // 🔹 Har bir route uchun to‘liq URL yuboramiz
            $routes = [
                'edit'   => route('admin.banners.edit', $row->id),
                'status' => route('admin.banners.status', $row->id),
                'delete' => route('admin.banners.delete', $row->id),
            ];
            return view('admin.actions', compact('row', 'routes'))->render();
        })
        ->rawColumns(['media', 'status', 'actions'])
        ->make(true);
}

    public function changeStatus(Banner $banner)
    {
        $banner->status = !$banner->status;
        $banner->save();

        return response()->json(['success' => true, 'message' => 'Status muvaffaqiyatli yangilandi!']);
    }

    public function destroy(Banner $banner)
    {
        Log::info('Banner deleted', ['banner_id' => $banner->id]);

        $banner->delete();

        return response()->json(['success' => true, 'message' => 'Banner muvaffaqiyatli o‘chirildi!']);
    }
}
