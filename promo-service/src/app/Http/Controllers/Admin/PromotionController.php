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

        $query = Promotions::with('company')
            ->where('company_id', $id)
            ->select('promotions.*');

        return DataTables::of($query)
            ->addColumn('name', fn($item) => Str::limit($item->getTranslation('name', 'uz') ?? '-', 15))
            ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 20))
            ->addColumn('description', fn($item) => Str::limit($item->getTranslation('description', 'uz') ?? '-', 25))
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

        $query = Promotions::with(['company'])
            ->select('promotions.*');
        Log::info('Promotion', ['promo' => $query->get()]);
        return DataTables::of($query)
            ->addColumn('name', fn($item) => Str::limit($item->getTranslation('name', 'uz') ?? '-', 15))
            ->addColumn('title', fn($item) => Str::limit($item->getTranslation('title', 'uz') ?? '-', 20))
            ->addColumn('description', fn($item) => Str::limit($item->getTranslation('description', 'uz') ?? '-', 25))
            ->addColumn('company_name', fn($item) => Str::limit($item->company->getTranslation('name', 'uz') ?? '-', 15))
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
        $user = $request->get('auth_user');

        Log::info("store user", ['user' => $user]);
        $validated = $request->validate([
            'name'                => 'required|array',
            'title'               => 'required|array',
            'description'         => 'required|array',

            'company_id'          => 'required|exists:companies,id',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date|after_or_equal:start_date',
            'created_by_user_id'  => 'required|string|max:255',
            // 'code_settings'       => 'nullable|json',
            // 'extra_conditions'    => 'nullable|json',

            'participants_type'   => 'nullable|array',
            'participants_type.*' => 'integer',

            'platforms'           => 'nullable|array',
            'platforms.*'         => 'integer',

            'logo'                => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'video'               => 'nullable|file|mimes:mp4,mov,avi,webm|max:10240',

            'status'              => 'nullable|boolean',
            'is_public'           => 'nullable|boolean',
            'is_prize'            => 'nullable|boolean',
        ]);

        $promotion = new Promotions();
        $promotion->setTranslations('name', $validated['name']);
        $promotion->setTranslations('title', $validated['title']);
        $promotion->setTranslations('description', $validated['description']);
        $promotion->company_id = $validated['company_id'];
        $promotion->start_date = $validated['start_date'] ?? null;
        $promotion->end_date   = $validated['end_date'] ?? null;
        // $promotion->code_settings      = json_decode($validated['code_settings'] ?? '{}', true);
        // $promotion->extra_conditions   = json_decode($validated['extra_conditions'] ?? '{}', true);
        $promotion->status             = $request->boolean('status');
        $promotion->is_public          = $request->boolean('is_public');
        $promotion->is_prize           = $request->boolean('is_prize');
        $promotion->created_by_user_id = $user['id']; // yoki $request->user()->id
        $promotion->save();

        // Platformlar va participants (agar alohida jadval bo‘lsa)
        $promotion->platforms()->sync($validated['platforms'] ?? []);
        $promotion->participants()->sync($validated['participants_type'] ?? []);

        // Fayllarni saqlash (media library yoki storage bo‘lsa)
        if ($request->hasFile('logo')) {
            $promotion->addMediaFromRequest('logo')->toMediaCollection('logo');
        }

        if ($request->hasFile('video')) {
            $promotion->addMediaFromRequest('video')->toMediaCollection('video');
        }

        return response()->json([
            'message' => 'Promoaksiya muvaffaqiyatli saqlandi.',
            'id'      => $promotion->id,
        ]);
    }

    // public function update(Request $request, $id)
    // {
    //     $user      = Promotions::findOrFail($id);
    //     $validated = $request->validate([
    //         'name'        => 'required|string|max:255',
    //         'email'       => 'nullable|email|max:255|unique:users,email,' . $user->id,
    //         'phone'       => 'required|string|max:50|regex:/^\+?\d{7,50}$/|unique:users,phone,' . $user->id,
    //         'phone2'      => 'nullable|string|max:50|regex:/^\+?\d{7,50}$/',
    //         'region_id'   => 'required|exists:regions,id',
    //         'district_id' => 'required|exists:districts,id',
    //         'birthdate'   => [
    //             'required',
    //             'date_format:Y-m-d',
    //             'before_or_equal:today',
    //         ],
    //         'chat_id'     => 'required|string|max:50|unique:users,chat_id,' . $user->id,
    //         'gender'      => 'required|in:M,F',
    //         'image'       => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
    //     ]);
    //     if ($request->hasFile('image')) {
    //         $file     = $request->file('image');
    //         $tempPath = $file->store('tmp', 'public');
    //         Log ::info("image mavjud" . $tempPath);
    //         // Queue::connection('rabbitmq')->push(new UserUpdateMediaJob($user['id'], $tempPath));
    //     }

    //     $user->update($validated);

    //     return response()->json(['message' => 'Foydalanuvchi yangilandi.']);
    // }

}