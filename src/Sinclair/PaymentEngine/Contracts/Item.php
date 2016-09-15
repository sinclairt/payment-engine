<?php

namespace Sinclair\PaymentEngine\Contracts;

/**
 * Interface Item
 * @package Sinclair\PaymentEngine\Contracts
 */
interface Item
{
    /**
     * @return mixed
     */
    public function transaction();
}