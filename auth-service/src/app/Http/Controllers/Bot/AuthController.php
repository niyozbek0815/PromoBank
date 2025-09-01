<?php
namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use App\Jobs\SyncUserToNotificationJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function __construct()
    {

    }
    public function check(Request $request)
    {
        $data = $request->validate([
            'phone'   => ['required', 'string', 'regex:/^\+998\d{9}$/'],
            'chat_id' => ['required', 'string'],
            'lang'    => ['nullable', 'in:uz,ru,kr'],
        ]);
        $message  = "User Not found!!!";
        $userData = null;
        $exist    = false;
        $user     = User::with(['region:id,name', 'district:id,name'])->where('phone', $data['phone'])->first();
        if ($user) {
            $message = "User already exists'!!!";
            $user->update([
                "lang" => $data['lang'],
                "chat_id" => $data['chat_id'],
            ]);
            $exist = true;
            Log::info("User:", ['user' => $user]);
            SyncUserToNotificationJob::dispatch(
                $user->id,
                false,
                $request->header('User-Ip'),
                $request['chat_id'],
                'telegram',
                "telegram",
                $request['app_version'] ?? null,
                $request->header('User-Agent'),
                $request['phone']
            )->onQueue('notification_queue');
            $userData = [
                'id'        => $user->id,
                'region'    => $user->region?->name,
                'district'  => $user->district?->name,
                'name'      => $user->name,
                'phone'     => $user->phone,
                'phone2'    => $user->phone2,
                'chat_id'   => $user->chat_id,
                'gender'    => $user->gender,
                'birthdate' => $user->birthdate,
            ];

        }

        return response()->json([
            'exist'   => $exist,
            'message' => $message,
            'user'    => $userData,
        ]);
    }
    public function create(Request $request)
    {
        $data = $request->validate([
            'phone'       => ['required', 'string', 'regex:/^\\+998\\d{9}$/'],
            'chat_id'     => ['required', 'string'],
            'name'        => ['nullable', 'string'],
            'region_id'   => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'gender'      => ['nullable', 'in:male,female'],
            'birthdate'   => ['nullable', 'date_format:Y-m-d'],
            'phone2'      => ['nullable', 'string', 'regex:/^\\+998\\d{9}$/'],
            'lang'        => ['nullable', 'in:uz,ru,kr'],
        ]);
        $data['gender'] = match ($data['gender'] ?? null) {
            'male' => 'e',
            'female' => 'a',
            default => null,
        };
        DB::beginTransaction();

        try {
            $user = User::where('phone', $data['phone'])->first();

            if ($user) {
                $user->update($data);

            } else {
                if (strlen($data['name'] ?? '') > 255) {
                    throw new \InvalidArgumentException('Name is too long.');
                }
                $user = User::create($data);

            }
            Log::info('User creation data:', [
                'user_id'     => $user->id,
                'sync'        => false,
                'ip'          => $request->header('User-Ip'),
                'chat_id'     => $request['chat_id'],
                'platform'    => 'telegram',
                'model'       => "telegram",
                'app_version' => "telegram" ?? null,
                'user_agent'  => $request->header('User-Agent'),
            ]);
            SyncUserToNotificationJob::dispatch(
                $user->id,
                false,
                $request->header('User-Ip'),
                $request['chat_id'],
                'telegram',
                "telegram",
                "telegram" ?? null,
                $request->header('User-Agent'),
                $request['phone']
            )->onQueue('notification_queue');

            DB::commit();
            Log::info("User:", ['user' => $user]);

            $userData = [
                'region'    => $user->region?->name,
                'district'  => $user->district?->name,
                'name'      => $user->name,
                'phone'     => $user->phone,
                'phone2'    => $user->phone2,
                'chat_id'   => $user->chat_id,
                'gender'    => $user->gender,
                'birthdate' => $user->birthdate,
            ];

            return response()->json([
                'message' => 'User created successfully.',
                'user'    => $userData,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User create/update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Foydalanuvchi yaratishda xatolik yuz berdi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'chat_id'     => ['required', 'string'],
            'name'        => ['nullable', 'string'],
            'region_id'   => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'gender'      => ['nullable', 'in:male,female'],
            'birthdate'   => ['nullable', 'date_format:Y-m-d'],
            'phone2'      => ['nullable', 'string', 'regex:/^\\+998\\d{9}$/'],
            'lang'        => ['nullable', 'in:uz,ru,kr'],
        ]);
        $data['gender'] = match ($data['gender'] ?? null) {
            'male' => 'e',
            'female' => 'a',
            default => null,
        };

        DB::beginTransaction();

        try {
            $user = User::where('chat_id', $data['chat_id'])->first();

            if (! $user) {
                return response()->json([
                    'message' => 'User not found.',
                ], 404);
            }

            $user->update($data);

            DB::commit();
            Log::info("User updated:", ['user' => $user]);

            $userData = [
                'id'        => $user->id,
                'region'    => $user->region?->name,
                'district'  => $user->district?->name,
                'name'      => $user->name,
                'phone'     => $user->phone,
                'phone2'    => $user->phone2,
                'chat_id'   => $user->chat_id,
                'gender'    => $user->gender,
                'birthdate' => $user->birthdate,
            ];

            return response()->json([
                'message' => 'User updated successfully.',
                'user'    => $userData,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User update failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Foydalanuvchini yangilashda xatolik yuz berdi.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
