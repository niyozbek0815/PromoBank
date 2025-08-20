<?php
namespace App\Http\Controllers;

use App\Jobs\StoreUploadedMediaJob;
use App\Models\Notification;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class NotificationsController extends Controller
{
    public function create()
    {
        return response()->json(['message' => 'Notification created successfully']);
    }
    public function getUsers(Request $request)
    {
        $query = UserDevice::query()
            ->whereNotNull('phone');
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where('phone', 'like', "%{$q}%");
        }
        $perPage = $request->get('per_page', 20);
        $phones = $query
            ->select('phone')
            ->distinct()
            ->orderBy('phone')
            ->paginate($perPage);
        $phones->getCollection()->transform(function ($item) {
            return [
                'id'   => $item->phone,
                'text' => $item->phone,
            ];
        });

        return response()->json($phones);
    }
    public function store(Request $request)
    {
        // === 1. Validatsiya ===
        $validated = $request->validate([
            'title'      => 'required|array',
            'title.uz'   => 'required|string|max:255',
            'title.ru'   => 'nullable|string|max:255',
            'title.kr'   => 'nullable|string|max:255',

            'text'       => 'required|array',
            'text.uz'    => 'required|string',
            'text.ru'    => 'nullable|string',
            'text.kr'    => 'nullable|string',

            'type'       => 'required|array|min:1',        // Bir nechta type kelishi mumkin
            'type.*'     => 'in:ios,android,web,telegram', // Yaroqli platformalar

            'link_type'  => 'required|string|in:game,promotion,url,message',
            'link'       => 'nullable|string|max:255',

            'user_ids'   => 'nullable|array',
            'user_ids.*' => 'integer|exists:users,id',

            'media'      => 'nullable|file|mimes:jpg,jpeg,png,gif,webp|max:20480', // 20MB
        ]);

        DB::beginTransaction();
        try {
            $notifications = [];

            foreach ($validated['type'] as $platform) {
                // === 2. Notification yaratish ===
                $notification = Notification::create([
                    'title'     => $validated['title'], // Spatie avtomatik JSON ga o‘giradi
                    'text'      => $validated['text'],
                    'type'      => $platform,
                    'link_type' => $validated['link_type'],
                    'link'      => $validated['link'] ?? null,
                    // 'user_ids'  => $validated['user_ids'] ?? null, // Remove this line, column does not exist
                ]);

                // === 3. Media biriktirish ===
                if ($request->hasFile('media')) {
                    $file     = $request->file('media');
                    $tempPath = $file->storeAs(
                        'tmp',
                        uniqid() . '.' . $file->getClientOriginalExtension(),
                        'public'
                    );
                    Log::info("📎 Media file mavjud. Yuklanmoqda..." . $tempPath);
                    Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'notification-image', $notification->id));
                }

                $notifications[] = $notification;
            }

            DB::commit();

            return response()->json(['message' => count($notifications) . " ta notification muvaffaqiyatli yaratildi."]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            return response()->json(['error' => 'Notification yaratishda xatolik yuz berdi.'], 500);
        }
    }
}
