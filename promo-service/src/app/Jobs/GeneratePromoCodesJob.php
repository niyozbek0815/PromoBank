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
use Illuminate\Support\Facades\Log;

class GeneratePromoCodesJob implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    /**
     * Create a new job instance.
     */
    protected $count;
    protected $createdByUserId;
    protected $promotionId;

    public function __construct(int $promotionId, int $count, int $createdByUserId)
    {
        $this->promotionId     = $promotionId;
        $this->count           = $count;
        $this->createdByUserId = $createdByUserId;
    }

    /**
     * Execute the job.
     */public function handle()
    {
        Log::info("🔁 Job started: Generating {$this->count} promo codes for promotion {$this->promotionId}");

        $settings   = PromotionSetting::where('promotion_id', $this->promotionId)->firstOrFail();
        $generation = PromoGeneration::create([
            'promotion_id'       => $this->promotionId,
            'type'               => 'generated',
            'created_by_user_id' => $this->createdByUserId,
        ]);

        $codes       = [];
        $maxAttempts = $this->count * 2;
        $attempts    = 0;

        while (count($codes) < $this->count && $attempts < $maxAttempts) {
            try {
                $code = self::generateCodeFromSettings(
                    $settings,
                    $this->promotionId,
                    $settings->unique_across_all_promotions
                );
            } catch (\Exception $e) {
                Log::error("❌ Promocode generation failed: " . $e->getMessage());
                break;
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

        PromoCode::insert($codes);

        Log::info("✅ Job finished: Successfully generated " . count($codes) . " promocodes for promotion {$this->promotionId}");
    }

    private static function generateCodeFromSettings(PromotionSetting $settings, int $promotionId, bool $checkGlobalUniqueness): string
    {
        $length  = $settings->length;
        $charset = $settings->charset;
        $exclude = $settings->exclude_chars ?? '';

        if ($exclude) {
            $charset = str_replace(str_split($exclude), '', $charset);
        }

        $prefix      = $settings->prefix ?? '';
        $suffix      = $settings->suffix ?? '';
        $coreLength  = max($length - strlen($prefix) - strlen($suffix), 4);
        $maxAttempts = 20;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $body = '';
            for ($i = 0; $i < $coreLength; $i++) {
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

        throw new \Exception("Failed to generate a unique promocode after {$maxAttempts} attempts.");
    }
}
