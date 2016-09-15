<?php

namespace Sinclair\PaymentEngine\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Sinclair\PaymentEngine\Contracts\Plan;

/**
 * Class TransactionFailedToGenerate
 * @package Sinclair\PaymentEngine\Events
 */
class TransactionFailedToGenerate implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var Plan
     */
    public $plan;

    /**
     * @var string
     */
    public $message;

    /**
     * TransactionFailedToGenerate constructor.
     *
     * @param Plan $plan
     * @param string $message
     */
    public function __construct( Plan $plan, $message = '' )
    {
        $this->plan = $plan;
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
