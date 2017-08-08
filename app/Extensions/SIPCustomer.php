<?php

namespace App\Extensions;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\Port;

namespace App\Extensions;


class SIPCustomer {

    public function addNewCustomer($firstName, $lastName, $email, $vip = '')
    {
        $customer = new Customer;
        $customer->first_name = $firstName;
        $customer->last_name = $lastName;
        $customer->email = $email;
        $customer->vip = $vip;
        $customer->id_status = config('const.status.enabled');
        $customer->save();

        return $customer;
    }

    public function addCustomerContact($customerId, $contactType, $value)
    {

        $contact = new Contact;
        $contact->id_customers = $customerId;
        $contact->id_types = $contactType;
        $contact->value = $value;
        $contact->save();

        return $contact;
    }

    public function addCustomerAddressByBuilding($customerId, $buildingId, $unit)
    {

        $locationAddress = Address::where('id_buildings', $buildingId)
            ->where('id_customers', null)
            ->first();

        $address = new Address;
        $address->address = $locationAddress->address;
        $address->code = $locationAddress->code;
        $address->unit = $unit;
        $address->city = $locationAddress->city;
        $address->zip = $locationAddress->zip;
        $address->state = $locationAddress->state;
        $address->id_customers = $customerId;
        $address->id_buildings = $locationAddress->id_buildings;
        $address->save();

        return $address;
    }

    public function addCustomerProduct($customerId, $productId){

        $product = Product::find($productId);
        if($product == null){
            Log::debug('addCustomerProduct(): product not found. id='. $productId);
            return false;
        }

        $expiration = $this->getTimeToAdd($product->frequency);
        $expires = null;

        if ($expiration != null){
            $expires = date("Y-m-d H:i:s", strtotime($expiration));
        }

        $customerProduct = new CustomerProduct;
        $customerProduct->id_customers = $customerId;
        $customerProduct->id_products = $productId;
        $customerProduct->id_status = config('const.status.active');
        $customerProduct->signed_up = date("Y-m-d H:i:s");
        $customerProduct->expires = $expires;
        $customerProduct->id_users = Auth::user()->id;
        $customerProduct->save();

        return $customerProduct;
    }

    /**
     * @param $type
     * Array index to get proper frequency to add.
     * @return Frequency of specific product.
     */
    public function getTimeToAdd($type)
    {
        $timeToAdd = array('annual'        => 'first day of next year',
                           'monthly'       => 'first day of next month',
                           'onetime'       => 'first day of next month',
                           'Included'      => 'first day of next month',
                           'included'      => 'first day of next month',
                           'complimentary' => null
        );

        return $timeToAdd[$type];
    }
}