<?php

namespace App\Services;

use App\Models\Platform;
use App\Models\PromoCode;
use App\Models\Promotions;
use App\Repositories\PromotionRepository;
use Illuminate\Support\Facades\Cache;

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


    public function checkCodeLength(string $promocode, string $operator, array $values)
    {
        $length = strlen($promocode);
        return $this->compare($length, $operator, $values[0]);
    }

    public function checkUppercaseCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[A-Z]/', $promocode, $matches);
        $uppercaseCount = count($matches[0]);
        return $this->compare($uppercaseCount, $operator, $values[0]);
    }

    public function checkLowercaseCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[a-z]/', $promocode, $matches);
        $lowercaseCount = count($matches[0]);
        return $this->compare($lowercaseCount, $operator, $values[0]);
    }

    public function checkDigitCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/\d/', $promocode, $matches);
        $digitCount = count($matches[0]);
        return $this->compare($digitCount, $operator, $values[0]);
    }

    public function checkSpecialCharCount(string $promocode, string $operator, array $values)
    {
        preg_match_all('/[^a-zA-Z0-9]/', $promocode, $matches);
        $specialCharCount = count($matches[0]);
        return $this->compare($specialCharCount, $operator, $values[0]);
    }

    public function checkStartsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'starts_with');
    }

    public function checkNotStartsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_starts_with');
    }

    public function checkEndsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'ends_with');
    }

    public function checkNotEndsWith(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_ends_with');
    }

    public function checkContains(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'contains');
    }

    public function checkNotContains(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'not_contains');
    }

    public function checkContainsSequence(string $promocode, string $operator, array $values)
    {
        return $this->checkStringCondition($promocode, $operator, $values, 'contains_sequence');
    }

    public function checkUniqueCharCount(string $promocode, string $operator, array $values)
    {
        $uniqueChars = count(array_unique(str_split($promocode)));
        return $this->compare($uniqueChars, $operator, $values[0]);
    }

    public function checkStringCondition(string $promocode, string $operator, array $values, string $type)
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

    public function compare($value, string $operator, $compareValue)
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
