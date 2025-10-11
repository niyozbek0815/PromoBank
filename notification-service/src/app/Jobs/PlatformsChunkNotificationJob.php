<?php
namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class PlatformsChunkNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300;
    public int $tries   = 3;

    private const BATCH_SIZE = 450; // FCM limit

    public function __construct(
        protected int $notificationId,
        protected int $rangeStartId,
        protected int $rangeEndId,
        protected array $fcmPlatforms
    ) {}

    public function handle(): void
    {
        $notification = Notification::findOrFail($this->notificationId);

        // Count bilan notification metricsni upfront update qilamiz
        $total = UserDevice::query()
            ->whereNotNull('fcm_token')
            ->whereIn('device_type', $this->fcmPlatforms)
            ->whereBetween('id', [$this->rangeStartId, $this->rangeEndId])
            ->count();

            // $devices = UserDevice::query()
//     ->select('user_devices.id', 'user_devices.user_id', 'user_devices.phone', 'user_devices.fcm_token')
//     ->leftJoin('notification_users as nu', function ($join) use ($notification) {
//         $join->on('nu.token', '=', 'user_devices.fcm_token')
//             ->where('nu.notification_id', '=', $notification->id);
//     })
//     ->whereNotNull('user_devices.fcm_token')
//     ->whereIn('user_devices.device_type', $this->fcmPlatforms)
//     ->whereBetween('user_devices.id', [$this->rangeStartId, $this->rangeEndId])
//     ->where(function ($q) {
//         $q->whereNull('nu.id')                                          // umuman notification_user yozuvi yo‘q
//             ->orWhereIn('nu.status', ['sent', 'not_registered', 'viewed']); // mavjud va status mos
//     })
//     ->get();


        if ($total > 0) {
            $notification->incrementEach([
                'total_recipients' => $total,
                'pending_count'    => $total,
            ]);
        }

        // Chunklash — lazy chunk oqimli, memory minimal
        UserDevice::query()
            ->select(['id', 'user_id', 'phone', 'fcm_token'])
            ->whereNotNull('fcm_token')
            ->whereIn('device_type', $this->fcmPlatforms)
            ->whereBetween('id', [$this->rangeStartId, $this->rangeEndId])
            ->orderBy('id')
            ->chunkById(self::BATCH_SIZE, fn(Collection $devices) =>
                $this->processBatch($devices, $notification));
    }

    private function processBatch(Collection $devices, Notification $n): void
    {
        $tokens = $devices->pluck('fcm_token')->filter()->unique()->values();
        if ($tokens->isEmpty()) {
            return;
        }

        $devicesByToken = $devices->keyBy('fcm_token');

        Log::debug('Preparing FCM batch', [
            'notification_id' => $n->id,
            'count'           => $tokens->count(),
            'range'           => [$this->rangeStartId, $this->rangeEndId],
        ]);

        $this->snapshotAndDispatch($tokens->all(), $devicesByToken, $n);
    }

    private function snapshotAndDispatch(array $tokens, Collection $devicesByToken, Notification $n): void
    {
        $now = now();

        // Insert yangi bo‘lganlarni, eski tokenlarni skip qiladi
        $inserts = array_map(function (string $t) use ($devicesByToken, $n, $now) {
            $d = $devicesByToken->get($t);
            return [
                'notification_id' => $n->id,
                'phone'           => (string) ($d?->phone ?? ''),
                'user_id'         => $d?->user_id,
                'device_id'       => $d?->id,
                'token'           => $t,
                'status'          => 'pending',
                'attempt_count'   => 0,
                'last_attempt_at' => null,
                'last_error'      => null,
                'meta'            => null,
                'created_at'      => $now,
                'updated_at'      => $now,
            ];
        }, $tokens);

        // insertOrIgnore -> duplikat tokenlarni avtomatik drop qiladi (DB-level)
        if ($inserts) {
            NotificationUser::insertOrIgnore($inserts);
        }

        // Keyingi async jobga batchni yuboramiz
        Queue::connection('rabbitmq')->push(
            new SendFcmBatchJob($n->id, $tokens)
        );
    }
}
