<?php

namespace Sinclair\PaymentEngine\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Engine
 * @package Sinclair\PaymentEngine
 */
interface Engine
{
    /**
     * @return array
     */
    public function getItems();

    /**
     * @return Model|Plan
     */
    public function getPlan();

    /**
     * @param Model|Plan $plan
     *
     * @return \Sinclair\PaymentEngine\Engine
     */
    public function setPlan( $plan );

    /**
     * @return \Sinclair\PaymentEngine\Engine
     */
    public function processScheduledPlans();

    /**
     * @param array|\Illuminate\Support\Collection|Collection $plans
     *
     * @return \Sinclair\PaymentEngine\Engine
     */
    public function processPlans( $plans );

    /**
     * @param $plan
     * @param bool $process
     *
     * @return \Sinclair\PaymentEngine\Engine
     */
    public function handleTransaction( Plan $plan, $process = true );

    /**
     * Get all charges for a plan and convert to a transaction item
     *
     * @param Plan $plan
     *
     * @return \Sinclair\PaymentEngine\Engine
     */
    public function calculateCharges( Plan $plan = null );

    /**
     * Create a transaction from plan
     *
     * @param Plan $plan
     *
     * @param bool $calculate
     *
     * @return Engine
     * @throws \Exception
     */
    public function generateTransaction( Plan $plan = null, $calculate = true );

    /**
     * @param Transaction|Model $transaction
     *
     * @return bool
     */
    public function processTransaction( Transaction $transaction = null );

    /**
     * @param $id
     * @param string $key
     *
     * @return mixed
     */
    public function getResult( $id, $key = 'plans' );
}