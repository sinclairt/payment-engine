<?php

namespace Sinclair\PaymentEngine;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidCreditCardException;
use Omnipay\Common\GatewayInterface;
use Omnipay\Common\Message\ResponseInterface;
use Sinclair\PaymentEngine\Contracts\Engine as EngineInterface;
use Sinclair\PaymentEngine\Contracts\Plan;
use Sinclair\PaymentEngine\Contracts\PlanRepository;
use Sinclair\PaymentEngine\Contracts\Transaction;
use Sinclair\PaymentEngine\Contracts\TransactionRepository;
use Sinclair\PaymentEngine\Events\TransactionFailedToGenerate;
use Sinclair\PaymentEngine\Events\TransactionFailedToProcess;
use Sinclair\PaymentEngine\Events\TransactionGenerated;
use Sinclair\PaymentEngine\Events\TransactionProcessed;
use Sinclair\PaymentEngine\Events\TransactionWasRedirected;
use Sinclair\PaymentEngine\Gateways\Factory;

/**
 * Class Engine
 * @package Sinclair\PaymentEngine
 */
class Engine implements EngineInterface
{
    /**
     * @var Plan|Model
     */
    public $plan;

    /**
     * @var array
     */
    public $items = [];

    /**
     * @var Transaction|Model
     */
    public $transaction;

    /**
     * @var GatewayInterface
     */
    private $gateway = null;

    /**
     * @var PlanRepository
     */
    private $planRepository;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var array
     */
    public $results = [];

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @return array
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @return Model|Plan
     */
    public function getPlan()
    {
        return $this->plan;
    }

    /**
     * @param Model|Plan $plan
     *
     * @return Engine
     */
    public function setPlan( $plan )
    {
        $this->plan = $plan;

        return $this;
    }

    /**
     * Engine constructor.
     *
     * @param PlanRepository $planRepository
     * @param TransactionRepository $transactionRepository
     * @param Factory $factory
     */
    public function __construct( PlanRepository $planRepository, TransactionRepository $transactionRepository, Factory $factory )
    {
        $this->planRepository = $planRepository;

        $this->transactionRepository = $transactionRepository;

        $this->factory = $factory;
    }

    public function setGateway()
    {
        if ( is_null($this->gateway) )
            $this->gateway = $this->factory->create();

        return $this;
    }

    /**
     * @return Engine
     */
    public function processScheduledPlans()
    {
        $this->planRepository->getAllScheduled()
                             ->map([ $this, 'handleTransaction' ]);

        return $this;
    }

    /**
     * @param array|\Illuminate\Support\Collection|Collection $plans
     *
     * @return Engine
     */
    public function processPlans( $plans )
    {
        if ( !is_array($plans) && !$plans instanceof \Illuminate\Support\Collection )
            throw new \InvalidArgumentException(self::class . ' ');

        if ( is_array($plans) )
            $plans = collect($plans);

        $plans->map([ $this, 'handleTransaction' ]);

        return $this;
    }

    /**
     * @param $plan
     * @param bool $process
     *
     * @return Engine
     */
    public function handleTransaction( Plan $plan, $process = true )
    {
        return $this->generateTransaction()
                    ->shouldProcess($process);
    }

    /**
     * Get all charges for a plan and convert to a transaction item
     *
     * @param Plan $plan
     *
     * @return Engine
     */
    public function calculateCharges( Plan $plan = null )
    {
        if ( !is_null($plan) )
            $this->plan = $plan;

        $this->items = [];

        foreach ( $plan->charges as $charge )
            foreach ( $charge->runDatesBetween($plan->schedule->last_ran_at) as $date )
                $this->items[] = [
                    'amount'      => $charge->amount,
                    'description' => $charge->description,
                    'charged_at'  => $date
                ];

        return $this;
    }

    /**
     * Create a transaction from plan
     *
     * @param Plan $plan
     *
     * @param bool $calculate
     *
     * @return Engine
     * @throws \Exception
     */
    public function generateTransaction( Plan $plan = null, $calculate = true )
    {
        if ( !is_null($plan) )
            $this->plan = $plan;

        if ( $calculate )
            $this->calculateCharges();

        try
        {
            $this->transaction = $this->transactionRepository->add([
                'account_number'  => $this->plan->account_number,
                'sort_code'       => $this->plan->sort_code,
                'card_number'     => $this->plan->card_number,
                'card_starts_at'  => $this->plan->card_starts_at,
                'card_expires_at' => $this->plan->card_expires_at,
                'items'           => $this->items
            ]);

            $this->results[ 'plans' ][ $this->plan->id ] = $this->results[ 'transactions' ][ $this->transaction->id ] = [ 'status' => true, 'message' => '' ];

            event(new TransactionGenerated($this->plan, $this->transaction));

            return $this;
        }
        catch ( \Exception $e )
        {
            $this->results[ 'plans' ][ $this->plan->id ] = $this->results[ 'transactions' ][ $this->transaction->id ] = [ 'status' => false, 'message' => $e->getMessage() ];

            event(new TransactionFailedToGenerate($this->plan, $e->getMessage()));

            throw $e;
        }
    }

    /**
     * @param Transaction|Model $transaction
     *
     * @return bool
     */
    public function processTransaction( Transaction $transaction = null )
    {
        if ( !is_null($transaction) )
            $this->transaction = $transaction;

        $card = $this->createCreditCard();

        if ( $this->cardIsValid($card) )
            return false;

        $response = $this->sendRequest($card);

        $result = $this->saveResponse($response)
                       ->responseIsSuccess($response);

        $this->results[ 'transactions' ][ $this->transaction->id ] = $this->results[ 'plans' ][ $this->plan->id ] = [ 'status' => $result, 'message' => $response->getMessage() ];
    }

    /**
     * @param $id
     * @param string $key
     *
     * @return mixed
     */
    public function getResult( $id, $key = 'plans' )
    {
        return $this->results[ $key ][ $id ];
    }

    /**
     * @return CreditCard
     */
    protected function createCreditCard()
    {
        $this->transaction->load([ 'plan', 'plan.plannable' ]);

        $data = [
            'firstName'        => $this->transaction->plan->plannable->getFirstName(),
            'lastName'         => $this->transaction->plan->plannable->getLastName(),
            'number'           => $this->transaction->plan->card_number,
            'expiryMonth'      => $this->transaction->plan->card_expires_at->month,
            'expiryYear'       => $this->transaction->plan->card_expires_at->year,
            'cvv'              => $this->transaction->plan->card_cvv,
            'startMonth'       => $this->transaction->plan->card_starts_at->month,
            'startYear'        => $this->transaction->plan->card_starts_at->year,
            'issueNumber'      => $this->transaction->plan->card_issue_number,
            'type'             => $this->transaction->plan->card_type,
            'billingAddress1'  => $this->transaction->plan->plannable->getBillingAddress1(),
            'billingAddress2'  => $this->transaction->plan->plannable->getBillingAddress2(),
            'billingCity'      => $this->transaction->plan->plannable->getBillingCity(),
            'billingPostcode'  => $this->transaction->plan->plannable->getBillingPostcode(),
            'billingState'     => $this->transaction->plan->plannable->getBillingState(),
            'billingCountry'   => $this->transaction->plan->plannable->getBillingCountry(),
            'billingPhone'     => $this->transaction->plan->plannable->getBillingPhone(),
            'shippingAddress1' => $this->transaction->plan->plannable->getShippingAddress1(),
            'shippingAddress2' => $this->transaction->plan->plannable->getShippingAddress2(),
            'shippingCity'     => $this->transaction->plan->plannable->getShippingCity(),
            'shippingPostcode' => $this->transaction->plan->plannable->getShippingPostcode(),
            'shippingState'    => $this->transaction->plan->plannable->getShippingState(),
            'shippingCountry'  => $this->transaction->plan->plannable->getShippingCountry(),
            'shippingPhone'    => $this->transaction->plan->plannable->getShippingPhone(),
            'company'          => $this->transaction->plan->plannable->getCompany(),
            'email'            => $this->transaction->plan->plannable->getEmail(),
        ];

        return app(CreditCard::class, $data);
    }

    /**
     * @param $card
     *
     * @return bool
     */
    protected function cardIsValid( CreditCard $card )
    {
        try
        {
            $card->validate();

            return true;
        }
        catch ( InvalidCreditCardException $e )
        {
            event(new TransactionFailedToProcess($this->transaction, $e->getMessage()));

            return false;
        }
    }

    /**
     * @param $card
     *
     * @return array
     */
    protected function purchaseData( $card )
    {
        return [
            'card'        => $card,
            'amount'      => $this->transaction->total(),
            'currency'    => $this->transaction->currency,
            'description' => $this->transaction->reference
        ];
    }

    /**
     * @param $card
     *
     * @return ResponseInterface
     */
    protected function sendRequest( $card )
    {
        $purchaseData = $this->purchaseData($card);

        $this->setGateway();

        if ( method_exists($this->gateway, 'authorize') )
            return $this->gateway->authorize($purchaseData)
                                 ->send();

        return $this->gateway->purchase($purchaseData)
                             ->send();
    }

    /**
     * @param ResponseInterface $response
     *
     * @return bool
     */
    protected function responseIsSuccess( ResponseInterface $response )
    {
        $this->setGateway();

        // this is intended to be a lights out operation so we will fire an event here to let the developer handle this how they want
        if ( $response->isRedirect() )
            event(new TransactionWasRedirected($this->transaction, $this->gateway, $response));

        if ( $success = $response->isSuccessful() )
            event(new TransactionProcessed($this->transaction));
        else
            event(new TransactionFailedToProcess($this->transaction, $response->getMessage()));

        return $success;
    }

    /**
     * @param ResponseInterface $response
     *
     * @return Engine
     */
    protected function saveResponse( $response )
    {
        $is_success = $response->isSuccessful();

        $is_failure = !$is_success;

        $response = json_encode($response);

        $this->transactionRepository->update(compact('response', 'is_success', 'is_failure'), $this->transaction);

        return $this;
    }

    /**
     * @param $process
     *
     * @return Engine
     */
    protected function shouldProcess( $process )
    {
        if ( $process )
            $this->processTransaction();

        return $this;
    }
}