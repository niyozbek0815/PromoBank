<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaBatchJob;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Company;
use App\Models\Messages;
use App\Models\ParticipationType;
use App\Models\Platform;
use App\Models\PlatformPromotion;
use App\Models\PrizeCategory;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    public function __construct()
    {

    }
    public function getTypes(Request $request)
    {
        $promotions = Promotions::where('is_public', true)
            ->where('status', true)
            ->get(['id', 'name']);

        $data =
            $promotions->map(function ($promo) {
                return [
                    'value' => $promo->id,
                    'label' => $promo->getTranslation('name', 'uz'),
                ];
            })->toArray();
        return response()->json($data);
    }
    public function companydata(Request $request, $id)
    {

        $query = Promotions::with([
            'company:id,name',
            'platforms:id,name',          // platforma nomlarini olish uchun
            'participationTypes:id,name', // qo‘shilgan aloqador turlar
        ])->where('company_id', $id)
            ->select('promotions.*');

        return DataTables::of($query)
            ->addColumn('name', fn($item) => Str::limit($item->getTranslation('name', 'uz') ?? '-', 15))
            ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 20))
            ->addColumn('description', fn($item) => Str::limit($item->getTranslation('description', 'uz') ?? '-', 25))
            ->addColumn('platform_names', function ($item) {
                return $item->platforms->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn('participant_types', function ($item) {
                return $item->participationTypes->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn(
                'status',
                fn($item) => $item->status
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
            )
            ->addColumn(
                'is_public',
                fn($item) => $item->is_public
                ? '<i class="ph ph-check-circle text-success"></i>'
                : '<i class="ph ph-x-circle text-danger"></i>'
            )
            ->addColumn('start_date', fn($item) => optional($item->start_date)->format('d.m.Y') ?? '-')
            ->addColumn('end_date', fn($item) => optional($item->end_date)->format('d.m.Y') ?? '-')
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row' => $row,
                    'routes' => [
                        'edit' => "/admin/promotion/{$row->id}/edit",
                        'delete' => "/admin/promotion/{$row->id}/delete",
                        'public' => "/admin/promotion/{$row->id}/public",
                        'status' => "/admin/promotion/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'is_public', 'actions'])
            ->make(true);
    }
    public function data(Request $request)
    {
        $query = Promotions::with([
            'company:id,name',
            'platforms:id,name',               // platforma nomlarini olish uchun
            'participationTypes:id,name,slug', // qo‘shilgan aloqador turlar
        ])->select('promotions.*');
        return DataTables::of($query)
            ->addColumn('name', fn($item) => Str::limit($item->getTranslation('name', 'uz') ?? '-', 15))
            ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 20))
            ->addColumn('description', fn($item) => Str::limit($item->getTranslation('description', 'uz') ?? '-', 25))
            ->addColumn('company_name', fn($item) => Str::limit($item->company->getTranslation('name', 'uz') ?? '-', 15))
            ->addColumn('platform_names', function ($item) {
                return $item->platforms->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn('participant_types', function ($item) {
                return $item->participationTypes->pluck('name')->implode(', ') ?: '-';
            })
            ->addColumn(
                'status',
                fn($item) => $item->status
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
            )
            ->addColumn(
                'is_public',
                fn($item) => $item->is_public
                ? '<i class="ph ph-check-circle text-success"></i>'
                : '<i class="ph ph-x-circle text-danger"></i>'
            )
            ->addColumn('start_date', fn($item) => optional($item->start_date)->format('d.m.Y') ?? '-')
            ->addColumn('end_date', fn($item) => optional($item->end_date)->format('d.m.Y') ?? '-')
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row' => $row,
                    'routes' => [
                        'edit' => "/admin/promotion/{$row->id}/edit",
                        'delete' => "/admin/promotion/{$row->id}/delete",
                        'public' => "/admin/promotion/{$row->id}/public",
                        'status' => "/admin/promotion/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'is_public', 'actions'])
            ->make(true);
    }









    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'name.uz' => 'required|string|max:255',
            'name.ru' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'name.kr' => 'required|string|max:255',

            // Sarlavha
            'title' => 'required|array',
            'title.uz' => 'required|string|max:255',
            'title.ru' => 'required|string|max:255',
            'title.en' => 'required|string|max:255',
            'title.kr' => 'required|string|max:255',

            // Tavsif
            'description' => 'required|array',
            'description.uz' => 'required|string',
            'description.ru' => 'required|string',
            'description.en' => 'required|string',
            'description.kr' => 'required|string',
            'company_id' => 'required|exists:companies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'participants_type' => 'nullable|array',
            'participants_type.*' => 'integer',

            'platforms' => 'nullable|array',
            'platforms.*' => 'integer',

            'offer_file' => 'required|file|mimes:pdf,doc,docx,odt,rtf,txt|max:2048',
            'media_preview' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:512',
            'media_gallery' => 'required|array|max:10',
            'media_gallery.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:240480',
            'created_by_user_id' => 'required|integer',
            'winning_strategy' => 'required|in:immediate,delayed,hybrid',
            'status' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $stored = [
            'offer_file' => null,
            'media_preview' => null,
            'media_gallery' => [],
        ];

        try {
            if ($request->hasFile('offer_file')) {
                $stored['offer_file'] = $request->file('offer_file')->store('tmp', 'public');
            }
            if ($request->hasFile('media_preview')) {
                $stored['media_preview'] = $request->file('media_preview')->store('tmp', 'public');
            }
            if ($request->hasFile('media_gallery')) {
                foreach ($request->file('media_gallery') as $file) {
                    $stored['media_gallery'][] = $file->store('tmp', 'public');
                }
            }
        } catch (Throwable $e) {
            // Fayl saqlashda muammo bo'lsa, allaqachon saqlangan tmp fayllarni o'chiramiz va xato qaytaramiz
            $this->cleanupTempFiles($stored);
            Log::error('File store error in Promotion::store', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Faylni saqlashda xatolik yuz berdi.'], 500);
        }
        try {
            DB::beginTransaction();
            $promotion = Promotions::create([
                'name' => $validated['name'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'company_id' => $validated['company_id'],
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
                'status' => $request->boolean('status'),
                'created_by_user_id' => $validated['created_by_user_id'],
                'is_public' => $request->boolean('is_public'),
                'winning_strategy' => $validated['winning_strategy'],
            ]);

            $platformData = collect($validated['platforms'] ?? [])->mapWithKeys(fn($platformId) => [
                $platformId => [
                    'is_enabled' => true,
                    'additional_rules' => null,
                ]
            ])->toArray();

            $promotion->platformIds()->sync($platformData);

            $participantData = collect($validated['participants_type'] ?? [])->mapWithKeys(fn($typeId) => [
                $typeId => [
                    'is_enabled' => true,
                    'additional_rules' => null,
                ]
            ])->toArray();

            $promotion->participantTypeIds()->sync($participantData);

            DB::commit();
        } catch (Throwable $e) {
            DB::rollBack();
            // agar DB yaratilishi xato bo'lsa, tmp fayllarni ham tozalaymiz
            $this->cleanupTempFiles($stored);
            Log::error('DB error while creating Promotion', ['error' => $e->getMessage(), 'validated' => $validated, 'trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Promoaksiya yaratishda xatolik yuz berdi.'], 500);
        }

        // 4) Transaction muvaffaqiyatli tugagach — faylni qayta ishlash job-larini navbatga qo'yamiz.
        // (Agar queue push xato bersa, promotion saqlangan bo'ladi — bu holatda log va ogohlantirish yetkaziladi.)
        $queued = [];
        try {
            if ($stored['offer_file']) {
                Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($stored['offer_file'], 'promotion-offer', $promotion->id));
                $queued[] = 'offer_file';
            }

            if ($stored['media_preview']) {
                Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($stored['media_preview'], 'promotion-banner', $promotion->id));
                $queued[] = 'media_preview';
            }

            if (!empty($stored['media_gallery'])) {
                Queue::connection('rabbitmq')->push(new StoreUploadedMediaBatchJob($stored['media_gallery'], 'promotion-gallery', $promotion->id));
                $queued[] = 'media_gallery';
            }
        } catch (Throwable $e) {
            // Job navbatga qo'yishda xato bo'ldi — bu jiddiy, ammo promotion mavjud. Log qilamiz va foydalanuvchini xabarlaymiz.
            Log::error('Queue push failed for Promotion media jobs', ['promotion_id' => $promotion->id, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return response()->json([
                'message' => 'Promoaksiya saqlandi, ammo media fayllarni qayta ishlash uchun navbatga qo‘yishda xato yuz berdi. Iltimos admin bilan bog‘laning.',
                'id' => $promotion->id
            ], 201);
        }

        // 5) Hammasi muvaffaqiyatli bo'lsa — javob qaytaramiz
        return response()->json([
            'message' => 'Promoaksiya muvaffaqiyatli saqlandi va media job-lar navbatga qo‘yildi.',
            'id' => $promotion->id,
            'queued_media' => $queued,
            'platform' => $promotion->platformIds,
            'attached_participants_type' => $promotion->participantTypeIds,
        ], 201);
    }

    protected function cleanupTempFiles(array $stored): void
    {

        try {
            if (!empty($stored['offer_file'])) {
                Storage::disk('public')->delete($stored['offer_file']);
            }
            if (!empty($stored['media_preview'])) {
                Storage::disk('public')->delete($stored['media_preview']);
            }
            if (!empty($stored['media_gallery'])) {
                foreach ($stored['media_gallery'] as $p) {
                    Storage::disk('public')->delete($p);
                }
            }
        } catch (Throwable $e) {
            Log::warning('Failed to cleanup temp files', ['error' => $e->getMessage()]);
        }
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|array',
            'title' => 'required|array',
            'description' => 'required|array',
            'company_id' => 'required|exists:companies,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',

            'participants_type_new' => 'nullable|array',
            'participants_type_new.*' => 'integer',

            'platforms_new' => 'nullable|array',
            'platforms_new.*' => 'integer',
            'created_by_user_id' => 'required|string|max:255',

            'offer_file' => 'nullable|file|mimes:pdf,doc,docx,odt,rtf,txt|max:5120',
            'media_preview' => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:5120',
            'media_gallery' => 'nullable|array|max:10',
            'media_gallery.*' => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:240480',
            'winning_strategy' => 'required|in:immediate,delayed,hybrid',
            'status' => 'nullable|boolean',
            'is_public' => 'nullable|boolean',
        ]);

        $promotion = Promotions::findOrFail($id);

        $promotion->update([
            'name' => $validated['name'],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'company_id' => $validated['company_id'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $request->boolean('status'),
            'is_public' => $request->boolean('is_public'),
            'created_by_user_id' => $validated['created_by_user_id'],
            'winning_strategy' => $validated['winning_strategy'],
        ]);
        $platformData = collect($validated['platforms_new'] ?? [])->mapWithKeys(function ($platformId) {
            return [
                $platformId => [
                    'is_enabled' => true, // default: true
                    'additional_rules' => null, // yoki ['min' => 1, 'max' => 3] kabi rule qo‘shsa bo‘ladi
                ]
            ];
        })->toArray();
        $promotion->platformIds()->syncWithoutDetaching($platformData);
        $participantData = collect($validated['participants_type_new'] ?? [])->mapWithKeys(function ($typeId) {
            return [
                $typeId => [
                    'is_enabled' => true,
                    'additional_rules' => null,
                ]
            ];
        })->toArray();
        $promotion->participantTypeIds()->syncWithoutDetaching($participantData, );
        if ($request->hasFile('offer_file')) {
            $file = $request->file('offer_file');
            $tempPath = $file->store('tmp', 'public');
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-offer', $promotion->id));
        }

        if ($request->hasFile('media_preview')) {
            $file = $request->file('media_preview');
            $tempPath = $file->store('tmp', 'public');
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-banner', $promotion->id));
        }

        if ($request->hasFile('media_gallery')) {
            $tempPaths = [];
            foreach ($request->file('media_gallery') as $index => $file) {
                $tempPath = $file->store('tmp', 'public');
                $tempPaths[] = $tempPath;
            }
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaBatchJob($tempPaths, 'promotion-gallery', $promotion->id));
        }

        // Platform/participants agar mavjud bo‘lsa
        // $promotion->platforms()->sync($validated['platforms'] ?? []);
        // $promotion->participants()->sync($validated['participants_type'] ?? []);

        return response()->json([
            'message' => 'Promoaksiya muvaffaqiyatli yangilandi.',
            'id' => $promotion->id,
        ]);
    }
    public function updateParticipantType(Request $request, $promotionId, $participantTypeId)
    {
        $request->merge([
            'is_enabled' => $request->has('is_enabled'),
        ]);
        $validated = $request->validate([
            'is_enabled' => 'boolean',
            'additional_rules' => 'nullable|json',
        ]);
        $additionalRules = $validated['additional_rules'] ?? null;
        if ($additionalRules === '{}' || $additionalRules === '[]') {
            $additionalRules = null; // bazaga bo‘sh yoziladi
        }
       DB::table('promotion_participation_types')->updateOrInsert(
            [
                'promotion_id' => $promotionId,
                'participation_type_id' => $participantTypeId,
            ],
            [
                'is_enabled' => $validated['is_enabled'],
                'additional_rules' => is_array($additionalRules) ? json_encode($additionalRules) : $additionalRules,
                'updated_at' => now(),
            ]
        );
        return response()->json(['success' => 'Ishtirok turi yangilandi.']);
    }



    public function updatePlatform(Request $request, $promotionId, $platformId)
    {
        $platform = Platform::findOrFail($platformId);
        $rules = [
            'promotion_id' => 'required|integer|exists:promotions,id',
            'platform_id' => 'required|integer|exists:platforms,id',
            'is_enabled' => 'nullable|string',
            'additional_rules' => 'nullable|json',
        ];
        if (strtolower($platform->name) === 'sms') {
            $rules['phone'] = [
                'required',
                'string',
                'regex:/^(\+998\d{9}|\d{3,5})$/'
            ];
        } else {
            $rules['phone'] = 'nullable|string';
        }
        $validated = $request->validate($rules);
        $isEnabled = ($validated['is_enabled'] ?? '') === 'on';
        $additionalRules = $validated['additional_rules'] ?? null;
        if ($additionalRules === '{}' || $additionalRules === '[]') {
            $additionalRules = null; // bazaga bo‘sh yoziladi
        }
        PlatformPromotion::updateOrCreate(
            [
                'promotion_id' => $validated['promotion_id'],
                'platform_id' => $validated['platform_id'],
            ],
            [
                'is_enabled' => $isEnabled,
                'additional_rules' => $additionalRules,
                'phone' => (strtolower($platform->type) === 'sms' || strtolower($platform->name) === 'sms')
                    ? $validated['phone']
                    : null,
            ]
        );
        return response()->json(['message' => 'Platform settings updated.']);
    }
    public function create()
    {
        $companies = Company::select('id', 'name', 'status')->where('status', true)->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->getTranslation('name', 'uz'),
            ];
        });
        $partisipants_type = ParticipationType::pluck('id', 'name')->toArray();
        $platforms = Platform::pluck('id', 'name')->toArray();
        return response()->json([
            'platforms' => $platforms,
            'companies' => $companies,
            'partisipants_type' => $partisipants_type,
        ]);

    }

    public function edit($id)
    {
        $promotion = Promotions::with([
            'participantTypeIds:id,name,slug',
            'company:id,name,status',
            'platforms:id,name',
            'participationTypes:id,name',
        ])->findOrFail($id);
        $messagesExists = Messages::where('scope_type', 'promotion')
            ->where('scope_id', $id)
            ->exists();


        $prizeCategories = PrizeCategory::withCount([
            'prizes as prize_count' => fn($q) => $q->where('promotion_id', $id),
        ])->get(['id', 'name', 'display_name', 'description']);
        $companies = Company::where('status', true)
            ->get(['id', 'name'])
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->getTranslation('name', 'uz'),
            ]);
        $selectedPlatforms = $promotion->platforms->map(fn($p) => $this->mapPlatforms($p));
        $availablePlatforms = Platform::whereNotIn('id', $selectedPlatforms->pluck('id'))
            ->pluck('id', 'name')
            ->toArray();
        $selectedParticipants = $promotion->participationTypes->map(fn($t) => $this->mapParticipants($t));
        $availableParticipants = ParticipationType::whereNotIn('id', $selectedParticipants->pluck('id'))->pluck('id', 'name');
        return response()->json([
            'promotion' => [
                'id' => $promotion->id,
                'company_id' => $promotion->company_id,
                'name' => $promotion->getTranslations('name'),
                'title' => $promotion->getTranslations('title'),
                'description' => $promotion->getTranslations('description'),
                'status' => (bool) $promotion->status,
                'is_public' => (bool) $promotion->is_public,
                'winning_strategy' => $promotion->winning_strategy,
                'start_date' => $promotion->start_date?->toDateTimeString(),
                'end_date' => $promotion->end_date?->toDateTimeString(),
                'created_by_user_id' => $promotion->created_by_user_id,
                'banner' => $promotion->banner,
                'offer' => $promotion->offer,
                'gallery' => $promotion->gallery,
                'platforms' => $promotion->platforms->map(fn($p) => $this->mapPlatforms($p)),
                'participants_type' => $promotion->participationTypes->map(fn($t) => $this->mapParticipants($t))
            ],
            'platforms' => $availablePlatforms,
            'companies' => $companies,
            'partisipants_type' => $availableParticipants,
            'prizeCategories' => $prizeCategories,
            'messagesExists'=>$messagesExists
        ]);
    }
    private function mapPlatforms($platform): array
    {
        return [
            'id' => $platform->id,
            'name' => $platform->name,
            'is_enabled' => (bool) $platform->pivot->is_enabled,
            'promotion_id' => $platform->pivot->promotion_id,
            'platform_id' => $platform->pivot->platform_id,
            'additional_rules' => $platform->pivot->additional_rules,
            'phone' => $platform->pivot->phone,
        ];
    }
    private function mapParticipants($type): array
    {
        return [
            'id' => $type->id,
            'name' => $type->name,
            'is_enabled' => (bool) $type->pivot->is_enabled,
            'promotion_id' => $type->pivot->promotion_id,
            'participation_type_id' => $type->pivot->participation_type_id,
            'additional_rules' => $type->pivot->additional_rules,
        ];
    }
    private function mapTranslations($model, array $fields, array $langs): array
    {
        $data = [];
        foreach ($fields as $field) {
            foreach ($langs as $lang) {
                $data[$field][$lang] = $model->getTranslation($field, $lang);
            }
        }
        return $data;
    }
    public function delete(Request $request, $id)
    {
        $user = Promotions::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Promotion o‘chirildi.');
    }
    public function changeStatus(int $id)
    {
        $promotion = Promotions::findOrFail($id);
        $promotion->update(['status' => !$promotion->status]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status' => $promotion->status,
        ]);
    }
    public function changePublic(int $id)
    {
        $promotion = Promotions::findOrFail($id);
        $promotion->is_public = !$promotion->is_public;
        $promotion->save();
        return response()->json([
            'message' => 'Public status yangilandi',
            'is_public' => $promotion->is_public,
        ]);
    }
}
