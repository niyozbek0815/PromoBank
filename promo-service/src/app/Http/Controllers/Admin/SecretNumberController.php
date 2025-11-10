<?php

namespace App\Http\Controllers\Admin;
use App\Models\SecretNumber;
use Yajra\DataTables\Facades\DataTables;
use App\Http\Controllers\Controller;
use App\Models\ParticipationType;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecretNumberController extends Controller
{
    public function index(){
        return response()->json(['data' => "success"]);
    }

    public function create(Request $request)
    {
        $secretNumberType = ParticipationType::where('slug', 'secret_number')->first();

        if (!$secretNumberType) {
            return response()->json(['message' => 'Secret number turi topilmadi'], 404);
        }

        $promotions = Promotions::whereHas('participantTypeIds', function ($query) use ($secretNumberType) {
            $query->where('participation_type_id', $secretNumberType->id);
        })
            ->select(['id', 'name'])
            ->get()
            ->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'name_uz' => $promotion->getTranslation('name', 'uz'),
                ];
            });

        return response()->json($promotions);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'number' => 'required|string|max:10',
            'points' => 'nullable|integer|min:1',
            'start_at' => 'required|date',
        ]);

        $validated['points'] = $validated['points'] ?? null;

        $data = SecretNumber::create($validated);

        return response()->json(['success' => 'Sirli raqam muvaffaqiyatli qo‘shildi.', 'number' => $data]);
    }
    public function in_promotion_data(Request $request, $promotionId)
    {
        Log::info("ishladi");
        $query = SecretNumber::query()
            ->with(['promotion'])
            ->withCount('entries') // entries_count ustuni uchun
            ->where('promotion_id', $promotionId)
            ->select('secret_numbers.*');

        return DataTables::of($query)
            ->addColumn('promotion_name', fn($sn) => $sn->promotion?->getTranslation('name', 'uz') ?? '-')
            ->editColumn('number', fn($sn) => $sn->number ?? '-')
            ->editColumn('points', fn($sn) => $sn->points ?? 'null')            ->editColumn('entries_count', fn($sn) => $sn->entries_count ?? 0)
            ->editColumn('start_at', fn($sn) => optional($sn->start_at)?->format('Y-m-d H:i') ?? '-')
            ->addColumn('status', function ($sn) {
                if (!$sn->start_at)
                    return 'Belgilangan emas';
                if ($sn->start_at->isFuture())
                    return 'Kutilmoqda';
                return 'Faol';
            })
            ->addColumn('actions', function ($sn) {


                // entries_count 0 bo'lsa delete qo'sh
                if (($sn->entries_count ?? 0) === 0) {
                    $routes = [
                        'edit' => "/admin/secret-number/{$sn->id}/edit",
                        "delete" => "/admin/secret-number/{$sn->id}/delete"
                    ];
                 }
                $routes['show'] = "/admin/secret-number/{$sn->id}/show";


                return view('admin.actions', [
                    'row' => $sn,
                    'routes' => $routes,
                ])->render();
            })
            ->rawColumns([ 'actions'])
            ->make(true);
    }

    public function delete(Request $request, $id){
        $secret = SecretNumber::withCount('entries')->find($id);

        if (!$secret) {
            return response()->json([
                'success' => false,
                'message' => 'Sirli raqam topilmadi.'
            ], 404);
        }

        // Foydalanuvchi tomonidan ishlatilgan raqamni o'chirib bo'lmaydi
        if (($secret->entries_count ?? 0) > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Foydalanilgan raqamni o‘chirib bo‘lmaydi.'
            ], 400);
        }

        $secret->delete();

        return response()->json([
            'success' => true,
            'message' => 'Sirli raqam muvaffaqiyatli o‘chirildi.'
        ]);
    }
    public function edit($id)
    {
        $secret = SecretNumber::find($id);

        if (!$secret) {
            return redirect()->back()->with('error', 'Sirli raqam topilmadi.');
        }
        $secretNumberType = ParticipationType::where('slug', 'secret_number')->first();

        // Promoaksiyalar ro'yxati (select input uchun)
        $promotions = Promotions::whereHas('participantTypeIds', function ($query) use ($secretNumberType) {
            $query->where('participation_type_id', $secretNumberType->id);
        })
            ->select(['id', 'name'])
            ->get()
            ->map(function ($promotion) {
                return [
                    'id' => $promotion->id,
                    'name_uz' => $promotion->getTranslation('name', 'uz'),
                ];
            });

        return response()->json(data: [
            'secret' => $secret,
            'promotions' => $promotions,
        ]);
    }

    // Editdan keyin saqlash (update)
    public function update(Request $request, $id)
    {
        $secret = SecretNumber::withCount('entries')->find($id);

        if (!$secret) {
            return redirect()->back()->with('error', 'Sirli raqam topilmadi.');
        }

        // Agar raqam foydalanuvchi tomonidan ishlatilgan bo'lsa update qilinmasin
        if (($secret->entries_count ?? 0) > 0) {
            return redirect()->back()->with('error', 'Foydalanilgan raqamni o‘zgartirib bo‘lmaydi.');
        }

        $validated = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'number' => 'required|string|max:10|unique:secret_numbers,number,' . $id,
            'points' => 'nullable|integer|min:1',
            'start_at' => 'required|date',
        ]);

        $secret->update([
            'promotion_id' => $validated['promotion_id'],
            'number' => $validated['number'],
            'points' => $validated['points'] ?? null,
            'start_at' => $validated['start_at'],
        ]);
        return response()->json(['success' => "Sirli raqam update qilindi"]);
    }
    public function show($id)
    {
        try {
            // Sirli raqamni promotion va entries bilan olish
            $secret = SecretNumber::with(['promotion', 'entries.user'])
                ->find($id);

            if (!$secret) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sirli raqam topilmadi.'
                ], 404);
            }

            // entries uchun toza array
            $entries = $secret->entries->map(function ($entry) {
                return [
                    'id' => $entry->id,
                    'user_id' => $entry->user_id,
                    'user_name' => $entry->user?->name ?? 'Noma\'lum',
                    'user_input' => $entry->user_input,
                    'points_awarded' => $entry->points_awarded,
                    'is_accepted' => $entry->is_accepted,
                    'created_at' => $entry->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $entry->updated_at->format('Y-m-d H:i:s'),
                ];
            });

            // Javobni JSON formatda qaytarish
            return response()->json([
                'data' => [
                    'id' => $secret->id,
                    'promotion' => [
                        'id' => $secret->promotion?->id,
                        'name_uz' => $secret->promotion?->getTranslation('name', 'uz') ?? '-',
                    ],
                    'number' => $secret->number,
                    'points' => $secret->points ?? 0,
                    'start_at' => $secret->start_at?->format('Y-m-d H:i:s'),
                    'entries_count' => $secret->entries->count(),
                    'entries' => $entries,
                    'created_at' => $secret->created_at->format('Y-m-d H:i:s'),
                    'updated_at' => $secret->updated_at->format('Y-m-d H:i:s'),
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error("SecretNumber show error: " . $e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Ichki xatolik yuz berdi. Iltimos, keyinroq urinib ko‘ring.'
            ], 500);
        }
    }

}
