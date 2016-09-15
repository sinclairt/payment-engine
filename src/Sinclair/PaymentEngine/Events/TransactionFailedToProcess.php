<?php

namespace Sinclair\PaymentEngine\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\Transaction;

/**
 * Class TransactionFailedToProcess
 * @package Sinclair\PaymentEngine\Events
 */
class TransactionFailedToProcess implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * @var string
     */
    public $message;

    /**
     * TransactionFailedToProcess constructor.
     *
     * @param Transaction $transaction
     * @param string $message
     */
    public function __construct( Transaction $transaction, string $message = '' )
    {
        $this->transaction = $transaction;
        $this->message = $message;
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
