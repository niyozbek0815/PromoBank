<?php

namespace App\Http\Controllers;

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
        return $data;
        return $this->successResponse(['message' => 'Receipt controller is working'], "success");
    }
}