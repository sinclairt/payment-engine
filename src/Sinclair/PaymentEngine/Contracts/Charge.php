<?php

namespace Sinclair\PaymentEngine\Contracts;

/**
 * Interface Charge
 * @package Sinclair\PaymentEngine\Contracts
 */
interface Charge
{
    /**
     * @return mixed
     */
    public function plan();
}