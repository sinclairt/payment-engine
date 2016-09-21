<?php

require_once 'ApiTest.php';

use Sinclair\PaymentEngine\Models\Charge;

/**
 * Class ChargeApiTest
 */
class ChargeApiTest extends ApiTest
{
    /**
     * ChargeApiTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = new Charge();

        $this->attributes = [
            'plan_id',
            'amount',
            'description',
            'created_at',
            'updated_at'
        ];

        $this->baseUri = '/payment/engine/api/v1/charge';
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
            'plan_id'     => $this->createPlan($attributes)
                                  ->first()->id,
            'amount'      => $this->faker->randomFloat(2, 0),
            'description' => $this->faker->sentence
        ];

        return array_replace($data, $this->randomSchedule(), $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return array
     */
    protected function updateData( $attributes = [] )
    {
        $data = [
            'plan_id'     => $this->createPlan($attributes)
                                  ->first()->id,
            'amount'      => $this->faker->randomFloat(2, 0),
            'description' => $this->faker->sentence
        ];

        return array_replace($data, $attributes);
    }
}