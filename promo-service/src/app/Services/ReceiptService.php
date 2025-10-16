<?php

namespace App\Services;

use App\Jobs\CreateReceiptAndProductJob;
use App\Models\Messages;
use App\Models\PlatformPromoSetting;
use App\Models\Prize;
use App\Models\PromotionShop;
use App\Models\UserPointBalance;
use App\Repositories\PlatformRepository;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

class ReceiptService
{
    public function __construct(private PlatformRepository $platformRepository)
    {
    }

    public function proccess(array $req, array $user, string $platformName = 'mobile'): array
    {
        $lang = $req['lang'] ?? 'uz';
        $action = 'invalid';
        $status = 'fail';
        $messages = [];
        $selectedPrizes = [];
        $manualPrizeCount = 0;

        $platformId = $this->getPlatformId($platformName);
        Log::info("Start",['name'=>$req]);
        $shop = PromotionShop::
            where('name', $req['name'])
            ->with(['products', 'promotion'])
            ->whereHas('promotion', function ($query) {
                $query->where('start_date', '<=', Carbon::today())
                    ->where('end_date', '>=', Carbon::today());
            })
            ->first();
        Log::info("Shop", [$shop]);

        if ($shop) {
            Log::info("Shop mavjud");
            [$action, $status, $messages, $selectedPrizes, $manualPrizeCount] =
                $this->handlePrizeEvaluation($shop, $req, $platformName, $lang);
        } else {
            Log::info("Points mavjud");
            [$action, $status, $messages] = $this->giveEncouragementPoints($lang);
        }

        Queue::connection('rabbitmq')->push(new CreateReceiptAndProductJob(
            $req,
            $user,
            null,
            $platformId,
            $selectedPrizes,
            null,
            $manualPrizeCount,
            $shop->promotion->id ?? null
        ));

        return compact('action', 'status', 'messages');
    }

    private function handlePrizeEvaluation(PromotionShop $shop, array $req, string $platformName, string $lang): array
    {
        $promotion = $shop->promotion;
        Log::info("Promotion data", ['promotion' => $promotion]);
        $entries = $this->getEntries($shop, $req);
        Log::info("Entries", [$entries]);
        $selectedPrizes = [];
        $messages = [];
        $manualPrizeCount = 0;
        $action = 'invalid';
        $status = 'fail';

        $weightedPrizes = collect();
        if (in_array($promotion->winning_strategy, ['immediate', 'hybrid'])) {
            $weightedPrizes = Prize::query()
                ->where('promotion_id', $shop->promotion_id)
                ->where('is_active', true)
                ->whereColumn('awarded_quantity', '<', 'quantity')
                ->whereHas('category', fn($q) => $q->where('name', 'weighted_random'))
                ->withCount(['prizeUsers as today_prize_users_count' => fn($q) => $q->whereDate('created_at', Carbon::today())])
                ->with('category:id,name')
                ->orderBy('index')
                ->get()
                ->filter(fn($p) => $p->today_prize_users_count < $p->daily_limit);
        }

        foreach ($entries as $entry) {
            $selected = false;
            // Weighted random prize
            if ($weightedPrizes->isNotEmpty()) {

                [$selectedPrizes, $messages, $selected, $action, $status] =
                    $this->selectWeightedPrize($weightedPrizes, $promotion, $entry, $req['chek_id'], $platformName, $lang, $selectedPrizes, $messages);
            }
            // Manual prize if weighted not selected
            if (!$selected && in_array($promotion->winning_strategy, ['delayed', 'hybrid'])) {
                $manualPrizeCount++;
                $action = 'manual_win';
                $status = 'pending';
                $messages[] = $this->getMessage($promotion->id, null, $lang, $status, $req['chek_id'], $platformName);
            }
        }
        Log::info("handleprizeData", [$action, $status, $messages, $selectedPrizes, $manualPrizeCount]);
        return [$action, $status, $messages, $selectedPrizes, $manualPrizeCount];
    }

    private function selectWeightedPrize($prizes, $promotion, $entry, $checkId, $platformName, $lang, $selectedPrizes, $messages): array
    {
        foreach ($prizes as $prize) {
            if (random_int(1, $prize->probability_weight) === 1) {
                $selectedPrizes[] = ['entry' => $entry, 'prize' => $prize];
                $action = 'weighted_win';
                $status = 'win';
                $messages[] = $this->getMessage($promotion->id, ['id' => $prize->id, 'name' => $prize->name], $lang, $status, $checkId, $platformName);
                return [$selectedPrizes, $messages, true, $action, $status];
            }
        }
        return [$selectedPrizes, $messages, false, 'invalid', 'fail'];
    }

    private function getEntries($shop, $req)
    {
        Log::info("data", [$req, $shop]);
        $checkProducts = collect($req['products']);
        $promoProductMap = collect($shop->products)
            ->keyBy(fn($product) => Str::lower($product->name));
        $entries = collect();

        foreach ($checkProducts as $checkProduct) {
            $checkName = Str::lower($checkProduct['name']);
            foreach ($promoProductMap as $promoName => $promoProduct) {
                if (str_contains($checkName, $promoName)) {
                    $count = ($checkProduct['count'] > 5) ? 5 : $checkProduct['count'];
                    for ($i = 0; $i < $count; $i++) {
                        $entries->push([
                            'shop_id' => $shop->id,
                            'product_id' => $promoProduct->id,
                            'product_name' => $promoProduct->name,
                            'summa' => $checkProduct['summa'],
                        ]);
                    }
                }
            }
        }
        return $entries;
    }


    private function giveEncouragementPoints(string $lang): array
    {
        $action = 'points_win';
        $status = 'win';
        $settings = Cache::remember('platform_promo_settings', now()->addHours(1), function () {
            return PlatformPromoSetting::default();
        });
        $message = $settings->getWinMessage($lang);
        return [$action, $status, [$message]];
    }

    private function getMessage(int $promotionId, ?array $prize, string $lang, string $status, string $checkId, string $channel): string
    {
        $template = Messages::resolveLocalizedMessage('receipt', [
            'status' => $status,
            'promotion_id' => $promotionId,
            'prize_id' => $prize['id'] ?? null,
            'lang' => $lang,
            'channel' => $channel,
        ]) ?? '';

        $prizeName = $prize['name'] ?? '—';
        $finalMessage = strtr($template, [':code' => $checkId, ':prize' => $prizeName]);

        return ($channel === 'sms') ? mb_strimwidth($finalMessage, 0, 160, '...') : $finalMessage;
    }

    private function getPlatformId(string $platformCode = 'mobile'): ?int
    {
        return Cache::store('redis')->remember(
            "platform:{$platformCode}:id",
            now()->addMinutes(60),
            fn() => $this->platformRepository->getPlatformGetId($platformCode)
        );
    }
    public function getPoints($user)
    {
        return UserPointBalance::where('user_id', $user['id'])->value('balance') ?? 0;
    }
}
