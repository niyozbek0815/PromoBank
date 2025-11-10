<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Messages;
use App\Models\Prize;
use App\Models\Promotions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MessagesController extends Controller
{
    public function data()
    {
        return $this->prepareMessagesData(
            Messages::query()
                ->where('scope_type', 'platform')
                ->whereNull('scope_id')
        );
    }

    public function promotionMessagesData($id)
    {
        return $this->prepareMessagesData(
            Messages::where('scope_type', 'promotion')->where('scope_id', $id)
        );
    }

    public function prizeMessagesData($id)
    {
        return $this->prepareMessagesData(
            Messages::where('scope_type', 'prize')->where('scope_id', $id)
        );
    }

    /**
     * Universal DataTables builder for messages
     */
    private function prepareMessagesData($query)
    {
        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('scope_type', static function ($item) {
                return match ($item->scope_type) {
                    'platform' => 'Platform (umumiy)',
                    'promotion' => 'Promotion (aksiya)',
                    'prize' => 'Prize (sovrin)',
                    default => 'Nomaʼlum',
                };
            })
            ->addColumn('type', static function ($item) {
                return match ($item->type) {
                    'promo' => '<i class="ph-gift me-1 text-success"></i> Promokod uchun',
                    'receipt' => '<i class="ph-receipt me-1 text-info"></i> Xarid cheki uchun',
                    'secret-number' => '<i class="ph-key me-1 text-warning"></i> Sirli raqam uchun',
                    default => '<i class="ph-question me-1 text-muted"></i> Nomaʼlum',
                };
            })
            ->addColumn('status', static function ($item) {
                $statuses = [
                    'claim' => ['bg-primary', 'Takroriy kiritish (Claim)'],
                    'pending' => ['bg-warning text-dark', 'Kutilmoqda (Pending)'],
                    'invalid' => ['bg-dark', 'Noto‘g‘ri (Invalid)'],
                    'win' => ['bg-success', 'Yutgan (Win)'],
                    'lose' => ['bg-danger', 'Yutqazgan (Lose)'],
                    'fail' => ['bg-secondary', 'Xato (Fail)'],
                ];

                [$class, $label] = $statuses[$item->status] ?? ['bg-light text-dark', ucfirst($item->status)];

                return "<span class=\"badge {$class}\">" . e($label) . '</span>';
            })
            ->addColumn('channel', static function ($item) {
                return match ($item->channel) {
                    'sms' => 'SMS',
                    'telegram' => 'Telegram',
                    'mobile' => 'Mobile',
                    'web' => 'Web',
                    default => '-',
                };
            })
            ->addColumn('message', static function ($item) {
                $raw = $item->getRawOriginal('message');
                $decoded = json_decode($raw, true);
                $isJson = json_last_error() === JSON_ERROR_NONE && is_array($decoded);

                $text = $isJson
                    ? ($decoded['uz'] ?? reset($decoded))
                    : trim($raw);

                $example = str_replace(
                    [':code', ':id', ':prize'],
                    ['A1B2C3', 'CHK12345', 'Powerbank #WJR'],
                    $text
                );

                return e(Str::limit($example, 120));
            })
            ->addColumn('actions', static function ($row) {
                return view('admin.actions', [
                    'row' => $row,
                    'routes' => [
                        'edit' => "/admin/settings/messages/{$row->id}/edit",
                    ],
                ])->render();
            })
            ->rawColumns(['scope_type', 'type', 'status', 'channel', 'actions'])
            ->make(true);
    }
    public function edit($id)
    {
        $data = Messages::findOrFail($id);
        return response()->json($data);
    }
    public function update(Request $request, $id)
    {
        $message = Messages::findOrFail($id);

        // 🔒 Validatsiya
        $validated = $request->validate(
            [
                'scope_type' => ['required', Rule::in(Messages::SCOPES)],
                'scope_id' => ['nullable', 'integer'],
                'type' => ['required', Rule::in(Messages::TYPES)],
                'status' => ['required', Rule::in(Messages::STATUSES)],
                'message' => is_array($request->message)
                    ? ['required', 'array', 'min:1']
                    : ['required', 'string', 'max:500'],
            ] + (
                is_array($request->message)
                ? [
                    'message.uz' => ['required', 'string', 'max:500'],
                    'message.ru' => ['required', 'string', 'max:500'],
                    'message.en' => ['required', 'string', 'max:500'],
                    'message.kr' => ['required', 'string', 'max:500'],
                ]
                : [] // 🔹 Aks holda (SMS bo‘lsa) qo‘shimcha validatsiya shart emas
            )
        );
        $message->update($validated);
        return response()->json(['message' => "Updatet saccesfullly", 'data' => $message]);
    }
    private function mapParticipants($type): array
    {
        return [
            'id' => $type->id,
            'name' => $type->name,
            'is_enabled' => (bool) $type->pivot->is_enabled,
            'promotion_id' => $type->pivot->promotion_id,
            'participation_type_id' => $type->pivot->participation_type_id,
            'additional_rules' => $type->pivot->additional_rules,
        ];
    }
    private function mapParticipantTypes(array|string $participantNames): array
    {
        $types = [];

        if (in_array('QR code', $participantNames) || in_array('Text code', $participantNames)) {
            $types[] = 'promo';
        }

        if (in_array('Receipt scan', $participantNames)) {
            $types[] = 'receipt';
        }

        if (in_array('Secret number', $participantNames)) {
            $types[] = 'secret-number';
        }

        return $types;
    }

    public function promotionGenerate(int $promotionId)
    {
        $promotion = Promotions::with(['participationTypes:name', 'platforms:name'])->findOrFail($promotionId);

        $participantNames = $promotion->participationTypes->pluck('name')->toArray();

        $types = $this->mapParticipantTypes($participantNames);
        $platformNames = $promotion->platforms->pluck('name')->toArray();

        Log::info('promotion participants', ['names' => $participantNames, 'types' => $types, 'Platform' => $platformNames]);

        return $this->cloneMessages('platform', null, 'promotion', $promotionId, $types,$platformNames);
    }

    public function prizeGenerate(int $prizeId)
    {
        $prize = Prize::findOrFail($prizeId);

        $promotion = Promotions::with(['participationTypes:name', 'platforms:name'])->findOrFail($prize->promotion_id);

        $participantNames = $promotion->participationTypes->pluck('name')->toArray();

        $types = $this->mapParticipantTypes($participantNames);
        $platformNames = $promotion->platforms->pluck('name')->toArray();

        $source = Messages::promotion($prizeId)->exists() ? 'promotion' : 'platform';
        Log::info('promotion participants', ['names' => $participantNames, 'types' => $types]);

        return $this->cloneMessages($source, $prizeId, 'prize', $prizeId, $types, $platformNames);
    }

    /**
     * Universal message cloning helper
     * — dublikatlarsiz
     * — bulk insert bilan
     * — transaction talab qilinmaydi
     */
    private function cloneMessages(string $fromScope, ?int $fromId, string $toScope, int $toId, array $types, array $platforms)
    {
        $sourceMessages = Messages::query()
            ->when($fromScope === 'platform', fn($q) => $q->platform())
            ->when($fromScope === 'promotion', fn($q) => $q->promotion($fromId))
            ->whereIn('type', $types)
            ->get();

        // if ($sourceMessages->isEmpty()) {
        //     return response()->json(['error' => 'Manba xabarlar topilmadi.'], 404);
        // }


        foreach ($sourceMessages as $msg) {
            // agar platform xabari bo'lsa, yoki type + channel allaqachon bor bo'lsa skip qilamiz
            if (in_array($msg->channel, $platforms)) {
                Messages::firstOrCreate(
                    [
                        'type' => $msg->type,
                        'channel' => $msg->channel,
                        'scope_type' => $toScope,
                        'scope_id' => $toId,
                        'status' => $msg->status,
                    ],
                    [
                        'message' => $msg->message, // stdClass uchun oddiy property
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

            }
        }
        return response()->json([
            'success' => ucfirst($toScope) . ' scope uchun default xabarlar muvaffaqiyatli yaratildi (dublikatlarsiz).',
        ]);
    }
}
