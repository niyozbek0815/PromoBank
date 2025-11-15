<?php

namespace App\Http\Resources;

use App\Models\EncouragementPoint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class PromotionShowWebAppResource extends JsonResource
{

    protected ?int $userId;

    public function __construct($resource)
    {
        parent::__construct($resource);
        // $this->userId = $userId;
    }
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $lang = $this->additional['lang'] ?? $request->get('lang', 'uz');

        $defaultimage = collect([
            ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
        ]);
        $defaultMedia = collect([
            ['url' => 'https://qadarun.com/namuna/1.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/2.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/3.gif', 'mime_type' => 'image/gif'],
            ['url' => 'https://qadarun.com/namuna/4.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/5.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/6.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/7.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/8.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/9.jpeg', 'mime_type' => 'image/jpeg'],
            ['url' => 'https://qadarun.com/namuna/video1.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video2.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video3.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video4.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video5.mp4', 'mime_type' => 'video/mp4'],
            ['url' => 'https://qadarun.com/namuna/video6.mp4', 'mime_type' => 'video/mp4'],
        ]);

        Log::info($this->progressBar);
        $progressBar = $this->progressBar ? [
            'daily_points' => $this->progressBar->daily_points,
            'step_0_threshold' => $this->progressBar->step_0_threshold,
            'step_1_threshold' => $this->progressBar->step_1_threshold,
            'step_2_threshold' => $this->progressBar->step_2_threshold,
            'day_start_at' => $this->progressBar->day_start_at,
            // 'all_points'=> EncouragementPoint::getUserTotalPoints($this->userId, ['referral_start', 'referral_register', 'secret_number']),
            // 'today_poinst'=>$usersPoints['total_points'],
            'all_points'=>0,
            'today_poinst'=>0,
        ] : null;

        return [
            'id'                 => $this->id,
            'name' =>           $this->getTranslation('name', $lang),
            'title'              => $this->getTranslation('title',$lang),
            'description'        => $this->getTranslation('description', $lang),
            'start_date'         => $this->start_date,
            'end_date'           => $this->end_date,

            'offer'              => is_array($this->offer) && isset($this->offer['url']) ? $this->offer['url'] : 'https://qadarun.com/namuna/php.docx',
            'banner'             => is_array($this->banner) && isset($this->banner['url']) ? $this->banner['url'] : $defaultimage->random()['url'],
            'gallery'            => ! empty($this->gallery) && count($this->gallery) >= 1
                ? $this->gallery
                : $defaultMedia->shuffle()->take(4)->values()->all(),
            'participation_type' => ParticipationTypeResource::collection($this->participationTypes),
            'progress_bar'=>$progressBar,
            'company'            => new CompanyResource(resource: $this->company),
            'platforms' => $this->platforms->map(function ($platform) {
                return [
                    'name'  => $platform->name,
                    'phone' => $platform->pivot->phone,
                ];
            }),
         ];
    }
}
