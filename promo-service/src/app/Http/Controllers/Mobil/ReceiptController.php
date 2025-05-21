<?php

namespace App\Http\Controllers\Mobil;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendReceiptRequest;
use App\Services\ReceiptService;
use Illuminate\Http\Request;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptService $receiptService,
    ) {
        $this->receiptService = $receiptService;
    }
    public function index(SendReceiptRequest $request)
    {
        $user = $request['auth_user'];
        $req = $request->validated();
        $data = $this->receiptService->process($req, $user);
        return $this->successResponse($data, "success");
    }
    public function points(Request $request)
    {
        $user = $request['auth_user'];;
        return $this->successResponse(["points" => $this->receiptService->getPoints($user)], "success");
    }
}