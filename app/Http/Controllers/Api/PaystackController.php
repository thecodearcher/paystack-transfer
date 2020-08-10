<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestTransferRequest;
use App\Services\PaystackService;
use Illuminate\Http\Request;

class PaystackController extends Controller
{
    private $paystackService;
    public function __construct(PaystackService $paystackService)
    {
        $this->paystackService = $paystackService;
    }

    public function requestFundTransfer(RequestTransferRequest $request)
    {
        $response = $this->paystackService->requestFundTransfer($request->validated());
        return $this->success($response, 'Fund Transfer Request Successful!');
    }

    public function getTransfers(Request $request)
    {
        $perPage = $request->query('perPage', 50);
        $page = $request->query('page', 1);

        $transfers = $this->paystackService->getTransferHistory($page, $perPage);
        return $this->success($transfers, 'Transfers retrieved');
    }

    public function getTransfer(string $transferId)
    {
        $transfer = $this->paystackService->getTransfer($transferId);
        return $this->success($transfer, 'Transfer retrieved');
    }
}
