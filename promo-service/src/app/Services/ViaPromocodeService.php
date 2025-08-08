<?php

namespace App\Services;

use App\Jobs\CreatePromoActionJob;
use App\Jobs\PrizePromoUpdateJob;
use App\Jobs\PromoCodeConsumeJob;
use App\Models\Prize;
use App\Models\PrizePromo;
use App\Repositories\PlatformRepository;
use App\Repositories\PrizeMessageRepository;
use App\Repositories\PromoCodeRepository;
use App\Repositories\PromotionMessageRepository;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class ViaPromocodeService
{
    public function __construct(
        private PromotionRepository $promotionRepository,
        private PromoCodeRepository $promoCodeRepository,
        private PlatformRepository $platformRepository,
        private PromotionMessageRepository $promotionMessageRepository,
        private PrizeMessageRepository $prizeMessageRepository
    ) {
        $this->promotionRepository = $promotionRepository;
        $this->promoCodeRepository = $promoCodeRepository;
        $this->platformRepository = $platformRepository;
        $this->promotionMessageRepository = $promotionMessageRepository;
        $this->prizeMessageRepository = $prizeMessageRepository;
    }


    public function getPromotionById($id)
    {
        $cacheKey = 'HasPromotion:mobile' . $id;
        $ttl = now()->addMinutes(5); // 5 daqiqa kesh
        return Cache::store('redis')->remember($cacheKey, $ttl, function () use ($id) {
            return  $this->promotionRepository->getPromotionByIdforVia($id, ['text_code', 'qr_code']);
        });
    }

    public function proccess($req, $user, $id)
    {
        $promocodeInput = $req['promocode'];
        $lang = $req['lang'];
        $today = Carbon::today();
        $action = "vote";
        $status = "failed";

        $platformId = $this->getPlatforms();
        $data = [];
        $promotion = $this->getPromotionById($id);
        if (!$promotion) {
            return ['promotion'=> true];
        }
        $promocode = $this->promoCodeRepository->getPromoCodeByPromotionIdAndByPromocode($id, $promocodeInput);

        if (!$promocode) {
            return ['promocode'=> true];
        }
        // return $this->successResponse(['promotions' => $promocode], "success");

        if ($promocode->is_used) {
            $action = "claim";
            $status = "blocked";
            $message = $this->getPromotionMessage($promotion->id, $lang, 'claim');
        } else {
            if (in_array($promotion->winning_strategy, ['immediate', 'hybrid'])) {
                $prizeId = $this->handlePrizeEvaluation($promocode, $promotion, $today, $lang, $action, $status, $message);
            }
            if (!in_array($promotion->winning_strategy, ['immediate', 'hybrid']) || !$prizeId) {
                $action = "vote";
                $status = "pending";
                $message = $this->getPromotionMessage($promotion->id, $lang, 'success');
            }

            Queue::connection('rabbitmq')->push(new PromoCodeConsumeJob(
                promoCodeId: $promocode->id,
                userId: $user['id'],
                platformId: $platformId,
                receiptId: $receiptId ?? null,
                promotionProductId: $promotionProductId ?? null,
                prizeId: $prizeId ?? null,
                subPrizeId: $subPrizeId ?? null,
                promotionId: $id
            ));
        }

        Queue::connection('rabbitmq')->push(new CreatePromoActionJob([
            'promotion_id' => $promotion->id,
            'promo_code_id' => $promocode->id,
            'user_id' => $user['id'],
            'prize_id' => $prizeId ?? null,
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
    }
    private function getPlatforms()
    {
        return  Cache::store('redis')->remember('platform:mobile:id', now()->addMinutes(60), function () {
            return $this->platformRepository->getPlatformGetId('mobile');
        });
    }


    private function getPromotionMessage($promotionId, $lang, $type): string
    {
        $message = $this->promotionMessageRepository->getMessageForPromotion($promotionId, 'mobile', $type);
        return $message->getTranslation('message', $lang);
    }

    private function getPrizeMessage($prize, $lang): string
    {
        $message = $this->prizeMessageRepository->getMessageForPrize($prize->id, 'mobile', 'success');
        return $message ? $message->getTranslation('message', $lang) : "Tabriklaymiz siz {$prize->name} yutdingiz.";
    }
    private function handlePrizeEvaluation($promocode, $promotion, $today, $lang, &$action, &$status, &$message)
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
            $message = $this->getPrizeMessage($prizePromo->prize, $lang);
            $wonPrize = $prizePromo->prize;
            Queue::connection('rabbitmq')->push(new PrizePromoUpdateJob($prizePromo->id));
        }

        $smartPrizes = Prize::where('promotion_id', $promotion->id)
            ->whereHas('category', fn($q) => $q->where('name', 'smart_random'))
            ->with(['smartRandomValues.rule'])->orderBy('index', 'asc')->get();

        foreach ($smartPrizes as $prize) {
            if ($this->isValidSmartPrize($prize, $promocode->promocode)) {
                $action = "auto_win";
                $status = "won";
                $message = $this->getPrizeMessage($prize, $lang);
                $wonPrize = $prize;
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
                $message = $this->getPromotionMessage($promotion->id, $lang, 'success');
            }
        }


        return isset($prize) ? $prize->id : null;

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
