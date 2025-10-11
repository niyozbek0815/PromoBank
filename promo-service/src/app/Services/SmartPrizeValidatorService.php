<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SmartPrizeValidatorService
{
    public function validate($prize, string $code): bool
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
            Log::info("Validating smart prize: {$prize->id} with code: {$code}", ['data' => $ruleValue->values]);

            if (!$method || !method_exists($this, $method)) {
                Log::warning("Unknown rule method for key: {$ruleValue->rule->key}");
                return false;
            }
            $values = is_string($ruleValue->values)
                ? json_decode($ruleValue->values, true)
                : $ruleValue->values;

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
