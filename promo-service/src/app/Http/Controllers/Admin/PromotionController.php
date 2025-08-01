<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\StoreUploadedMediaBatchJob;
use App\Jobs\StoreUploadedMediaJob;
use App\Models\Company;
use App\Models\ParticipationType;
use App\Models\Platform;
use App\Models\PlatformPromotion;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PromotionController extends Controller
{
    public function __construct()
    {

    }
    public function companydata(Request $request, $id)
    {

        $query = Promotions::with([
            'company:id,name',
            'platforms:id,name',                                 // platforma nomlarini olish uchun
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
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/promotion/{$row->id}/edit",
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
            'platforms:id,name',                                 // platforma nomlarini olish uchun
            'participationTypes:id,name,slug', // qo‘shilgan aloqador turlar
        ])->select('promotions.*');
        // Log::info('Promotion', ['promo' => $query->get()]);
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
                    'row'    => $row,
                    'routes' => [
                        'edit'   => "/admin/promotion/{$row->id}/edit",
                        'delete' => "/admin/promotion/{$row->id}/delete",
                        'public' => "/admin/promotion/{$row->id}/public",
                        'status' => "/admin/promotion/{$row->id}/status",
                    ],
                ])->render();
            })
            ->rawColumns(['status', 'is_public', 'actions'])
            ->make(true);
    }
    public function changeStatus(Request $request, $id)
    {
        $data         = Promotions::findOrFail($id);
        $data->status = ! $data->status;
        $data->save();
        Log::info('User status changed', [
            'user_id'    => $data->id,
            'new_status' => $data->status,
        ]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status'  => $data->status,
        ]);
    }
    public function changePublic(Request $request, $id)
    {
        $data            = Promotions::findOrFail($id);
        $data->is_public = ! $data->is_public;
        $data->save();
        Log::info('User status changed', [
            'user_id'    => $data->id,
            'new_status' => $data->is_public,
        ]);
        return response()->json([
            'message' => 'Status yangilandi',
            'status'  => $data->status,
        ]);
    }

    public function delete(Request $request, $id)
    {
        $user = Promotions::findOrFail($id);
        Log::info(message: "so'rov keldi delete");

        $user->delete();
        return redirect()->back()->with('success', 'Promotion o‘chirildi.');
    }

    public function create()
    {
        $companies = Company::select('id', 'name', 'status')->where('status', 'active')->get()->map(function ($item) {
            return [
                'id'   => $item->id,
                'name' => $item->getTranslation('name', 'uz'),
            ];
        });
        $partisipants_type = ParticipationType::pluck('id', 'name')->toArray();
        $platforms         = Platform::pluck('id', 'name')->toArray();
        return response()->json([
            'platforms'         => $platforms,
            'companies'         => $companies,
            'partisipants_type' => $partisipants_type,
        ]);

    }
    public function edit($id)
    {
        $promotion = Promotions::with([

            'participantTypeIds:id,name,slug',
            'company:id,name,status',
        ])->findOrFail($id);

        $companies = Company::select('id', 'name', 'status')
            ->where('status', 'active')
            ->get()
            ->map(fn($item) => [
                'id'   => $item->id,
                'name' => $item->getTranslation('name', 'uz'),
            ]);

        $selectedPlatforms = $promotion->platforms->map(fn($type) => [
            'id'               => $type->id,
            'name'             => $type->name,
            'is_enabled'       => (bool) $type->pivot->is_enabled,
            'promotion_id'     => $type->pivot->promotion_id,
            'platform_id'      => $type->pivot->platform_id,
            'additional_rules' => $type->pivot->additional_rules,
            'phone'            => $type->pivot->phone,
        ]);
        $selectedParticipants = $promotion->participationTypes->map(fn($type) => [
            'id'               => $type->id,
            'name'             => $type->name,
            'is_enabled'       => (bool) $type->pivot->is_enabled,
            'promotion_id'     => $type->pivot->promotion_id,
            'participation_type_id'      => $type->pivot->participation_type_id,
            'additional_rules' => $type->pivot->additional_rules,
        ]);

        $availablePlatforms = Platform::whereNotIn('id', $selectedPlatforms->pluck('id'))
            ->pluck('id', 'name')
            ->toArray();

        $availableParticipants = ParticipationType::whereNotIn('id', $selectedParticipants->pluck('id'))
            ->pluck('id', 'name')
            ->toArray();

        return response()->json([
            'promotion'         => [
                'id'                 => $promotion->id,
                'name'               => [
                    'uz' => $promotion->getTranslation('name', 'uz'),
                    'ru' => $promotion->getTranslation('name', 'ru'),
                    'kr' => $promotion->getTranslation('name', 'kr'),
                ],
                'title'              => [
                    'uz' => $promotion->getTranslation('title', 'uz'),
                    'ru' => $promotion->getTranslation('title', 'ru'),
                    'kr' => $promotion->getTranslation('title', 'kr'),
                ],
                'description'        => [
                    'uz' => $promotion->getTranslation('description', 'uz'),
                    'ru' => $promotion->getTranslation('description', 'ru'),
                    'kr' => $promotion->getTranslation('description', 'kr'),
                ],
                'company_id'         => $promotion->company_id,
                'start_date'         => optional($promotion->start_date)->toDateTimeString(),
                'end_date'           => optional($promotion->end_date)->toDateTimeString(),
                'status'             => (bool) $promotion->status,
                'banner'             => $promotion->banner,
                'offer'              => $promotion->offer,
                'gallery'            => $promotion->gallery,
                'is_public'          => (bool) $promotion->is_public,
                'winning_strategy' => $promotion->winning_strategy,
                'created_by_user_id' => $promotion->created_by_user_id,
                'platforms'          => $selectedPlatforms,
                'participants_type'  => $selectedParticipants,
            ],
            'platforms'         => $availablePlatforms,
            'companies'         => $companies,
            'partisipants_type' => $availableParticipants,
        ]);
    }
    public function store(Request $request)
    {

        $validated = $request->validate([
            'name'                => 'required|array',
            'title'               => 'required|array',
            'description'         => 'required|array',
            'company_id'          => 'required|exists:companies,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',

            'participants_type'   => 'nullable|array',
            'participants_type.*' => 'integer',

            'platforms'           => 'nullable|array',
            'platforms.*'         => 'integer',

            'offer_file'          => 'required|file|mimes:pdf,doc,docx,odt,rtf,txt|max:5120',
            'media_preview'       => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:5120',
            'media_gallery'       => 'required|array|max:10',
            'media_gallery.*'     => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:240480',
            'created_by_user_id'  => 'required|integer',
            'winning_strategy' => 'required|in:immediate,delayed,hybrid',
            'status'              => 'nullable|boolean',
            'is_public'           => 'nullable|boolean',
        ]);
        $promotion = Promotions::create([
            'name'               => $validated['name'],
            'title'              => $validated['title'],
            'description'        => $validated['description'],
            'company_id'         => $validated['company_id'],
            'start_date'         => $validated['start_date'] ?? null,
            'end_date'           => $validated['end_date'] ?? null,
            'status'             => $request->boolean('status'),
            'created_by_user_id' => $validated['created_by_user_id'],
            'is_public'          => $request->boolean('is_public'),
            'winning_strategy' => $validated['winning_strategy'],
        ]);
        $platformData = collect($validated['platforms'] ?? [])->mapWithKeys(function ($platformId) {
            return [$platformId => [
                'is_enabled'       => true, // default: true
                'additional_rules' => null, // yoki ['min' => 1, 'max' => 3] kabi rule qo‘shsa bo‘ladi
            ]];
        })->toArray();
        $promotion->platformIds()->sync($platformData);
        $participantData = collect($validated['participants_type'] ?? [])->mapWithKeys(function ($typeId) {
            return [$typeId => [
                'is_enabled'       => true,
                'additional_rules' => null,
            ]];
        })->toArray();
        $promotion->participantTypeIds()->sync($participantData);
        if ($request->hasFile('offer_file')) {
            $file     = $request->file('offer_file');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 Offer file mavjud. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-offer', $promotion->id));
        }

        if ($request->hasFile('media_preview')) {
            $file     = $request->file('media_preview');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 media_preview mavjud. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-banner', $promotion->id));
        }

        if ($request->hasFile('media_gallery')) {
            $tempPaths = [];
            Log::info('📎 Media galereya fayllari mavjud. Fayllar soni: ' . count($request->file('media_gallery')));
            foreach ($request->file('media_gallery') as $index => $file) {
                $tempPath    = $file->store('tmp', 'public');
                $tempPaths[] = $tempPath;
            }
            Log::info("📎 Media galereya fayllari: ", ['Paths' => $tempPaths]);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaBatchJob($tempPaths, 'promotion-gallary', $promotion->id));
        }

        return response()->json([
            'message'                    => 'Promoaksiya muvaffaqiyatli saqlandi.',
            'id'                         => $promotion->id,
            'platform'                   => $promotion->platformIds,
            'attached_participants_type' => $promotion->participantTypeIds,
        ]);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name'                    => 'required|array',
            'title'                   => 'required|array',
            'description'             => 'required|array',
            'company_id'              => 'required|exists:companies,id',
            'start_date'              => 'nullable|date',
            'end_date'                => 'nullable|date|after_or_equal:start_date',

            'participants_type_new'   => 'nullable|array',
            'participants_type_new.*' => 'integer',

            'platforms_new'           => 'nullable|array',
            'platforms_new.*'         => 'integer',
            'created_by_user_id'      => 'required|string|max:255',

            'offer_file'              => 'nullable|file|mimes:pdf,doc,docx,odt,rtf,txt|max:5120',
            'media_preview'           => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:5120',
            'media_gallery'           => 'nullable|array|max:10',
            'media_gallery.*'         => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:240480',
            'winning_strategy' => 'required|in:immediate,delayed,hybrid',
            'status'                  => 'nullable|boolean',
            'is_public'               => 'nullable|boolean',
        ]);

        $promotion = Promotions::findOrFail($id);

        $promotion->update([
            'name'               => $validated['name'],
            'title'              => $validated['title'],
            'description'        => $validated['description'],
            'company_id'         => $validated['company_id'],
            'start_date'         => $validated['start_date'] ?? null,
            'end_date'           => $validated['end_date'] ?? null,
            'status'             => $request->boolean('status'),
            'is_public'          => $request->boolean('is_public'),
            'created_by_user_id' => $validated['created_by_user_id'],
            'winning_strategy'   => $validated['winning_strategy'],
        ]);
        $platformData = collect($validated['platforms_new'] ?? [])->mapWithKeys(function ($platformId) {
            return [$platformId => [
                'is_enabled'       => true, // default: true
                'additional_rules' => null, // yoki ['min' => 1, 'max' => 3] kabi rule qo‘shsa bo‘ladi
            ]];
        })->toArray();
        $promotion->platformIds()->syncWithoutDetaching($platformData);
        $participantData = collect($validated['participants_type_new'] ?? [])->mapWithKeys(function ($typeId) {
            return [$typeId => [
                'is_enabled'       => true,
                'additional_rules' => null,
            ]];
        })->toArray();
        $promotion->participantTypeIds()->syncWithoutDetaching($participantData, );
        Log::info('Promotion updated: ', [' $platformData' => $platformData, ' $participantData' => $participantData]);
        if ($request->hasFile('offer_file')) {
            $file     = $request->file('offer_file');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 Offer file mavjud. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-offer', $promotion->id));
        }

        if ($request->hasFile('media_preview')) {
            $file     = $request->file('media_preview');
            $tempPath = $file->store('tmp', 'public');
            Log::info("📎 media_preview mavjud. Yuklanmoqda..." . $tempPath);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaJob($tempPath, 'promotion-banner', $promotion->id));
        }

        if ($request->hasFile('media_gallery')) {
            $tempPaths = [];
            Log::info('📎 Media galereya fayllari mavjud. Fayllar soni: ' . count($request->file('media_gallery')));
            foreach ($request->file('media_gallery') as $index => $file) {
                $tempPath    = $file->store('tmp', 'public');
                $tempPaths[] = $tempPath;
            }
            Log::info("📎 Media galereya fayllari: ", ['Paths' => $tempPaths]);
            Queue::connection('rabbitmq')->push(new StoreUploadedMediaBatchJob($tempPaths, 'promotion-gallary', $promotion->id));
        }

        // Platform/participants agar mavjud bo‘lsa
        // $promotion->platforms()->sync($validated['platforms'] ?? []);
        // $promotion->participants()->sync($validated['participants_type'] ?? []);

        return response()->json([
            'message' => 'Promoaksiya muvaffaqiyatli yangilandi.',
            'id'      => $promotion->id,
        ]);
    }
    public function updateParticipantType(Request $request, $promotionId, $participantTypeId)
    {
        Log::info('🎯 Participant Type Update Request:', [
            'promotion_id'        => $promotionId,
            'participant_type_id' => $participantTypeId,
            'request_data'        => $request->all(),
        ]);

        $request->merge([
            'is_enabled' => $request->has('is_enabled'),
        ]);

        $validated = $request->validate([
            'is_enabled'       => 'boolean',
            'additional_rules' => 'nullable|string',
        ]);

        $additionalRules = $validated['additional_rules'] ?? null;
        if ($additionalRules) {
            $decoded         = json_decode($additionalRules, true);
            $additionalRules = json_last_error() === JSON_ERROR_NONE ? $decoded : $additionalRules;
        }

        $updated = DB::table('promotion_participation_types')->updateOrInsert(
            [
                'promotion_id'          => $promotionId,
                'participation_type_id' => $participantTypeId,
            ],
            [
                'is_enabled'       => $validated['is_enabled'],
                'additional_rules' => is_array($additionalRules) ? json_encode($additionalRules) : $additionalRules,
                'updated_at'       => now(),
            ]
        );

        Log::info('✅ Participant Type Updated or Inserted:', [
            'updated'          => $updated,
            'is_enabled'       => $validated['is_enabled'],
            'additional_rules' => $additionalRules,
        ]);
        return response()->json(['success'=> 'Ishtirok turi yangilandi.']);
    }
   public function updatePlatform(Request $request,  $promotionId, $platformId)
    {
     Log::info('🎯 Participant Type Update Request:', [
    'promotion_id'        => $promotionId,
    'participant_type_id' => $platformId,
    'request_data'        => $request->all(),
]);

        $validated = $request->validate([
            'promotion_id' => 'required|integer|exists:promotions,id',
            'platform_id' => 'required|integer|exists:platforms,id',
            'is_enabled' => 'nullable|boolean',
            'additional_rules' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Normalize
        $isEnabled = filter_var($validated['is_enabled'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $rules = $validated['additional_rules'] ?? '{}';

        // Optional: validate JSON format
        if (!is_array(json_decode($rules, true))) {
            return response()->json(['message' => 'Invalid additional_rules JSON format.'], 422);
        }

        // Save or update pivot
        PlatformPromotion::updateOrCreate(
            [
                'promotion_id' => $validated['promotion_id'],
                'platform_id' => $validated['platform_id'],
            ],
            [
                'is_enabled' => $isEnabled,
                'additional_rules' => $rules,
            ]
        );

        return response()->json(['message' => 'Platform settings updated.']);
    }
}
