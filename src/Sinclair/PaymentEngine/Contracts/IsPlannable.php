<?php

namespace Sinclair\PaymentEngine\Contracts;

interface IsPlannable
{
    public function getFirstName();

    public function getLastName();

    public function getType();

    public function getBillingAddress1();

    public function getBillingAddress2();

    public function getBillingCity();

    public function getBillingPostcode();

    public function getBillingState();

    public function getBillingCountry();

    public function getBillingPhone();

    public function getShippingAddress1();

    public function getShippingAddress2();

    public function getShippingCity();

    public function getShippingPostcode();

    public function getShippingState();

    public function getShippingCountry();

    public function getShippingPhone();

    public function getCompany();

    public function getEmail();
}