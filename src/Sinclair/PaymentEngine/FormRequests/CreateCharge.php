<?php

namespace Sinclair\PaymentEngine\FormRequests;

use Illuminate\Foundation\Http\FormRequest;
use Sinclair\PaymentEngine\Traits\ScheduleValidation;

/**
 * Class CreateCharge
 * @package Sinclair\PaymentEngine\FormRequests
 */
class CreateCharge extends FormRequest
{
    use ScheduleValidation;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function getValidatorInstance()
    {
        $validator = parent::getValidatorInstance();

        $validator->sometimes('starts_at', 'before:expires_at', function ( $input )
        {
            return !is_null($input->expires_at);
        });

        $validator->sometimes('expires_at', 'after:starts_at', function ( $input )
        {
            return !is_null($input->starts_at);
        });

        return $validator;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return array_replace([
            'plan_id'     => 'required|exists:plans,id',
            'amount'      => 'required|numeric|min:0',
            'description' => 'sometimes|nullable|string|max:255',
        ], $this->scheduleCreationRules());
    }
}
