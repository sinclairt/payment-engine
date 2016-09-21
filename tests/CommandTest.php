<?php

use Sinclair\PaymentEngine\Models\Transaction;
use Sinclair\PaymentEngine\Models\Plan;

require_once 'DbTestCase.php';
require_once 'Models/Dummy.php';

/**
 * Class CommandTest
 */
class CommandTest extends DbTestCase
{
    /**
     *
     */
    public function setUp()
    {
        parent::setUp();

        $this->migrate(__DIR__ . '/migrations');

        $this->migrate(__DIR__ . '/../vendor/laravel/laravel/database/migrations');

        $this->artisan('vendor:publish');
    }

    public function test_i_can_generate_a_transaction_without_calculating_charges()
    {
        $plan = $this->createPlan()
                     ->first();

        $this->assertEquals(0, Transaction::count());

        $this->artisan('transaction:generate', ['plan' => $plan->id, '--calculate' => false, '--process' => false]);

        $this->assertEquals(1, Transaction::count());

        $this->assertEquals(0, Transaction::all()->first()->items()->count());
    }

    /**
     *
     */
    public function test_i_can_generate_a_transaction_from_a_plan_without_processing_it()
    {
        $plan = $this->createPlan()
                     ->first();

        $schedule = $plan->schedule;

        $lastRanAt = \Carbon\Carbon::now()
                                   ->subMonth();

        $last_ran_at_ts = clone $lastRanAt;

        $schedule->last_ran_at = $last_ran_at_ts->toDateTimeString();

        $schedule->save();

        $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, Transaction::all()
                                          ->count());

        $this->artisan('transaction:generate', [ 'plan' => $plan->id, '--process' => false ]);

        $this->assertEquals(1, Transaction::all()
                                          ->count());

        $transaction = Transaction::all()
                                  ->first();

        $this->assertNull($transaction->gateway_response);

        $this->assertFalse($transaction->is_success);

        $this->assertFalse($transaction->is_failure);

        // 3 weekly charges across a month 3 * 4 = 12
        $this->assertEquals(12, $transaction->items->count());
    }

    /**
     *
     */
    public function test_i_can_generate_a_transaction_from_a_plan_and_process_it()
    {
        $plan = $this->createPlan()
                     ->first();

        $schedule = $plan->schedule;

        $lastRanAt = \Carbon\Carbon::now()
                                   ->subMonth();

        $schedule->last_ran_at = $lastRanAt->toDateTimeString();

        $schedule->save();

        $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()->addDay()->dayOfWeek ], 3);

        $this->assertEquals(0, Transaction::all()
                                          ->count());

        $this->artisan('transaction:generate', [ 'plan' => $plan->id, '--process' => true ]);

        $this->assertEquals(1, Transaction::all()
                                          ->count());

        $transaction = Transaction::all()
                                  ->first();

        $this->assertJson($transaction->gateway_response);

        $this->assertFalse($transaction->is_success);

        $this->assertTrue($transaction->is_failure);

        // 3 weekly charges across a month 3 * 4 = 12
        $this->assertEquals(12, $transaction->items->count());
    }

    /**
     *
     */
    public function test_i_can_process_a_single_transaction()
    {
        $this->test_i_can_generate_a_transaction_from_a_plan_without_processing_it();

        $transaction = Transaction::all()
                                  ->first();

        $this->artisan('transaction:process', [ 'transaction' => $transaction->id ]);

        $transaction = $transaction->fresh();

        $this->assertJson($transaction->gateway_response);

        $this->assertFalse($transaction->is_success);

        $this->assertTrue($transaction->is_failure);
    }

    /**
     *
     */
    public function test_i_can_process_all_failed_transactions()
    {
        $this->createPlan([], 3);

        foreach ( Plan::all() as $plan )
            $plan->generateTransaction();

        $transactions = Transaction::all();

        // we need to force the timestamps back a minute to check for change later
        foreach ( $transactions as $transaction )
        {
            $transaction->created_at = \Carbon\Carbon::now()->subMinute();
            $transaction->updated_at = \Carbon\Carbon::now()->subMinute();
            $transaction->save();
        }

        $this->artisan('transaction:process');

        foreach ( $transactions as $transaction )
            $this->assertNotEquals($transaction->created_at, $transaction->fresh()->updated_at);

    }
}