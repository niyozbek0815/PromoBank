<?php

namespace App\Services;

use App\Jobs\StoreBase64MediaJob;
use App\Jobs\SyncUserToNotificationJob;
use App\Models\User;
use App\Models\UserOtps;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthService
{
    protected $smsService;
    public function __construct(SmsSendService $smsSendService)
    {
        $this->smsService = $smsSendService;
    }

    public function login($phone)
    {
        // Userni yaratish yoki olish
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name'     => 'User' . rand(0, 100000),
                'phone'    => $phone,
                'is_guest' => false, // default - yangi user guest
                'status'   => false,
            ]
        );

        $is_new = $user->wasRecentlyCreated;

        // Agar mavjud user bo‘lsa, is_guest ni false qilamiz va OTP cheklovini tekshiramiz
        if (! $is_new) {
            $recentOtpCount = UserOtps::where('user_id', $user->id)
                ->where('created_at', '>', Carbon::now()->subMinutes(2))
                ->count();
            if ($recentOtpCount > 3) {
                return [
                    "message" => "Juda ko'p urunishlar qildingiz. Iltimos keyinroq qayta urinib ko'ring!!!",
                    "code"    => 422,
                ];
            }
        }

        // OTP generatsiya va sms yuborish
        $userOtp = $this->generateOtp($user, $user->phone);
        $this->smsService->sendMessage($userOtp->otp, $phone);

        return [
            'is_new'  => $is_new,
            'token'   => $userOtp->token,
            'user_id' => $user->id,
            "code"    => 200,
        ];
    }
    private function generateOtp($user, $phone)
    {
        $now = now();
        $otp = 111111;
        // if ($phone == "+998900191099") {
        //     $otp = 111111;
        // } else {
        //     $otp = rand(100000, 999999);
        // }
        return UserOtps::create([
            'user_id'     => $user['id'],
            'phone'       => $user['phone'],
            'token'       => Hash::make($user['phone']),
            'otp'         => $otp,
            'otp_sent_at' => $now,
            'expires_at'  => $now->addMinutes(5),
        ]);
    }
    public function check($user, $userOld, array $req, $request)
    {
        if ($user->userOtps && $user->userOtps->otp == $req['password']) {
            $user->status = true;
            $user->save();
            SyncUserToNotificationJob::dispatch(
                $user->id,
                $user->is_guest,
                $request->header('User-Ip'),
                $req['fcm_token'],
                $req['platform'],
                $req['model'],
                $req['app_version'] ?? null,
                $request->header('User-Agent'),
                $user->phone
            )->onQueue('notification_queue');
            if ($userOld) {
                // boshqa microservicelarga user malumotlarini
                //  yangi userga olib o'tishga xabar yuboriladi
            }
            JWTAuth::factory()->setTTL(60);
            $token = JWTAuth::claims([
                'user_id'  => $user->id,
                'phone'    => $user->phone,
                'is_guest' => $user->is_guest,
                'ip'       => $request->header('User-Ip'),
            ])->fromUser($user);

            return ([
                "user_id" => $user->id,
                'token'   => $token,
                'error'   => null,
            ]);
        } else {
            return [
                'success' => "Parol xato yoki eskirgan iltimos qayta urinib ko'ring !!!",
                'error'   => 422,
            ];
        }
    }
    public function checkUpdate($user, array $req)
    {
        if ($user->userOtps && $user->userOtps->otp == $req['password']) {
            $user->update([
                'name'        => $req['name'],
                'phone'       => $req['phone'],
                'region_id'   => $req['region_id'],
                'district_id' => $req['district_id'],
                'phone2'      => $req['phone2'],
                'gender'      => $req['gender'],
            ]);
            if ($req['avatar'] !== null) {
                $deletedImages = $user->media
                    ->where('collection_name', 'user_avatar')
                    ->sortByDesc('created_at')
                    ->pluck('url')
                    ->toArray();

                StoreBase64MediaJob::dispatch(
                    base64: $req['avatar'],
                    context: 'user_avatar',
                    correlationId: $user->id,
                    callbackQueue: 'auth-queue',
                    deleteMediaUrls: $deletedImages
                )->onQueue('media_queue');
            }
            return ["error_type" => 200];
        } else {
            return [
                "error_type" => 422,
            ];
        }
    }

    public function update($user, $data)
    {
        $userOtp = $this->generateOtp($user, $data['phone']);

        $this->smsService->sendMessage($userOtp['otp'], $data['phone']);

        return [
            "error_type" => null,
            'token'      => $userOtp['token'],
            'user_id'    => $user->id,
        ];
    }
}
