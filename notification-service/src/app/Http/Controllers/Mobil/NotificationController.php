<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationsResource;
use App\Models\Notification;
use App\Models\NotificationUser;
use App\Models\UserDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        $user = $request->get('auth_user');
        if (!$user) {
            return $this->errorResponse(message: 'User not authenticated', code: 401);
        }

        $query = Notification::query()
            ->whereHas('platforms', fn($q) => $q->where('platform', 'ios'));

        if ($user['is_guest']) {
            $query->whereHas(
                'users',
                fn($q) => $q
                    ->where('user_id', $user['id'])
                    ->whereIn('status', ['sent', 'viewed'])
            )->with([
                'users' => fn($q) => $q
                    ->where('user_id', $user['id'])
                    ->whereIn('status', ['sent', 'viewed'])
                    ->latest()
                    ->limit(1)
            ]);
        } else {
            $query->whereHas(
                'users',
                fn($q) => $q
                    ->where('phone', $user['phone'])
                    ->whereIn('status', ['sent', 'viewed'])
            )->with([
                'users' => fn($q) => $q
                    ->where('phone', $user['phone'])
                    ->whereIn('status', ['sent', 'viewed'])
                    ->latest()
                    ->limit(1)
            ]);
        }
        $perPage = $request->get('per_page', 2);
        $notifications = $query->orderByDesc('id')->paginate($perPage);
        $data = [
            'items' => NotificationsResource::collection($notifications),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page'     => $notifications->perPage(),
                'total'        => $notifications->total(),
                'last_page'    => $notifications->lastPage(),
                'next_page'    => $notifications->hasMorePages() ? $notifications->currentPage() + 1 : null,
                'prev_page'    => $notifications->currentPage() > 1 ? $notifications->currentPage() - 1 : null,
            ],
        ];

        return $this->successResponse( $data, 'success');
    }

    public function unreadCount(Request $request)
    {
        $user = $request->get('auth_user');
        if (!$user) {
            return $this->errorResponse(message: 'User not authenticated', code: 401);
        }

        $query = Notification::whereHas('platforms', function ($q) {
            $q->where('platform', 'ios');
        });

        // guest bo'lsa user_id bo'yicha, ro'yxatdan o'tgan bo'lsa phone bo'yicha tekshiradi
        if ($user['is_guest'] == true) {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('user_id', $user['id'])
                    ->where('status', 'sent'); // faqat o‘qilmaganlar
            });
        } else {
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('phone', $user['phone'])
                    ->where('status', 'sent');
            });
        }

        $unreadCount = $query->count();

        return $this->successResponse(
            data: ['unread_count' => $unreadCount],
            message: 'success'
        );
    }
    public function markAsRead(Request $request, $notificationId)
    {
        $user = $request->get('auth_user');
        if (! $user) {
            return $this->errorResponse(message: 'User not authenticated', code: 401);
        }

        $query = NotificationUser::where('notification_id', $notificationId);

        if ($user['is_guest'] == true) {
            $query->where('user_id', $user['id']);
        } else {
            $query->where('phone', $user['phone']);
        }

        $notificationUser = $query->orderByDesc('id')->first();

        if (! $notificationUser) {
            return $this->errorResponse(message: 'Notification not found for this user', code: 404);
        }

        if ($notificationUser->status === 'sent') {
            $notificationUser->status = 'viewed';
            $notificationUser->save();
        }
        return $this->successResponse(
            data: ['notification_id' => $notificationId, 'status' => $notificationUser['status']],
            message: 'Notification marked as read'
        );
    }
    public function markAllAsRead(Request $request)
    {
        $user = $request->get('auth_user');
        if (! $user) {
            return $this->errorResponse(message: 'User not authenticated', code: 401);
        }

        $query = NotificationUser::query()
            ->whereHas('notification.platforms', function ($q) {
                $q->whereIn('platform', ['ios', 'android']);
            });

        if ($user['is_guest'] == true) {
            $query->where('user_id', $user['id']);
        } else {
            $query->where('phone', $user['phone']);
        }

        // faqat ko‘rilmaganlarini yangilaymiz
        $updatedCount = $query->where('status', '=', 'sent')
            ->update([
                'status'     => 'viewed',
                'updated_at' => now(),
            ]);

        return $this->successResponse(
            data: ['updated_count' => $updatedCount],
            message: 'All mobile notifications marked as read'
        );
    }
}
