<?php

namespace Sinclair\PaymentEngine\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\Transaction;

/**
 * Class TransactionProcessed
 * @package Sinclair\PaymentEngine\Events
 */
class TransactionWasRedirected implements ShouldBroadcast
{
    use SerializesModels;

    /**
     * @var Transaction
     */
    public $transaction;

    /**
     * @var GatewayInterface
     */
    public $gateway;

    /**
     * @var ResponseInterface
     */
    public $response;

    /**
     * TransactionGenerated constructor.
     *
     * @param Transaction $transaction
     * @param GatewayInterface $gateway
     * @param ResponseInterface $response
     */
    public function __construct( Transaction $transaction, GatewayInterface $gateway, ResponseInterface $response )
    {
        $this->transaction = $transaction;
        $this->gateway = $gateway;
        $this->response = $response;
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
