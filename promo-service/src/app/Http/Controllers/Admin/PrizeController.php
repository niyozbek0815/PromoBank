<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\PrizePromo;
use App\Models\PromoAction;
use App\Models\PromoCode;
use App\Models\Promotions;
use App\Models\SmartRandomRule;
use App\Models\SmartRandomValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class PrizeController extends Controller
{

    public function prizeData(Request $request)  {
        $query = Prize::with([
            'category:id,name,display_name',
            'promotion:id,name', // faqat kerakli ustunlar
        ])
            ->orderBy('index', 'desc')
            ->select('prizes.*');

        return DataTables::of($query)
            ->addColumn('index', fn($item) => $item->index)
            ->addColumn('category', fn($item) => $item->category->display_name ?? '-')
            ->addColumn('promotion_name', function ($item) {
                // Promotion mavjud bo'lsa tarjimani olamiz
                return $item->promotion
                ? $item->promotion->getTranslation('name', 'uz') // Spatie translatable
                : '-';
            })
            ->addColumn('valid_from', fn($item) => optional($item->valid_from)->format('d.m.Y'))
            ->addColumn('valid_until', fn($item) => optional($item->valid_until)->format('d.m.Y'))
            ->addColumn('awarded_quantity', fn($item) => $item->awarded_quantity)
            ->addColumn('probability_weight', fn($item) => $item->probability_weight)
            ->addColumn('status', fn($item) => $item->is_active
                ? '<span class="badge bg-success">Faol</span>'
                : '<span class="badge bg-danger">Nofaol</span>')
            ->addColumn('actions', fn($row) => view('admin.actions', [
                'row'    => $row,
                'routes' => [
                    'edit'   => "/admin/prize/{$row->id}/edit",
                    'status' => "/admin/prize/{$row->id}/status",
                ],
            ])->render())
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }

    public function changeStatus(Request $request, $id)
    {
        $data            = Prize::findOrFail($id);
        $data->is_active = ! $data->is_active;
        $data->save();
        return response()->json([
            'message'   => 'Status yangilandi',
            'is_active' => $data->is_active,
        ]);
    }
    public function edit($id)
    {
        $prize = Prize::with([
            'category:id,name,display_name',
            'promotion:id,name',
            'promotion.platforms:id,name',
            'promotion.participationTypes:id,name,slug',
            'promoActions',
            'smartRandomValues',
        ])
            ->withCount([
                'prizePromos as used_count'   => function ($query) {
                    $query->where('is_used', true);
                },
                'prizePromos as unused_count' => function ($query) {
                    $query->where('is_used', false);
                },
            ])
            ->findOrFail($id);
        $messagesExists = Messages::where('scope_type', 'prize')
            ->where('scope_id', $id)
            ->exists();
        $smartRule = [];
        if ($prize->category->name === 'smart_random') {
            $smartRule = SmartRandomRule::select('id', 'key', 'label', 'input_type', 'description', 'accepted_operators')->get();
        }
        $prizecategory = PrizeCategory::select('id', 'name', "display_name")->get();
        return response()->json([
            'prize'         => $prize,
            'smartRule'     => $smartRule,
            'prizecategory' => $prizecategory,
            'messagesExists'=>$messagesExists
        ]);
    }
    public function update(Request $request, Prize $prize)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'description'        => 'required|string',
            'quantity'           => 'required|integer|min:0',
            'daily_limit'        => 'nullable|integer|min:0',
            'index'              => 'required|integer|min:1',
            'category_id'        => 'required|exists:prize_categories,id',
            'promotion_id'       => 'required|exists:promotions,id',
            'valid_from'         => 'required|date',
            'valid_until'        => 'required|date|after:valid_from',
            'is_active'          => 'nullable|boolean',
            'probability_weight' => 'nullable|integer|min:0',
        ]);

        DB::beginTransaction();

        try {
            $categoryName = $prize->category->name;

            // Doimiy update bo'ladigan fieldlar
            $prize->fill([
                'name'         => $validated['name'],
                'description'  => $validated['description'],
                'quantity'     => $validated['quantity'],
                'daily_limit'  => $validated['daily_limit'] ?? null,
                'index'        => $validated['index'],
                'category_id'  => $validated['category_id'],
                'promotion_id' => $validated['promotion_id'],
                'valid_from'   => $validated['valid_from'],
                'valid_until'  => $validated['valid_until'],
                'is_active'    => $request->boolean('is_active'),
            ]);

            // Faqat weighted_random uchun kerak
            if ($categoryName === 'weighted_random') {
                $prize->probability_weight = $validated['probability_weight'] ?? 0;
            } else {
                $prize->probability_weight = 0; // ✅ null emas, default qiymat
            }

            $prize->save();

            DB::commit();

            return response()
                ->json([
                    'message' => 'Sovg‘a ma’lumotlari muvaffaqiyatli yangilandi.',
                ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            return response()
                ->json([
                    'error' => 'Xatolik yuz berdi: ' . $e->getMessage(),
                ]);
        }
    }


    public function storeRules(Request $request, $prizeId)
    {
        $validated = $request->validate([
            'rule_id'  => 'required|exists:smart_random_rules,id',
            'operator' => 'required|string',
            'values'   => 'required',
        ]);

        $prize = Prize::findOrFail($prizeId);

        if ($prize->category->name !== 'smart_random') {
            return response()->json([
                'error' => 'Bu amal faqat smart_random kategoriyasidagi sovg‘alar uchun amal qiladi.',
            ], 400);
        }

        // Frontenddan values textarea orqali kelgan bo'lsa: "12, AB,   1C ,  GH"
        $rawValues = $validated['values'];

        if (is_string($rawValues)) {
            // string bo'lsa, explode qilamiz
            $parsedValues = collect(explode(',', $rawValues))
                ->map(fn($v) => trim($v))     // bo'shliqlarni olib tashla
                ->filter(fn($v) => $v !== '') // bo'sh qiymatlarni chiqarib yubor
                ->values()
                ->all();
        } elseif (is_array($rawValues)) {
            // array bo'lsa, trim & clean qilamiz
            $parsedValues = collect($rawValues)
                ->map(fn($v) => trim($v))
                ->filter(fn($v) => $v !== '')
                ->values()
                ->all();
        } else {
            return response()->json([
                'error' => 'Qiymatlar noto‘g‘ri formatda.',
            ], 422);
        }

        if (empty($parsedValues)) {
            return response()->json([
                'error' => 'Kamida bitta qiymat kiritilishi kerak.',
            ], 422);
        }

        // Qoidani saqlash
        SmartRandomValue::updateOrCreate(
            [
                'prize_id' => $prize->id,
                'rule_id'  => $validated['rule_id'],
            ],
            [
                'operator' => $validated['operator'],
                'values'   => $parsedValues, // json cast modelda bo‘lsa kerak
            ]
        );

        return response()->json([
            'message' => 'Qoidalar muvaffaqiyatli saqlandi.',
        ]);
    }
    public function deleteRule($prizeId, $ruleId)
    {
        $prize = Prize::findOrFail($prizeId);

        if ($prize->category->name !== 'smart_random') {
            return response()->json([
                'error' => 'Bu amal faqat smart_random kategoriyasidagi sovg‘alar uchun amal qiladi.',
            ], 400);
        }

        $rule = SmartRandomValue::where('prize_id', $prize->id)
            ->where('rule_id', $ruleId)
            ->first();

        if (! $rule) {
            return response()->json([
                'error' => 'Qoidani topib bo‘lmadi.',
            ], 404);
        }

        $rule->delete();

        return response()->json([
            'message' => 'Qoida muvaffaqiyatli o‘chirildi.',
        ]);
    }
    public function data(Request $request, int $prizeId)
    {
        $prize = Prize::with('smartRandomValues.rule')->findOrFail($prizeId);

        if (! $prize || $prize->category->name !== 'smart_random') {
            return DataTables::of(collect())->make(true); // Bo‘sh datatable qaytariladi
        }

        $rules = $prize->smartRandomValues;

        $query = PromoCode::query()
            ->where('promotion_id', $prize->promotion_id)
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

        return DataTables::of($query)
            ->addColumn('promocode', fn($item) => $item->promocode)
            ->addColumn('is_used', function ($item) {
                return $item->is_used
                ? '<span class="badge bg-success bg-opacity-10 text-success">Foydalangan</span>'
                : '<span class="badge bg-secondary bg-opacity-10 text-secondary">Foydalanilmagan</span>';
            })
            ->addColumn('used_at', fn($item) => optional($item->used_at)?->format('d.m.Y H:i') ?? '-')
            ->addColumn('generation_name', function ($item) {
                if (! $item->generation_id) {
                    return '-';
                }
                $label = $item->generation_type === 'import' ? 'import' : 'generatsiya';
                return "{$item->generation_id}-idli {$label}";
            })
            ->addColumn('platform', fn($item) => $item->platform_name ?? '-')
            ->addColumn('created_at', fn($item) => optional($item->created_at)?->format('d.m.Y H:i') ?? '-')
            ->rawColumns(['is_used'])
            ->make(true);
    }
    public function autobind(Request $request, $prizeId)
    {
        $request->validate([
            'promocodes'   => 'required|array',
            'promocodes.*' => 'exists:promo_codes,id',
        ]);

        $prize = Prize::with('category')->findOrFail($prizeId);

        if ($prize->category->name !== 'auto_bind') {
            return response()->json([
                'error' => 'Bu amal faqat auto_bind kategoriyasidagi sovg‘alar uchun amal qiladi.',
            ], 400);
        }

        DB::transaction(function () use ($prize, $request) {
            foreach ($request->promocodes as $promoId) {
                PrizePromo::updateOrCreate(
                    [
                        'prize_id'      => $prize->id,
                        'promo_code_id' => $promoId,
                    ],
                    [
                        'promotion_id' => $prize->promotion_id,
                        'category_id'  => $prize->category_id,
                        'sub_prize'    => null,
                        'is_used'      => false,
                    ]
                );
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Promo kodlar muvaffaqiyatli auto-bind qilindi (ustiga yozildi).',
        ]);
    }

    public function autobindDelete(Request $request, $prizeId, $promocodeId)
    {

        $prize = Prize::with('category')->findOrFail($prizeId);

        if ($prize->category->name !== 'auto_bind') {
            return response()->json([
                'error' => 'Bu amal faqat auto_bind kategoriyasidagi sovg‘alar uchun amal qiladi.',
            ], 400);
        }

        $promoBind = PrizePromo::where('prize_id', $prize->id)
            ->where('promo_code_id', $promocodeId)
            ->first();

        if (! $promoBind) {
            return response()->json([
                'success' => false,
                'message' => 'Bunday promo kod topilmadi yoki bog‘lanmagan.',
            ], 404);
        }

        if ($promoBind->is_used) {
            return response()->json([
                'success' => false,
                'message' => 'Ushbu promo kod ishlatilgan, o‘chirish mumkin emas.',
            ], 403);
        }

        $promoBind->delete();

        return response()->json([
            'success' => true,
            'message' => 'Promo kod muvaffaqiyatli o‘chirildi.',
        ]);

    }


    public function actionsData(Request $request, Prize $prize)
    {
        $query = PromoAction::with([
            'promoCode:id,promocode',
            'promotion:id,name',
            'platform:id,name',
            'userCache:user_id,name,phone',
            'shop:name'

        ])
            ->where('prize_id', $prize->id)
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('id', fn($item) => $item->id)
            ->addColumn('promotion_name', fn($item) => $item->promotion?->name ?? '—')
            ->addColumn('promocode', fn($item) => $item->promoCode?->promocode ?? '—')
            ->addColumn('used_at', fn($item) =>
                optional($item->promoCode?->used_at)->format('d.m.Y H:i') ?? '—')
            ->addColumn('platform', fn($item) => $item->platform?->name ?? '—')
            ->addColumn('user', fn($item) => $item->userCache?->phone ?? $item->user_id)

            ->addColumn('action', fn($item) => $item->action ?? '—')

            ->addColumn('status', fn($item) => $item->status ?? '—')
            ->addColumn('created_at', fn($item) =>
                optional($item->created_at)->format('d.m.Y H:i') ?? '—')
            ->addColumn('shop', fn($item) => $item->shop?->name ?? '—')
            ->addColumn('receipt_id', fn($item) => $item->receipt_id ?? '—')
            ->addColumn('message', fn($item) => e(Str::limit($item->message, 120)) ?? '—')
            ->rawColumns(['is_used', 'status', 'actions'])
            ->make(true);
    }
    public function createByCategory(Request $request, $category, $promotionId)
    {

        $promotion = Promotions::findOrFail($promotionId);
        $categoryData = PrizeCategory::where('name', $category)->firstOrFail();

        return response()->json([
            'promotion' => $promotion->toArray(),
            'category' => $categoryData->toArray(),
        ]);
    }
    public function storeByCategory(Request $request, $category, $promotionId)
    {
        try {
            // 1️⃣ Validatsiya
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'description' => ['nullable', 'string', 'max:2000'],
                'index' => ['nullable', 'integer', 'min:0'],
                'quantity' => ['required', 'integer', 'min:1'],
                'daily_limit' => ['nullable', 'integer', 'min:0'],
                'probability_weight' => ['nullable', 'integer', 'min:0', 'max:100'],
                'is_active' => ['nullable', 'boolean'],
                'valid_from' => ['nullable', 'date'],
                'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            ]);

            // 2️⃣ Qo‘shimcha maydonlar
            $validated['promotion_id'] = $promotionId;
            $validated['category_id'] = $request->input('category_id');
            $validated['created_by_user_id'] = $request->input('created_by_user_id');
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['awarded_quantity'] = 0;

            // 3️⃣ Default qiymatlar
            if (empty($validated['index'])) {
                $validated['index'] = \App\Models\Prize::where('promotion_id', $promotionId)->max('index') + 1;
            }

            // 4️⃣ Amal qilish sanalari
            $validated['valid_from'] = $validated['valid_from'] ?? null;
            $validated['valid_until'] = $validated['valid_until'] ?? null;

            // 5️⃣ Yaratish
            $prize =Prize::create($validated);

            Log::info('Prize created', [
                'promotion_id' => $promotionId,
                'category' => $category,
                'prize_id' => $prize->id,
                'created_by' => $validated['created_by_user_id'],
            ]);

            return redirect()
                ->route('admin.prize-category.show', [
                    'promotion' => $promotionId,
                    'type' => $category,
                ])
                ->with('success', 'Sovgʻa muvaffaqiyatli yaratildi.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Agar validatsiyada xato bo‘lsa
            return back()->withErrors($e->validator)->withInput();

        } catch (\Throwable $e) {
            // Har qanday boshqa xato uchun
            Log::error('Prize creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Sovgʻa yaratishda xatolik yuz berdi.')->withInput();
        }
    }
    public function importByCategory(Request $request, $category, $promotionId)
    {
        $validated = $request->validate([
            'prize_file' => 'required|file|mimes:xlsx,xls|max:5120',
            'created_by_user_id' => 'required|integer',
        ]);
        $path = $request->file('prize_file')->store('prize-imports', 'public');
        $fullPath = storage_path('app/public/' . $path);
        try {
            $sheets = \Maatwebsite\Excel\Facades\Excel::toArray(null, $fullPath);
        } catch (\Throwable $e) {
            Log::error("❌ Excel faylni o‘qishda xatolik: " . $e->getMessage());
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ['Excel faylni o‘qib bo‘lmadi. Iltimos, formatni tekshiring.'],
                ],
            ], 422);
        }
        $rows = $sheets[0];
        $header = array_map('strtolower', $rows[0] ?? []);

        // Jadvaldagi ustunlar ro‘yxati
        $requiredColumns = [
            'promotion_id',
            'category_id',
            'index',
            'name',
            'description',
            'quantity',
            'daily_limit',
            'awarded_quantity',
            'probability_weight',
            'is_active',
            'created_by_user_id',
            'valid_from',
            'valid_until',
        ];

        // 1️⃣ — Excel fayl bo‘sh yoki noto‘g‘ri formatda
        if (empty($header)) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ['Excel fayl bo‘sh yoki noto‘g‘ri formatda. Ustunlar topilmadi.'],
                ],
            ], 422);
        }

        // 2️⃣ — Barcha ustunlar mavjudligini tekshirish
        $missingColumns = array_diff($requiredColumns, $header);
        if (!empty($missingColumns)) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ['Excel faylda quyidagi ustunlar yo‘q: ' . implode(', ', $missingColumns)],
                ],
            ], 422);
        }

        // 3️⃣ — Ortiqcha ustunlar (jadvalda yo‘q ustunlar)
        $invalidColumns = array_diff($header, $requiredColumns);
        if (!empty($invalidColumns)) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ['Excel faylda nomaqbul ustun(lar) mavjud: ' . implode(', ', $invalidColumns)],
                ],
            ], 422);
        }

        // 4️⃣ — Ma’lumotlar mavjudligini tekshirish
        $dataRows = array_slice($rows, 1);
        if (count($dataRows) === 0) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ['Excel faylda ma’lumot topilmadi.'],
                ],
            ], 422);
        }

        if (count($dataRows) > 5000) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => [
                    'prize_file' => ["Excel faylda sovg‘alar soni 5,000 tadan oshmasligi kerak. Siz yuborgansiz: " . count($dataRows)],
                ],
            ], 422);
        }

        // 5️⃣ — Har bir qatordagi ustun qiymatlarini validatsiya
        foreach ($dataRows as $rowIndex => $row) {
            $mapped = array_combine($header, $row);

            foreach ($requiredColumns as $col) {
                // name, quantity, created_by_user_id va category_id bo‘sh bo‘lmasin
                $mustNotBeEmpty = ['name', 'quantity', 'promotion_id', 'category_id', 'created_by_user_id'];

                if (in_array($col, $mustNotBeEmpty) && empty($mapped[$col])) {
                    return response()->json([
                        'message' => 'Validation Error',
                        'errors' => [
                            'prize_file' => ["{$rowIndex} - qatorda '{$col}' ustuni bo‘sh bo‘lmasligi kerak."],
                        ],
                    ], 422);
                }

                // quantity — musbat son
                if ($col === 'quantity' && (!is_numeric($mapped[$col]) || $mapped[$col] < 0)) {
                    return response()->json([
                        'message' => 'Validation Error',
                        'errors' => [
                            'prize_file' => ["{$rowIndex} - qatorda 'quantity' musbat raqam bo‘lishi kerak."],
                        ],
                    ], 422);
                }

                // probability_weight — 0–100 oralig‘ida
                if ($col === 'probability_weight' && $mapped[$col] !== '') {
                    if (!is_numeric($mapped[$col]) || $mapped[$col] < 0 || $mapped[$col] > 100) {
                        return response()->json([
                            'message' => 'Validation Error',
                            'errors' => [
                                'prize_file' => ["{$rowIndex} - qatorda 'probability_weight' 0–100 oralig‘ida bo‘lishi kerak."],
                            ],
                        ], 422);
                    }
                }

                // is_active — boolean sifatida kiritilishi kerak (1 yoki 0)
                if ($col === 'is_active' && $mapped[$col] !== '') {
                    if (!in_array($mapped[$col], [1, 0, '1', '0', true, false], true)) {
                        return response()->json([
                            'message' => 'Validation Error',
                            'errors' => [
                                'prize_file' => ["{$rowIndex} - qatorda 'is_active' faqat 1 yoki 0 qiymat bo‘lishi kerak."],
                            ],
                        ], 422);
                    }
                }

                // valid_from / valid_until — datetime formatda bo‘lishi kerak
                if (in_array($col, ['valid_from', 'valid_until']) && !empty($mapped[$col])) {
                    try {
                        \Carbon\Carbon::parse($mapped[$col]);
                    } catch (\Exception $e) {
                        return response()->json([
                            'message' => 'Validation Error',
                            'errors' => [
                                'prize_file' => ["{$rowIndex} - qatorda '{$col}' sanasi noto‘g‘ri formatda. (Masalan: 2025-10-15 14:00:00)"],
                            ],
                        ], 422);
                    }
                }
            }
        }
        // Queue orqali importni ishga tushiramiz
        // Queue::connection('rabbitmq')->push(new \App\Jobs\ImportPrizesJob(
        //     $promotionId,
        //     $category,
        //     $validated['created_by_user_id'],
        //     $path
        // ));

        return response()->json([
            'message' => "✅ Sovg‘alarni import qilish jarayoni queue orqali boshlandi.",
        ]);
       }
}
