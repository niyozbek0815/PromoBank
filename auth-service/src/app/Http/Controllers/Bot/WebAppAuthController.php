<?php

namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use Tymon\JWTAuth\Facades\JWTAuth;use Illuminate\Support\Facades\Log;

class WebAppAuthController extends Controller
{
    public function login(Request $request)
    {
        $initData = $request->input('initData');
        $botToken = config('services.telegram.bot_token');

        if (!$initData) {
            return response()->json(['message' => 'initData missing'], 400);
        }

        try {
            $telegramUser = $this->validateInitData($initData, $botToken);
        } catch (\Exception $e) {
            Log::warning('Telegram WebApp validation failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Invalid initData'], 401);
        }

        $chatId = (string) ($telegramUser['id'] ?? null);
        if (!$chatId)
            return response()->json(['message' => 'No chat id'], 400);
        $authDate = (int) ($telegramUser['auth_date'] ?? 0);
        if (abs(time() - $authDate) > 3600) { // 1 hour
            return response()->json(['message' => 'initData expired'], 401);
        }
        $user = User::where('chat_id', $chatId)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        Log::info("User logged in via Telegram WebApp", ['user' => $user, 'chat_id' => $chatId]);
        [$accessToken, $ttl] = $this->issueAccessToken($user);
        $refreshToken = $this->createRefreshToken($user);
        $cookie = cookie('refresh_token', $refreshToken, env('REFRESH_TOKEN_TTL', 604800) / 60, '/', null, true, true, false, 'Strict');
        return response()->json([
            'access_token' => $accessToken,
            'expires_in' => $ttl,
            'user' => $user->only(['id', 'name', 'chat_id'])
        ])->cookie($cookie);
    }

    private function validateInitData(string $initData, string $botToken): array
    {
        parse_str($initData, $data);

        if (!isset($data['hash'])) {
            throw new \Exception("No hash in initData");
        }

        $hash = $data['hash'];
        unset($data['hash']);

        ksort($data);

        $checkString = implode("\n", array_map(fn($k, $v) => "$k=$v", array_keys($data), $data));
        $secretKey = hash_hmac('sha256', $botToken, 'WebAppData', true);
        $hmac = hash_hmac('sha256', $checkString, $secretKey, true);
        $calcHash = bin2hex($hmac);

        if (!hash_equals($calcHash, $hash)) {
            throw new \Exception("Invalid hash");
        }
        Log::info("Data:", ['data' => $data]);

        return json_decode($data['user'], true) + ['auth_date' => $data['auth_date']];
    }



    private function issueAccessToken($user): array
    {
        $now = time();
        $exp = $now + (int) env('JWT_TTL', 900); // JWTAuth TTL daqiqa asosida ishlaydi, lekin sekundga aylantiramiz

        // 🔹 Minimal payload
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'chat_id' => $user->chat_id,
            'iat' => $now,
            'exp' => $exp,
            'jti' => Str::uuid()->toString(),
        ];

        // TTL ni int ga cast qilamiz (Carbon xatolik bermasligi uchun)
        JWTAuth::factory()->setTTL((int) env('JWT_TTL', 60));

        // 🔹 Token generatsiya qilamiz
        $jwt = JWTAuth::claims($payload)->fromUser($user);

        return [$jwt, $exp - $now];
    }

    private function createRefreshToken($user): string
    {
        $token = Str::random(80);
        $ttl = (int) env('REFRESH_TOKEN_TTL', 604800); // sec
        Redis::setex("refresh:$token", $ttl, json_encode([
            'user_id' => $user->id,
            'created_at' => now()->toDateTimeString(),
        ]));
        return $token;
    }
}
