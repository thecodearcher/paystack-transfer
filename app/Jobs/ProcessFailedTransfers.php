<?php

namespace App\Jobs;

use App\Services\PaystackService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessFailedTransfers implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 2;
    protected $transferData;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $transferData)
    {
        $this->transferData = $transferData;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(PaystackService $paystackService)
    {
        try {
            $paystackService->requestPaystackTransfer($this->transferData);
        } catch (\Illuminate\Http\Client\HttpClientException $th) {
            //TODO: Send out mail to user letting them know this transfer failed

        }
    }
}
