<?php

namespace Sinclair\PaymentEngine\Repositories;

use Sinclair\PaymentEngine\Contracts\Charge;
use Sinclair\PaymentEngine\Contracts\ChargeRepository as ChargeRepositoryInterface;
use Sinclair\PaymentEngine\Traits\CanBeScheduled;
use Sinclair\Repository\Repositories\Repository;

/**
 * Class Charge
 * @package Sinclair\PaymentEngine\Repositories
 */
class ChargeRepository extends Repository implements ChargeRepositoryInterface
{
    use CanBeScheduled;

    /**
     * @var Charge
     */
    public $model;

    /**
     * Charge constructor.
     *
     * @param Charge $model
     */
    public function __construct( Charge $model )
    {
        $this->model = $model;
    }

    /**
     * @param array $attributes
     * @param null $model
     *
     * @return \Sinclair\PaymentEngine\Models\Plan
     */
    public function save( $attributes, $model = null )
    {
        \DB::beginTransaction();

        $model = is_null($model) ? new $this->model : $model;

        $fillable = $this->onlyFillable($attributes, $model);

        $model->fill($fillable)
              ->save();

        $this->schedule($attributes, $model);

        \DB::commit();

        return $model;
    }
}