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
            'plan_id'           => $this->createPlan($attributes)->id,
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

    private function randomSchedule( $attributes = [] )
    {
        $data = [
            'minute'        => $this->faker->numberBetween(0, 59),
            'hour'          => $this->faker->numberBetween(0, 23),
            'day_of_week'   => $this->faker->numberBetween(0, 6),
            'day_of_month'  => $this->faker->numberBetween(0, 28),
            'month_of_year' => $this->faker->numberBetween(1, 12),
            'year'          => $this->faker->numberBetween(date('Y'), date('Y') + 3),
            'frequency'     => $this->faker->randomElement([ 'minutely', 'hourly', 'daily', 'weekly', 'monthly', 'annually', 'adhoc' ]),
            'starts_at'     => $this->faker->boolean ? \Carbon\Carbon::now()
                                                                     ->addWeeks($this->faker->numberBetween(0, 6))
                                                                     ->toDateTimeString() : null,
            'expires_at'    => $this->faker->boolean ? \Carbon\Carbon::now()
                                                                     ->addYears($this->faker->numberBetween(1, 3))
                                                                     ->toDateTimeString() : null,

        ];

        return array_replace($data, $attributes);
    }

    /**
     * @param array $attributes
     *
     * @return Plan
     */
    protected function createPlan( $attributes = [] )
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
        ];

        return Plan::create(array_replace($data, $attributes, $this->randomSchedule()));
    }
}