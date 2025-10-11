<?php
namespace App\Jobs;

use App\Models\Notification;
use App\Models\NotificationAttempt;
use App\Models\NotificationUser;
use App\Models\UserDevice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification as FcmNotification;

class SendFcmBatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $notificationId;
    public array $tokens;
    public int $timeout = 300; // s

    public function __construct(int $notificationId, array $tokens)
    {
        $this->notificationId = $notificationId;
        $this->tokens         = array_values(array_unique(array_filter($tokens)));
    }

    public function handle()
    {
        // Log::info('SendFcmBatchJob ishga tushdi', [
        //     'notification_id' => $this->notificationId,
        //     'tokens'          => $this->tokens,
        // ]);
        if (empty($this->tokens)) {
            Log::warning("SendFcmBatchJob: tokenlar bo'sh keldi");
            return;
        }

        $notification = Notification::find($this->notificationId);
        $credentials  = config('firebase.credentials.file');
        $messaging    = (new Factory)
            ->withServiceAccount(base_path($credentials))
            ->createMessaging();

        $message = CloudMessage::new ()
            ->withNotification(
                FcmNotification::create(
                    $notification->getTranslation('title', 'uz'), // ✅ string (uz or fallback)
                    $notification->getTranslation('text', 'uz'),  // ✅ string (uz or fallback)
                    $notification->image                          // ✅ rasm (image URL as third argument)
                )
            )
            ->withData([
                'link_type'       => $notification->link_type ?? '',
                'link'            => $notification->link ?? '',
                'title'           => json_encode($notification->title, JSON_UNESCAPED_UNICODE), // ✅ barcha tillar
                'text'            => json_encode($notification->text, JSON_UNESCAPED_UNICODE),  // ✅ barcha tillar
                'image'           => $notification->image,
                'notification_id' => (string) $notification->id, // frontend/event tracking uchun qulay                                    // ✅ optional fallback sifatida
            ]);
        try {
            $report       = $messaging->sendMulticast($message, $this->tokens);
            $successCount = $report->successes()->count();
            $failCount    = $report->failures()->count();
            $totalResp    = $successCount + $failCount;
            Log::info('FCM batch natija', [
                'notification_id' => $this->notificationId,
                'tokens_count'    => count($this->tokens),
                'success'         => $successCount,
                'fail'            => $failCount,
                'total'           => $totalResp,
                'report'          => $report,
            ]);

            $tokenToNotifUserId = NotificationUser::query()
                ->where('notification_id', $this->notificationId)
                ->whereIn('token', $this->tokens)
                ->pluck('id', 'token')
                ->all();
            DB::transaction(function () use ($report, $tokenToNotifUserId, $notification, $successCount, $failCount, $totalResp) {
                $now         = now();
                $sentIds     = [];
                $failedIds   = [];
                $deletedIds  = [];
                $attemptRows = [];
                $badTokens   = [];

                foreach ($report->getItems() as $idx => $response) {
                    $token       = $this->tokens[$idx] ?? null;
                    $notifUserId = $token ? ($tokenToNotifUserId[$token] ?? null) : null;

                    if (! $token || ! $notifUserId) {
                        continue;
                    }

                    if ($response->isSuccess()) {
                        // ✅ Yuborilgan
                        $sentIds[]     = $notifUserId;
                        $attemptRows[] = $this->makeAttempt($notifUserId, $notification->id, 'success', null, $now);
                        continue;
                    }

                    // ❌ Xato bo‘lsa
                    $error = $response->error();
                    $msg   = $error ? $error->getMessage() : 'Unknown error';

                    if ($msg == "The registration token is not a valid FCM registration token") {
                        // 🗑 Token yaroqsiz → device delete + user status update
                        $deletedIds[]  = $notifUserId;
                        $badTokens[]   = $token;
                        $attemptRows[] = $this->makeAttempt($notifUserId, $notification->id, 'not_registered', $msg, $now);
                    } else {
                        // ❌ Oddiy failure
                        $failedIds[]   = $notifUserId;
                        $attemptRows[] = $this->makeAttempt($notifUserId, $notification->id, 'failed', $msg, $now);
                    }
                }

                if($badTokens) {
                    UserDevice::whereIn('fcm_token', $badTokens)->delete();
                }
                if ($sentIds) {
                    NotificationUser::whereIn('id', $sentIds)->update([
                        'status'          => 'sent',
                        'last_attempt_at' => $now,
                        'last_error'      => null,
                    ]);
                    NotificationUser::whereIn('id', $sentIds)->increment('attempt_count');
                }

                if ($failedIds) {
                    NotificationUser::whereIn('id', $failedIds)->update([
                        'status'          => 'failed',
                        'last_attempt_at' => $now,
                    ]);
                    NotificationUser::whereIn('id', $failedIds)->increment('attempt_count');
                }

                if ($deletedIds) {
                    NotificationUser::whereIn('id', $deletedIds)->update([
                        'status'          => 'not_registered',
                        'last_attempt_at' => $now,
                    ]);
                    NotificationUser::whereIn('id', $deletedIds)->increment('attempt_count');
                }



                // 📥 Bulk insert attempts
                if ($attemptRows) {
                    NotificationAttempt::insert($attemptRows);
                }

                // 📊 Statistikani yangilash
                $notification->incrementEach([
                    'sent_count'   => $successCount,
                    'failed_count' => $failCount,
                ]);
                $notification->decrement('pending_count', $totalResp);
            });

        } catch (\Throwable $e) {
            Log::error("FCM yuborishda xatolik: " . $e->getMessage(), [
                'notification_id' => $this->notificationId,
            ]);
        }
    }
    private function makeAttempt(int $notifUserId, int $notificationId, string $result, ?string $msg, $now): array
    {
        return [
            'notification_user_id' => $notifUserId,
            'notification_id'      => $notificationId,
            'device_id'            => null,
            'channel'              => 'fcm',
            'result'               => $result,
            'error_message'        => $msg,
            'request_payload'      => null,
            'response_payload'     => null,
            'latency_ms'           => null,
            'created_at'           => $now,
            'updated_at'           => $now,
        ];
    }
}
