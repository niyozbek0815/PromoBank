<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoAction;
use App\Models\PromoCodeUser;
use App\Models\SalesReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SelesReceiptController extends Controller
{
    public function data(Request $request)
    {
        $query = SalesReceipt::query()
            ->with('userCache:id,user_id,name,phone')
            ->withCount([
                'promoCodeUsers as manual_count' => fn($q) => $q->whereNull('prize_id'),
                'promoCodeUsers as prize_count' => fn($q) => $q->whereNotNull('prize_id'),
            ]);

        return DataTables::of($query)
            ->addColumn('id', fn(SalesReceipt $r) => $r->id)
            ->addColumn('user_info', function (SalesReceipt $r) {
                $name = e($r->userCache?->name ?? '—');
                $phone = e($r->userCache?->phone ?? '—');

                return "<div>
                <strong>{$name}</strong>
                <div class='text-muted' style='font-size:12px;'>{$phone}</div>
            </div>";
            })            ->addColumn('manual_count', fn(SalesReceipt $r) => $r->manual_count)
            ->addColumn('prize_count', fn(SalesReceipt $r) => $r->prize_count)
            ->addColumn('summa', fn(SalesReceipt $r) => number_format($r->summa ?? 0, 0, '.', ' '))
            ->addColumn('check_date', fn(SalesReceipt $r) => $r->check_date?->format('d.m.Y H:i') ?? '-')
            ->addColumn('created_at', fn(SalesReceipt $r) => $r->created_at?->format('d.m.Y H:i') ?? '-')
            ->addColumn(
                'actions',
                fn($r) =>
                view('admin.actions', [
                    'row' => $r,
                    'routes' => ['show' => "/admin/sales-receipts/show/{$r->id}"],
                ])->render()
            )
            ->rawColumns(['actions', 'user_info'])
            ->make(true);
    }

    public function winningByPromotion($promotionId)
    {
        $query = SalesReceipt::query()
            ->select(
                'sales_receipts.*',
                'users_caches.phone',
                'users_caches.name as user_name',
                DB::raw('
                SUM(CASE WHEN promo_code_users.prize_id IS NULL THEN 1 ELSE 0 END) AS manual_count,
                SUM(CASE WHEN promo_code_users.prize_id IS NOT NULL THEN 1 ELSE 0 END) AS prize_count
            ')
            )
            ->join('promo_code_users', 'promo_code_users.receipt_id', '=', 'sales_receipts.id')
            ->leftJoin('users_caches', 'users_caches.user_id', '=', 'sales_receipts.user_id')
            ->where('promo_code_users.promotion_id', $promotionId)
            ->groupBy('sales_receipts.id', 'users_caches.phone', 'users_caches.name');
        return DataTables::of($query)
            ->addColumn('id', fn($r) => $r->id)
            ->addColumn('user_info', function ($r) {
                $name = $r->user_name ?? '-';
                $phone = $r->phone ?? '-';
                return "<div>{$name}</div><div style='color: #6c757d; font-size: 12px;'>{$phone}</div>";
            })
            ->addColumn('manual_count', fn($r) => (int) $r->manual_count)
            ->addColumn('prize_count', fn($r) => (int) $r->prize_count)
            ->addColumn('summa', fn($r) => number_format((float) str_replace(' ', '', $r->summa), 0, '.', ' '))
            ->addColumn('check_date', fn($r) => $r->check_date ? date('d.m.Y H:i', strtotime($r->check_date)) : '-')
            ->addColumn('created_at', fn($r) => $r->created_at ? date('d.m.Y H:i', strtotime($r->created_at)) : '-')
            ->addColumn(
                'actions',
                fn($r) =>
                view('admin.actions', [
                    'row' => $r,
                    'routes' => ['show' => "/admin/sales-receipts/show/{$r->id}"],
                ])->render()
            )
            ->rawColumns(['actions', 'user_info']) // HTML chiqishi uchun
            ->make(true);
    }
    public function show($id)
    {
        $receipt = SalesReceipt::query()
            ->with([
                'userCache:id,user_id,name,phone',
                'products:id,receipt_id,name,count,summa,created_at',
            ])
            ->withCount([
                'promoCodeUsers as manual_count' => fn($q) => $q->whereNull('prize_id'),
                'promoCodeUsers as prize_count' => fn($q) => $q->whereNotNull('prize_id'),
            ])
            ->findOrFail($id);
        $formatted = [
            'id' => $receipt->id,
            'check_id' => $receipt->chek_id,
            'company_name' => $receipt->name ?: '—',
            'address' => $receipt->address ?: '—',
            'nkm_number' => $receipt->nkm_number ?: '—',
            'sn' => $receipt->sn ?: '—',
            'check_date' => $receipt->check_date?->format('d.m.Y H:i') ?? '—',
            'summa' => number_format($receipt->summa ?? 0, 2, '.', ' '),
            'qqs_summa' => number_format($receipt->qqs_summa ?? 0, 2, '.', ' '),
            'manual_count' => (int) $receipt->manual_count,
            'prize_count' => (int) $receipt->prize_count,
            'user' =>$receipt->userCache?->phone ?? $receipt->user_id ,
            'products' => $receipt->products->map(fn($p) => [
                'id' => $p->id,
                'name' => $p->name,
                'count' => (int) $p->count,
                'summa' => number_format($p->summa ?? 0, 2, '.', ' '),
                'created_at' => $p->created_at?->format('d.m.Y H:i') ?? '—',
            ]),
            'created_at' => $receipt->created_at?->format('d.m.Y H:i'),
        ];

        $promoCodes = PromoCodeUser::with([
            'prize',           // prize bilan bog‘liq ma’lumot
            'promotion',
            'platform',
            'promotionProduct'        // promotionProduct bilan bog‘liq ma’lumot
        ])
            ->where('receipt_id', $id)
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($item): array {
                return [
                    'id' => $item->id,
                    'promotion_id' => $item->promotion_id,
                    'prize_name' => $item->prize?->name,            // nullable safe
                    'created_at' => $item->created_at,
                    'promotion_name' => $item->promotion?->name,    // nullable safe
                    'platform_name' => $item->platform?->name,        // nullable safe
                    'product_name' => $item->promotionProduct?->name, // nullable safe
                    'user_id' => $item->user_id,
                ];
            });

        // Foydalanuvchiga berilgan rag‘bat ballari (agar mavjud bo‘lsa)
        $encouragement = DB::table('encouragement_points')
            ->select('points', 'created_at')
            ->where('receipt_id', $id)
            ->orderByDesc('created_at')
            ->first();

        // Kvitansiyaga oid barcha harakatlar (PromoAction loglari)

$actions = PromoAction::with([
        'promotion:id,name',
        'promoCode:id,code',
        'prize:id,name',
        'userCache:user_id,name,phone',
        'platform:id,name',
        'shop:name'
    ])
    ->where('receipt_id', $id)
    ->orderByDesc('created_at')
    ->get()
            ->map(function ($item): array {
                return [
                    'id' => $item->id,
                    'action' => $item->action,
                    'status' => $item->status,
                    'message' => $item->message??'-',
                    'user' => $item->userCache?->phone  ?? $item->user_id,
                    'prize_name' => $item->prize?->name,            // nullable safe
                    'promotion_name' => $item->promotion?->name,    // nullable safe
                    'platform_name' => $item->platform?->name,
                    'shop_name' => $item->shop?->name ?? '-',
                    'created_at' => $item->created_at,
                ];
            });

        // JSON chiqish so‘ralgan bo‘lsa
        if (request()->wantsJson()) {
            return response()->json([
                'receipt' => $receipt,
                'promo_codes' => $promoCodes,
                'encouragement' => $encouragement,
                'actions' => $actions,
            ]);
        }

        // Blade uchun ma’lumotlarni yuborish
        return view('admin.sales_receipts.show', compact(
            'receipt',
            'products',
            'promoCodes',
            'encouragement',
            'actions'
        ));
    }
}
