<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePromoCodesJob;
use App\Models\PromoCode;
use App\Models\PromoGeneration;
use App\Models\PromotionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Yajra\DataTables\Facades\DataTables;

class PromoCodeController extends Controller
{
    public function create(Request $request, $id)
    {
        $settings = PromotionSetting::where('promotion_id', $id)->first();
        // $settings->delete();
        // $settings = null;
        return response()->json([
            'settings' => $settings,
        ]);
    }

    public function updatePromocodeSettings(Request $request, $promotionId)
    {
        $validated = $request->validate([
            'length'                       => 'required|integer|min:4|max:255',
            'charset'                      => 'required|string',
            'exclude_chars'                => 'nullable|string',
            'prefix'                       => 'nullable|string|max:255',
            'suffix'                       => 'nullable|string|max:255',
            'unique_across_all_promotions' => 'nullable|boolean',
        ]);

        // Fallback agar checkbox jo‘natilmasa false qo‘shamiz
        $validated['unique_across_all_promotions'] = $request->has('unique_across_all_promotions');
        $validated['promotion_id']                 = $promotionId;
        $setting                                   = PromotionSetting::updateOrCreate(
            ['promotion_id' => $promotionId],
            $validated
        );
        return response()->json([
            'setting' => $setting,
        ]);
    }
    public function showPromocodeSettingsForm(Request $request, $promotionId)
    {
        $settings = PromotionSetting::where('promotion_id', $promotionId)->first();
        return response()->json([
            'settings' => $settings,
        ]);
    }
    public function generatePromoCodes(Request $request, $promotionId)
    {
        $validated = $request->validate([
            'count'              => 'required|integer|min:1|max:100000',
            'created_by_user_id' => 'required',
        ]);
        Log::info("🔁 Job started: Generating {$validated['count']} promo codes for promotion {$promotionId}");

        Queue::connection('rabbitmq')->push(new GeneratePromoCodesJob(
            $promotionId,
            $validated['count'],
            $validated['created_by_user_id']
        ));


        return response()->json([
            'message' => "{$validated['count']} ta promo kod generatsiyasi queue orqali ishga tushdi.",
        ]);
    }

    // public function importPromoCodes(Request $request, $promotionId)
    // {
    //     $request->validate([
    //         'count'              => 'required|integer|min:1|max:100000',
    //         'created_by_user_id' => 'required|exists:users,id',
    //     ]);
    //     $settings   = PromotionSetting::where('promotion_id', $promotionId)->firstOrFail();
    //     $generation = PromoGeneration::create([
    //         'promotion_id'       => $promotionId,
    //         'created_by_user_id' => $request->created_by_user_id,
    //         'type'               => 'generated',
    //     ]);
    //     $codes       = [];
    //     $maxAttempts = $request->count * 2; // fallback for uniqueness
    //     $attempts    = 0;
    //     while (count($codes) < $request->count && $attempts < $maxAttempts) {
    //         $code = self::generateCodeFromSettings($settings);

    //         // Uniqueness check
    //         if ($settings->unique_across_all_promotions) {
    //             if (PromoCode::where('promocode', $code)->exists()) {
    //                 $attempts++;
    //                 continue;
    //             }
    //         } else {
    //             if (PromoCode::where('promotion_id', $promotionId)->where('promocode', $code)->exists()) {
    //                 $attempts++;
    //                 continue;
    //             }
    //         }

    //         $codes[] = [
    //             'generation_id' => $generation->id,
    //             'promotion_id'  => $promotionId,
    //             'promocode'     => $code,
    //             'is_used'       => false,
    //             'created_at'    => now(),
    //             'updated_at'    => now(),
    //         ];
    //     }
    //     PromoCode::insert($codes);
    //     return redirect()->back()->with('success', count($codes) . ' ta promo kod generatsiya qilindi.');
    // }
    public function generatedata(Request $request, $promotionId)
    {
        $query = PromoGeneration::withCount([
            'promoCodes',
            'promoCodes as used_promo_codes_count' => function ($query) {
                $query->where('is_used', true);
            },
        ])->orderBy('id', 'asc')
            ->where('promotion_id', $promotionId); // faqat kerakli ustunlar
        return DataTables::of($query)
            ->addColumn('name', content: fn($item) => "{$item->id}-idli generatsiya")
            ->addColumn('count', fn($item) => $item->promo_codes_count)
            ->addColumn('type', fn($item) => $item->type)
            ->addColumn('used_count', fn($item) => $item->used_promo_codes_count)
            ->addColumn('actions', function ($row) {
                return view('admin.actions', [
                    'row'    => $row,
                    'routes' => [
                        'show' => "/admin/promocode/{$row->id}/showgenerate",
                    ],
                ])->render();
            })
            ->addColumn('created_at', fn($item) => $item->created_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn('created_by', fn($item) => $item->created_by_user_id ?? '-')
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function generatePromocodeData(Request $request, $generateId)
    {
        $query = PromoCode::with(['generation', 'platform'])
            ->where('generation_id', $generateId)
            ->select('promo_codes.*'); // optimize qilingan select

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)

            ->addColumn('is_used', function ($item) {
                return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })

            ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')

            ->addColumn('generation', fn($item) => $item->generation?->id ?? '-')

            ->addColumn('platform', fn($item) => $item->platform?->name ?? '-')

            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row'    => $item,
                    'routes' => [
                        'show' => "/admin/promocode/{$item->id}/show",
                    ],
                ])->render();
            })
            ->addColumn('created_at', fn($item) => $item->created_at?->format('d.m.Y H:i') ?? '-')
            ->rawColumns(['is_used', 'actions'])
            ->make(true);
    }
    public function promocodeData(Request $request, $promotionId)
    {
        $query = PromoCode::with(['generation', 'platform'])
            ->where('promotion_id', $promotionId)
            ->select('promo_codes.*'); // optimize qilingan select

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', fn($item) => $item->generation_id ? "{$item->generation_id}-idli generatsiya" : '-')
            ->addColumn('platform', fn($item) => $item->platform?->name ?? '-')

            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row'    => $item,
                    'routes' => [
                        'show' => "/admin/promocode/{$item->id}/show",
                    ],
                ])->render();
            })
            ->addColumn('created_at', fn($item) => $item->created_at?->format('d.m.Y H:i') ?? '-')
            ->rawColumns(['is_used', 'actions'])
            ->make(true);
    }
}
