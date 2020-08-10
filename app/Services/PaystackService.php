<?php

namespace App\Services;

use App\Exceptions\ApiError;
use App\Jobs\ProcessFailedTransfers;
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
        try {
            return $this->requestPaystackTransfer($payoutData);
        } catch (\Illuminate\Http\Client\HttpClientException $th) {
            ProcessFailedTransfers::dispatch($payoutData)->delay(now()->addHour());
            return ['status' => 'pending'];
        }
    }

    public function requestPaystackTransfer(array $payoutData)
    {
        $recipientResponse = $this->createTransferRecipient($payoutData);
        if ($recipientResponse['status']) {
            $transferData['recipient_code'] = $recipientResponse['data']['recipient_code'];
            $transferData['amount'] = $payoutData['amount'];

            $transferResponse = $this->initiateTransfer($transferData);
            if ($transferResponse['status']) {
                //TODO: Send user email for successful transfer
                return $transferResponse['data'];
            }
        }

        throw new ApiError('Failed to request fund transfer!');
    }

    public function getTransferHistory(int $page, int $perPage)
    {

        try {
            $response = Http::withHeaders($this->headers)->get("{$this->baseUrl}/transfer?page={$page}&perPage={$perPage}");
            $body = $response->json();

            if ($response->successful()) {
                return $body['data'];
            }

            throw new ApiError($body['message'] ?? 'Failed to retrieve transfer history');

        } catch (\Illuminate\Http\Client\HttpClientException $th) {
            throw new ApiError('Failed to retrieve transfer history', null, 502);
        }

    }

    public function getTransfer(string $transferId)
    {
        try {
            $response = Http::withHeaders($this->headers)->get("{$this->baseUrl}/transfer/{$transferId}");
            $body = $response->json();

            if ($response->successful()) {
                return ($response->json());
            }

            throw new ApiError($body['message'] ?? 'Failed to retrieve transfer!');

        } catch (\Illuminate\Http\Client\HttpClientException $th) {
            throw new ApiError('Failed to retrieve transfer!', null, 502);
        }

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

        if ($response->clientError()) {
            throw new ApiError("Failed to retrieve recipient details: {$body['message']}");
        }

        $response->throw();
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

        if ($response->clientError()) {
            throw new ApiError("Failed to initiate transfer: {$body['message']}");
        }

        $response->throw();
    }

}
