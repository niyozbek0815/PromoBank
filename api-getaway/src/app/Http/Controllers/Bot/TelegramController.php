<?php
namespace App\Http\Controllers\Bot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    protected $url;

    public function __construct()
    {
        $this->url = config('services.urls.telegram_service');
    }
    public function webhook(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, '/internal/telegram/webhook', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return $response->json();
    }
    public function setwebhook(Request $request)
    {
        $response = $this->forwardRequest("POST", $this->url, 'setwebhook', $request);
        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }
        return $response->json();
    }
}
