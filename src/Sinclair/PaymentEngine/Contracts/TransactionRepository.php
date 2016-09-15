<?php

namespace Sinclair\PaymentEngine\Contracts;

use Sinclair\Repository\Contracts\Repository;

/**
 * Interface TransactionRepository
 * @package Sinclair\PaymentEngine\Contracts
 */
interface TransactionRepository extends Repository
{
    /**
     * @return mixed
     */
    public function getFailed();;
}