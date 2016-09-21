<?php

require_once 'DbTestCase.php';
require_once 'Models/Dummy.php';

use Sinclair\PaymentEngine\Models\Transaction;

/**
 * Class RepositoryTest
 */
class RepositoryTest extends DbTestCase
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

    /**
     *
     */
    public function test_a_schedule_is_created_when_i_save_a_charge()
    {
        $charge = $this->createCharge()
                       ->first();

        $this->assertNotNull($charge->fresh()->schedule);
    }

    /**
     *
     */
    public function test_a_schedule_is_created_when_i_save_a_plan()
    {
        $plan = $this->createPlan()
                     ->first();

        $this->assertNotNull($plan->fresh()->schedule);
    }

    /**
     *
     */
    public function test_i_can_get_all_scheduled_plans()
    {
        $minute = date('i');
        $hour = date('G');
        $day = date('j');

        $this->createPlan([
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

        $this->assertEquals(3, app('PlanRepository')
            ->getAllScheduled()
            ->count());
    }

    /**
     *
     */
    public function test_items_are_saved_when_i_save_the_transaction_with_items()
    {
        $plan = $this->createPlan()
                     ->first();

        $this->createCharge([ 'plan_id' => $plan->id, 'day_of_week' => \Carbon\Carbon::now()
                                                                                     ->addDay()->dayOfWeek ], 3);

        $items = [];

        $plan->charges->map(function ( $item ) use ( &$items )
        {
            $items[] = $item->toArray();
        });

        $transactionData = [
            'account_number'  => $plan->account_number,
            'sort_code'       => $plan->sort_code,
            'card_number'     => $plan->card_number,
            'card_starts_at'  => $plan->card_starts_at,
            'card_expires_at' => $plan->card_expires_at,
            'items'           => $items,
            'plan_id'         => $plan->id,
            'is_success'      => 0,
            'is_failure'      => 0
        ];

        app('TransactionRepository')->add($transactionData);

        $this->assertEquals(1, Transaction::where('plan_id', $plan->id)
                                          ->count());

        $transaction = Transaction::where('plan_id', $plan->id)
                                  ->first();

        $this->assertEquals(3, $transaction->items->count());
    }

    /**
     *
     */
    public function test_i_can_get_failed_transactions()
    {
        foreach ( $this->createPlan([], 20) as $plan )
            $plan->generateTransaction();

        foreach ( Transaction::take(5)
                             ->get() as $transaction )
            $transaction->update([ 'is_success' => 1, 'is_failure' => 0 ]);

        $expected = Transaction::where('is_failure', 1)
                               ->where('is_success', 0)
                               ->get();

        $this->assertEquals(15, $expected->count());
        $this->assertEquals(15, app('TransactionRepository')
            ->getFailed()
            ->count());

        foreach ( app('TransactionRepository')->getFailed() as $key => $transaction )
            $this->assertArraySubset($transaction->toArray(), $expected->get($key)
                                                                       ->toArray());
    }
}