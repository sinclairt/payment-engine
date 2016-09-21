<?php

use Sinclair\PaymentEngine\Models\Transaction;

require_once 'DbTestCase.php';
require_once 'Models/Dummy.php';

/**
 * Class CommandTest
 */
class ModelTest extends DbTestCase
{

    /**
     * @var Faker\Generator
     */
    protected $faker;

    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/migrations');

        $this->migrate(__DIR__ . '/../vendor/laravel/laravel/database/migrations');

        $this->faker = Faker\Factory::create();

        $this->artisan('vendor:publish');
    }

    public function test_i_can_generate_a_transaction_from_a_plan()
    {
        $plan = $this->createPlan()
                     ->first();

        $this->assertEquals(0, Transaction::count());

        $plan->generateTransaction();

        $this->assertEquals(1, Transaction::count());

        $this->assertEquals(1, $plan->fresh()->transactions->count());
    }

    public function test_i_can_get_the_total_of_a_transaction()
    {
        $plan = $this->createPlan()
                     ->first();

        $schedule = $plan->schedule;

        $schedule->last_ran_at = \Carbon\Carbon::now()
                                               ->subMonth()
                                               ->toDateTimeString();

        $schedule->save();

        $this->createCharge([ 'amount' => 10, 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                                     ->addDay()->dayOfWeek ], 3);

        $total = 3 * 4 * 10;

        $transaction = $plan->fresh()
                            ->generateTransaction()
                            ->getTransaction()
                            ->fresh();

        $this->assertEquals($total, $transaction->total());
    }

    public function test_i_can_process_a_transaction()
    {
        $plan = $this->createPlan()
                     ->first();

        $this->createCharge([ 'amount' => 10, 'day_of_week' => \Carbon\Carbon::now()
                                                                             ->addDay()->dayOfWeek ], 3);

        $transaction = $plan->generateTransaction(true, false)
                            ->getTransaction();

        $this->assertFalse($transaction->is_success);
        $this->assertFalse($transaction->is_failure);
        $this->assertNull($transaction->gateway_response);

        $transaction->process();

        $this->assertFalse($transaction->fresh()->is_success);
        $this->assertTrue($transaction->fresh()->is_failure);
        $this->assertJson($transaction->fresh()->gateway_response);
    }
}