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
    protected function schedule( $attributes, $model )
    {
        if ( sizeof($this->getScheduleKeysFromAttributes($attributes)) > 0 )
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
    protected function scheduleKeys()
    {
        return [ 'minute', 'hour', 'day_of_week', 'day_of_month', 'month_of_year', 'year', 'starts_at', 'expires_at', 'frequency' ];
    }

    /**
     * @param $attributes
     *
     * @return array
     */
    protected function getScheduleKeysFromAttributes( $attributes )
    {
        return array_intersect(array_keys($attributes), $this->scheduleKeys());
    }
}