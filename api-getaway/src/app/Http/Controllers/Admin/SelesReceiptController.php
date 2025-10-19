<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelesReceiptController extends Controller
{
    protected string $url;

    public function __construct()
    {
        // promo_service URL ni config/services.php ichidan olish
        $this->url = config('services.urls.promo_service');
    }
    public function index(Request $request)
    {
        return view('admin.reciepts.index');
    }

    public function data(Request $request)
    {
        $endpoint = "front/seles_receipts/data";
        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);
Log::info('Fetching sales receipts data', $response->json());

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['message' => 'Promo service error'], 500);
    }

    public function winningByPromotion(Request $request, $promotion_id)
    {
        // ✅ to‘g‘rilangan — underscore emas, hyphen ishlatilgan
        $endpoint = "front/sales-receipts/{$promotion_id}/winning";

        $response = $this->forwardRequest("GET", $this->url, $endpoint, $request);

        Log::info('Fetching winning sales receipts', [
            'promotion_id' => $promotion_id,
            'endpoint' => $endpoint,
            'response' => $response instanceof \Illuminate\Http\Client\Response ? $response->json() : 'invalid'
        ]);

        if ($response instanceof \Illuminate\Http\Client\Response) {
            return response()->json($response->json(), $response->status());
        }

        return response()->json(['message' => 'Promo service error'], 500);
    }
}
