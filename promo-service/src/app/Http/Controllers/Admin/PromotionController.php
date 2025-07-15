<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\ParticipationType;
use App\Models\Platform;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'participationTypes.participationType:id,name,slug', // qo‘shilgan aloqador turlar
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
                return $item->participationTypes->pluck('participationType.name')->implode(', ') ?: '-';
            })
            ->addColumn('status', fn($item) => $item->status
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
            )
            ->addColumn('is_public', fn($item) => $item->is_public
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
            'participationTypes.participationType:id,name,slug', // qo‘shilgan aloqador turlar
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
                return $item->participationTypes->pluck('participationType.name')->implode(', ') ?: '-';
            })
            ->addColumn('status', fn($item) => $item->status
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>'
            )
            ->addColumn('is_public', fn($item) => $item->is_public
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

    public function edit($id)
    {
        $data = Promotions::findOrFail($id);
        Log::info('data', ['data' => $data]);
        return response()->json([
            'data' => $data,
        ]);
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
            'created_by_user_id'  => 'required|string|max:255',

            'offer_file'          => 'required|file|mimes:pdf,doc,docx,odt,rtf,txt|max:5120',
            'media_preview'       => 'required|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:5120',
            'media_gallery'       => 'required|array|max:10',
            'media_gallery.*'     => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:20480',

            'status'              => 'nullable|boolean',
            'is_public'           => 'nullable|boolean',
            'is_prize'            => 'nullable|boolean',
        ]);
        $promotion = Promotions::create([
            'name'               => $validated['name'],
            'title'              => $validated['title'],
            'description'        => $validated['description'],
            'company_id'         => $validated['company_id'],
            'start_date'         => $validated['start_date'] ?? null,
            'end_date'           => $validated['end_date'] ?? null,
            'status'             => $request->boolean('status'),
            'is_public'          => $request->boolean('is_public'),
            'is_prize'           => $request->boolean('is_prize'),
            'created_by_user_id' => $validated['created_by_user_id'],
        ]);
        $promotion->platformIds()->sync($validated['platforms'] ?? []);
        $promotion->participantTypeIds()->sync($validated['participants_type'] ?? []);

        if ($request->hasFile('offer_file')) {
            Log::info('📎 Offer file mavjud. Yuklanmoqda...');
            // $promotion->addMediaFromRequest('offer_file')->toMediaCollection('offer');

        }

        if ($request->hasFile('media_preview')) {
            Log::info('📎 Media preview fayl mavjud. Yuklanmoqda...');
            // $promotion->addMediaFromRequest('media_preview')->toMediaCollection('preview');
        }

        if ($request->hasFile('media_gallery')) {
            Log::info('📎 Media galereya fayllari mavjud. Fayllar soni: ' . count($request->file('media_gallery')));
            foreach ($request->file('media_gallery') as $index => $file) {
            }
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
            'created_by_user_id'  => 'required|string|max:255',

            'offer_file'          => 'nullable|file|mimes:pdf,doc,docx,odt,rtf,txt|max:5120',
            'media_preview'       => 'nullable|file|mimes:jpg,jpeg,png,gif,mp4,webm|max:5120',
            'media_gallery'       => 'nullable|array|max:10',
            'media_gallery.*'     => 'file|mimes:jpg,jpeg,png,gif,mp4,webm|max:20480',

            'status'              => 'nullable|boolean',
            'is_public'           => 'nullable|boolean',
            'is_prize'            => 'nullable|boolean',
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
            'is_prize'           => $request->boolean('is_prize'),
            'created_by_user_id' => $validated['created_by_user_id'],
        ]);

        // Fayllar yangilanishi
        if ($request->hasFile('offer_file')) {
            Log::info('📎 Offer fayli yangilanmoqda...');
            // $promotion->clearMediaCollection('offer');
            // $promotion->addMediaFromRequest('offer_file')->toMediaCollection('offer');
        }

        if ($request->hasFile('media_preview')) {
            Log::info('📎 Preview fayli yangilanmoqda...');
            // $promotion->clearMediaCollection('preview');
            // $promotion->addMediaFromRequest('media_preview')->toMediaCollection('preview');
        }

        if ($request->hasFile('media_gallery')) {
            Log::info('📎 Media galereyasi yangilanmoqda...');
            // $promotion->clearMediaCollection('gallery');
            foreach ($request->file('media_gallery') as $file) {
                // $promotion->addMedia($file)->toMediaCollection('gallery');
            }
        }

        // Platform/participants agar mavjud bo‘lsa
        // $promotion->platforms()->sync($validated['platforms'] ?? []);
        // $promotion->participants()->sync($validated['participants_type'] ?? []);

        return response()->json([
            'message' => 'Promoaksiya muvaffaqiyatli yangilandi.',
            'id'      => $promotion->id,
        ]);
    }

}