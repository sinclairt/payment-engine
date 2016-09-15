<?php

namespace Sinclair\PaymentEngine\Repositories;

use Sinclair\PaymentEngine\Contracts\Transaction;
use Sinclair\PaymentEngine\Contracts\TransactionRepository as TransactionRepositoryInterface;
use Sinclair\Repository\Repositories\Repository;

/**
 * Class Transaction
 * @package Sinclair\PaymentEngine\Repositories
 */
class TransactionRepository extends Repository implements TransactionRepositoryInterface
{
    /**
     * @var Transaction
     */
    public $model;

    /**
     * Transaction constructor.
     *
     * @param Transaction $model
     */
    public function __construct( Transaction $model )
    {
        $this->model = $model;
    }

    /**
     * @param array $attributes
     * @param null $model
     *
     * @return \Sinclair\PaymentEngine\Models\Transaction
     */
    public function save( $attributes, $model = null )
    {
        \DB::beginTransaction();

        $model = is_null($model) ? new $this->model : $model;

        $fillable = $this->onlyFillable($attributes, $model);

        $model->fill($fillable)
              ->save();

        $this->saveItems($attributes, $model);

        \DB::commit();

        return $model;
    }

    /**
     * @return mixed
     */
    public function getFailed()
    {
        return $this->model->where('is_failure', 1)
                           ->where('is_success', 0)
                           ->get();
    }

    /**
     * @param $attributes
     * @param $model
     */
    private function saveItems( $attributes, $model )
    {
        if ( !is_null($items = array_get($attributes, 'items')) )
            foreach ( $items as $item )
                app('ItemRepository')->add(array_replace($item, [ 'transaction_id' => $model->id ]));
    }
}