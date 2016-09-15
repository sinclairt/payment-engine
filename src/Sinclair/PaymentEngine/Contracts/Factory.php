<?php

namespace Sinclair\PaymentEngine\Contracts;

use Omnipay\Common\GatewayInterface;

/**
 * Class Factory
 * @package Sinclair\PaymentEngine\Gateways
 */
interface Factory
{
    /**
     * @return GatewayInterface
     */
    public function create();
}