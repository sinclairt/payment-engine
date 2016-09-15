<?php

namespace Sinclair\PaymentEngine\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\Transaction;

/**
 * Class TransactionGenerated
 * @package Sinclair\PaymentEngine\Events
 */
class TransactionGenerated implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var Plan
     */
    public $plan;

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * TransactionGenerated constructor.
     *
     * @param Plan $plan
     * @param Transaction $transaction
     */
    public function __construct( Plan $plan, Transaction $transaction )
    {
        $this->plan = $plan;
        $this->transaction = $transaction;
    }

    /**
     * Get the channels the event should be broadcast on.
     *
     * @return array
     */
    public function broadcastOn()
    {
        return [];
    }
}
