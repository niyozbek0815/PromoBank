<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        try {
            $data = $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed', [
                'errors' => $e->errors(),
                'input'  => $request->all(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        }

        Log::info('Login attempt', ['email' => $data['email']]);

        $authServiceUrl = config('services.urls.auth_service') . '/front/login';
        Log::info('Auth service URL', ['url' => $authServiceUrl]);

        try {
            $response = Http::post($authServiceUrl, $data);
        } catch (\Exception $e) {
            Log::error('HTTP request failed', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['email' => 'Service unavailable. Please try again later.'])->withInput();
        }

        if ($response->ok()) {
            $responseData = $response->json();
            // dd($responseData);
            Session::put('jwt_token', $responseData['token']);

// Optional: Save user role to session for permission checks
            if (isset($responseData['roles'])) {
                Session::put('user_roles', $responseData['roles']);
            }
            Session::put('token_last_verified_at', now());

            // $data = session()->all();
            // dd($data);

            return redirect()->route('admin.dashboard');
        }

        Log::error('Login failed', [
            'status' => $response->status(),
            'body'   => $response->body(),
            'json'   => $response->json(),
            'input'  => $data,
        ]);

        return back()->withErrors(['email' => 'Login failed. Please check your credentials.'])->withInput();
    }

    public function logout()
    {
        Session::forget('jwt_token');
        return redirect()->route('admin.login');
    }

}
