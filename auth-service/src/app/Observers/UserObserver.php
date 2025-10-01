<?php

namespace App\Observers;

use App\Jobs\SyncUserFromAuth;
use App\Models\User;
use Spatie\Permission\Models\Role;
class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        SyncUserFromAuth::dispatch($user['id'], $user['phone'], $user['name'])->onQueue('promo_queue');
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        SyncUserFromAuth::dispatch($user['id'], $user['phone'], $user['name'])->onQueue('promo_queue');
        if (!$user->roles()->exists()) {
            $userRole = Role::firstOrCreate([
                'name' => 'user',
                'guard_name' => 'web',
            ]);
            $user->assignRole($userRole);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
