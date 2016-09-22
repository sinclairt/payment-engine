<?php

require_once 'DbTestCase.php';

use Sinclair\PaymentEngine\Models\Plan;

class EngineTest extends DbTestCase
{
    /**
     * @var \Faker\Generator
     */
    protected $faker;

    /**
     * @var \Sinclair\PaymentEngine\Engine
     */
    protected $engine;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/migrations');

        $this->migrate(__DIR__ . '/../vendor/laravel/laravel/database/migrations');

        $this->artisan('vendor:publish');

        $this->engine = app('PaymentEngine');
    }

    public function test_i_can_get_items()
    {
        $this->assertEquals([], $this->engine->getItems());
    }

    public function test_i_can_set_the_plan()
    {
        $this->engine->setPlan($this->createPlan()
                                    ->first());

        $this->assertNotNull($this->engine->plan);
    }

    public function test_i_can_set_the_initialise_the_gateway()
    {
        $this->assertInstanceOf(\Sinclair\PaymentEngine\Engine::class, $this->engine->initGateway());
    }

    public function test_i_can_process_scheduled_plans()
    {
        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plans = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ], 3);

        $this->engine->processScheduledPlans();

        // the plans will fail to process - but we are tested that they were processed regardless of result
        foreach ( $plans as $plan )
        {
            $this->assertFalse($this->engine->getResult($plan->id)[ 'status' ]);
            $this->assertNotEquals(0, $plan->transactions->count());
        }
    }

    public function test_i_can_process_supplied_plans()
    {
        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plans = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ], 3);

        $this->engine->processPlans($plans);

        // the plans will fail to process - but we are tested that they were processed regardless of result
        foreach ( $plans as $plan )
        {
            $this->assertFalse($this->engine->getResult($plan->id)[ 'status' ]);
            $this->assertNotEquals(0, $plan->transactions->count());
        }
    }

    public function test_i_get_an_exception_if_the_supplied_plans_are_not_collections_or_an_array()
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        $this->engine->processPlans(null);
    }

    public function test_i_can_generate_a_transaction_but_not_process_it()
    {
        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plan = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ])
                     ->first();

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->generateTransaction($plan);

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertFalse($plan->transactions->first()->is_failure);
        $this->assertFalse($plan->transactions->first()->is_success);
        $this->assertNull($plan->transactions->first()->gateway_response);
    }

    public function test_i_can_generate_a_transaction_and_process_it()
    {
        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plan = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ])
                     ->first();

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->handleTransaction($plan);

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertTrue($plan->transactions->first()->is_failure);
        $this->assertFalse($plan->transactions->first()->is_success);
        $this->assertJson($plan->transactions->first()->gateway_response);
    }

    public function test_i_can_calculate_the_charges_for_a_plan()
    {
        $plan = $this->createPlan([ 'frequency' => 'monthly' ])
                     ->first();

        $schedule = $plan->schedule;

        $schedule->last_ran_at = \Carbon\Carbon::now()
                                               ->subMonth();

        $schedule->save();

        $charges = $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                                ->addDay()->dayOfWeek ], 3);

        $items = collect($this->engine->calculateCharges($plan)
                                      ->getItems());

        $this->assertEquals(12, $items->count());

        foreach ( $charges as $charge )
            $this->assertEquals(4, $items->where('description', $charge->description)
                                         ->where('amount', $charge->amount)
                                         ->count());
    }

    public function test_i_can_generate_a_transaction_for_a_given_plan_without_calculating_its_charges()
    {
        $plan = $this->createPlan([ 'frequency' => 'monthly' ])
                     ->first();

        $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                     ->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->generateTransaction($plan, false);

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertEmpty($this->engine->getItems());

        $this->assertEquals(0, $plan->transactions->first()->items->count());
    }

    public function test_i_can_generate_a_transaction_for_a_given_plan_and_calculate_its_charges()
    {
        $plan = $this->createPlan([ 'frequency' => 'monthly' ])
                     ->first();

        $schedule = $plan->schedule;

        $schedule->last_ran_at = \Carbon\Carbon::now()
                                               ->subMonth();

        $schedule->save();

        $charges = $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                                ->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->generateTransaction($plan);

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $items = collect($this->engine->getItems());

        $this->assertEquals(12, $items->count());

        foreach ( $charges as $charge )
            $this->assertEquals(4, $items->where('description', $charge->description)
                                         ->where('amount', $charge->amount)
                                         ->count());

        $items = $plan->transactions->first()->items;

        $this->assertEquals(12, $items->count());

        foreach ( $charges as $charge )
            $this->assertEquals(4, $items->where('description', $charge->description)
                                         ->where('amount', $charge->amount)
                                         ->count());
    }

    public function test_i_can_generate_a_transaction_for_the_current_plan_without_calculating_charges()
    {
        $plan = $this->createPlan([ 'frequency' => 'monthly' ])
                     ->first();

        $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                     ->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->setPlan($plan)
                     ->generateTransaction(null, false);

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertEmpty($this->engine->getItems());

        $this->assertEquals(0, $plan->transactions->first()->items->count());
    }

    public function test_i_can_generate_a_transaction_for_the_current_plan_and_calculate_its_charges()
    {
        $plan = $this->createPlan([ 'frequency' => 'monthly' ])
                     ->first();

        $schedule = $plan->schedule;

        $schedule->last_ran_at = \Carbon\Carbon::now()
                                               ->subMonth();

        $schedule->save();

        $charges = $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                                ->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->engine->setPlan($plan)
                     ->generateTransaction();

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $items = collect($this->engine->getItems());

        $this->assertEquals(12, $items->count());

        foreach ( $charges as $charge )
            $this->assertEquals(4, $items->where('description', $charge->description)
                                         ->where('amount', $charge->amount)
                                         ->count());

        $items = $plan->transactions->first()->items;

        $this->assertEquals(12, $items->count());

        foreach ( $charges as $charge )
            $this->assertEquals(4, $items->where('description', $charge->description)
                                         ->where('amount', $charge->amount)
                                         ->count());
    }

    public function test_i_can_process_the_current_transaction()
    {
        $this->test_i_can_generate_a_transaction_for_a_given_plan_and_calculate_its_charges();

        $plan = Plan::first();

        $this->engine->processTransaction();

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertTrue($plan->transactions->first()->is_failure);
        $this->assertFalse($plan->transactions->first()->is_success);
        $this->assertJson($plan->transactions->first()->gateway_response);
    }

    public function test_i_can_process_a_given_transaction()
    {
        $this->test_i_can_generate_a_transaction_for_a_given_plan_and_calculate_its_charges();

        $plan = Plan::first();

        $this->engine->processTransaction(\Sinclair\PaymentEngine\Models\Transaction::first());

        $this->assertEquals(1, \Sinclair\PaymentEngine\Models\Transaction::count());

        $this->assertEquals(1, $plan->transactions->count());

        $this->assertTrue($plan->transactions->first()->is_failure);
        $this->assertFalse($plan->transactions->first()->is_success);
        $this->assertJson($plan->transactions->first()->gateway_response);
    }

    public function test_i_can_get_the_result_message_of_a_transaction_using_the_plan_id()
    {
        $this->test_i_can_generate_a_transaction_and_process_it();

        $result = $this->engine->getResult(Plan::first()->id);

        $this->assertNull($result[ 'message' ]);
    }

    public function test_i_can_get_the_result_message_of_a_transaction_using_the_transaction_id()
    {
        $this->test_i_can_generate_a_transaction_and_process_it();

        $result = $this->engine->getResult(\Sinclair\PaymentEngine\Models\Transaction::first()->id, 'transactions');

        $this->assertNull($result[ 'message' ]);
    }

    public function test_the_transaction_generated_event_is_fired_when_a_transaction_is_generated_successfully()
    {
        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionGenerated::class);

        $this->test_i_can_process_a_given_transaction();
    }

    public function test_the_transaction_failed_to_generate_event_is_fired_when_a_transaction_is_generated_unsuccessfully()
    {
        $this->setExpectedException(Illuminate\Database\QueryException::class);

        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionFailedToGenerate::class);

        $this->engine->generateTransaction(new Plan());
    }

    public function test_the_transaction_failed_to_process_event_is_fired_when_a_credit_card_object_fails_validation()
    {
        $this->setExpectedException(ErrorException::class);

        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionFailedToProcess::class);

        $this->engine->processTransaction(new \Sinclair\PaymentEngine\Models\Transaction());
    }

    public function test_the_transaction_was_redirected_event_is_fired_when_the_gateway_response_needs_to_be_redirected()
    {
        $this->app[ 'config' ]->offsetSet('gateway', 'WorldPay');

        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionWasRedirected::class);

        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plans = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ]);

        $this->engine->processPlans($plans);
    }

    public function test_the_transaction_processed_event_is_fired_when_the_gateway_response_is_successful()
    {
        $this->app[ 'config' ]->offsetSet('gateway', 'WorldPay');

        $response = Mockery::mock(Omnipay\WorldPay\Message\PurchaseResponse::class);

        $response->shouldReceive('isRedirect')
                 ->andReturn(false);

        $response->shouldReceive('isSuccessful')
                 ->andReturn(true);

        $response->shouldReceive('getMessage')
                 ->andReturnNull();

        $gateway = Mockery::mock(Omnipay\WorldPay\Gateway::class);

        $gateway->shouldReceive('send')
                ->andReturn($response);

        $gateway->shouldReceive('purchase')
                ->andReturn($gateway);

        $factory = Mockery::mock(\Sinclair\PaymentEngine\Gateways\Factory::class);

        $factory->shouldReceive('create')
                ->andReturn($gateway);

        $factory->shouldReceive('getOptions')
                ->andReturn([]);

        $engine = new \Sinclair\PaymentEngine\Engine(app('PlanRepository'), app('TransactionRepository'), $factory);

        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionProcessed::class);

        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $plans = $this->createPlan([
            'minute'        => $minute,
            'hour'          => $hour,
            'day_of_week'   => null,
            'day_of_month'  => $day,
            'month_of_year' => null,
            'year'          => null,
            'frequency'     => 'monthly',
            'starts_at'     => null,
            'expires_at'    => null
        ]);

        $engine->processPlans($plans);
    }

    public function test_the_transaction_failed_to_process_event_is_fired_when_the_gateway_response_is_unsuccessful()
    {
        $this->expectsEvents(\Sinclair\PaymentEngine\Events\TransactionFailedToProcess::class);

        $this->test_i_can_generate_a_transaction_and_process_it();
    }
}