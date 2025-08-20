<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationsController extends Controller
{
    protected string $url;

    public function __construct()
    {
        // notification-service URL config/services.php ichidan olinadi
        $this->url = config('services.urls.notification_service');
    }

    /**
     * Index page (blade)
     */
    public function index(Request $request)
    {
        return view('admin.notifications.index');
    }

    /**
     * Fetch notifications data (AJAX datatable uchun)
     */
    public function data(Request $request)
    {
        Log::info("Fetching notifications data", ['request' => $request->all()]);
        $endpoint = "front/notifications/data";

        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['message' => 'Notification service error'], 500);
    }

    public function getUsers(Request $request)
    {
        $endpoint = "front/notifications/users";

        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['message' => 'User service error'], 500);
    }

    /**
     * Create form
     */
    public function create(Request $request)
    {
        // Agar URL larni boshqa service’lardan olish kerak bo‘lsa shu yerda forward qilamiz
        $serviceUrls = [
            'promotion' => config('services.urls.promo_service'),
            'game'      => config('services.urls.game_service'),
        ];

        $endpoints = [
            'promotion' => 'front/promotion/gettypes',
            'game'      => 'front/games/gettypes',
        ];

        $promotionUrls = [];
        $gameUrls      = [];

        $resp1 = $this->forwardRequest("GET", $serviceUrls['promotion'], $endpoints['promotion'], $request);
        if ($resp1 instanceof \Illuminate\Http\Client\Response  && $resp1->successful()) {
            $promotionUrls = $resp1->json() ?? [];
        }

        $resp2 = $this->forwardRequest("GET", $serviceUrls['game'], $endpoints['game'], $request);
        if ($resp2 instanceof \Illuminate\Http\Client\Response  && $resp2->successful()) {
            $gameUrls = $resp2->json() ?? [];
        }

        return view('admin.notifications.create', compact('promotionUrls', 'gameUrls'));
    }

    /**
     * Store new notification
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $response = $this->forwardRequestMedias(
            'POST',
            $this->url,
            'front/notifications/store',
            $request,
            ['media']// Fayl input name
        );
dd($response->json());
        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->route('admin.notifications.index')
                ->with('success', "Notification muvaffaqiyatli qo‘shildi.");
        }

        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }

    /**
     * Edit form
     */
    public function edit(Request $request, $id)
    {
        $serviceUrls = [
            'promotion' => config('services.urls.promo_service'),
            'game'      => config('services.urls.game_service'),
        ];

        $endpoints = [
            'promotion'    => 'front/promotion/gettypes',
            'game'         => 'front/games/gettypes',
            'notification' => "front/notifications/{$id}/edit",
        ];

        $promotionUrls = [];
        $gameUrls      = [];
        $notification  = "";

        $resp1 = $this->forwardRequest("GET", $serviceUrls['promotion'], $endpoints['promotion'], $request);
        if ($resp1 instanceof \Illuminate\Http\Client\Response  && $resp1->successful()) {
            $promotionUrls = $resp1->json() ?? [];
        }

        $resp2 = $this->forwardRequest("GET", $this->url, $endpoints['notification'], $request);
        if ($resp2 instanceof \Illuminate\Http\Client\Response  && $resp2->successful()) {
            $notification = $resp2->json() ?? [];
        }

        $resp3 = $this->forwardRequest("GET", $serviceUrls['game'], $endpoints['game'], $request);
        if ($resp3 instanceof \Illuminate\Http\Client\Response  && $resp3->successful()) {
            $gameUrls = $resp3->json() ?? [];
        }

        return view('admin.notifications.edit', compact('promotionUrls', 'gameUrls', 'notification'));
    }

    /**
     * Update notification
     */
    public function update(Request $request, $id)
    {
        $response = $this->forwardRequestMedias(
            'PUT',
            $this->url,
            "front/notifications/{$id}",
            $request,
            ['media']
        );

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return redirect()
                ->route('admin.notifications.index')
                ->with('success', "Notification muvaffaqiyatli yangilandi.");
        }

        if ($response->status() === 422) {
            return redirect()
                ->back()
                ->withErrors($response->json('errors'))
                ->withInput();
        }

        abort($response->status(), 'Xatolik yuz berdi: ' . $response->body());
    }

    /**
     * Delete notification
     */
    public function destroy(Request $request, $id)
    {
        Log::info("Notification delete request", ['id' => $id]);

        $endpoint = "front/notifications/{$id}/delete";
        $response = $this->forwardRequest("POST", $this->url, $endpoint, $request);

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json(['success' => true, 'message' => 'Notification muvaffaqiyatli o‘chirildi!']);
        }

        return response()->json(['success' => false, 'message' => 'O‘chirishda xatolik!'], $response->status());
    }

    /**
     * Re-send notification
     */
    public function resent(Request $request, $id)
    {
        $endpoint = "front/notifications/{$id}/resent";
        $response = $this->forwardRequest("POST", $this->url, $endpoint, $request);

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json(['success' => true, 'message' => 'Notification qayta yuborildi!']);
        }

        return response()->json(['success' => false, 'message' => 'Qayta yuborishda xatolik!'], $response->status());
    }

    /**
     * Get urls by type (for dropdown select)
     */
    public function getUrls(Request $request, string $type)
    {
        $map = [
            'promotion' => [config('services.urls.promo_service'), 'front/promotion/gettypes'],
            'game'      => [config('services.urls.game_service'), 'front/games/gettypes'],
        ];

        if (! isset($map[$type])) {
            return response()->json(['message' => 'Invalid type'], 400);
        }

        [$serviceUrl, $endpoint] = $map[$type];

        $response = $this->forwardRequest("GET", $serviceUrl, $endpoint, $request);

        if ($response instanceof \Illuminate\Http\Client\Response  && $response->successful()) {
            return response()->json($response->json(), 200);
        }

        return response()->json(['message' => 'Xatolik yuz berdi'], $response->status());
    }
}
