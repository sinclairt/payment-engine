<?php

namespace Sinclair\PaymentEngine\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\PlanRepository as PlanRepositoryInterface;
use Sinclair\PaymentEngine\Traits\CanBeScheduled;
use Sinclair\Repository\Repositories\Repository;

/**
 * Class Plan
 * @package Sinclair\PaymentEngine\Repositories
 */
class PlanRepository extends Repository implements PlanRepositoryInterface
{
    use CanBeScheduled;

    /**
     * @var Plan
     */
    public $model;

    /**
     * Plan constructor.
     *
     * @param Plan $model
     */
    public function __construct( Plan $model )
    {
        $this->model = $model;
    }

    /**
     * @param array $columns
     * @param null $orderBy
     * @param string $direction
     *
     * @return Collection
     */
    public function getAllScheduled( $columns = [ '*' ], $orderBy = null, $direction = 'asc' )
    {
        $query = $this->model->isDue()
                             ->with([ 'charges' => function ( $query )
                             {
                                 $query->between($this->model->schedule->last_ran_at);
                             } ]);

        if ( !is_null($orderBy) )
            $query = $query->orderBy($orderBy, $direction);

        return $query->get($columns);
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