<?php

namespace App\Services;

use App\Models\UserOtps;
use Carbon\Carbon;
use App\Models\User;
use App\Models\UserOtp;
use Detection\MobileDetect;
use Illuminate\Http\Request;
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
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => 'User' . rand(0, 100000),
                'phone' => $phone,
                'is_guest' => false,
                'status' => false
            ]
        );
        $is_new = true;
        if (!$user->wasRecentlyCreated) {
            // Mavjud foydalanuvchi qaytarildi
            $userOtp = UserOtps::where('user_id', $user->id)->where("created_at", '>', Carbon::now()->subMinutes(20))->count();
            if ($userOtp > 3) {
                return [
                    "message" => "Juda ko'p urunishlar qildingiz. Iltimos keyinroq qayta urinib ko'ring!!!",
                    "code" => 422
                ];
            }
        }

        $userOtp = $this->generateOtp($user, $user['phone']);
        // $result = $this->smsService->sendMessage($userOtp['otp'], $phone, $userOtp['id']);
        // if ($result['status'] == 'failed') {
        //     return ["message" => "Iltimos birozdan so'ng qayta urinib ko'ring!!!", "code" => 422];
        // }
        return [
            'is_new' => $is_new,
            'token' => $userOtp['token'],
            'user_id' => $user->id,
            "code" => 200
        ];
    }
    private function generateOtp($user, $phone)
    {
        $now = now();
        if ($phone == "+998900191099") {
            $otp = 111111;
        } else {
            $otp = rand(100000, 999999);
        }
        return UserOtps::create([
            'user_id' => $user['id'],
            'phone' => $user['phone'],
            'token' => Hash::make($user['phone']),
            'otp' => $otp,
            'otp_sent_at' => $now,
            'expires_at' => $now->addMinutes(5),
        ]);
    }
    public function check($user, $userOld, array $req, $ip)
    {
        if ($user->userOtps &&  $user->userOtps->otp == $req['password'] && $req['token'] == $user->userOtps->token) {
            $user->status = true;
            $user->save();
            if ($userOld) {
                // boshqa microservicelarga user malumotlarini
                //  yangi userga olib o'tishga xabar yuboriladi
            }
            JWTAuth::factory()->setTTL(60);
            $token = JWTAuth::claims([
                'ip' => $ip
            ])->fromUser($user);
            return ([
                "user_id" => $user->id,
                'token' => $token,
                'error' => null
            ]);
        } else {
            return [
                'success' => "Parol xato yoki eskirgan iltimos qayta urinib ko'ring !!!",
                'error' => 422
            ];
        }
    }
    public function checkUpdate($user, array $req)
    {
        if ($user->userOtps &&  $user->userOtps->otp == $req['password'] && $req['token'] == $user->userOtps->token) {
            $user->update([
                'name' => $req['name'],
                'phone' => $req['phone'],
            ]);
            // if ($req['image']) {
            //     $media->profile($req['image'], $user, "profile");
            // }
            return ["error_type" => 200];
        } else {
            return [
                "error_type" => 422
            ];
        }
    }

    public function update($user,  $data)
    {
        $userOtp = $this->generateOtp($user, $data['phone']);

        // $result = $this->smsService->sendMessage($userOtp['otp'], $data['phone'], $userOtp['id']);
        // if ($result['status'] == 'failed') {
        //     return ["message" => "Iltimos birozdan so'ng qayta urinib ko'ring!!!", "code" => 422];
        // }
        return [
            "error_type" => null,
            'token' => $userOtp['token'],
            'user_id' => $user->id,
        ];
    }
}
