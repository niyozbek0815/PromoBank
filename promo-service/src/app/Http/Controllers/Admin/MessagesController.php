<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;

class MessagesController extends Controller
{

    public function data()
    {
        $query = Messages::query()
            ->where('scope_type', 'platform')
            ->whereNull('scope_id');

        return DataTables::of($query)
            ->addIndexColumn() // avtomatik tartib raqami
            ->addColumn('type', function ($item) {
                return match ($item->type) {
                    'promo' => '<i class="ph-gift me-1"></i> Promocode uchun',
                    'receipt' => '<i class="ph-receipt me-1"></i> Xarid cheki',
                    default => '<i class="ph-question me-1"></i> Nomaʼlum',
                };
            })
            ->rawColumns(['type'])->addColumn('status', function ($item) {
                $map = [
                    'claim' => ['class' => 'bg-primary', 'label' => 'Claim'],
                    'pending' => ['class' => 'bg-warning text-dark', 'label' => 'Pending'],
                    'invalid' => ['class' => 'bg-dark', 'label' => 'Invalid'],
                    'win' => ['class' => 'bg-success', 'label' => 'Win'],
                    'lose' => ['class' => 'bg-danger', 'label' => 'Lose'],
                    'fail' => ['class' => 'bg-secondary', 'label' => 'Fail'],
                ];

                $status = $item->status;

                if (!isset($map[$status])) {
                    return '<span class="badge bg-light text-dark">' . e(ucfirst($status)) . '</span>';
                }

                return '<span class="badge ' . $map[$status]['class'] . '">' . $map[$status]['label'] . '</span>';
            })->addColumn('message', fn($item) => $item->message['uz'] ?? '-')
            ->addColumn('actions', fn($row) => view('admin.actions', [
                'row' => $row,
                'routes' => [
                    'edit' => "/admin/settings/messages/{$row->id}/edit",
                ],
            ])->render())
            ->rawColumns(['actions', 'status','type'])
            ->make(true);
    }
    public function edit($id){
        $data = Messages::findOrFail($id);
        return response()->json($data);
    }
    public function update(Request $request, $id)
    {
        $message = Messages::findOrFail($id);

        // 🔒 Validatsiya
        $validated = $request->validate([
            'scope_type' => ['required', Rule::in(Messages::SCOPES)],
            'scope_id' => ['nullable', 'integer'],
            'type' => ['required', Rule::in(Messages::TYPES)],
            'status' => ['required', Rule::in(Messages::STATUSES)],
            'message' => ['required', 'array'],
            'message.uz' => ['required', 'string', 'max:500'],
            'message.ru' => ['required', 'string', 'max:500'],
            'message.en' => ['required', 'string', 'max:500'],
            'message.kr' => ['required', 'string', 'max:500'],
        ]);
        $message->update($validated);
        return response()->json(['message'=>"Updatet saccesfullly",'data'=>$message]);
    }
}
