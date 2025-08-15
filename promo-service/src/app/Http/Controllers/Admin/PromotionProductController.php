<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromotionProduct;
use App\Models\Promotions;
use App\Models\PromotionShop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PromotionProductController extends Controller
{
    /**
     * Yaratish formasi uchun kerak bo‘lgan ma'lumotlarni olish
     */
    public function create(Request $request, $shop_id = null)
    {
        // 🔹 Barcha promotionlarni olish
        $promotions = Promotions::select('id', 'name')
            ->get()
            ->map(fn($promotion) => [
                'id'   => $promotion->id,
                'name' => $promotion->getTranslation('name', 'uz'),
            ]);

        // 🔹 Barcha do‘konlar (promotion_id bilan birga)
        $shops = PromotionShop::select('id', 'name', 'promotion_id')->get();

        // 🔹 Agar shop_id berilgan bo‘lsa, shu do‘konning promotion_id sini topish
        $selected_promotion = $shop_id
        ? optional($shops->firstWhere('id', $shop_id))->promotion_id
        : null;

        return response()->json([
            'promotions'         => $promotions,
            'shops'              => $shops,
            'selected_shop'      => $shop_id,
            'selected_promotion' => $selected_promotion,
        ]);
    }

    /**
     * Yangi product saqlash
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'shop_id'      => 'required|exists:promotion_shops,id',
            'name'         => 'required|string|max:255',
            'status'       => 'required|boolean',
        ], [
            'promotion_id.required' => 'Aksiya tanlanishi shart.',
            'shop_id.required'      => 'Do‘kon tanlanishi shart.',
            'name.required'         => 'Mahsulot nomini kiriting.',
            'status.required'       => 'Holat tanlanishi shart.',
        ]);

        PromotionProduct::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mahsulot qo‘shildi',
        ]);
    }
    public function data(Request $request)
    {
        $query = PromotionProduct::query()
            ->with(['promotion', 'shop'])
            ->select('promotion_products.*');
        return DataTables::of($query)
            ->addColumn('promotion_name', fn($product) => $product->promotion?->getTranslation('name', 'uz') ?? '-')
            ->addColumn('shop_name', fn($product) => $product->shop?->name ?? '-')
            ->addColumn('status_label', fn($product) => $product->status ? '✅ Faol' : '❌ Faol emas')
            ->addColumn('created_at', fn($product) => optional($product->created_at)?->format('d.m.Y H:i') ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row'    => $item,
                    'routes' => [
                        'edit'   => "/admin/promotion_products/{$item->id}/edit",
                        'status' => "/admin/promotion_products/{$item->id}/change_status",
                    ],
                ])->render();
            })
            ->rawColumns(['promotion_name', 'shop_name', 'status_label', 'actions'])
            ->make(true);
    }

    /**
     * DataTables uchun ma'lumotlar
     */
    public function promotiondata(Request $request, $shopId)
    {
        $query = PromotionProduct::query()
            ->with(['promotion', 'shop'])
            ->where('shop_id', $shopId)
            ->select('promotion_products.*');
        Log::info("Querying promotion products for shop ID: {$shopId}", ['query' => $query->get()]);
        return DataTables::of($query)
            ->addColumn('promotion_name', fn($product) => $product->promotion?->getTranslation('name', 'uz') ?? '-')
            ->addColumn('shop_name', fn($product) => $product->shop?->name ?? '-')
            ->addColumn('status_label', fn($product) => $product->status ? '✅ Faol' : '❌ Faol emas')
            ->addColumn('created_at', fn($product) => optional($product->created_at)?->format('d.m.Y H:i') ?? '-')
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row'    => $item,
                    'routes' => [
                        'edit'   => "/admin/promotion_products/{$item->id}/edit",
                        'status' => "/admin/promotion_products/{$item->id}/change_status",
                    ],
                ])->render();
            })
            ->rawColumns(['promotion_name', 'shop_name', 'status_label', 'actions'])
            ->make(true);
    }

    /**
     * Tahrirlash formasi uchun ma'lumot olish
     */
    public function edit(Request $request, $id)
    {
        $product = PromotionProduct::findOrFail($id);

        $promotions = Promotions::select('id', 'name')
            ->get()
            ->map(function ($promotion) {
                return [
                    'id'   => $promotion->id,
                    'name' => $promotion->getTranslation('name', 'uz'),
                ];
            });

        $shops = PromotionShop::where('promotion_id', $product->promotion_id)
            ->select('id', 'name')
            ->get();

        return response()->json([
            'product'    => $product,
            'promotions' => $promotions,
            'shops'      => $shops,
        ]);
    }

    /**
     * Ma'lumotlarni yangilash
     */
    public function update(Request $request, $id)
    {
        $product = PromotionProduct::findOrFail($id);

        $validated = $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'shop_id'      => 'required|exists:promotion_shops,id',
            'name'         => 'required|string|max:255',
            'status'       => 'required|boolean',
        ], [
            'promotion_id.required' => 'Aksiya tanlanishi shart.',
            'shop_id.required'      => 'Do‘kon tanlanishi shart.',
            'name.required'         => 'Mahsulot nomini kiriting.',
            'status.required'       => 'Holat tanlanishi shart.',
        ]);

        $product->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Mahsulot ma’lumotlari muvaffaqiyatli yangilandi',
        ]);
    }
}
