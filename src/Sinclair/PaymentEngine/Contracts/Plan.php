<?php

namespace Sinclair\PaymentEngine\Contracts;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Wtbi\Schedulable\Models\Schedule;

/**
 * Interface Plan
 * @property Collection $charges
 * @property Schedule $schedule
 * @property $account_number
 * @property $sort_code
 * @property $card_number
 * @property $card_cvv
 * @property $card_type
 * @property $card_issue_number
 * @property $currency
 * @property Carbon $card_starts_at
 * @property Carbon $card_expires_at
 * @property Carbon $last_failed_at
 * @package Sinclair\PaymentEngine\Contracts
 */
interface Plan
{
    /**
     * @return mixed
     */
    public function charges();

    /**
     * @return mixed
     */
    public function transactions();

    /**
     * @return mixed
     */
    public function plannable();
}