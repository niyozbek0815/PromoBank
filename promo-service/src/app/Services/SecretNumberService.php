<?php
namespace App\Services;
use App\Models\EncouragementPoint;
use App\Models\Messages;
use App\Models\ParticipationType;
use App\Models\Promotions;
use App\Models\SecretNumber;
use App\Models\SecretNumberEntry;
use App\Models\UserPointBalance;
use App\Repositories\PlatformRepository;
use App\Repositories\PromoCodeRepository;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SecretNumberService
{




    public function proccess($req, $user, $promotionId)
    {
        $lang = $req['lang'] ?? 'uz';

        try {
            Log::info('SecretNumberService started', [
                'promotion_id' => $promotionId,
                'user_id' => $user['id'],
                'input' => $req,
            ]);
            $secretConfig = $this->getPromotionSecretConfig($promotionId);
            Log::info($secretConfig);

            if (!$secretConfig) {
                $messages = [
                    'uz' => 'Aksiya topilmadi yoki sirli raqam turi yo‘q.',
                    'ru' => 'Акция не найдена или не поддерживает тип secret_number.',
                    'en' => 'Promotion not found or not using secret_number type.',
                    'kr' => 'Акция топилмади ёки сирли рақам тури йўқ.',
                ];
                return $this->returnformat($messages[$lang] ?? $messages['uz'],null,422);
            }
            if(!$this->isPromotionActive($secretConfig)){
                $message = $this->getMessage(
                    $promotionId,
                    null,
                    $lang,
                    'inactive_window',
                    $req['secret_number'],
                    "telegram"
                );
                return $this->returnformat($message,null, 422);
            }
            $number = $this->findValidSecretNumber($promotionId, $req['secret_number'], $secretConfig['secret_number_seconds']);
            if (!$number) {
                $message = $this->getMessage(
                    $promotionId,
                    null,
                    $lang,
                    'invalid',
                    $req['secret_number'],
                    "telegram"
                );
                return $this->returnformat($message,null,422);
            }
            if ($this->entryExists($number->id, $user['id'])) {
                $message = $this->getMessage(
                    $promotionId,
                    null,
                    $lang,
                    'claim',
                    $req['secret_number'],
                    "telegram"
                );
                return $this->returnformat($message,null,422);
            }

            return DB::transaction(function () use ($number, $user, $req, $secretConfig) {
                $points = $number->points > 0
                    ? $number->points
                    : ($secretConfig['secret_number_points'] ?? 5);

                // 1️⃣ Yangi yozuv qo‘shish
                $entry = SecretNumberEntry::create([
                    'secret_number_id' => $number->id,
                    'user_id' => $user['id'],
                    'points_awarded' => $points,
                    'user_input' => $req['secret_number'],
                    'is_accepted' => true,
                ]);

                Log::info('SecretNumberEntry created', ['entry' => $entry]);

                // 2️⃣ Foydalanuvchi balansini yangilash
                UserPointBalance::firstOrCreate(['user_id' => $user['id']], ['balance' => 0]);
                UserPointBalance::where('user_id', $user['id'])->increment('balance', $points);

                Log::info('UserPointBalance incremented', [
                    'user_id' => $user['id'],
                    'points' => $points,
                ]);

                // 3️⃣ Rag‘bat ballari logi
                $encourage = EncouragementPoint::create([
                    'user_id' => $user['id'],
                    'scope_id' => $entry->id,
                    'scope_type' => 'secret_number',
                    'points' => $points,
                ]);

                Log::info('EncouragementPoint created', ['entry' => $encourage]);
                $message = $this->getMessage(
                    $number['promotion_id'],
                    $points,
                    $req['lang'],
                    'win',
                    $req['secret_number'],
                    "telegram"
                );
                return $this->returnformat($message,$points, 200);
            });
        } catch (Throwable $e) {
            Log::error('SecretNumberService failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return $this->returnformat('Internal error, please try again later',null, 500);
        }
    }

    public function isPromotionActive($secretConfig)
    {
        $now = Carbon::now();
        $start = Carbon::createFromTimeString($secretConfig['promotion_start_time']);
        $end = Carbon::createFromTimeString($secretConfig['promotion_end_time']);

        $isActive = $start->lt($end)
            ? $now->between($start, $end)
            : ($now->gte($start) || $now->lte($end));

        Log::info("Aksiya ID: " . ($isActive ? '✅ faol' : '⛔ yopiq'), ['$now'=>$now,'start'=>$start,'end'=>$end]);
        return $isActive;
    }
    private function returnformat(string $message, $poinst=null, int $code = 422): array
    {
        return [
            'success' => false,
            'status' => 'failed',
            'code' => $code,
            'points'=>$poinst,
            'message' => $message,
        ];
    }
    private function getPromotionSecretConfig(int $promotionId): ?array
    {
        Cache::forget("promotion_{$promotionId}_secret_number_config");
        return Cache::remember("promotion_{$promotionId}_secret_number_config", 3600, function () use ($promotionId) {
            $type = Cache::remember('participation_type_secret_number', 86400, function () {
                return ParticipationType::where('slug', 'secret_number')->first();
            });

            if (!$type) {
                Log::warning('ParticipationType secret_number not found');
                return null;
            }

            $promotion = Promotions::whereHas('participantTypeIds', function ($q) use ($type) {
                $q->where('participation_type_id', $type->id);
            })->find($promotionId);

            if (!$promotion) {
                Log::warning('Promotion with secret_number not found', ['promotion_id' => $promotionId]);
                return null;
            }

            return [
                'secret_number_seconds' => (int) ($promotion->extra_conditions['secret_number_seconds'] ?? 60),
                'secret_number_points' => (int) ($promotion->extra_conditions['secret_number_points'] ?? 5),
                'promotion_start_time' =>  ($promotion->extra_conditions['promotion_start_time'] ?? "16:00"),
                'promotion_end_time' =>  ($promotion->extra_conditions['promotion_end_time'] ?? "18:00"),
            ];
        });
    }
    private function findValidSecretNumber(int $promotionId, int $numberValue, int $validSeconds): ?SecretNumber
    {

       $now = now('UTC');

        // DB dan faqat promotion_id va number bilan filter
        $numbers = SecretNumber::where('promotion_id', $promotionId)
            ->where('number', trim((string) $numberValue))
            ->get();

        // Carbon bilan $validSeconds tekshirish
        $number = $numbers->first(function ($sn) use ($now, $validSeconds) {
            $startAt = $sn->start_at->copy()->setTimezone('UTC');
            $endAt = $startAt->copy()->addSeconds($validSeconds);

            return $startAt->lte($now) && $now->lte($endAt);
        });

        Log::info('findValidSecretNumber result', [
            'promotion_id' => $promotionId,
            'number_value' => trim((string) $numberValue),
            'number_found' => (bool) $number,
            'number_object' => $number,
            'now' => $now->toDateTimeString(),
            'valid_seconds' => $validSeconds,
        ]);

        return $number;
    }

    private function entryExists(int $secretNumberId, int $userId): bool
    {
        $exists = SecretNumberEntry::where('secret_number_id', $secretNumberId)
            ->where('user_id', $userId)
            ->exists();

        Log::info('entryExists check', [
            'secret_number_id' => $secretNumberId,
            'user_id' => $userId,
            'exists' => $exists,
        ]);

        return $exists;
    }

    private function getMessage(int $promotionId, ?int $points, string $lang, string $status, string $promocode, ?string $channel = 'mobile')
    {
        $message = Messages::resolveLocalizedMessage('secret-number', [
            'status' => $status,
            'prize_id' => $prize['id'] ?? null,
            'promotion_id' => $promotionId,
            'lang' => $lang,
            'channel' => $channel,
        ]);

        if (!$message) {
            return null; // fallback topilmasa
        }

        // 3️⃣ Sovrin nomini aniqlaymiz (multi-lang yoki oddiy)


        // 4️⃣ Dinamik tokenlarni real qiymatlar bilan almashtirish
        $replacements = [
            ':code' => $promocode,
            ':id' => $promotionId,
            ':prize' => $points,
        ];

        $finalMessage = strtr($message, $replacements);

        // 5️⃣ Xabarni platformaga moslab formatlaymiz (masalan, SMS uchun max 160 belgi)
        if ($channel === 'sms') {
            return mb_strimwidth($finalMessage, 0, 160, '...');
        }
        Log::info("message:" . $finalMessage);
        return $finalMessage;
    }


}
