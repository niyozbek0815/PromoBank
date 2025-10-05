<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
    public function promotionMessagesData($id)
    {
        $query = Messages::where('scope_type', 'promotion')
            ->where('scope_id', $id);

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
            ->rawColumns(['actions', 'status', 'type'])
            ->make(true);
    }
    public function prizeMessagesData($id)
    {
        $query = Messages::where('scope_type', 'prize')
            ->where('scope_id', $id);
        return DataTables::of($query->get())
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
            ->rawColumns(['actions', 'status', 'type'])
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
    public function promotionGenerate($id)
    {
        // Platform-level xabarlarni olamiz
        $platformMessages = Messages::where('scope_type', 'platform')
            ->whereNull('scope_id')
            ->get();


        DB::transaction(function () use ($platformMessages, $id) {
            foreach ($platformMessages as $msg) {
                Messages::firstOrCreate(
                    [
                        'scope_type' => 'promotion',
                        'scope_id' => $id,
                        'type' => $msg->type,
                        'status' => $msg->status, // 🔑 qo‘shildi
                    ],
                    [
                        'message' => $msg->message,
                    ]
                );
            }
        });
        return response()->json(['success'=>"Default messagelar muafaqiyatli yaratildi"]);
    }
    public function prizeGenerate($id)
    {
        // Platform-level xabarlarni olamiz
        $defaultMessages = Messages::where('scope_type', 'promotion')
        ->where('scope_id', $id)
            ->get();

        if ($defaultMessages->isEmpty()){
            $defaultMessages = Messages::where('scope_type', 'platform')
                ->whereNull('scope_id')
                ->get();
        }

        DB::transaction(function () use ($defaultMessages, $id) {
            foreach ($defaultMessages as $msg) {
                Messages::firstOrCreate(
                    [
                        'scope_type' => 'prize',
                        'scope_id' => $id,
                        'type' => $msg->type,
                        'status' => $msg->status, // 🔑 qo‘shildi
                    ],
                    [
                        'message' => $msg->message,
                    ]
                );
            }
        });
        return response()->json(['success' => "Default messagelar muafaqiyatli yaratildi"]);
    }
}
