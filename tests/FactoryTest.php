<?php

require_once 'DbTestCase.php';

class FactoryTest extends DbTestCase
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

    public function test_i_can_create_a_WorldPay_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'WorldPay');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_Stripe_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'Stripe');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_SagePay_Direct_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'SagePay_Direct');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_SagePay_Server_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'SagePay_Server');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_PayPal_Express_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'PayPal_Express');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_PayPal_Pro_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'PayPal_Pro');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_PayPal_Rest_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'PayPal_Rest');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

    public function test_i_can_create_a_TwoCheckout_gateway()
    {
        $this->app[ 'config' ]->offsetSet('payment-engine.gateway', 'TwoCheckout');

        $gateway = $this->app->make('PaymentEngineFactory')
                             ->create();

        $this->assertTrue(in_array(Omnipay\Common\GatewayInterface::class, class_implements($gateway)));
    }

}