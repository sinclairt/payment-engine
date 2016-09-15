<?php

namespace Sinclair\PaymentEngine\Repositories;

use Sinclair\PaymentEngine\Contracts\Item;
use Sinclair\PaymentEngine\Contracts\ItemRepository as ItemRepositoryInterface;
use Sinclair\Repository\Repositories\Repository;

/**
 * Class Item
 * @package Sinclair\PaymentEngine\Repositories
 */
class ItemRepository extends Repository implements ItemRepositoryInterface
{
    /**
     * @var Item
     */
    public $model;

    /**
     * Item constructor.
     *
     * @param Item $model
     */
    public function __construct( Item $model )
    {
        $this->model = $model;
    }
}