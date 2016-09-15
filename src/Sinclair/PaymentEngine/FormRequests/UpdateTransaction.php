<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class UpdateTransaction
 * @package Sinclair\PaymentEngine\FormRequests
 */
class UpdateTransaction extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'plan_id'          => 'sometimes|exists:plans,id',
            'card_number'      => 'sometimes|size:16',
            'card_starts_at'   => 'sometimes|date|before:card_expires_at|before:today',
            'card_expires_at'  => 'sometimes|date|after:card_starts_at|after:today',
            'card_cvv'         => 'sometimes|size:3|numeric',
            'gateway_response' => 'sometimes|nullable|json',
            'is_success'       => 'sometimes|boolean',
            'is_failure'       => 'sometimes|boolean',
        ];
    }
}
