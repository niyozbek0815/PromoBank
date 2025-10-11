<?php
namespace App\Jobs;

use App\Models\PromoCode;
use App\Models\PromoGeneration;
use App\Models\PromotionSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GeneratePromoCodesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $promotionId;
    protected int $count;
    protected int $createdByUserId;

    public function __construct(int $promotionId, int $count, int $createdByUserId)
    {
        $this->promotionId     = $promotionId;
        $this->count           = $count;
        $this->createdByUserId = $createdByUserId;
    }

    public function handle()
    {
        Log::info("🔁 [PromoGeneration] Job started: {$this->count} codes for promotion {$this->promotionId}");

        try {
            $settings = PromotionSetting::where('promotion_id', $this->promotionId)->firstOrFail();
        } catch (\Throwable $e) {
            Log::critical("❌ Promotion settings not found for promotion_id={$this->promotionId}: " . $e->getMessage());
            return;
        }

        $generation = PromoGeneration::create([
            'promotion_id'       => $this->promotionId,
            'type'               => 'generated',
            'created_by_user_id' => $this->createdByUserId,
        ]);

        $codes       = [];
        $maxAttempts = $this->count * 5;
        $attempts    = 0;
        $generated   = 0;
        $chunkSize   = 1000;
        while ($generated < $this->count && $attempts < $maxAttempts) {
            $remaining = $this->count - $generated;
            $codes     = [];
            for ($i = 0; $i < min($chunkSize, $remaining); $i++) {
                try {
                    $code = self::generateCodeFromSettings(
                        $settings,
                        $this->promotionId,
                        $settings->unique_across_all_promotions
                    );
                } catch (\Throwable $e) {
                    Log::error("⚠️ Code generation error: " . $e->getMessage());
                    break 2; // exit both for-loop and while-loop
                }
                $codes[] = [
                    'generation_id' => $generation->id,
                    'promotion_id'  => $this->promotionId,
                    'promocode'     => $code,
                    'is_used'       => false,
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
                $attempts++;
            }
            $insertResult = $this->bulkInsert($codes, $this->promotionId, $settings->unique_across_all_promotions);
            $generated += $insertResult['inserted_count'];
        }
        if ($generated < $this->count) {
            Log::warning("⚠️ Not all promocodes were inserted. Needed: {$this->count}, Inserted: {$generated}");
        }
        Log::info("✅ [PromoGeneration] Job finished: {$generated} / {$this->count} codes created (promotion {$this->promotionId})");
    }

    /**
     * Insert promocodes in chunks with exception handling
     */
    protected function bulkInsert(array $codes, int $promotionId, bool $uniqueAcrossAllPromotions = false)
    {
        if (empty($codes)) {
            return ['inserted_count' => 0, 'skipped_count' => 0, 'inserted_codes' => []];
        }

        $promocodes  = array_column($codes, 'promocode');
        $existingMap = [];

        // Select only once per 1000 codes
        foreach (array_chunk($promocodes, 1000) as $chunk) {
            $query = PromoCode::query()->whereIn('promocode', $chunk);

            if (! $uniqueAcrossAllPromotions) {
                $query->where('promotion_id', $promotionId);
            }

            $query->pluck('promocode')->each(function ($code) use (&$existingMap) {
                $existingMap[$code] = true;
            });
        }

        // Filter out duplicates based on existingMap
        $unique = array_filter($codes, fn($code) => ! isset($existingMap[$code['promocode']]));

        try {
            if (! empty($unique)) {
                DB::table('promo_codes')->insert($unique);
            }
        } catch (\Throwable $e) {
            Log::error("❌ Bulk insert failed: " . $e->getMessage());
            return ['inserted_count' => 0, 'skipped_count' => count($codes), 'inserted_codes' => []];
        }

        return [
            'inserted_count' => count($unique),
            'skipped_count'  => count($codes) - count($unique),
        ];
    }

    /**
     * Generate a single unique promocode from settings
     */
    private static function generateCodeFromSettings(PromotionSetting $settings, int $promotionId, bool $checkGlobalUniqueness)
    {
        $length  = $settings->length;
        $charset = $settings->charset;
        $exclude = $settings->exclude_chars ?? '';

        if ($exclude) {
            $charset = str_replace(str_split($exclude), '', $charset);
        }

        if (empty($charset)) {
            throw new \Exception("Charset is empty after excluding characters.");
        }

        $prefix     = $settings->prefix ?? '';
        $suffix     = $settings->suffix ?? '';
        $coreLength = max($length - strlen($prefix) - strlen($suffix), 4);
        $maxRetries = 20;

        for ($i = 0; $i < $maxRetries; $i++) {
            $body = '';
            for ($j = 0; $j < $coreLength; $j++) {
                $body .= $charset[random_int(0, strlen($charset) - 1)];
            }

            $code = $prefix . $body . $suffix;

            $exists = $checkGlobalUniqueness
            ? PromoCode::where('promocode', $code)->exists()
            : PromoCode::where('promotion_id', $promotionId)->where('promocode', $code)->exists();

            if (! $exists) {
                return $code;
            }
        }

        throw new \Exception("Failed to generate unique code after {$maxRetries} attempts.");
    }
}
