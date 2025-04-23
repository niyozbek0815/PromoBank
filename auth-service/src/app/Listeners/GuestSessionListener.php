<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Support\Str;
use GuzzleHttp\Psr7\Request;
use App\Events\GuestSessionEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class GuestSessionListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(GuestSessionEvent $event): void
    {
        $request = $event->request;
        $user = $event->user;
        DB::table('sessions')->insert([
            'id' => Str::random(32),
            'user_id' => $user->id,
            'ip_address' => $event->ip,
            'device' => $request['device'] ?? 'mobile',
            'device_model' => $request['model'] ?? 'Unknown',
            'platform' => $request['platform'] ?? 'Unknown',
            'payload' => json_encode($request),
            'user_agent' => $event->agent,
            'last_activity' => now()->timestamp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}