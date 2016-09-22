<?php

require_once 'DbTestCase.php';
require_once 'ApiTest.php';
require_once 'Models/Dummy.php';

use Sinclair\PaymentEngine\Models\Transaction;
use Sinclair\PaymentEngine\Models\Plan;

/**
 * Class TransactionItemApiTest
 */
class TransactionItemApiTest extends ApiTest
{
    /**
     * ChargeApiTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = new \Sinclair\PaymentEngine\Models\Item();

        $this->attributes = [
            'transaction_id',
            'amount',
            'description',
            'charged_at',
            'created_at',
        ];

        $this->baseUri = '/payment/engine/api/v1/item';
    }

    /**
     *
     */
    public function test_i_can_filter_items_by_transaction()
    {
        $this->createDummies(20);

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'transaction' => 1,
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $this->checkAttributes($this->model->find(1), $content);

        $this->assertEquals(1, sizeof($content->data));
    }

    /**
     * @param $count
     *
     * @return array
     */
    protected function createDummies( $count = 20 )
    {
        $dummies = [];
        for ( $i = 0; $i < $count; $i++ )
            $dummies[] = $this->model->create(array_filter($this->createData(), [ $this->model, 'isFillable' ], ARRAY_FILTER_USE_KEY));

        return $dummies;
    }

    protected function createData( $attributes = [] )
    {
        $data = [
            'transaction_id' => Transaction::create($this->createTransaction())->id,
            'amount'         => $this->faker->randomFloat(2),
            'description'    => implode(' ', $this->faker->words()),
            'charged_at'     => \Carbon\Carbon::now()
                                              ->toDatetimeString(),
        ];

        return array_replace($data, $attributes);

    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function updateData( $attributes = [] )
    {
        return $this->createData($attributes);
    }
}