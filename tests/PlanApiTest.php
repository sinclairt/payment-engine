<?php

require_once 'DbTestCase.php';
require_once 'ApiTest.php';

use Sinclair\PaymentEngine\Models\Plan;

/**
 * Class PlanApiTest
 */
class PlanApiTest extends ApiTest
{
    /**
     * PlanApiTest constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->model = new Plan();

        $this->attributes = [
            'plannable_type',
            'plannable_id',
            'card_number',
            'card_starts_at',
            'card_expires_at',
            'card_cvv',
            'card_type',
            'card_issue_number',
            'currency',
            'last_failed_at',
            'created_at',
            'updated_at'
        ];

        $this->baseUri = '/payment/engine/api/v1/plan';
    }

    // filters

    /**
     *
     */
    public function test_i_can_filter_plans_by_plannable_id()
    {
        $this->createDummies(20);

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'plannable_id' => 1,
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $this->checkAttributes($this->model->find(1), $content);

        $this->assertEquals(1, sizeof($content->data));
    }

    /**
     *
     */
    public function test_i_can_filter_plans_by_plannable_type()
    {
        // create a fake entry
        $plan = Plan::create([
            'plannable_type'    => 'SomeRandomClass',
            'plannable_id'      => 1,
            'card_number'       => $this->faker->creditCardNumber,
            'card_starts_at'    => null,
            'card_expires_at'   => \Carbon\Carbon::instance($this->faker->creditCardExpirationDate)
                                                 ->toDateTimeString(),
            'card_cvv'          => $this->faker->numberBetween(100, 999),
            'card_type'         => $this->faker->creditCardType,
            'card_issue_number' => 1,
            'currency'          => 'GBP',
            'last_failed_at'    => null
        ]);

        $this->createDummies(20);

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'plannable_type' => 'SomeRandomClass',
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $this->checkAttributes($plan, $content);

        $this->assertEquals(1, sizeof($content->data));
    }

    /**
     *
     */
    public function test_i_can_filter_plans_by_frequency()
    {
        for ( $i = 0; $i < 20; $i++ )
            $this->json('POST', $this->baseUri, $this->createData($this->randomSchedule([ 'frequency' => $this->faker->boolean(80) ? 'monthly' : 'daily' ])))->response;

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'frequency' => 'monthly',
            'rows'      => 20
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        $plans = Plan::isMonthly()
                     ->get();

        $this->assertEquals(sizeof($plans), sizeof($content->data));

        foreach ( $plans as $key => $plan )
            $this->checkAttributes($plan, $content, $key);

        $this->assertEquals($plans->count(), sizeof($content->data));
    }

    public function test_i_can_filter_plans_by_currency()
    {
        $expected = collect($this->createDummies(20))->slice(5, 10);

        foreach ( $expected as $key => $plan )
        {
            $plan->currency = 'EUR';
            $plan->save();
            $expected->offsetSet($key, $plan->fresh());
        }

        $expected = $expected->values();

        $response = $this->json('POST', $this->baseUri . '/filter', [
            'currency' => 'EUR',
        ])->response;

        $content = json_decode($response->content());

        $this->checkStructure($content);

        foreach ( $expected as $key => $plan )
            $this->checkAttributes($plan, $content, $key);

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

        foreach ( $expected as $key => $plan )
            $this->checkAttributes($plan, $content, $key);

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
        $plannable = Dummy::create([
            'first_name'        => $this->faker->firstName,
            'last_name'         => $this->faker->lastName,
            'type'              => $this->faker->creditCardType,
            'billing_address_1' => $this->faker->streetAddress,
            'billing_address_2' => '',
            'billing_city'      => $this->faker->city,
            'billing_postcode'  => $this->faker->postcode,
            'billing_state'     => '',
            'billing_country'   => $this->faker->country,
            'billing_phone'     => $this->faker->phoneNumber,
            'shipping_address1' => $this->faker->streetAddress,
            'shipping_address2' => '',
            'shipping_city'     => $this->faker->city,
            'shipping_postcode' => $this->faker->postcode,
            'shipping_state'    => '',
            'shipping_country'  => $this->faker->country,
            'shipping_phone'    => $this->faker->phoneNumber,
            'company'           => $this->faker->company,
            'email'             => $this->faker->email,
        ]);

        $data = [
            'plannable_type'    => get_class($plannable),
            'plannable_id'      => $plannable->id,
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
            'last_failed_at'    => null,
            'minute'            => 30,
            'hour'              => 9,
            'day_of_month'      => 1,
            'frequency'         => 'monthly',
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
        $data = [
            'plannable_type'    => 'Foo',
            'plannable_id'      => 1,
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
            'last_failed_at'    => null,
        ];

        return array_replace($data, $attributes);
    }
}