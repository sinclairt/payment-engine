<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateTransaction
 * @package Sinclair\PaymentEngine\FormRequests
 */
class CreateTransaction extends FormRequest
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
            'plan_id'          => 'required|exists:plans,id',
            'card_number'      => 'required|size:16',
            'card_starts_at'   => 'required|date|before:card_expires_at|before:today',
            'card_expires_at'  => 'required|date|after:card_starts_at|after:today',
            'card_cvv'         => 'required|min:100|max:999|numeric',
            'gateway_response' => 'sometimes|nullable|json',
            'is_success'       => 'sometimes|boolean',
            'is_failure'       => 'sometimes|boolean'
        ];
    }
}
