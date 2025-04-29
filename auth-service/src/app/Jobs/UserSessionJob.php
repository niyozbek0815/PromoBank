<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Str;

class UserSessionJob implements ShouldQueue
{
    use Queueable;
    protected $id, $request, $ip, $user_agent;
    /**
     * Create a new job instance.
     */
    public function __construct($id, $ip, array $request, $user_agent)
    {
        $this->id = $id;
        $this->ip = $ip;
        $this->request = $request;
        $this->user_agent = $user_agent;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        DB::table('sessions')->insert([
            'id' => \Illuminate\Support\Str::random(32), // Random id
            'user_id' => $this->id,
            'ip_address' => $this->ip,
            'device' => $$this->request['device'] ?? 'mobile', // Qurilma turi, agar yuborilmasa, "mobile" bo'ladi
            'device_model' => $$this->request['model'] ?? 'Unknown', // Qurilma modeli, agar yuborilmasa, 'Unknown'
            'platform' => $$this->request['platform'] ?? 'Unknown', // Platforma, agar yuborilmasa, 'Unknown'
            'payload' => json_encode($$this->request), // Requestning barcha ma'lumotlari
            'user_agent' => $$this->user_agent, // User agent
            'last_activity' => now()->timestamp, // Faoliyat vaqti
            'created_at' => now(), // Yaratilgan vaqt
            'updated_at' => now(), // Yangilangan vaqt
        ]);
    }
}