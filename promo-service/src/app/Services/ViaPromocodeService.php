<?php

namespace App\Services;

use App\Jobs\PrizePromoUpdateJob;
use App\Models\Platform;
use App\Models\Prize;
use App\Models\PrizeMessage;
use App\Models\PrizePromo;
use App\Models\PromoCode;
use App\Models\PromotionMessage;
use App\Models\Promotions;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ViaPromocodeService
{
    public function __construct(
        private PromotionRepository $promotionRepository
    ) {
        $this->promotionRepository = $promotionRepository;
    }

    public function getPromotion()
    {
        $cacheKey = 'promotions:platform:mobile:page:' . request('page', 1);
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        return Cache::store('redis')->remember($cacheKey, $ttl, function () {
            return  $this->promotionRepository->getAllPromotionsForMobile();
        });
    }
    public function getPromotionById($id)
    {
        $cacheKey = 'HasPromotion:mobile' . $id;
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        return Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return  $this->promotionRepository->getPromotionByIdforViaPromocode($id);
        });
    }
    public function getPlatforms()
    {
        return   Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return Platform::where('name', 'mobile')->value('id');
        });
    }


    public function getPromotionMessage($promotionId, $lang, $type): string
    {
        $message = PromotionMessage::getMessageForPromotionId($promotionId, 'mobile', $type);
        return $message ? $message->getTranslation('message', $lang) : "Promocode muvaffaqiyatli ro'yhatga olindi.";
    }

    public function getPrizeMessage($prize, $lang): string
    {
        $message = PrizeMessage::getMessageFor($prize, 'mobile', 'success');
        return $message ? $message->getTranslation('message', $lang) : "Tabriklaymiz siz {$prize->name} yutdingiz.";
    }

    public function handlePrizeEvaluation($promocode, $promotion, $today, $lang, &$action, &$status, &$message): ?int
    {
        // 1. Auto prize
        $prizePromo = PrizePromo::with(['prize.message', 'prize.prizeUsers'])
            ->where('promo_code_id', $promocode->id)
            ->whereHas('prize', function ($q) use ($today) {
                $q->where('is_active', true)
                    ->whereHas('prizeUsers', fn($query) => $query->whereDate('created_at', $today), '<', DB::raw('daily_limit'));
            })->first();

        if ($prizePromo) {
            $action = "auto_win";
            $status = "won";
            $message = $this->getPrizeMessage($prizePromo->prize, $lang);
            Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
            return $prizePromo->prize->id;
        }

        // 2. Smart prize
        $smartPrizes = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
            ->with(['smartRandomValues.rule'])->get();

        foreach ($smartPrizes as $prize) {
            if ($this->isValidSmartPrize($prize, $promocode->promocode)) {
                $action = "auto_win";
                $status = "won";
                $message = $this->getPrizeMessage($prize, $lang);
                return $prize->id;
            }
        }

        // 3. Manual prize fallback
        $hasManualPrize = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'manual'))->exists();

        if ($hasManualPrize) {
            $action = "vote";
            $status = "pending";
            $message = $this->getPromotionMessage($promotion->id, $lang, 'success');
        }

        return null;
    }

    public function isValidSmartPrize($prize, string $code): bool
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
