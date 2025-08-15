<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promotions;
use App\Models\PromotionShop;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class PromotionShopController extends Controller
{
    public function create(Request $request, $promotion_id = null)
    {
        $promotions = Promotions::select('id', 'name')
            ->get()
            ->map(function ($promotion) {
                return [
                    'id'   => $promotion->id,
                    'name' => $promotion->getTranslation('name', 'uz'), // faqat uz tilini olish
                ];
            });
        return response()->json([
            'promotions'         => $promotions,
            'selected_promotion' => $promotion_id,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'promotion_id' => 'required|exists:promotions,id',
            'name'         => 'required|string|max:255',
            'adress'       => 'required|string|max:500',
        ]);

        $shop = PromotionShop::create($request->only(['promotion_id', 'name', 'adress']));

        return response()->json([
            'success' => true,
            'message' => 'Do‘kon qo‘shildi',
        ]);
    }
    public function promotiondata(Request $request, $promotionId)
    {
        $query = PromotionShop::query()
        ->where('promotion_id', $promotionId)
            ->with(['promotion'])   // promotion modelini olish
            ->withCount('products') // products_count qo‘shish
            ->select('promotion_shops.*');
        return DataTables::of($query)
            ->addColumn('promotion_name', function ($shop) {
                return $shop->promotion?->getTranslation('name', 'uz') ?? '-';
            })
            ->addColumn('products_count', function ($shop) {
                return $shop->products_count ?? 0;
            })
            ->addColumn('created_at', function ($shop) {
                return optional($shop->created_at)?->format('d.m.Y H:i') ?? '-';
            })
            ->addColumn('actions', function ($item) {
                return view('admin.actions', [
                    'row'    => $item,
                    'routes' => [
                        'edit' => "/admin/promotion_shops/{$item->id}/edit",
                    ],
                ])->render();
            })
            ->rawColumns(['promotion_name', 'actions'])
            ->make(true);
    }
    public function edit(Request $request, $id)
    {
        $shop       = PromotionShop::findOrFail($id);
        $promotions = Promotions::select('id', 'name')
            ->get()
            ->map(function ($promotion) {
                return [
                    'id'   => $promotion->id,
                    'name' => $promotion->getTranslation('name', 'uz'), // faqat uz tilini olish
                ];
            });

        return response()->json([
            'shop'       => $shop,
            'promotions' => $promotions,
        ]);
    }
    public function update(Request $request, $id)
    {
        $shop = PromotionShop::findOrFail($id);
        $shop->update($request->all());
        return response()->json([
            'success' => true,
            'message' => 'Do‘kon o‘zgartirildi',
        ]);
    }
}
