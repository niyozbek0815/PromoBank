<?php
namespace App\Jobs;

use App\Models\UserDevice;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncUserToNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $userId;
    protected bool $isGuest;
    protected ?string $userIp;
    protected ?string $deviceToken;
    protected ?string $platform;
    protected ?string $deviceName;
    protected ?string $appVersion;
    protected ?string $userAgent;

    public function __construct(
        int $userId,
        bool $isGuest,
        ?string $userIp,
        ?string $deviceToken,
        ?string $platform,
        ?string $deviceName,
        ?string $appVersion = null,
        ?string $userAgent = null
    ) {
        $this->userId      = $userId;
        $this->isGuest     = $isGuest;
        $this->userIp      = $userIp;
        $this->deviceToken = $deviceToken;
        $this->platform    = $platform;
        $this->deviceName  = $deviceName;
        $this->appVersion  = $appVersion;
        $this->userAgent   = $userAgent;
    }

    public function handle(): void
    {
        if (! $this->deviceToken || ! $this->platform) {
            Log::warning("Device malumotlari to'liq emas, sync bekor qilindi", [
                'user_id'      => $this->userId,
                'device_token' => $this->deviceToken,
                'platform'     => $this->platform,
            ]);
            return;
        }

        DB::transaction(function () {
          $device=  UserDevice::updateOrCreate(
                ['device_token' => $this->deviceToken], // unique constraint
                [
                    'user_id'       => $this->userId,
                    'is_guest'      => $this->isGuest,
                    'ip_address'    => $this->userIp,
                    'device_type'   => $this->platform,
                    'device_name'   => $this->deviceName,
                    'app_version'   => $this->appVersion,
                    'user_agent'    => $this->userAgent,
                    'last_activity' => time(),
                ]
            );
            Log::info("User device updated or created", context: ['device'=>$device]);
        });

        Log::info("SyncUserToNotificationJob bajarildi", [
            'user_id'      => $this->userId,
            'is_guest'     => $this->isGuest,
            'user_ip'      => $this->userIp,
            'device_token' => $this->deviceToken,
            'platform'     => $this->platform,
            'device_name'  => $this->deviceName,
            'app_version'  => $this->appVersion,
            'user_agent'   => $this->userAgent,
        ]);
    }
}
