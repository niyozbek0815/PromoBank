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
    public function exists(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required', 'string'],
        ]);
        $user = User::with(['regionlang:id,name', 'district:id,name'])
            ->where('chat_id', $data['chat_id'])
            ->where('is_guest', false)
            ->first();

        if (!$user) {
            return response()->json([
                'exist' => false,
                'message' => 'User not found!',
                'user' => null,
            ]);
        }
        return response()->json([
            'exist' => true,
            'message' => 'User already exists!',
            'user' => $this->formatUser($user),
        ]);
    }
    public function check(Request $request)
    {
        $data = $request->validate([
            // 'phone' => ['required', 'regex:/^\+998\d{9}$/'],
            'phone' => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'chat_id' => ['required', 'string'],
            'lang' => ['nullable', 'in:uz,ru,kr,en'],
        ]);

        // Guest userni o‘chir
        User::where('chat_id', $data['chat_id'])
            ->where('is_guest', true)
            ->delete();

        $user = User::with(['regionlang:id,name', 'district:id,name'])
            ->where('phone', $data['phone'])
            ->first();

        if (!$user) {
            return response()->json([
                'exist' => false,
                'message' => 'User not found!',
                'user' => null,
            ]);
        }

        $user->update([
            'chat_id' => $data['chat_id'],
            'lang' => $data['lang'],
        ]);

        Log::info('User checked:', ['user_id' => $user->id]);

        SyncUserToNotificationJob::dispatch(
            $user->id,
            false,
            $request->header('User-Ip'),
            $data['chat_id'],
            'telegram',
            'telegram',
            $request->header('App-Version'),
            $request->header('User-Agent'),
            $data['phone']
        )->onQueue('notification_queue');

        return response()->json([
            'exist' => true,
            'message' => 'User already exists!',
            'user' => $this->formatUser($user),
        ]);
    }
    private function formatUser(User $user): array
    {
        return [
            'id' => $user->id,
            'region' => $user->regionlang?->name,
            // 'district' => $user->district?->name,
            'name' => $user->name,
            'phone' => $user->phone,
            'phone2' => $user->phone2,
            'chat_id' => $user->chat_id,
            'gender' => $user->gender,
            'birthdate' => $user->birthdate,
            'lang' => $user->lang,
        ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            // 'phone' => ['required', 'string', 'regex:/^\\+998\\d{9}$/'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9]\d{7,14}$/'],
            'chat_id' => ['required', 'string'],
            'name' => ['nullable', 'string', 'max:255'],
            'region_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'gender' => ['nullable', 'in:male,female'],
            'birthdate' => ['nullable', 'date_format:Y-m-d'],
            'phone2' => ['nullable', 'string', 'regex:/^\\+998\\d{9}$/'],
            'lang' => ['nullable', 'in:uz,ru,kr,en'],
        ]);
        $data['gender'] = $data['gender'] === 'male' ? 'e' : ($data['gender'] === 'female' ? 'a' : null);

        DB::beginTransaction();

        try {
            // Guest userni o‘chir
            User::where('chat_id', $data['chat_id'])
                ->where('is_guest', true)
                ->delete();
            $data['status'] = true;
            // Telefon orqali tekshir
            $user = User::updateOrCreate(['phone' => $data['phone']], $data);

            Log::info('User created or updated:', ['user_id' => $user->id]);

            SyncUserToNotificationJob::dispatch(
                $user->id,
                false,
                $request->header('User-Ip'),
                $data['chat_id'],
                'telegram',
                'telegram',
                $request->header('App-Version'),
                $request->header('User-Agent'),
                $data['phone']
            )->onQueue('notification_queue');

            DB::commit();

            return response()->json([
                'message' => 'User created successfully.',
                'user' => $this->formatUser($user),
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('User create failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal server error'], 500);
        }
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required', 'string'],
            'name' => ['nullable', 'string'],
            'region_id' => ['nullable', 'integer'],
            'district_id' => ['nullable', 'integer'],
            'gender' => ['nullable', 'in:male,female'],
            'birthdate' => ['nullable', 'date_format:Y-m-d'],
            'phone2' => ['nullable', 'string', 'regex:/^\\+998\\d{9}$/'],
            'lang' => ['nullable', 'in:uz,ru,kr,en'],
        ]);
        $user = User::where('chat_id', $data['chat_id'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $data['gender'] = $data['gender'] === 'male' ? 'e' : ($data['gender'] === 'female' ? 'a' : null);
        $user->update($data);

        Log::info('User updated:', ['user_id' => $user->id]);

        return response()->json([
            'message' => 'User updated successfully.',
            'user' => $this->formatUser($user),
        ]);
    }
    // O'chiriladigan telefon raqamlar
// $phonesToDelete = [
//     '+998944427787',
//     '+998994477787',
//     '+998977851797',
//     '+998887851797',
//     '+998940940994',
//     '+998981211228',
//     '+998900191099',
// ];

    // // Delete query
// User::whereIn('phone', $phonesToDelete)->delete();
    public function botStart(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required'],
            'username' => ['nullable', 'string'],
            'referrer_id' => ['nullable'],
        ]);

        $chatId = $data['chat_id'];

        // Foydalanuvchini oldindan olish
        $user = User::with(['regionlang:id,name', 'district:id,name'])
            ->where('chat_id', $chatId)
            ->where('is_guest', false)
            ->first();
        // $user->delete();
        if ($user) {
            return response()->json([
                'exist' => true,
                'new_user' => false,
                'referrer_user' => null,
                'user' => $user,
            ]);
        }

        $referrerUser = $data['referrer_id']
            ? User::where('chat_id', $data['referrer_id'])->first()
            : null;

        // Guest foydalanuvchini yaratish yoki mavjudini olish
        $user_created = User::firstOrCreate(
            ['chat_id' => $chatId],
            [
                'name' => "Guest_{$data['username']}",
                'phone' => "guest_{$chatId}",
                'is_guest' => true,
                'status' => false,
            ]
        );

        $newUser = $user_created->wasRecentlyCreated;

        return response()->json([
            'exist' => false,
            'new_user' => $newUser,
            'referrer_user' => $referrerUser,
            'user' => $user_created,
        ]);
    }
}
