<?php

namespace Sinclair\PaymentEngine\Contracts;

/**
 * Interface Transaction
 * @property $reference
 * @property $currency
 * @package Sinclair\PaymentEngine\Contracts
 */
interface Transaction
{
    /**
     * @return mixed
     */
    public function plan();

    /**
     * @return mixed
     */
    public function items();

    /**
     * @return mixed
     */
    public function total();
}