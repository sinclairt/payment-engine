<?php

/**
 * Available gateways:
 * WorldPay
 * Stripe
 * SagePay_Direct
 * SagePay_Server
 * PayPal_Express
 * PayPal_Pro
 * PayPal_Rest
 * TwoCheckout
 */

return [
    'gateway'  => 'WorldPay',
    'settings' => [
        'returnUrl' => 'http://example.com'
    ],
];