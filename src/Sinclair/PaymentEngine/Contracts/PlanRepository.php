<?php

namespace Sinclair\PaymentEngine\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Sinclair\Repository\Contracts\Repository;

/**
 * Interface PlanRepository
 * @package Sinclair\PaymentEngine\Contracts
 */
interface PlanRepository extends Repository
{
    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAllScheduled( $columns = [ '*' ], $orderBy = null, $direction = 'asc' );
}