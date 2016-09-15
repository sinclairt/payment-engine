<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Sinclair\PaymentEngine\Traits\CanBeScheduled;

/**
 * Class UpdateCharge
 * @package Sinclair\PaymentEngine\FormRequests
 */
class UpdateCharge extends FormRequest
{
    use CanBeScheduled;

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
        return array_replace([
            'plan_id'     => 'sometimes|exists:plans,id',
            'amount'      => 'sometimes|numeric|min:0',
            'description' => 'sometimes|nullable|string|max:255',
        ], $this->scheduleUpdateRules());
    }
}