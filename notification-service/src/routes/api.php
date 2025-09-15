<?php

use App\Http\Controllers\Mobil\NotificationController;
use App\Http\Controllers\NotificationsController;
use App\Jobs\DispatchNotificationFcmJob;
use App\Models\User;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
Route::prefix('notifications')->controller(NotificationController::class)->group(function () {
    // GET /notifications → barcha bildirishnomalar ro‘yxati
    Route::post('/', 'index')->name('notifications.index');

    // GET /notifications/unread-count → o‘qilmaganlar soni
    Route::post('/unread-count', 'unreadCount')->name('notifications.unreadCount');

    // PATCH /notifications/{id}/read → bitta bildirishnomani o‘qilgan qilish
    Route::post('/{id}/read', 'markAsRead')->name('notifications.read');

    // DELETE /notifications → barcha bildirishnomalarni tozalash
    Route::post('/all-read', 'markAllAsRead')->name('notifications.allRead');
});
Route::prefix('front')->group(function () {
    Route::prefix('notifications')->name('admin.notifications.')
        ->controller(NotificationsController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/data', 'data')->name('data');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{banner}/edit', 'edit')->name('edit');
            Route::put('/{banner}', 'update')->name('update');
            Route::post('/{banner}/delete', 'destroy')->name('delete');
            Route::post('/{notification}/resent', function ($notification) {
                // $token = 'cnUmexLNBHZKC3w-l11ijw:APA91bGOmj3nRRRgcD20eRYGvW2ZMPMbXMjPrMmdyBZ2qLVuxwI1Cqi1aPSQ3z67L4xRzK-AaErmNYGE1ZS8-SS0nqKYtjJZBeSr7mbRE5e3A8NEwU28ghU';
                // $user = UserDevice::where('fcm_token', $token)->delete();
                // $device = UserDevice::where('id', '>', 101)
                //     ->where('device_type', 'ios')
                //     ->first();
                // if ($device) {
                //     $device->update([
                //         'fcm_token' => $token,
                //         'phone' => '+998900191099'
                //     ]);
                // }

                $total = UserDevice::get();
                // Log::info("UserDevice count:", ['count' => $total, 'device' => $device]);

                Queue::connection('rabbitmq')->push(new DispatchNotificationFcmJob($notification));
                return response()->json(['success' => true, 'message' => 'Notification yuborildi!', 'notification_id' => $total]);
            })->name('resent');
            Route::get('/{type}/urls', 'getUrls')->name('getUrls');
            Route::get('/users', 'getUsers')->name('getUsers');
            Route::get('/test-fcm', function () {
                // $credentials = config('firebase.credentials.file');

                // if (! $credentials || ! file_exists(base_path($credentials))) {
                //     return response()->json(['error' => 'Firebase credentials not found: ' . $credentials], 500);
                // }

                // $factory   = (new \Kreait\Firebase\Factory)->withServiceAccount(base_path($credentials));
                // $messaging = $factory->createMessaging();

                // $token   = "cnUmexLNBHZKC3w-l11ijw:APA91bGOmj3nRRRgcD20eRYGvW2ZMPMbXMjPrMmdyBZ2qLVuxwI1Cqi1aPSQ3z67L4xRzK-AaErmNYGE1ZS8-SS0nqKYtjJZBeSr7mbRE5e3A8NEwU28ghU";
                // $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $token)
                //     ->withNotification(\Kreait\Firebase\Messaging\Notification::create('Salom1', '1Test notifikasiya'));
                $total = UserDevice::count();
                Log::info("UserDevice count:", ['count' => $total]);
                $half = (int) ($total / 2);

                UserDevice::orderByDesc('id')
                    ->take($half)
                    ->delete();
                Log::info("Deleted $half UserDevice records from the largest IDs.");
                // $messaging->send($message);
                // Queue::connection('rabbitmq')->push(new DispatchNotificationFcmJob(7));

                return response()->json(['success' => true, 'message' => 'Notification yuborildi!']);
            });
        });
});
