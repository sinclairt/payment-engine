<?php

namespace Sinclair\PaymentEngine\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\Transaction;

/**
 * Class TransactionProcessed
 * @package Sinclair\PaymentEngine\Events
 */
class TransactionProcessed implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * TransactionGenerated constructor.
     *
     * @param Transaction $transaction
     */
    public function __construct( Transaction $transaction )
    {
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
