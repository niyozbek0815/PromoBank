<?php

namespace App\Http\Controllers\Telegram;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\SocialLink;

class TelegramController extends Controller
{
    public function index(Request $request)
    {
        $cacheKey = "frontend:socials";
        $ttl = now()->addMinutes(5);
        $data = Cache::store('redis')->remember($cacheKey, $ttl, function ()  {
            $socialLinks = SocialLink::where('status', 1)
                ->orderBy('position')
                ->get(['type', 'url'])
                ->map(fn($link) => [
                    'type' => $link->type,
                    'url' => $link->url,
                ])
                ->toArray();
            return $socialLinks;
        });
        return response()->json($data);
    }
}
