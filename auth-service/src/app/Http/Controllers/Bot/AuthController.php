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
            'phone' => ['required', 'regex:/^\+998\d{9}$/'],
            'chat_id' => ['required', 'string'],
            'lang' => ['nullable', 'in:uz,ru,kr,en'],
        ]);

        // Guest userni o‘chir
        User::where('chat_id', $data['chat_id'])
            ->where('is_guest', true)
            ->delete();

        $user = User::with(['region:id,name', 'district:id,name'])
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
            'region' => $user->region?->name,
            'district' => $user->district?->name,
            'name' => $user->name,
            'phone' => $user->phone,
            'phone2' => $user->phone2,
            'chat_id' => $user->chat_id,
            'gender' => $user->gender,
            'birthdate' => $user->birthdate,
        ];
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'regex:/^\\+998\\d{9}$/'],
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
            'lang' => ['nullable', 'in:uz,ru,kr'],
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

    public function botStart(Request $request)
    {
        $data = $request->validate([
            'chat_id' => ['required', 'string'],
            'lang' => ['nullable', 'in:uz,ru,kr,en'],
            'referrer_id' => ['nullable', 'string'],
        ]);

        $lang = $data['lang'] ?? 'uz';
        $chatId = $data['chat_id'];
        $referrerId = $data['referrer_id'] ?? null;

        $referrerUser = $referrerId
            ? User::where('chat_id', $referrerId)->first()
            : null;

        $user = User::where('chat_id', $chatId)->first();
        // $user->delete();
        $newUser = false;

        if (!$user) {
            $user = User::create([
                'name' => "Guest_{$chatId}",
                'phone' => "guest_{$chatId}",
                'chat_id' => $chatId,
                'lang' => $lang,
                'is_guest' => true,
                'status' => false,
            ]);
            $newUser = true;

            if ($referrerUser && $referrerId !== $chatId) {
                Log::info('Referral success', [
                    'new_user' => $user->id,
                    'referrer_id' => $referrerId,
                ]);
            }
        } else {
            Log::info('User already exists', ['chat_id' => $chatId]);
        }

        return response()->json([
            'message' => $newUser
                ? 'Yangi foydalanuvchi yaratildi.'
                : 'Foydalanuvchi allaqachon mavjud.',
            'new_user' => $newUser,
            'referral_exists' => (bool) $referrerUser,
            'referrer_user' => $referrerUser,
            'user' => $user,
        ]);
    }
}
