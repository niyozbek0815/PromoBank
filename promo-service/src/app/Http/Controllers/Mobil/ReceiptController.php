<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendReceiptRequest;
use App\Models\SalesReceipt;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $receiptService,
    ) {
    }
    public function index(SendReceiptRequest $request)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        Log::info("Request", ['data' => $req]);
        SalesReceipt::query()->delete();
          $data = $this->receiptService->proccess($req, $user,'mobile');
        if($data['status']=="fail"){
            return $this->errorResponse($data, "failed");
        }else{
            return $this->successResponse($data, "success");
        }
    }
    public function points(Request $request)
    {
        $user = $request['auth_user'];
        return $this->successResponse(["points" => $this->receiptService->getPoints($user)], "success");
    }
}
