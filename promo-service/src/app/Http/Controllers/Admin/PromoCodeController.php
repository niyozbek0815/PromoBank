<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\GeneratePromoCodesJob;
use App\Jobs\ImportPromoCodesJob;
use App\Models\Prize;
use App\Models\PromoCode;
use App\Models\PromoGeneration;
use App\Models\PromotionSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class PromoCodeController extends Controller
{

    public function create(Request $request, $id)
    {
        $settings = PromotionSetting::where('promotion_id', $id)->first();
        return response()->json([
            'settings' => $settings,
        ]);
    }
    public function data()
    {
        $query = PromoCode::query()
            ->leftJoin('promotions', 'promo_codes.promotion_id', '=', 'promotions.id')
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->select(
                'promo_codes.*',
                'promo_generations.type as generation_type',
                DB::raw("promotions.name ->> 'uz' as promotion_name")
            );

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                return $item->is_used
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                    : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->editColumn('promotion_name', fn($item) => $item->promotion_name ?? '-')
            ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', function ($item) {
                if (!$item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row' => $item,
                    'routes' => [
                        'show' => "/admin/promocode/{$item->id}/show",
                    ],
                ])->render();
            })
            ->addColumn('created_at', fn($item) => $item->created_at?->format('d.m.Y H:i') ?? '-')
            ->rawColumns(['is_used', 'actions'])
            ->make(true);
    }
    public function updatePromocodeSettings(Request $request, int $promotionId)
    {
        $validated = $request->validate([
            'length' => 'required|integer|min:4|max:255',
            'charset' => 'required|string',
            'exclude_chars' => 'nullable|string',
            'prefix' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:255',
            'unique_across_all_promotions' => 'sometimes|boolean',
        ]);

        $setting = PromotionSetting::updateOrCreate(
            ['promotion_id' => $promotionId],
            array_merge($validated, [
                'promotion_id' => $promotionId,
                'unique_across_all_promotions' => $request->boolean('unique_across_all_promotions'),
            ])
        );

        return response()->json(['setting' => $setting]);
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
            'count' => 'required|integer|min:1|max:10001',
            'created_by_user_id' => 'required',
        ]);
        Queue::connection('rabbitmq')->push(new GeneratePromoCodesJob(
            $promotionId,
            $validated['count'],
            $validated['created_by_user_id']
        ));

        return response()->json([
            'message' => "{$validated['count']} ta promo kod generatsiyasi queue orqali ishga tushdi.",
        ]);
    }
    public function storePromoCodes(Request $request, $promotionId)
    {
        $validated = $request->validate([
            'promocode' => ['required', 'string', 'max:255'],
            'created_by_user_id' => ['required', 'integer'],
        ]);

        $code = strtoupper(trim($validated['promocode'])); // normalize
        $userId = $validated['created_by_user_id'];

        // 1️⃣ Sozlamalarni topamiz
        $settings = PromotionSetting::where('promotion_id', $promotionId)->first();

        // 1️⃣ Sozlamalarni topamiz
        if (!$settings) {
            return response()->json([
                'success' => false,
                'errors' => ['settings' => "Promotion uchun sozlamalar topilmadi."],
            ], 422);
        }
        $validationResult = $this->isCodeValidBySettings($code, $settings);

        if ($validationResult !== true) {
            return response()->json([
                'success' => false,
                'errors' => $validationResult,
            ], 422);
        }
        $exists = PromoCode::query()
            ->when(!$settings->unique_across_all_promotions, fn($q) => $q->where('promotion_id', $promotionId))
            ->where('promocode', $code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'errors' => ['promocode' => "Ushbu promokod allaqachon mavjud."],
            ], 422);
        }
        DB::beginTransaction();
        try {
            $generation = PromoGeneration::firstOrCreate(
                [
                    'promotion_id' => $promotionId,
                    'type' => 'generated',
                    'created_by_user_id' => $userId,
                ]
            );
            $promo = PromoCode::create([
                'generation_id' => $generation->id,
                'promotion_id' => $promotionId,
                'promocode' => $code,
                'is_used' => false,
            ]);

            DB::commit();

            Log::info("✅ Manual promocode created", [
                'promotion_id' => $promotionId,
                'promocode' => $code,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Promocode muvaffaqiyatli yaratildi",
                'data' => $promo,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            Log::error("❌ Manual promocode creation failed: " . $e->getMessage(), [
                'promotion_id' => $promotionId,
                'promocode' => $code,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => "Promocode yaratishda xatolik yuz berdi: " . $e->getMessage(),
            ], 500);
        }
    }
    public function importPromoCodes(Request $request, $promotionId)
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
            'settings_rules' => 'nullable|boolean',
            'created_by_user_id' => 'required|integer',
        ]);
        $validated['settings_rules'] = $request->boolean('settings_rules');
        $path = $request->file('file')->store('promo-imports', 'public');
        $fullPath = storage_path('app/public/' . $path);
        try {
            $sheets = Excel::toArray(null, $fullPath);
        } catch (\Throwable $e) {
            Log::error("❌ Excel faylni o'qishda xatolik: " . $e->getMessage());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ['Excel faylni o‘qib bo‘lmadi. Iltimos, formatni tekshiring.'],
                ],
            ], 422);
        }

        if (empty($sheets) || empty($sheets[0])) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ['Excel fayl bo‘sh yoki noto‘g‘ri formatda.'],
                ],
            ], 422);
        }
        $rows = $sheets[0];
        $header = $sheets[0][0] ?? [];
        if (!in_array('promocode', array_map('strtolower', $header))) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ["Excel faylda 'promocode' ustuni topilmadi. Birinchi qatorda ustun nomlari bo‘lishi va 'promocode' degan ustun mavjud bo‘lishi shart."],
                ],
            ], 422);
        }
        $promoRows = array_slice($rows, 1);
        if (count($promoRows) > 10000) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'file' => ["Excel faylda promo kodlar soni 10,000 tadan oshmasligi kerak. Siz yuborgansiz: " . count($promoRows)],
                ],
            ], 422);
        }
        Queue::connection('rabbitmq')->push(new ImportPromoCodesJob(
            $promotionId,
            $validated['created_by_user_id'],
            $path,
            $validated['settings_rules']
        ));

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
                    'row' => $row,
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
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->where('promo_codes.generation_id', $generateId)
            ->select(
                'promo_codes.*',
                'promo_generations.type as generation_type'
            );

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                return $item->is_used
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                    : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', function ($item) {
                if (!$item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row' => $item,
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
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->where('promo_codes.promotion_id', $promotionId)->select(
                'promo_codes.*',
                'promo_generations.type as generation_type'
            );

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                return $item->is_used
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                    : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->addColumn('used_at', fn($item) => $item->used_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', function ($item) {
                if (!$item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row' => $item,
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

        $prize = Prize::with(['smartRandomValues.rule', 'category'])->findOrFail($prizeId);

        // Null-check qo‘shdik
        if (!$prize || !$prize->category || $prize->category->name !== 'smart_random') {
            return DataTables::of(collect())->make(true); // Bo‘sh datatable
        }

        $rules = $prize->smartRandomValues;

        $query = PromoCode::query()
            ->where('promo_codes.promotion_id', $prize->promotion_id)
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->select(
                'promo_codes.*',
                'promo_generations.type as generation_type'
            );

        foreach ($rules as $ruleValue) {
            $key = $ruleValue->rule->key;
            $operator = $ruleValue->operator;
            $values = $ruleValue->values;

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

        return DataTables::of($query)
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
                if (!$item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row' => $item,
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
    public function searchPromocodes(Request $request, $promotionId)
    {
        $perPage = (int) $request->input('per_page', 20);
        $query = PromoCode::query()
            ->select(['id', 'promocode'])
            ->where('promotion_id', $promotionId);
        if ($search = $request->input('q')) {
            $query->where('promocode', 'like', "%{$search}%");
        }
        $promocodes = $query->latest('id')->paginate($perPage);
        return response()->json([
            'data' => $promocodes->map(function ($item) {
                return [
                    'id' => $item->id,
                    'code' => $item->promocode, // Frontda text sifatida ishlatiladi
                ];
            }),
            'current_page' => $promocodes->currentPage(),
            'last_page' => $promocodes->lastPage(),
            'next_page_url' => $promocodes->nextPageUrl(),
            'prev_page_url' => $promocodes->previousPageUrl(),
        ]);
    }
    public function autobindData(Request $request, int $prizeId)
    {
        $prize = Prize::with('category')->findOrFail($prizeId);

        if (!$prize || $prize->category->name !== 'auto_bind') {
            return DataTables::of(collect())->make(true); // Bo‘sh datatable
        }

        $query = PromoCode::query()
            ->join('prize_promos', 'promo_codes.id', '=', 'prize_promos.promo_code_id')
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->where('prize_promos.prize_id', $prize->id)
            ->select(
                'promo_codes.*',
                'platforms.name as platform_name',
                'promo_generations.type as generation_type',
                'prize_promos.is_used as bind_is_used' // PrizePromo dagi is_used
            );

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                $isUsed = $item->bind_is_used ?? $item->is_used; // PrizePromo ustuniga ustunlik beramiz
                return $isUsed
                    ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                    : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->addColumn('used_at', fn($item) => optional($item->used_at)?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', function ($item) {
                if (!$item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('actions', function ($item) use ($prize) {
                $isUsed = $item->bind_is_used ?? $item->is_used;
                $routes = [
                    'show' => "/admin/promocode/{$item->id}/show",
                ];
                if (!$isUsed) {
                    $routes['delete_bind'] = "/admin/prize/{$prize->id}/autobind/{$item->id}";
                }
                return view('admin.actions', [
                    'row' => $item,
                    'routes' => $routes,
                ])->render();
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('created_at', fn($item) => optional($item->created_at)?->format('d.m.Y H:i') ?? '-')
            ->rawColumns(['is_used', 'actions'])
            ->make(true);
    }

    public function show(Request $request, int $id)
    {
        $promocode = PromoCode::query()
            ->with([
                'generation:id,type',
                'promotion:id,name',
                'actions' => function ($q) {
                    $q->select(
                        'id',
                        'promotion_id',
                        'promo_code_id',
                        'user_id',
                        'prize_id',
                        'platform_id',
                        'action',
                        'status',
                        'attempt_time',
                        'message'
                    )
                        ->with([
                            'userCache:id,user_id,name,phone,status',
                            'prize:id,name',
                            'platform:id,name' // ✅ Platform ma’lumotini olib keladi
                        ])
                        ->orderByDesc('attempt_time');
                },
                // 🔹 PromoCodeUser (foydalanuvchilar)
                'codeUsers' => function ($q) {
                    $q->select(
                        'id',
                        'promo_code_id',
                        'user_id',
                        'platform_id',
                        'promotion_id',
                        'prize_id',
                        'sub_prize_id',
                        'created_at'
                    )
                        ->with([
                            'userCache:id,user_id,name,phone,status',
                            'platform:id,name',
                            'promotion:id,name',
                            'prize:id,name',
                        ]);
                }
            ])
            ->leftJoin('promotions', 'promo_codes.promotion_id', '=', 'promotions.id')
            ->leftJoin('promo_generations', 'promo_codes.generation_id', '=', 'promo_generations.id')
            ->select(
                'promo_codes.*',
                'promo_generations.type as generation_type',
                DB::raw("promotions.name ->> 'uz' as promotion_name")
            )
            ->findOrFail($id);

        return response()->json([
            'promocode' => $promocode,
            'actions' => $promocode->actions,
            'users' => $promocode->codeUsers,
        ]);
    }

    private function isCodeValidBySettings(string $code, PromotionSetting $settings): bool|array
    {
        $prefix = $settings->prefix ?? '';
        $suffix = $settings->suffix ?? '';
        $charset = $settings->charset ?? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $exclude = str_split($settings->exclude_chars ?? '');
        $length = $settings->length ?? strlen($code);


        if (strlen($code) !== $length) {
            return [
                'promocode' => "Promokod uzunligi {$length} ta belgi bo‘lishi kerak."
            ];
        }

        // ❌ 2️⃣ Prefiks tekshiruvi
        if ($prefix && !str_starts_with($code, $prefix)) {
            return [
                'promocode' => "Promokod '{$prefix}' prefiksi bilan boshlanishi kerak."
            ];
        }

        // ❌ 3️⃣ Suffiks tekshiruvi
        if ($suffix && !str_ends_with($code, $suffix)) {
            return [
                'promocode' => "Promokod '{$suffix}' suffiksi bilan tugashi kerak."
            ];
        }

        // 4️⃣ Asosiy qismni ajratamiz
        $core = substr($code, strlen($prefix), strlen($code) - strlen($prefix) - strlen($suffix));

        // 5️⃣ Ruxsat etilgan belgilarni tayyorlaymiz
        $allowed = str_split($charset);
        foreach ($exclude as $char) {
            $allowed = array_diff($allowed, [$char]);
        }

        // ❌ 6️⃣ Belgilarni tekshiramiz
        foreach (str_split($core) as $ch) {
            if (!in_array($ch, $allowed, true)) {
                return [
                    'promocode' => "Promokod ichida ruxsat etilmagan belgi '{$ch}' mavjud."
                ];
            }
        }

        return true;
    }
}
