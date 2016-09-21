<?php

require_once 'DbTestCase.php';
require_once 'ApiTest.php';
require_once 'Models/Dummy.php';

use Sinclair\PaymentEngine\Models\Transaction;
use Sinclair\PaymentEngine\Models\Plan;

/**
 * Class TransactionApiTest
 */
class TransactionApiTest extends ApiTest
{
    /**
     * ChargeApiTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = new Transaction();

        $this->attributes = [
            'plan_id',
            'reference',
            'is_success',
            'is_failure',
            'gateway_response',
            'card_number',
            'card_starts_at',
            'card_expires_at',
            'card_cvv',
            'card_type',
            'card_issue_number',
            'currency',
            'created_at',
            'updated_at'
        ];

        $this->baseUri = '/payment/engine/api/v1/transaction';
    }

    // filters

    /**
     *
     */
    public function test_i_can_filter_charges_by_plan()
    {
        $this->createDummies(20);

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'plan' => 1,
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $this->checkAttributes($this->model->find(1), $content);

        $this->assertEquals(1, sizeof($content->data));
    }

    public function test_i_can_filter_plans_by_currency()
    {
        $expected = collect($this->createDummies(20))->slice(5, 10);

        foreach ( $expected as $key => $transaction )
        {
            $transaction->currency = 'EUR';
            $transaction->save();
            $expected->offsetSet($key, $transaction->fresh());
        }

        $expected = $expected->values();

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'currency' => 'EUR',
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        foreach ( $expected as $key => $transaction )
            $this->checkAttributes($transaction, $content, $key);

        $this->assertEquals($expected->count(), sizeof($content->data));
    }

    public function test_i_can_filter_by_card_type()
    {
        $dummies = $this->createDummies();

        $card_type = collect($dummies)
            ->pluck('card_type')
            ->first();

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'card_type' => $card_type,
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $expected = $this->model->where('card_type', $card_type)
                                ->get();

        foreach ( $expected as $key => $transaction )
            $this->checkAttributes($transaction, $content, $key);

        $this->assertEquals($expected->count(), sizeof($content->data));
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

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function createData( $attributes = [] )
    {
        $data = [
            'plan_id'           => $this->createPlan($attributes)->first()->id,
            'reference'         => implode(' ', $this->faker->words()),
            'is_success'        => $success = $this->faker->boolean,
            'is_failure'        => !$success,
            'gateway_response'  => null,
            'card_number'       => 1234567891234567,
            'card_starts_at'    => \Carbon\Carbon::now()
                                                 ->subYear()
                                                 ->toDateTimeString(),
            'card_expires_at'   => \Carbon\Carbon::now()
                                                 ->addYears(2)
                                                 ->toDateTimeString(),
            'card_cvv'          => $this->faker->numberBetween(100, 999),
            'card_type'         => $this->faker->creditCardType,
            'card_issue_number' => 1,
            'currency'          => 'GBP',
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