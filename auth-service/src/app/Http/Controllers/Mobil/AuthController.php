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
use App\Http\Requests\Mobil\CheckUpdateRequest;
use App\Http\Requests\Mobil\GuestRequest;
use App\Http\Requests\Mobil\LoginCheckRequest;
use App\Http\Requests\Mobil\LoginRequuest;
use App\Http\Requests\Mobil\UserUpdateRequest;
use App\Http\Resources\Mobil\UserResource;
use App\Models\Media;
use App\Services\AuthService;
use App\Services\SmsSendService;
use Illuminate\Support\Facades\Http;
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
                    'id' => $payload->get('user_id'),
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

        DB::beginTransaction();

        try {
            $user_req =  $request['auth_user'];
            $id = $user_req['id'];
            $user = User::findOrFail($id);
            $user->region_id = $request['region_id'];
            $user->district_id = $request['district_id'];
            $user->name = $request->input('name');
            $user->phone2 = $request['phone2'];
            $user->gender = $request['gender'];
            $user->save();
            if ($request->filled('avatar')) {
                $mediaResponse = $this->uploadToMediaService($request['avatar'], $user_req);
                $this->saveMediaData($id, $mediaResponse);
            }
            DB::commit();
            return $this->successResponse(['user' => new UserResource($user->load(['media', 'district', 'region'])),], "User muvaffaqiyatli ro‘yxatdan o‘tdi");
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->errorResponse('Xatolik: ' . $e->getMessage(), 500);
        }
    }
    public function userupdate(UserUpdateRequest $request)
    {
        $data = $request->validated();
        $user_req = $request['auth_user'];
        $user = User::findOrFail($user_req['id']);
        if ($user->phone === $data['phone']) {
            $user->update(['name' => $data['name']]);
            // if ($request['image']) {
            //     $media->profile($request['image'], $user, "profile");
            // }
            return $this->successResponse(['user' => new UserResource($user->load(['media', 'district', 'region'])),], "User data updated Successfully!!!");
        } else {
            $return = $this->authService->update($user,  $data);
            if ($return['error_type'] == 422) {
                return $this->errorResponse($return["result"], 422);
            } else {
                return $this->successResponse(
                    [
                        'token' => $return['token'],
                        'user_id' => $return['user_id'],
                        "response" => $data
                    ],
                    "Updated Code Sended!!!"
                );
            }
        }
    }
    public function saveMediaData($id, $mediaResponse)
    {
        $media = Media::create([
            'model_type' => \App\Models\User::class, // model class nomi (to'liq namespace bilan)
            'model_id' => $id, // bog'lanadigan modelning IDsi
            'uuid' => $mediaResponse['uuid'],
            'collection_name' => $mediaResponse['collection_name'],
            'file_name' => $mediaResponse['file_name'],
            'name' => $mediaResponse['name'],
            'mime_type' => $mediaResponse['mime_type'],
            'path' => $mediaResponse['path'],
            'url' => $mediaResponse['url'],
        ]);
    }
    public function uploadToMediaService(string $base64Image, $user)
    {
        if (!preg_match("/^data:image\/(\w+);base64,/", $base64Image, $type)) {
            throw new \InvalidArgumentException('Rasm formati noto‘g‘ri');
        }

        $imageType = strtolower($type[1]);
        $fileName = Str::uuid() . '.' . $imageType;
        $base64Str = preg_replace('/^data:image\/\w+;base64,/', '', $base64Image);
        $base64Str = str_replace(' ', '+', $base64Str);

        $tempDir = storage_path('app/temp');
        $tempFilePath = "{$tempDir}/{$fileName}";

        try {
            // temp katalogi mavjudligini tekshirish va kerak bo‘lsa yaratish
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            // Faylni vaqtinchalik saqlash
            file_put_contents($tempFilePath, base64_decode($base64Str));

            // Media-service'ga yuborish
            $response = Http::attach(
                'file',
                file_get_contents($tempFilePath),
                $fileName
            )->post(config('services.urls.media_service') . '/api/media/upload', [
                'context' => 'user_avatar',
                'user_id' => $user['id'],
            ]);

            if (!$response->successful()) {
                throw new \Exception('Media-service xato: ' . $response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            throw $e;
        } finally {
            // Faylni o‘chirish
            if (file_exists($tempFilePath)) {
                unlink($tempFilePath);
            }
        }
    }
    public function checkUpdate(CheckUpdateRequest $request)
    {
        $req = $request->validated();
        $user_req = $request['auth_user'];
        $id = $user_req['id'];
        $user = User::with(['media', 'district', 'region', 'userOtps'])->findOrFail($id);
        $data = $this->authService->checkUpdate($user, $req,);

        if ($data['error_type'] == 422) {

            return $this->errorResponse("Parol xato yoki eskirgan iltimos qayta urinib ko'ring !!!", 422);
        } else {
            return $this->successResponse(
                ['user' => new UserResource($user)],
                "User Updated Successfully!!!"
            );
        }
    }
    // public function logout(Request $request, AuthService $authService)
    // {
    //     $req = $request->validate([
    //         'uuid' => "required"
    //     ]);
    //     $user = Auth::user();
    //     $user->currentAccessToken()->delete();
    //     $user = User::firstOrCreate(
    //         ['phone' => $req['uuid'], 'status' => 1],
    //         [
    //             'name' => 'User' . rand(0, 100000),
    //             'phone' => $req['uuid'],
    //             'status' => true
    //         ]
    //     );
    //     return response()->json([
    //         "user_id" => $user->id,
    //         'success' => 'Login Successfully',
    //         'token' => $user->createToken($user->phone)->plainTextToken,
    //     ]);
    // }
}