<?php
namespace App\Http\Controllers;

use App\Jobs\StoreUploadedMediaJob;
use App\Models\Notification;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Yajra\DataTables\DataTables;

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
        $phones  = $query
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
        $validated = $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $notification = Notification::create([
                'title'        => $validated['title'],
                'text'         => $validated['text'],
                'target_type'  => $validated['target_type'],
                'link_type'    => $validated['link_type'],
                'link'         => $validated['link'] ?? null,
                'status'       => ! empty($validated['scheduled_at']) ? 'scheduled' : 'draft',
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);
            $this->handleMedia($request, $notification);
            $this->handleTargets($validated, $request, $notification);
            DB::commit();
            return response()->json([
                'message'      => 'Notification muvaffaqiyatli yaratildi',
                'notification' => $notification->load(['platforms', 'users', 'excel']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['error' => 'Xatolik', 'details' => $e->getMessage()], 500);
        }
    }
    public function update(Request $request, int $id)
    {
        $validated = $request->validate($this->rules(isUpdate: true, id: $id));

        DB::beginTransaction();
        try {
            $notification = Notification::findOrFail($id);

            $notification->update([
                'title'        => $validated['title'],
                'text'         => $validated['text'],
                'target_type'  => $validated['target_type'],
                'link_type'    => $validated['link_type'],
                'link'         => $validated['link'] ?? null,
                'scheduled_at' => $validated['scheduled_at'] ?? null,
            ]);

            $this->handleMedia($request, $notification, true);
            $notification->platforms()->delete();
            $notification->users()->delete();
            $notification->excel()?->delete();
            $this->handleTargets($validated, $request, $notification);

            DB::commit();
            return response()->json([
                'message'      => 'Notification yangilandi',
                'notification' => $notification->load(['platforms', 'users', 'excel']),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()->json(['error' => 'Xatolik', 'details' => $e->getMessage()], 500);
        }
    }
    public function data(Request $request)
    {
        $query = Notification::with([
            'platforms:id,notification_id,platform',
            'users:id,notification_id,phone,status',
            'excel:id,notification_id,total_rows,processed_rows',
        ]);

        return DataTables::of($query)
        // === Asosiy ustunlar ===
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 25))
            ->addColumn('text', fn($item) => Str::limit($item->getTranslation('text', 'uz') ?? '-', 35))

        // === Target Type ===
            ->addColumn('target_type', fn($item) => ucfirst($item->target_type))
            ->addColumn('recipients', function ($item) {
                if ($item->target_type === 'platform') {
                    return 'Platformalar: ' . ($item->platforms->pluck('platform')->implode(', ') ?: '-');
                }

                if ($item->target_type === 'users') {
                    return 'Users: ' . ($item->users->count() ?: '-');
                }

                if ($item->target_type === 'excel' && $item->excel) {
                    return "Excel Rows: {$item->excel->processed_rows}/{$item->excel->total_rows}";
                }

                return '-';
            })

        // === Link Type & Link ===
            ->addColumn('link_type', fn($item) => ucfirst($item->link_type))
            ->addColumn('link', fn($item) => $item->link ?? '-')

        // === Status ===
            ->addColumn('status', function ($item) {
                return match ($item->status) {
                    'draft' => '<span class="badge bg-secondary">Draft</span>',
                    'pending' => '<span class="badge bg-warning">Kutilmoqda</span>',
                    'sent'    => '<span class="badge bg-success">Yuborilgan</span>',
                    'failed'  => '<span class="badge bg-danger">Xato</span>',
                    default   => '<span class="badge bg-dark">' . e($item->status) . '</span>',
                };
            })

        // === Scheduled Date ===
            ->addColumn('scheduled_at', fn($item) => $item->scheduled_at ? $item->scheduled_at->format('d.m.Y H:i') : '-')

        // === Image ===
            ->addColumn('image', fn($item) => $item->image ? '<img src="' . $item->image . '" alt="image" style="max-width:60px;max-height:60px;">' : '-')

        // === Actions ===
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/notifications/{$row->id}/edit",
                        'delete' => "/admin/notifications/{$row->id}/delete",
                        'send'   => "/admin/notifications/{$row->id}/send",
                    ],
                ])->render();
            })

            ->rawColumns(['status', 'actions', 'image'])
            ->make(true);
    }
    public function destroy(int $id)
    {
        try {
            $notification = Notification::findOrFail($id);

            // Soft delete qilish
            $notification->delete();

            return response()->json([
                'message' => 'Notification muvaffaqiyatli o‘chirildi',
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Notification topilmadi',
            ], 404);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error'   => 'Notification o‘chirishda xatolik yuz berdi.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    private function rules($isUpdate = false, $id = null): array
    {
        $linkRule = $isUpdate ? 'nullable' : 'required';

        $excelRule = 'required_if:target_type,excel|file|mimes:xlsx,xls,csv|max:20480';

        if ($isUpdate && $id) {
            $notification = Notification::find($id);

            if ($notification) {
                $oldType = $notification->target_type;
                $newType = request('target_type');

                // Agar eski ham yangi ham excel bo‘lsa → majburiy emas
                if ($oldType === 'excel' && $newType === 'excel') {
                    $excelRule = 'nullable|file|mimes:xlsx,xls,csv|max:20480';
                }
            }
        }

        return [
            'title'        => 'required|array',
            'title.uz'     => 'required|string|max:255',
            'title.ru'     => 'required|string|max:255',
            'title.kr'     => 'required|string|max:255',

            'text'         => 'required|array',
            'text.uz'      => 'required|string',
            'text.ru'      => 'required|string',
            'text.kr'      => 'required|string',

            'target_type'  => 'required|string|in:platform,users,excel',
            'type'         => 'required_if:target_type,platform|array|min:1',
            'type.*'       => 'in:ios,android,web,telegram',
            'users'        => 'required_if:target_type,users|array|min:1',
            'users.*'      => 'string|regex:/^\+?[0-9]{7,15}$/',

            'excel_file'   => $excelRule,

            'link_type'    => 'required|string|in:game,promotion,url,message',
            'link'         => 'required_unless:link_type,message|string|max:255',

            'media'        => ($isUpdate ? 'nullable' : 'required') . '|file|mimes:jpg,jpeg,png,gif,webp|max:20480',
            'scheduled_at' => 'nullable|date',
        ];
    }
    private function handleMedia(Request $request, Notification $notification, bool $isUpdate = false)
    {
        if ($request->hasFile('media')) {
            if ($isUpdate) {
                $notification->clearMediaCollection('notification-image');
            }
            $file     = $request->file('media');
            $tempPath = $file->storeAs('tmp', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'notification-image', $notification->id));
        }
    }
    public function edit(int $id)
    {
        try {
            // === Notification with relations ===
            $notification = Notification::with([
                'platforms:id,notification_id,platform',
                'users:id,notification_id,phone,status',
                'excel:id,notification_id,total_rows,processed_rows',
            ])->findOrFail($id);

            // === Selected users for select2 ===
            $selectedUsers = $notification->users->map(fn($u) => [
                'id'   => $u->phone,
                'text' => $u->phone,
            ])->toArray();

            // === Response ===
            return response()->json([
                'message'        => 'Notification topildi',
                'notification'   => $notification,
                'selected_users' => $selectedUsers,
            ]);

            /**
             * Agar JSON emas, balki Blade page qaytarilsa:
             *
             * return view('admin.notifications.edit', [
             *     'notification'   => $notification,
             *     'selectedUsers'  => $selectedUsers,
             *     'isEdit'         => true,
             * ]);
             */
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Notification topilmadi',
            ], 404);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error'   => 'Notificationni olishda xatolik yuz berdi.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    private function handleTargets(array $validated, Request $request, Notification $notification)
    {
        if ($validated['target_type'] === 'platform') {
            foreach ($validated['type'] as $platform) {
                $notification->platforms()->create(['platform' => $platform]);
            }
        }
        if ($validated['target_type'] === 'users') {
            foreach (array_unique($validated['users']) as $phone) {
                $notification->users()->create(['phone' => $phone, 'status' => 'pending']);
            }
        }
        if ($validated['target_type'] === 'excel' && $request->hasFile('excel_file')) {
            $file     = $request->file('excel_file');
            $tempPath = $file->storeAs('tmp', uniqid() . '.' . $file->getClientOriginalExtension(), 'public');
            $excel    = $notification->excel()->create(['total_rows' => 0, 'processed_rows' => 0]);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'notification-excel', $excel->id));
        }
    }
}
