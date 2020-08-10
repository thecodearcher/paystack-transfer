<?php

namespace App\Http\Requests;

class RequestTransferRequest extends CustomFormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'amount' => 'required|numeric',
            'accountNumber' => 'required|numeric',
            'bankCode' => 'required|numeric',
            'accountName' => 'required|string',
        ];
    }
}
