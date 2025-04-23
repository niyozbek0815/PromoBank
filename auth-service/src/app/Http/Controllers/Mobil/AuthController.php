<?php

namespace App\Http\Controllers\Mobil;

use App\Events\GuestSessionEvent;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobil\GuestRequest;
use App\Http\Requests\Mobil\LoginRequuest;
use App\Services\AuthService;
use App\Services\SmsSendService;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;

class AuthController extends Controller
{
    protected $authService, $smsSend;
    public function __construct(AuthService $authService, SmsSendService $smsSendService)
    {
        $this->authService = $authService;
        $this->smsSend = $smsSendService;
    }
    public function guest(GuestRequest $request)
    {

        $req = $request->validated();
        $user = User::firstOrCreate(
            ['phone' => $req['uuid'], 'is_guest' => true],
            [
                'name' => 'User' . rand(0, 100000),
                'phone' => $req['uuid'],
                'is_guest' => true
            ]
        );
        JWTAuth::factory()->setTTL(60);
        $token = JWTAuth::claims([
            'ip' => $request->header('User-Ip')
        ])->fromUser($user);
        // event(new GuestSessionEvent($user, $req, $request->header('User-Ip'),  $request->header('User-Agent')));
        $session = DB::table('sessions')->insert([
            'id' => Str::random(32), // Random id
            'user_id' => $user->id,
            'ip_address' => $request->header('User-Ip'),
            'device' => $req['device'] ?? 'mobile', // Qurilma turi, agar yuborilmasa, "mobile" bo'ladi
            'device_model' => $req['model'] ?? 'Unknown', // Qurilma modeli, agar yuborilmasa, 'Unknown'
            'platform' => $req['platform'] ?? 'Unknown', // Platforma, agar yuborilmasa, 'Unknown'
            'payload' => json_encode($req), // Requestning barcha ma'lumotlari
            'user_agent' => $request->header('User-Agent'), // User agent
            'last_activity' => now()->timestamp, // Faoliyat vaqti
            'created_at' => now(), // Yaratilgan vaqt
            'updated_at' => now(), // Yangilangan vaqt
        ]);
        return $this->successResponse(
            [
                'token' => $token,
                'user' => $user,
                'ses' => $session
            ],
            "Guest token created saccecfully"
        );
    }
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->errorResponse('Token not provided', ['error' => 'Token not provided'], 401);
        }
        try {
            JWTAuth::factory()->setTTL(60);
            $newToken = JWTAuth::setToken($token)->refresh();
            $payload = JWTAuth::setToken($newToken)->getPayload();
            return $this->successResponse(
                [
                    'token' => $newToken,
                    'user_id' => $payload->get('user_id'),
                    'phone' => $payload->get('phone'),
                    'is_guest' => $payload->get('is_guest'),
                    'ip' => $payload->get('ip')
                ],
                "Guest token created saccecfully"
            );
        } catch (TokenExpiredException $e) {
            return $this->errorResponse('Token expired and cannot be refreshed', $e->getMessage(), 401);
        } catch (\Exception $e) {
            return $this->errorResponse('Cannot refresh token', $e->getMessage(), 401);
        }
    }
    public function login(LoginRequuest $request)
    {
        $phone = $request->input('phone');
        $data = $this->authService->login($phone);
        if ($data['code'] == 422) {
            return $this->errorResponse($data["message"], $data["message"], 422);
        } else {
            // return response()->json($data["result"]);
            return $this->successResponse(
                [
                    'phone' => $phone,
                    'token' => $data['token'],
                    'is_new' => $data['is_new']
                ],
                "Sms muofaqiyatli jo'natildi"
            );
        }
    }
}
//     public function register(Request $request)
//     {
//         // Telefon raqamini tekshirish
//         $request->validate([
//             'phone' => 'required|unique:users,phone'
//         ]);

//         // Foydalanuvchini yaratish (phone va name)
//         $user = User::create([
//             'phone' => $request->phone,
//             'name' => $request->name, // name ham yuborilishi mumkin
//         ]);

//         // OTP yaratish (6 raqamli random)
//         // $otp = rand(100000, 999999);
//         $otp = 111111;

//         // OTPni user_sessions jadvaliga saqlash
//         $userAgent = $request->userAgent();

//         $device = 'desktop';
//         if (Str::contains($userAgent, ['Android', 'iPhone', 'Mobile'])) {
//             $device = 'mobile';
//         } elseif (Str::contains($userAgent, 'Telegram')) {
//             $device = 'telegram';
//         }

//         $platform = 'Unknown';
//         if (Str::contains($userAgent, 'Android')) {
//             $platform = 'Android';
//         } elseif (Str::contains($userAgent, 'iPhone')) {
//             $platform = 'iOS';
//         } elseif (Str::contains($userAgent, 'Telegram')) {
//             $platform = 'Telegram';
//         }

//         DB::table('sessions')->insert([
//             'user_id' => $user->id,
//             'ip_address' => $request->ip(),
//             'device' => $device,
//             'platform' => $platform,
//             'payload' => json_encode([
//                 'user_agent' => $userAgent,
//                 'ip' => $request->ip(),
//                 'timestamp' => now()->toDateTimeString()
//             ]),
//             'last_activity' => now()->timestamp,
//             'otp' => $otp,
//             'otp_sent_at' => now(),
//             'created_at' => now(),
//             'updated_at' => now(),
//         ]);

//         // SMS API orqali OTP yuborish (Twilio, Nexmo va hokazo)
//         $this->sendOtpViaSms($user->phone, $otp);

//         return response()->json(['message' => 'OTP yuborildi!']);
//     }

//     // SMS orqali OTP yuborish
//     protected function sendOtpViaSms($phone, $otp)
//     {
//         // Masalan, Twilio yoki Nexmo API bilan OTP yuborish
//         // API orqali OTP yuboriladi
//     }

//     // OTPni tasdiqlash va JWT tokenni yaratish
//     public function verifyOtp(Request $request)
//     {
//         // Foydalanuvchi tomonidan yuborilgan OTPni tekshirish
//         $request->validate([
//             'phone' => 'required',
//             'otp' => 'required|numeric'
//         ]);

//         // OTPni tekshirish: Foydalanuvchi telefon raqami va OTPni ma'lumotlar bazasidan tekshirish
//         $session = DB::table('user_sessions')
//             ->where('phone', $request->phone)
//             ->where('otp', $request->otp)
//             ->where('otp_sent_at', '>', now()->subMinutes(5)) // OTP 5 daqiqadan ko‘proq eski bo‘lmasligi kerak
//             ->first();

//         if (!$session) {
//             return response()->json(['message' => 'OTP noto‘g‘ri yoki muddati o‘tgan!'], 400);
//         }

//         // Foydalanuvchini topish
//         $user = User::find($session->user_id);

//         // JWT token yaratish
//         $token = JWTAuth::fromUser($user);

//         // Tokenni qaytarish
//         return response()->json(['token' => $token]);
//     }
// }