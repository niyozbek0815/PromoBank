<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use App\Services\PrizeCategoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;

class PrizeCategoryController extends Controller
{
    protected $service;
    public function __construct(PrizeCategoryService $service)
    {
        $this->service = $service;
    }
    public function data($promotion, $type, Request $request)
    {
        $query = Prize::where('promotion_id', $promotion)
            ->whereHas('category', fn($q) => $q->where('name', $type))
            ->with(['category:id,name,display_name'])
            ->orderBy('index', 'desc')
            ->select('prizes.*');

        return DataTables::of($query)
            ->addColumn('index', fn($item) => $item->index)
            ->addColumn('category', fn($item) => $item->category->display_name ?? '-')
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
                    // 'delete' => "/admin/prize/{$row->id}/delete",
                    'status' => "/admin/prize/{$row->id}/status",
                ],
            ])->render())
            ->rawColumns(['status', 'actions'])
            ->make(true);
    }
    public function show(int $promotion, string $type, Request $request)
    {
        $data = $this->fetchDataFromService($type, $promotion);
        if (blank($data)) {
            abort(404, 'Maʼlumot topilmadi');
        }
        return response()->json($data);
    }

    protected function fetchDataFromService(string $type, int $promotion)
    {
        return match ($type) {
            'manual' => $this->service->getManualData($promotion, $type),
            'smart_random' => $this->service->getSmartRandomData($promotion, $type),
            'auto_bind' => $this->service->autoBindData($promotion, $type),
            'weighted_random' => $this->service->getWeightedRandom($promotion, $type),
            default => [],
        };
    }
}
