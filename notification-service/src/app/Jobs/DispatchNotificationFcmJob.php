<?php
namespace App\Jobs;

use App\Jobs\SendFcmBatchJob;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\UserDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class DispatchNotificationFcmJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $notification;

    public int $timeout = 300; // s

    // Bitta batch uchun maksimal token (FCM 500 limit; xavfsiz 450)
    private const BATCH_SIZE = 450;
    /**
     * Create a new job instance.
     */
    public function __construct($notificationId)
    {
        $this->notification = Notification::with([
            'platforms:id,notification_id,platform',
            'users:id,notification_id,phone,user_id,device_id,token,status',
        ])->findOrFail($notificationId);
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $notification = $this->notification;
        // Yuborishni boshladigan payt statusni processing ga o‘tkazamiz (agar kerak bo‘lsa)
        if ($notification->status === 'draft' || $notification->status === 'scheduled') {
            $notification->update(['status' => 'processing']);
        }
        $platforms    = $notification->platforms->pluck('platform')->all();
        $fcmPlatforms = collect($platforms)
            ->intersect(['ios', 'android', 'web'])
            ->values()
            ->all();
        $hasFcm = ! empty($fcmPlatforms);

        $hasTelegram = collect($platforms)
            ->contains('telegram');
        $hasSms = collect($platforms)
            ->contains('sms');
        if ($notification['target_type'] === 'users') {
            $phones = $notification->users->pluck('phone')->filter()->unique()->values()->all();
            // Log::info('Job Platforms', ['platforms' => $platforms]);
            Log::info('Job Users', ['users' => $phones]);

            if ($hasFcm) {
                $this->dispatchToDevices(
                    phones: $phones,
                    platforms: $fcmPlatforms,
                    callback: fn(array $tokens, array $devices) => $this->snapshotAndDispatch($tokens, $devices, $notification)
                );
            }
            if ($hasTelegram) {
                $c = 0;
                // UserDevice::whereNotNull('fcm_token')
                //     ->whereIn('phone', $phones)
                //     ->where('device_type', 'telegram')
                //     ->orderBy('id') // chunkById uchun shart
                //     ->chunkById(450, function ($devices, $c) use ($notification) {
                //         $c = $c + count($devices);
                //         echo "chunk device: " . $c;
                //         Log::info('Inserted Devices Count:' . $c);
                //         $tokens = $devices->pluck('fcm_token')->filter()->unique()->toArray();
                //         Log::info('Chunk User Devices Tokens', ['user_tokens' => $tokens]);
                //         if (! empty($tokens)) {
                //             // Send notif for telegram job
                //             // Queue::connection('rabbitmq')->push(new SendFcmBatchJob($notification->id, $tokens));
                //         }
                //     });

            }
        }
        if ($notification['target_type'] === 'platform') {
            if ($hasFcm) {
                $total     = UserDevice::max('id');
                $minId     = UserDevice::min('id');
                Log::info("device max and min", ['max' => $total, 'min' => $minId]);
                $rangeSize = 10000; // har job nechta id qamrab oladi
                for ($start = $minId; $start <= $total; $start += $rangeSize) {
                    $end = $start + $rangeSize - 1;
                    Queue::connection('rabbitmq')->push(
                        new PlatformsChunkNotificationJob($notification->id, $start, $end, $fcmPlatforms)
                    );
                }
            }
            if ($hasTelegram) {
                // UserDevice::whereNotNull('fcm_token')
                //     ->where('device_type', 'telegram')
                //     ->orderBy('id') // chunkById uchun shart
                //     ->chunkById(450, function ($devices) use ($notification) {
                //         $tokens = $devices->pluck('fcm_token')->filter()->unique()->toArray();
                //         Log::info('Chunk User Devices Tokens', ['user_tokens' => $tokens]);
                //         if (! empty($tokens)) {
                //             // Send notif for telegram job
                //             // Queue::connection('rabbitmq')->push(new SendFcmBatchJob($notification->id, $tokens));
                //         }
                //     });
            }

            // Log::info('Job Platform', [
            //     'notification_id' => $this->notification['id'],
            // ]);
        }
        if ($this->notification['target_type'] === 'excel') {
            Log::info('Job Excel', [
                'notification_id' => $this->notification['id'],
            ]);
        }
    }
    protected function dispatchToDevices(array $phones, array $platforms, \Closure $callback): void
    {
        $query = UserDevice::query()
            ->whereNotNull('fcm_token')
            ->whereIn('device_type', $platforms);

        if (! empty($phones)) {
            $query->whereIn('phone', $phones);
        }

        // progress’ni hisoblash uchun umumiy count
        $total = (clone $query)->count();
        if ($total > 0) {
            // pending_count/total_recipients ni boshlash
            $this->notification->incrementEach([
                'total_recipients' => $total,
                'pending_count'    => $total,
            ]);
        }

        $query->orderBy('id') // chunkById uchun kerak
            ->chunkById(self::BATCH_SIZE, function ($devices) use ($callback) {
                $tokens  = $devices->pluck('fcm_token')->filter()->unique()->values()->all();
                $devices = $devices->keyBy('fcm_token')->all();

                if (! empty($tokens)) {
                    // Log::info('Dispatch: chunk tayyor', [
                    //     'notification_id' => $this->notification->id,
                    //     'count'           => count($tokens),
                    // ]);

                    $callback($tokens, $devices);
                }
            });
    }
    protected function snapshotAndDispatch(array $tokens, array $devicesByToken, Notification $n): void
    {
        // Mavjud snapshot’larni tekshirish (bor bo‘lsa skip)
        $existing = NotificationUser::query()
            ->where('notification_id', $n->id)
            ->whereIn('token', $tokens)
            ->pluck('id', 'token')
            ->all();

        $now     = now();
        $inserts = [];

        foreach ($tokens as $t) {
            if (isset($existing[$t])) {
                continue;
            }
            $d         = $devicesByToken[$t] ?? null;
            $inserts[] = [
                'notification_id' => $n->id,
                'phone'           => (string) ($d->phone ?? ''),
                'user_id'         => $d->user_id ?? null,
                'device_id'       => $d->id ?? null,
                'token'           => $t,
                'status'          => 'pending',
                'attempt_count'   => 0,
                'last_attempt_at' => null,
                'last_error'      => null,
                'meta'            => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }

        if (! empty($inserts)) {
            NotificationUser::insert($inserts);
        }
        Queue::connection('rabbitmq')->push(
            new SendFcmBatchJob($n->id, $tokens)
        );

    }
}
