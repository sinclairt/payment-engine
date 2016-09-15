<?php

namespace Sinclair\PaymentEngine\Traits;

/**
 * Class CanBeScheduled
 * @package Sinclair\PaymentEngine\Traits
 */
trait CanBeScheduled
{
    /**
     * @param $attributes
     * @param $model
     */
    protected function schedule( $attributes, $model ):void
    {
        schedule($model)
            ->minute(array_get($attributes, 'minute'))
            ->hour(array_get($attributes, 'hour'))
            ->dayOfWeek(array_get($attributes, 'day_of_week'))
            ->dayOfMonth(array_get($attributes, 'day_of_month'))
            ->monthOfYear(array_get($attributes, 'month_of_year'))
            ->year(array_get($attributes, 'year'))
            ->startsAt(array_get($attributes, 'starts_at'))
            ->expiresAt(array_get($attributes, 'expires_at'))
            ->{array_get($attributes, 'frequency', 'monthly')}()
            ->save();
    }

    /**
     * @return array
     */
    protected function scheduleCreationRules()
    {
        return [
            'minute'        => 'required_if:frequency,hourly,weekly,monthly,annually,yearly,adhoc',
            'hour'          => 'required_if:frequency,weekly,monthly,annually,yearly,adhoc',
            'day_of_week'   => 'required_if:frequency,weekly',
            'day_of_month'  => 'required_if:frequency,monthly,yearly,annually,adhoc',
            'month_of_year' => 'required_if:frequency,yearly,annually,adhoc',
            'year'          => 'required_if:frequency,adhoc',
            'frequency'     => 'required|in:minutely,hourly,weekly,monthly,annually,yearly,adhoc',
            'starts_at'     => 'sometimes|nullable|date|before:expires_at',
            'expires_at'    => 'sometimes|nullable|date|after:starts_at'
        ];
    }

    /**
     * @return array
     */
    protected function scheduleUpdateRules()
    {
        return array_replace($this->scheduleCreationRules(), [ 'frequency' => 'sometimes|in:minutely,hourly,weekly,monthly,annually,yearly,adhoc' ]);
    }
}