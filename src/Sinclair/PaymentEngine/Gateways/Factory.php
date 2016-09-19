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
     * @return GatewayInterface
     */
    public function create()
    {
        return $this->setGateway()
                    ->applySettings()
                    ->getGateway();
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

        if ( $this->settingsAreValid() )
            return $this->settings;

        throw new \Exception('You have supplied settings that cannot be used with this gateway: ' . $this->gatewayName);
    }

    /**
     */
    protected function applySettings()
    {
        foreach ( $this->getSettings() as $setting => $value )
            if ( method_exists($this->gateway, 'set' . studly_case($setting)) )
                $this->gateway->{'set' . studly_case($setting)}($value);

        return $this;
    }

    /**
     * @param $gateway
     *
     * @return bool
     */
    protected function gatewaySupported( $gateway )
    {
        return in_array($gateway, Omnipay::getSupportedGateways());
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

    /**
     * @return bool
     */
    protected function settingsAreValid():bool
    {
        return array_diff($this->settings, $this->getDefaultSettings()) == 0;
    }
}