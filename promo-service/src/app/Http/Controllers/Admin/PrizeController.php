<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\Prize;
use App\Models\PrizeCategory;
use App\Models\PrizePromo;
use App\Models\PromoCode;
use App\Models\SmartRandomRule;
use App\Models\SmartRandomValue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

}
