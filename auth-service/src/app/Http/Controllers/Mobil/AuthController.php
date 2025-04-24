<?php

namespace App\Http\Controllers\Mobil;

use App\Events\GuestSessionEvent;
use App\Http\Requests\Mobil\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobil\GuestRequest;
use App\Http\Requests\Mobil\LoginCheckRequest;
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
                    'user_id' => $data['user_id'],
                    'token' => $data['token'],
                    'is_new' => $data['is_new']
                ],
                "Sms muofaqiyatli jo'natildi"
            );
        }
    }
    public function check(LoginCheckRequest $request, $id)
    {
        $req = $request->validated();
        DB::transaction(function () use ($req, $request, $id) {
            $user = User::findOrFail($id)->load('userOtps');
            $userOld = User::where('phone', $req['uuid'])->latest()->first();
            $data = $this->authService->check($user, $userOld, $req, $request->header('User-Ip'));
            if ($data['error']) {
                return $this->errorResponse(
                    $data['success'],
                    $data['success'],
                    422
                );
            } else {
                unset($data["error"]);
                return $this->successResponse(
                    $data,
                    "Check saccessfully!!!"
                );
            }
        });
    }
    public function register(RegisterRequest $request)
    {
        return $this->successResponse(
            [
                'phone' => $request['auth_user']
            ],
            "Sms muofaqiyatli jo'natildi"
        );
        // $user = Auth::user();
        // DB::transaction(function () use ($user, $request) {
        //     // Update user name
        //     $user->name = $request->input('name');
        //     $user->save();
        //     // Handle profile image
        //     if ($request->has('image')) {
        //         $media->profile($request->input('image'), $user, 'profile');
        //     }
        // });
        // return response()->json([
        //     'success' => "User created successfully!!!"
        // ]);
    }
}
