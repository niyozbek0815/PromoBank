<?php
namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobil\CheckUpdateRequest;
use App\Http\Requests\Mobil\GuestRequest;
use App\Http\Requests\Mobil\LoginCheckRequest;
use App\Http\Requests\Mobil\LoginRequuest;
use App\Http\Requests\Mobil\RegisterRequest;
use App\Http\Requests\Mobil\UserUpdateRequest;
use App\Http\Resources\Mobil\UserResource;
use App\Jobs\StoreBase64MediaJob;
use App\Jobs\SyncUserToNotificationJob;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    protected $authService, $smsSend;
    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }
    public function guest(GuestRequest $request)
    {
        $req  = $request->validated();
        $user = User::firstOrCreate(
            ['phone' => $req['uuid'], 'is_guest' => true],
            [
                'name'     => 'User' . rand(0, 100000),
                'phone'    => $req['uuid'],
                'is_guest' => true,
            ]
        );
        JWTAuth::factory()->setTTL(10);
        $token = JWTAuth::claims([
            'user_id'  => $user->id,
            'phone'    => $user->phone,
            'is_guest' => $user->is_guest,
            'ip'       => $request->header('User-Ip'),
        ])->fromUser($user);
        SyncUserToNotificationJob::dispatch(
            $user->id,
            $user->is_guest,
            $request->header('User-Ip'),
            $req['fcm_token'],
            $req['platform'],
            $req['model'],
            $req['app_version'] ?? null,
            $request->header('User-Agent'),
            null
        )->onQueue('notification_queue');
        return $this->successResponse(
            [
                'token' => $token,
                // 'user'  => $user,
            ],
            "Guest token created saccecfully"
        );
    }
    public function refreshToken(Request $request)
    {
        $token = $request->bearerToken();

        if (! $token) {
            return $this->errorResponse(
                'Validatsiya xatoligi',
                ['token' => ['Token not provided']],
                401
            );
        }

        try {
            JWTAuth::factory()->setTTL(1);
            $newToken = JWTAuth::setToken($token)->refresh();
            $payload  = JWTAuth::setToken($newToken)->getPayload();

            return $this->successResponse(
                [
                    'token'    => $newToken,
                    'id'       => $payload->get('user_id'),
                    'phone'    => $payload->get('phone'),
                    'is_guest' => $payload->get('is_guest'),
                    'ip'       => $payload->get('ip'),
                ],
                'Guest token created successfully'
            );

        } catch (TokenExpiredException $e) {
            return $this->errorResponse(
                'Token muddati tugagan, qayta olishning iloji yo‘q',
                ['token' => ['Token expired and cannot be refreshed']],
                401
            );

        } catch (\Exception $e) {
            return $this->errorResponse(
                'Tokenni yangilab bo‘lmadi',
                ['token' => [$e->getMessage()]],
                401
            );
        }

    }
    public function login(LoginRequuest $request)
    {
        $phone = $request->input('phone');
        $data  = $this->authService->login($phone);
        if ($data['code'] == 422) {
            return $this->errorResponse($data["message"], ['token' => [$data["message"]]], 422);
        } else {
            // return response()->json($data["result"]);
            return $this->successResponse(
                [
                    'phone'   => $phone,
                    'user_id' => $data['user_id'],
                    'token'   => $data['token'],
                    'is_new'  => $data['is_new'],
                ],
                "Sms muofaqiyatli jo'natildi"
            );
        }
    }
    public function check(LoginCheckRequest $request, $id)
    {
        $req = $request->validated();
        Log::info($req['fcm_token']);
        return DB::transaction(function () use ($req, $request, $id) {
            $user    = User::findOrFail($id)->load('userOtps');
            $userOld = User::where('phone', $req['uuid'])->latest()->first();

            $data = $this->authService->check($user, $userOld, $req,  $request);

            if ($data['error']) {
                return $this->errorResponse(
                    $data['success'],
                    ['token' => [$data['success']]],
                    422
                );
            } else {
                unset($data["error"]);
                return $this->successResponse(
                    $data,
                    "Check successfully!!!"
                );
            }
        });
    }
    public function register(RegisterRequest $request)
    {
        $userReq = $request['auth_user'];
        $user    = User::findOrFail($userReq['id']);
        $user->update([
            'region_id'   => $request['region_id'],
            'district_id' => $request['district_id'],
            'name'        => $request->input('name'),
            'phone2'      => $request['phone2'],
            'gender'      => $request['gender'],
            'birthdate'   => $request['birthdate'],
        ]);
        $user->load('media');

        if ($request->filled('avatar')) {
            $deletedImages = $user->media
                ->where('collection_name', 'user_avatar')
                ->sortByDesc('created_at')
                ->pluck('url')
                ->toArray();
            StoreBase64MediaJob::dispatch(
                base64: $request['avatar'],
                context: 'user_avatar',
                correlationId: $user->id,
                callbackQueue: 'auth-queue',
                deleteMediaUrls: $deletedImages
            )->onQueue('media_queue');
        }
        return $this->successResponse(
            new UserResource($user->load(['media', 'district', 'region'])),
            "User muvaffaqiyatli ro‘yxatdan o‘tdi");
    }
    public function userupdate(UserUpdateRequest $request)
    {
        $data     = $request->validated();
        $user_req = $request['auth_user'];
        $user     = User::findOrFail($user_req['id']);
        Log::info("datas", ['user->phone' => $user->phone, 'request->phone' => $data['phone']]);
        if ($user->phone === $data['phone']) {
            $user->update([
                'name'        => $data['name'],
                'phone'       => $data['phone'],
                'region_id'   => $data['region_id'],
                'district_id' => $data['district_id'],
                'phone2'      => $data['phone2'],
                'gender'      => $data['gender'],
                'birthdate'   => $request['birthdate'],
            ]);
            if ($request->filled('avatar')) {
                $deletedImages = $user->media
                    ->where('collection_name', 'user_avatar')
                    ->sortByDesc('created_at')
                    ->pluck('url')
                    ->toArray();

                StoreBase64MediaJob::dispatch(
                    base64: $request['avatar'],
                    context: 'user_avatar',
                    correlationId: $user->id,
                    callbackQueue: 'auth-queue',
                    deleteMediaUrls: $deletedImages
                )->onQueue('media_queue');
            }
            return $this->successResponse([
                "is_verification" => true,
                'user'            => new UserResource($user->load(['district', 'region']))], "User data updated Successfully!!!");
        } else {
            $return = $this->authService->update($user, $data);

            return $this->successResponse(
                [
                    'token'           => $return['token'],
                    'user_id'         => $return['user_id'],
                    "is_verification" => true,
                    "response"        => $data,
                ],
                "Updated Code Sended!!!"
            );
        }

    }

    public function checkUpdate(CheckUpdateRequest $request)
    {
        $req      = $request->validated();
        $user_req = $request['auth_user'];
        $id       = $user_req['id'];
        $user     = User::with(['userOtps'])->findOrFail($id);
        $data     = $this->authService->checkUpdate($user, $req, );

        if ($data['error_type'] == 422) {
            return $this->errorResponse("Parol xato yoki eskirgan iltimos qayta urinib ko'ring !!!", ['token' => ["Parol xato yoki eskirgan iltimos qayta urinib ko'ring !!!"]], 422);
        } else {
            return $this->successResponse(
                new UserResource($user->load(['district', 'region'])),
                "User Updated Successfully!!!"
            );
        }
    }
    public function user(Request $request)
    {
        $user_req = $request['auth_user'];
        $id       = $user_req['id'];
        $user     = User::with(['district', 'region'])->findOrFail($id);
        return $this->successResponse(
            new UserResource($user),
            "User Get Successfully!!!"
        );
    }

    public function userForSms(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:50',
        ]);
        $phone = $request->input('phone');
        $user  = User::where('phone', $phone)->first();
        if (! $user) {
            $user = User::create([
                'name'     => 'Guest',
                'phone'    => $phone,
                'is_guest' => false,
                'status'   => false,
            ]);
            $status = 'created';
        } else {
            $status = 'found';
        }
        return $this->successResponse([
            'status' => $status,
            'user'   => $user,
        ]);
    }
}
