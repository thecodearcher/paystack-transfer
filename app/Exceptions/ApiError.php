<?php

namespace App\Exceptions;

use App\Traits\ApiResponseTrait;
use Exception;

class ApiError extends Exception
{
    use ApiResponseTrait;

    protected $data;
    protected $message;
    protected $statusCode;
    public function __construct(string $message = 'Something went south!', $data = null, $statusCode = 400)
    {
        $this->data = $data;
        $this->message = $message;
        $this->statusCode = $statusCode;
    }

    public function render()
    {
        return $this->custom($this->data, $this->message, false, $this->statusCode);
    }
}
