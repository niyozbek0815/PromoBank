<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected $url;
    public function __construct()
    {
        $this->url = config('services.urls.notification_service');
    }

    public  function index(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/notifications', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            $user = $request->get('auth_user'); // Middleware qo‘shgan array
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function unreadCount(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/notifications/unread-count', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function markAsRead(Request $request, $id)
    {
        $response = $this->forwardRequest("POST", $this->url, '/notifications/' . $id . '/read', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response;
    }
    public function markAllAsRead(Request $request)
{
    $response = $this->forwardRequest("POST", $this->url, '/notifications/all-read', $request);
    if ($response instanceof \Illuminate\Http\Client\Response) {
        return response()->json($response->json(), $response->status());
    }
    return $response;
}


    }
