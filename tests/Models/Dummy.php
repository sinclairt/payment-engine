<?php

class Dummy extends \Illuminate\Database\Eloquent\Model implements \Sinclair\PaymentEngine\Contracts\IsPlannable
{
    use \Illuminate\Database\Eloquent\SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'type',
        'billing_address_1',
        'billing_address_2',
        'billing_city',
        'billing_postcode',
        'billing_state',
        'billing_country',
        'billing_phone',
        'shipping_address1',
        'shipping_address2',
        'shipping_city',
        'shipping_postcode',
        'shipping_state',
        'shipping_country',
        'shipping_phone',
        'company',
        'email',
    ];

    protected $dates = [ 'created_at', 'updated_at', 'deleted_at' ];

    public function getFirstName()
    {
        return $this->first_name;
    }

    public function getLastName()
    {
        return $this->last_name;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getBillingAddress1()
    {
        return $this->billing_address_1;
    }

    public function getBillingAddress2()
    {
        return $this->billing_address_2;
    }

    public function getBillingCity()
    {
        return $this->billing_city;
    }

    public function getBillingPostcode()
    {
        return $this->billing_postcode;
    }

    public function getBillingState()
    {
        return $this->billing_state;
    }

    public function getBillingCountry()
    {
        return $this->billing_cuntry;
    }

    public function getBillingPhone()
    {
        return $this->billing_phone;
    }

    public function getShippingAddress1()
    {
        return $this->shipping_address_1;
    }

    public function getShippingAddress2()
    {
        return $this->shipping_address_2;
    }

    public function getShippingCity()
    {
        return $this->shipping_city;
    }

    public function getShippingPostcode()
    {
        return $this->shipping_postcode;
    }

    public function getShippingState()
    {
        return $this->shipping_state;
    }

    public function getShippingCountry()
    {
        return $this->shipping_country;
    }

    public function getShippingPhone()
    {
        return $this->shipping_phone;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function getEmail()
    {
        return $this->email;
    }
}