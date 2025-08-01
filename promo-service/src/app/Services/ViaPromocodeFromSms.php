<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Prize;
use App\Models\Platform;
use App\Models\PromoCode;
use App\Models\PrizePromo;
use App\Jobs\PrizePromoUpdateJob;
use App\Jobs\PromoCodeConsumeJob;
use App\Jobs\CreatePromoActionJob;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class ViaPromocodeFromSms
{
    public function __construct(private FromServiceRequest $forwarder)
    {
        $this->forwarder = $forwarder;
    }
    public function viaPromocode($data)
    {
        $promocode = $data['promo_code'] ?? null;
        $shortPhone = $data['short_phone'] ?? null;
        $phone = $data['phone'] ?? null;
        $baseUrl = config('services.urls.auth_service');
        $message = "Kechirasiz, bu promocode yutuqsiz";
        $shortPhone2 = null;
        $action = "vote";
        $status = "failed";
        $today = Carbon::today();
        $platformId = $this->getPlatforms();


        $promo = PromoCode::with('promotion')->where('promocode', $promocode)->first();
        $promotion = $promo?->promotion;

        if ($promotion && $promotion->participationTypesSms->isNotEmpty()) {
            $rules = json_decode($promotion->participationTypesSms->first()->additional_rules, true);
            $shortPhone2 = $rules['phone'] ?? null;
        }

        if (!$promo) {
            $message = 'Promo code topilmadi';
            logger()->warning($message, ['promocode' => $promocode]);
        } elseif ($shortPhone2 !== $shortPhone) {
            $message = 'Promo code noto\'g\'ri yoki mos kelmaydi';
            logger()->warning($message, [
                'promocode' => $promocode,
                'short_phone' => $shortPhone,
            ]);
        } else {

            return   $this->proocessPromoCode($baseUrl, $phone, $promo, $promotion, $platformId, $today, $action, $status, $message);
        }
        return [
            'action' => $action,
            'status' => $status,
            'message' => $message,
        ];
    }
    private function proocessPromoCode($baseUrl, $phone, $promo, $promotion, $platformId, $today, &$action, &$status, &$message)
    {

        $response = $this->forwarder->forward(
            'POST',
            $baseUrl,
            '/users_for_sms',
            ['phone' => $phone]
        );
        if ($response->successful()) {
            $user = data_get($response->json(), 'data.user');
            if (!$user) {
                logger()->error('User ID mavjud emas, lekin response successful', [
                    'response' => $response->json(),
                    'base_url' => $baseUrl,
                ]);
            }

            logger()->info('User topildi yoki yaratildi', ['user' => $user]);
            if ($promo->is_used) {
                $action = "claim";
                $status = "blocked";
                $message = "Kechirasiz, bu promocode avval ishlatilgan.";
            } else {
                if (in_array($promotion->winning_strategy, ['immediate', 'hybrid'])) {
                    $prize = $this->handlePrizeEvaluation($promo, $promotion, $today, $action, $status, $message);
                }

                Queue::connection('rabbitmq')->push(new PromoCodeConsumeJob(
                    promoCodeId: $promo->id,
                    userId: $user['id'],
                    platformId: $platformId,
                    receiptId: $receiptId ?? null,
                    promotionProductId: $promotionProductId ?? null,
                    prizeId: $prize['id'] ?? null,
                    subPrizeId: $subPrizeId ?? null,
                    promotionId: $promotion->id,
                ));
            }

            Queue::connection('rabbitmq')->push(new CreatePromoActionJob([
                'promotion_id' => $promotion->id,
                'promo_code_id' => $promo->id,
                'user_id' => $user['id'],
                'prize_id' => $prize['id'] ?? null,
                'action' => $action,
                'status' => $status,
                'attempt_time' => now(),
                'message' => null,
            ]));
            return  [
                'action' => $action,
                'status' => $status,
                'message' => $message,
            ];

            // ➤ Shu yerda user_id bilan ishlashni davom ettir
            // Misol:
            // PromoRegistrationService::registerUser($userId, $this->data['promo_code']);

        } else {
            logger()->error('Userni olishda xatolik', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return;
        }
    }
    private function getPlatforms()
    {
        return  Cache::store('redis')->remember('platform:sms:id', now()->addMinutes(60), function () {
            return Platform::where('name', 'sms')->value('id');
        });
    }
    private function handlePrizeEvaluation($promocode, $promotion, $today, &$action, &$status, &$message)
    {
        // 1. Auto prize
        $prizePromo = PrizePromo::with(['prize.message', 'prize.prizeUsers'])
            ->where('promo_code_id', $promocode->id)
            ->whereHas('prize', function ($q) use ($today) {
                $q->where('is_active', true)
                    ->whereHas('prizeUsers', fn($query) => $query->whereDate('created_at', $today), '<', DB::raw('daily_limit'));
            })->with('prize')->first();

        if ($prizePromo) {
            $action = "auto_win";
            $status = "won";
            $message = "Siz sovg'a yutib oldingiz";
            $prize = $prizePromo->prize;
            Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
        }

        $smartPrizes = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
            ->with(['smartRandomValues.rule'])->orderBy('index', 'asc')->get();

        foreach ($smartPrizes as $smartprize) {
            if ($this->isValidSmartPrize($smartprize, $promocode->promocode)) {
                $action = "auto_win";
                $status = "won";
                $message = "Siz sovg'a yutib oldingiz";
                $prize = $smartprize;
                break;
            }
        }

        // 3. Manual prize fallback
        if ($status !== "won") {
            $hasManualPrize = Prize::where('promotion_id', $promotion->id)
                ->whereHas('category', fn($q) => $q->where('name', 'manual'))->exists();

            if ($hasManualPrize) {
                $action = "vote";
                $status = "pending";
                $message = " Sizning ovozingiz qabul qilindi. Natijalar keyinroq e'lon qilinadi.";
            }
        }


        return [
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'prize' => $prize ?? null,
        ];
    }
    private function isValidSmartPrize($prize, string $code): bool
    {
        foreach ($prize->smartRandomValues as $ruleValue) {
            $method = match ($ruleValue->rule->key) {
                'code_length' => 'checkCodeLength',
                'uppercase_count' => 'checkUppercaseCount',
                'lowercase_count' => 'checkLowercaseCount',
                'digit_count' => 'checkDigitCount',
                'special_char_count' => 'checkSpecialCharCount',
                'starts_with' => 'checkStartsWith',
                'not_starts_with' => 'checkNotStartsWith',
                'ends_with' => 'checkEndsWith',
                'not_ends_with' => 'checkNotEndsWith',
                'contains' => 'checkContains',
                'not_contains' => 'checkNotContains',
                'contains_sequence' => 'checkContainsSequence',
                'unique_char_count' => 'checkUniqueCharCount',
                default => null
            };

            if (!$method || !method_exists($this, $method)) {
                Log::warning("Unknown rule method for key: {$ruleValue->rule->key}");
                return false;
            }

            $values = json_decode($ruleValue->values, true);

            if (!$this->{$method}($code, $ruleValue->operator, $values)) {
                return false;
            }
        }

        return true;
    }
    private function checkCodeLength(string $promocode, string $operator, array $values)
    {
        $length = strlen($promocode);
        return $this->compare($length, $operator, $values[0]);
    }

    private function checkUppercaseCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[A-Z]/', $promocode, $matches);
        $uppercaseCount = count($matches[0]);
        return $this->compare($uppercaseCount, $operator, $values[0]);
    }

    private function checkLowercaseCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[a-z]/', $promocode, $matches);
        $lowercaseCount = count($matches[0]);
        return $this->compare($lowercaseCount, $operator, $values[0]);
    }

    private function checkDigitCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/\d/', $promocode, $matches);
        $digitCount = count($matches[0]);
        return $this->compare($digitCount, $operator, $values[0]);
    }

    private function checkSpecialCharCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[^a-zA-Z0-9]/', $promocode, $matches);
        $specialCharCount = count($matches[0]);
        return $this->compare($specialCharCount, $operator, $values[0]);
    }

    private function checkStartsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'starts_with');
    }

    private function checkNotStartsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_starts_with');
    }

    private function checkEndsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'ends_with');
    }

    private function checkNotEndsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_ends_with');
    }

    private function checkContains(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'contains');
    }

    private function checkNotContains(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_contains');
    }

    private function checkContainsSequence(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'contains_sequence');
    }

    private function checkUniqueCharCount(string $promocode, string $operator, array $values)
    {
        $uniqueChars = count(array_unique(str_split($promocode)));
        return $this->compare($uniqueChars, $operator, $values[0]);
    }

    private function checkStringCondition(string $promocode, string $operator, array $values, string $type)
    {
        $isValid = false;
        foreach ($values as $value) {
            if ($type === 'starts_with') {
                $isValid = str_starts_with($promocode, $value);
            } elseif ($type === 'not_starts_with') {
                $isValid = !str_starts_with($promocode, $value);
            } elseif ($type === 'ends_with') {
                $isValid = str_ends_with($promocode, $value);
            } elseif ($type === 'not_ends_with') {
                $isValid = !str_ends_with($promocode, $value);
            } elseif ($type === 'contains') {
                $isValid = strpos($promocode, $value) !== false;
            } elseif ($type === 'not_contains') {
                $isValid = strpos($promocode, $value) === false;
            } elseif ($type === 'contains_sequence') {
                $isValid = strpos($promocode, $value) !== false;
            }
            if ($operator === 'in' && !$isValid) {
                return false;
            }
        }
        return $isValid;
    }

    private function compare($value, string $operator, $compareValue)
    {
        switch ($operator) {
            case '=':
                return $value == $compareValue;
            case '>=':
                return $value >= $compareValue;
            case '<=':
                return $value <= $compareValue;
            case '!=':
                return $value != $compareValue;
            case 'in':
                return in_array($value, $compareValue);
            default:
                return false;
        }
    }
}
