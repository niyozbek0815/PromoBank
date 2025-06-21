<?php
namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
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
        $message = "User Not found!!!";
        $exist   = false;
        $user    = User::where('phone', $data['phone'])->first();
        if ($user) {
            $message = "User already exists'!!!";
            $user->update([
                "lang" => $data['lang'],
            ]);
            $exist = true;
        }
        Log::info("User:", ['user' => $user]);

        return response()->json([
            'exist'   => $exist,
            'message' => $message,
            'user'    => $user,
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
            'male' => 'M',
            'female' => 'F',
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

            DB::commit();
            Log::info("User:", ['user' => $user]);
            return response()->json([
                'message' => 'User created successfully.',
                'user'    => $user,
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
}