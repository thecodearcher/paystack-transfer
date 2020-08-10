<?php

namespace App\Services;

use App\Exceptions\ApiError;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    private $headers;
    private $baseUrl;
    public function __construct()
    {
        $this->baseUrl = "https://api.paystack.co";
        $secret = App::environment('production') ? env('PAYSTACK_LIVE_SECRET') : env('PAYSTACK_TEST_SECRET');
        $this->headers = [
            'Authorization' => "Bearer " . $secret,
            'Content-Type' => 'application/json',
        ];
    }

    public function requestFundTransfer(array $payoutData)
    {
        $recipientResponse = $this->createTransferRecipient($payoutData);
        if ($recipientResponse['status']) {
            $transferData['recipient_code'] = $recipientResponse['data']['recipient_code'];
            $transferData['amount'] = $payoutData['amount'];

            $transferResponse = $this->initiateTransfer($transferData);
            if ($transferResponse['status']) {
                return $transferResponse['data'];
            }
        }

        throw new ApiError('Failed to request fund transfer!');
    }

    public function getTransferHistory(int $page, int $perPage)
    {
        $url = "{$this->baseUrl}/transfer?page={$page}&perPage={$perPage}";

        $response = Http::withHeaders($this->headers)->get($url);
        $body = $response->json();

        if ($response->successful()) {
            return $body['data'];
        }

        throw new ApiError($body['message']);
    }

    public function getTransfer(string $transferId)
    {
        $url = "{$this->baseUrl}/transfer/{$transferId}";

        $response = Http::withHeaders($this->headers)->get($url);
        $body = $response->json();

        if ($response->successful()) {
            return ($response->json());
        }

        throw new ApiError($body['message']);
    }

    private function createTransferRecipient(array $bankDetails)
    {
        $response = Http::withHeaders($this->headers)->post("{$this->baseUrl}/transferrecipient", [
            "type" => "nuban",
            "name" => $bankDetails['accountName'],
            "account_number" => $bankDetails['accountNumber'],
            "bank_code" => $bankDetails['bankCode'],
            "currency" => "NGN",
        ]);
        $body = $response->json();

        if ($response->successful()) {
            return $body;
        }

        throw new ApiError("Failed to retrieve recipient details: {$body['message']}");
    }

    private function initiateTransfer(array $transferData)
    {
        $response = Http::withHeaders($this->headers)->post("{$this->baseUrl}/transfer", [
            "source" => "balance",
            "recipient" => $transferData['recipient_code'],
            "amount" => $transferData['amount'] * 100,
            "reason" => "Transfer request",
        ]);
        $body = $response->json();

        if ($response->successful()) {
            return $body;
        }

        throw new ApiError("Failed to initiate transfer: {$body['message']}");
    }

}
