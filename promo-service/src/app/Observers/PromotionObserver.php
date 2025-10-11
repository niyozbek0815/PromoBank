<?php

namespace App\Observers;

use App\Models\Promotions;

use App\Models\Messages;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PromotionObserver
{
    /**
     * Handle the Promotion "created" event.
     */
    public function created(Promotions $promotion): void
    {
        // $platformMessages = Messages::where('scope_type', 'platform')
        //     ->whereNull('scope_id')
        //     ->get();
        // if ($platformMessages->isEmpty()) {
        //     return;
        // }
        // DB::transaction(function () use ($platformMessages, $promotion) {
        //     foreach ($platformMessages as $msg) {
        //         Messages::updateOrCreate(
        //             [
        //                 'scope_type' => 'promotion',
        //                 'scope_id' => $promotion->id,
        //                 'type' => $msg->type,
        //                 'status' => $msg->status,
        //             ],
        //             [
        //                 'message' => $msg->message,
        //             ]
        //         );
        //     }
        // });
    }

    /**
     * Handle the Promotion "updated" event.
     */
    public function updated(Promotions $promotion): void
    {
        // // Platform-level xabarlarni olamiz
        // $platformMessages = Messages::where('scope_type', 'platform')
        //     ->whereNull('scope_id')
        //     ->get();
        // Log::info("Default messages", ['data' => $platformMessages]);
        // if ($platformMessages->isEmpty()) {
        //     return;
        // }

        // DB::transaction(function () use ($platformMessages, $promotion) {
        //     foreach ($platformMessages as $msg) {
        //         Messages::firstOrCreate(
        //             [
        //                 'scope_type' => 'promotion',
        //                 'scope_id' => $promotion->id,
        //                 'type' => $msg->type,
        //                 'status' => $msg->status, // 🔑 qo‘shildi
        //             ],
        //             [
        //                 'message' => $msg->message,
        //             ]
        //         );
        //     }
        // });
        // $allMessages = Messages::where('scope_type', 'promotion')
        //     ->where('scope_id', $promotion->id)
        //     ->get();

        // // Bitta umumiy log
        // Log::info("Promotion uchun barcha xabarlar", [
        //     'promotion_id' => $promotion->id,
        //     'messages' => $allMessages->toArray(),
        // ]);
    }

    /**
     * Handle the Promotion "deleted" event.
     */
    public function deleted(Promotions $promotion): void
    {
        //
    }

    /**
     * Handle the Promotion "restored" event.
     */
    public function restored(Promotions $promotion): void
    {
        //
    }

    /**
     * Handle the Promotion "force deleted" event.
     */
    public function forceDeleted(Promotions $promotion): void
    {
        //
    }
}
