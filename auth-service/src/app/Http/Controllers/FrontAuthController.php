<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class FrontAuthController extends Controller
{
    // Login API endpoint
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', [
                'errors' => $validator->errors()->toArray(),
                'input'  => $request->all(),
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        try {
            $user = User::where('email', $credentials['email'])->first();
            Log::info('UserData', [
                'user' => $user,
            ]);
            if (! $user || ! \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
                Log::warning('Invalid credentials attempt', [
                    'email' => $request->input('email'),
                ]);
                return response()->json(['message' => 'Invalid credentials'], 401);
            }

            // Create token with custom claims including role and permissions
            $customClaims = array_merge($user->getJWTCustomClaims(), [
                'roles'       => $user->getRoleNames(), // Spatie Role names
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]);
            Log::info('JWTAuth attempt', [
                'user'         => $user,
                'customClaims' => $customClaims,
            ]);

            // Explicitly cast TTL to integer to avoid Carbon errors from string values
            JWTAuth::factory()->setTTL((int) env('JWT_TTL', 60));
            $token = JWTAuth::claims($customClaims)->fromUser($user);

        } catch (\Exception $e) {
            Log::error('JWTAuth attempt failed', [
                'exception' => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Authentication error'], 500);
        }

        return response()->json([
            'token'       => $token,
            'user'        => $user->only(['id', 'name', 'email', 'phone', 'chat_id']),
            'roles'       => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

    // Retrieve authenticated user
    public function me()
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            return response()->json(['user' => $user]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token is invalid or expired'], 401);
        }
    }
    public function verify(Request $request)
    {
        try {
            $token = $request->bearerToken();
            JWTAuth::factory()->setTTL(60);

            $user = JWTAuth::parseToken()->authenticate();
            Log::info('User authenticated during verify', [
                'user_id' => $user->id,
                'email'   => $user->email,
            ]);
            if (! $user) {
                Log::warning('User not found during verify');
                return response()->json(['message' => 'User not found'], 401);
            }
            $newToken = JWTAuth::setToken($token)->refresh();

            Log::info('Token refreshed successfully', [
                'user_id'   => $user->id,
                'email'     => $user->email,
                'new_token' => $newToken,
            ]);

            return response()->json([
                'token'       => $newToken,
                'user'        => $user->only(['id', 'name', 'email', 'phone']),
                'roles'       => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ]);
        } catch (TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (TokenInvalidException $e) {
            return response()->json(['message' => 'Token invalid'], 401);
        } catch (JWTException $e) {
            return response()->json(['message' => 'Token missing or malformed'], 401);
        } catch (\Exception $e) {
            Log::error('Unexpected verify error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return response()->json(['message' => 'Unexpected error'], 500);
        }
    }
}
