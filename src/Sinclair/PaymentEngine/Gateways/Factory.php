<?php

namespace Sinclair\PaymentEngine\Gateways;

use Omnipay\Common\GatewayInterface;
use Omnipay\Omnipay;

/**
 * Class Factory
 * @package Sinclair\PaymentEngine\Gateways
 */
class Factory implements \Sinclair\PaymentEngine\Contracts\Factory
{
    private $supportedGateways = [
        "WorldPay",
        "Stripe",
        "SagePay_Direct",
        "SagePay_Server",
        "PayPal_Express",
        "PayPal_Pro",
        "PayPal_Rest",
        "TwoCheckout"
    ];

    /**
     * @var GatewayInterface
     */
    protected $gateway;

    /**
     * @var string
     */
    protected $gatewayName;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    protected $options = [];

    /**
     * @return GatewayInterface
     */
    public function create()
    {
        return $this->setGateway()
                    ->applySettings()
                    ->getGateway();
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     *
     * @return $this
     */
    public function setOptions( array $options )
    {
        $this->options = $options;

        return $this;
    }

    /**
     *
     */
    protected function getGateWayName()
    {
        $this->gatewayName = config('payment-engine.gateway');

        if ( is_callable($this->gatewayName) )
            $this->gatewayName = call_user_func($this->gatewayName);

        if ( $this->gatewaySupported($this->gatewayName) )
            return $this->gatewayName;

        if ( is_string($this->gatewayName) )
            throw new \Exception($this->gatewayName . ' is not a supported gateway for the payment engine');

        throw new \Exception('Payment engine could not find your gateway');
    }

    /**
     * @return array|mixed
     * @throws \Exception
     */
    protected function getSettings()
    {
        $this->settings = config('payment-engine.settings');

        if ( is_callable($this->settings) )
            $this->settings = call_user_func($this->settings);

        return $this->settings;
    }

    /**
     */
    protected function applySettings()
    {
        foreach ( $this->getSettings() as $setting => $value )
            if ( method_exists($this->gateway, 'set' . studly_case($setting)) )
            {
                $this->gateway->{'set' . studly_case($setting)}($value);
            }
            else
            {
                $this->options[ $setting ] = $value;
            }

        return $this;
    }

    /**
     * @param $gateway
     *
     * @return bool
     */
    protected function gatewaySupported( $gateway )
    {
        return in_array($gateway, $this->supportedGateways);
    }

    /**
     * @return mixed
     */
    protected function getDefaultSettings()
    {
        $gateway = is_null($this->gateway) ? Omnipay::create($this->gatewayName) : $this->gateway;

        return $gateway->getDefaultParameters();
    }

    /**
     *
     */
    protected function setGateway()
    {
        $this->gateway = Omnipay::create($this->getGateWayName());

        return $this;
    }

    /**
     * @return mixed
     */
    protected function getGateway()
    {
        return $this->gateway;
    }
}