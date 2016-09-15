<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Sinclair\PaymentEngine\Traits\CanBeScheduled;

/**
 * Class CreatePlan
 * @package Sinclair\PaymentEngine\FormRequests
 */
class CreatePlan extends FormRequest
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
            'plannable_type'  => 'required',
            'plannable_id'    => 'required',
            'card_number'     => 'required|size:16',
            'card_starts_at'  => 'required|date|before:card_expires_at|before:today|before:starts_at',
            'card_expires_at' => 'required|date|after:card_starts_at|after:today|after:expires_at',
            'card_cvv'        => 'required|size:3|numeric',
            'last_ran_at'     => 'sometimes|nullable|date',
            'last_failed_at'  => 'sometimes|nullable|date',
        ], $this->scheduleCreationRules(), [
            'starts_at'  => 'sometimes|nullable|date|before:card_expires_at|after:card_starts_at|before:expires_at',
            'expires_at' => 'sometimes|nullable|date|before:card_expires_at|after:card_starts_at|after:starts_at'
        ]);
    }
}
