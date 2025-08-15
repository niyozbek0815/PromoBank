<?php
namespace App\Jobs;

use App\Models\User;
use App\Models\UsersCache;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncUserFromAuth implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
protected  $id,$phone,$name;

    /**
     * Create a new job instance.
     */
      public function __construct($id, $phone, $name)
    {

            $this->id = $id;
            $this->phone = $phone;
            $this->name = $name;

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Syncing user from auth', ['data' => [
            'id'    => $this->id,
            'phone' => $this->phone,
            'name'  => $this->name,
        ]]);
        UsersCache::updateOrCreate(
            ['user_id' => $this->id],
            [
                'user_id' => $this->id,
                'phone' => $this->phone,
                'name'  => $this->name,
                'status' => 'active',
            ]
        );
    }
}
