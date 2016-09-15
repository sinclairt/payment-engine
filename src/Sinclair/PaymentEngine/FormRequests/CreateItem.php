<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Class CreateItem
 * @package Sinclair\PaymentEngine\FormRequests
 */
class CreateItem extends FormRequest
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
            'transaction_id' => 'required|exists:transactions,id',
            'amount'         => 'required|numeric|min:0',
            'description'    => 'sometimes|nullable|string|max:255'
        ];
    }
}
