<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SalesReceipt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class SelesReceiptController extends Controller
{
    public function data(Request $request)
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
                    'routes' => ['show' => "/admin/sales-receipts/{$r->id}/show"],
                ])->render()
            )
            ->rawColumns(['actions', 'user_info']) // HTML chiqishi uchun
            ->make(true);
    }
    public function wonPromotionSelesReceipts($promotionId)
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
                    'routes' => ['show' => "/admin/sales-receipts/{$r->id}/show"],
                ])->render()
            )
            ->rawColumns(['actions', 'user_info']) // HTML chiqishi uchun
            ->make(true);
    }
}
