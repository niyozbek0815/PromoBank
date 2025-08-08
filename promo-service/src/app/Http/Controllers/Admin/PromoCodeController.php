<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePromoCodesJob;
use App\Jobs\ImportPromoCodesJob;
use App\Models\Prize;
use App\Models\PromoCode;
use App\Models\PromoGeneration;
use Maatwebsite\Excel\Facades\Excel;

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
    public function importPromoCodes(Request $request, $promotionId)
    {
        $validated = $request->validate([
            'file'               => 'required|file|mimes:xlsx,xls',
             'settings_rules'     => 'nullable|boolean',
            'created_by_user_id' => 'required|integer',
        ]);
    $validated['settings_rules'] = $request->boolean('settings_rules');
        Log::info('validated data', ['validated data' => $validated]);
        // Faylni vaqtinchalik saqlaymiz
        $path = $request->file('file')->store('promo-imports', 'public');

        // $path     = $request->file('file')->store('promo-imports');
        $fullPath = storage_path('app/public/' . $path);

        // Excel faylni array formatga o‘qib olamiz
        try {
            $sheets = Excel::toArray(null, $fullPath);
        } catch (\Throwable $e) {
            Log::error("❌ Excel faylni o'qishda xatolik: " . $e->getMessage());
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'file' => ['Excel faylni o‘qib bo‘lmadi. Iltimos, formatni tekshiring.'],
                ],
            ], 422);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'file' => ['Excel fayl bo‘sh yoki noto‘g‘ri formatda.'],
                ],
            ], 422);
        }

        $header = $sheets[0][0] ?? [];
        if (! in_array('promocode', array_map('strtolower', $header))) {
            return response()->json([
                'message' => 'Validation Error',
                'errors'  => [
                    'file' => ["Excel faylda 'promocode' ustuni topilmadi. Birinchi qatorda ustun nomlari bo‘lishi va 'promocode' degan ustun mavjud bo‘lishi shart."],
                ],
            ], 422);
        }

        Log::info(message: "📥 PromoCode import queued from Excel: {$path}");

        // ✅ Job'ni queue'ga yuborish
        Queue::connection('rabbitmq')->push(new ImportPromoCodesJob(
         $promotionId, $validated['created_by_user_id'], $path, $validated['settings_rules']
        ));

        // ImportPromoCodesJob::dispatch($promotionId, $validated['created_by_user_id'], $path)
        //     ->onQueue('default');

        return response()->json([
            'message' => "✅ Promo kod import jarayoni queue orqali boshlandi.",
        ]);
    }

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
    $query = PromoCode::query()
        ->leftJoin('platforms', 'promo_codes.platform_id', '=', 'platforms.id')
        ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
        ->where('promo_codes.generation_id', $generateId)
        ->select(
            'promo_codes.*',
            'platforms.name as platform_name',
            'promo_generations.type as generation_type'
        );

    return DataTables::of($query)
        ->filterColumn('platform_name', function ($query, $keyword) {
            $query->whereRaw('LOWER(platforms.name) LIKE ?', ["%" . strtolower($keyword) . "%"]);
        })
        ->addColumn('promocode', fn($item) => $item->promocode)
        ->addColumn('is_used', function ($item) {
            return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
        })
        ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
        ->addColumn('generation_name', function ($item) {
            if (! $item->generation_id) {
                return '-';
            }
            $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
            return "{$item->generation_id}-idli {$label}";
        })
        ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
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
    $query = PromoCode::query()
        ->leftJoin('platforms', 'promo_codes.platform_id', '=', 'platforms.id')
        ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
->where('promo_codes.promotion_id', $promotionId)        ->select(
            'promo_codes.*',
            'platforms.name as platform_name',
            'promo_generations.type as generation_type'
        );

    return DataTables::of($query)
        ->filterColumn('platform_name', function($query, $keyword) {
            $query->whereRaw('LOWER(platforms.name) LIKE ?', ["%".strtolower($keyword)."%"]);
        })
        ->addColumn('promocode', fn($item) => $item->promocode)
        ->addColumn('is_used', function ($item) {
            return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
        })
        ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
        ->addColumn('generation_name', function ($item) {
            if (! $item->generation_id) {
                return '-';
            }
            $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
            return "{$item->generation_id}-idli {$label}";
        })
        ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
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
public function prizeData(Request $request, int $prizeId)
{
    Log::info("Fetching prize data for prize ID: {$prizeId}");

    $prize = Prize::with(['smartRandomValues.rule', 'category'])->findOrFail($prizeId);

    // Null-check qo‘shdik
    if (! $prize || ! $prize->category || $prize->category->name !== 'smart_random') {
        return DataTables::of(collect())->make(true); // Bo‘sh datatable
    }

    $rules = $prize->smartRandomValues;

    $query = PromoCode::query()
        ->where('promo_codes.promotion_id', $prize->promotion_id)
        ->leftJoin('platforms', 'promo_codes.platform_id', '=', 'platforms.id')
        ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
        ->select(
            'promo_codes.*',
            'platforms.name as platform_name',
            'promo_generations.type as generation_type'
        );

    foreach ($rules as $ruleValue) {
        $key      = $ruleValue->rule->key;
        $operator = $ruleValue->operator;
        $values   = $ruleValue->values;

        $query->where(function ($q) use ($key, $operator, $values) {
            switch ($key) {
                case 'code_length':
                    foreach ($values as $value) {
                        $q->orWhereRaw("CHAR_LENGTH(promocode) {$operator} ?", [$value]);
                    }
                    break;
                case 'uppercase_count':
                    foreach ($values as $value) {
                        $q->orWhereRaw("LENGTH(REGEXP_REPLACE(promocode, '[^A-Z]', '', 'g')) {$operator} ?", [$value]);
                    }
                    break;
                case 'lowercase_count':
                    foreach ($values as $value) {
                        $q->orWhereRaw("LENGTH(REGEXP_REPLACE(promocode, '[^a-z]', '', 'g')) {$operator} ?", [$value]);
                    }
                    break;
                case 'digit_count':
                    foreach ($values as $value) {
                        $q->orWhereRaw("LENGTH(REGEXP_REPLACE(promocode, '[^0-9]', '', 'g')) {$operator} ?", [$value]);
                    }
                    break;
                case 'special_char_count':
                    foreach ($values as $value) {
                        $q->orWhereRaw("LENGTH(REGEXP_REPLACE(promocode, '[a-zA-Z0-9]', '', 'g')) {$operator} ?", [$value]);
                    }
                    break;
                case 'unique_char_count':
                    foreach ($values as $value) {
                        $q->orWhereRaw("LENGTH(REGEXP_REPLACE(promocode, '(.)(?=.*\\1)', '', 'g')) {$operator} ?", [$value]);
                    }
                    break;

                case 'starts_with':
                    foreach ($values as $value) {
                        $q->orWhere('promocode', 'LIKE', "$value%");
                    }
                    break;
                case 'not_starts_with':
                    foreach ($values as $value) {
                        $q->where('promocode', 'NOT LIKE', "$value%");
                    }
                    break;
                case 'ends_with':
                    foreach ($values as $value) {
                        $q->orWhere('promocode', 'LIKE', "%$value");
                    }
                    break;
                case 'not_ends_with':
                    foreach ($values as $value) {
                        $q->where('promocode', 'NOT LIKE', "%$value");
                    }
                    break;
                case 'contains':
                case 'contains_sequence':
                    foreach ($values as $value) {
                        $q->orWhere('promocode', 'LIKE', "%$value%");
                    }
                    break;
                case 'not_contains':
                    foreach ($values as $value) {
                        $q->where('promocode', 'NOT LIKE', "%$value%");
                    }
                    break;
            }
        });
    }

    Log::info("getBindings", $query->getBindings());
    Log::info("toSql", ['sql' => $query->toSql()]);

    return DataTables::of($query)
        ->filterColumn('platform_name', function ($query, $keyword) {
            $query->whereRaw('LOWER(platforms.name) LIKE ?', ["%" . strtolower($keyword) . "%"]);
        })
        ->addColumn('promocode', fn($item) => $item->promocode)
        ->addColumn('is_used', function ($item) {
            return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
        })
        ->addColumn('used_at', function ($item) {
            return $item->used_at
                ? date('d.m.Y H:i', strtotime($item->used_at))
                : '-';
        })
        ->addColumn('generation_name', function ($item) {
            if (! $item->generation_id) {
                return '-';
            }
            $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
            return "{$item->generation_id}-idli {$label}";
        })
        ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
        ->addColumn('actions', function ($item) {
            return view('admin.actions', [
                'row'    => $item,
                'routes' => [
                    'show' => "/admin/promocode/{$item->id}/show",
                ],
            ])->render();
        })
        ->addColumn('created_at', function ($item) {
            return $item->created_at
                ? date('d.m.Y H:i', strtotime($item->created_at))
                : '-';
        })
        ->rawColumns(['is_used', 'actions'])
        ->make(true);
}
}
