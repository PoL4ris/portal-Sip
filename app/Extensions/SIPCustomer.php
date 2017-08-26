<?php

namespace App\Extensions;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\Port;
use Validator;

class SIPCustomer {

    protected $validationRules = [
        'Customer' => ['first_name' => 'required|alpha_dash|max:255',
                       'last_name'  => 'required|alpha_dash|max:255',
                       'email'      => 'required|e-mail|max:255'],
        'Contact'  => ['id_customers' => 'required|numeric|max:255',
                       'id_types'     => 'required|numeric|max:255',
                       'value'        => 'required|max:255'],

        'Address' => ['address'      => 'required|max:255',
                      'code'         => 'required|max:255',
                      'unit'         => 'required|max:255',
                      'city'         => 'required|alpha|max:255',
                      'zip'          => 'required|numeric|size:5',
                      'state'        => 'required|size:2|alpha|max:2',
                      'id_customers' => 'required|numeric|max:255',
                      'id_buildings' => 'required|numeric|max:255'],

        'CustomerProduct' => ['id_customers' => 'required|max:255',
                              'id_products'  => 'required|max:255',
                              'id_status'    => 'required|max:255']


    ];

//        'phone_number'      => 'required|alpha_dash|max:255',
//        'street_address'    => 'required|max:255',
//        'unit'              => 'required|max:255',
//        'city'              => 'required|alpha|max:255',
//        'state'             => 'required|size:2|alpha|max:2',
//        //            'zip' => 'required|numeric|size:5',
//        'service_plan'      => ['required', 'alpha_dash', 'max:255', 'regex:/^[0-9]+-([a-z|A-Z])+$/'],
//        'wireless_router'   => 'required|max:255',
//        'cc_type'           => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|alpha|size:2',
//        'cc_number'         => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
//        'cc_exp_month'      => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
//        'cc_exp_year'       => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
//        'cc_sec_code'       => 'present|required_unless:total_charges,0.00|required_unless:recurring_charges,0.00|numeric',
//        't_and_c_check_box' => 'required',


    protected $validationMessages = [
        'unit.required'                => 'A unit number is required',
        't_and_c_check_box.required'   => 'You must read and agree with the terms and conditions',
        'wireless_router.required'     => 'Please select a router option',
        'service_plan.required'        => 'Please select an internet plan',
        'service_plan.regex'           => 'Please select the Monthly or Annual option',
        'required'                     => ':attribute is required',
        'alpha_dash'                   => 'Please use letters and numbers only',
        'alpha'                        => 'Please use letters only',
        'size'                         => 'The :attribute must be exactly :size.',
        'cc_type.required_unless'      => 'Please select your card type',
        'cc_number.required_unless'    => 'Please enter your card number',
        'cc_exp_month.required_unless' => 'Please select an expiration month',
        'cc_exp_year.required_unless'  => 'Please select an expiration year',
        'cc_sec_code.required_unless'  => 'Please enter your card\'s security code',
    ];

    protected $validationAttributes = ['first_name'      => 'first name',
                                       'last_name'       => 'last name',
                                       'email'           => 'email',
                                       'phone_number'    => 'phone number',
                                       'street_address'  => 'address',
                                       'unit'            => 'unit number',
                                       'city'            => 'city',
                                       'state'           => 'state',
                                       'zip'             => 'zip code',
                                       'service_plan'    => 'plan',
                                       'wireless_router' => 'wireless router',
                                       'cc_type'         => 'card type',
                                       'cc_number'       => 'card number',
                                       'cc_exp_month'    => 'expiration month',
                                       'cc_exp_year'     => 'expiration year',
                                       'cc_sec_code'     => 'security code'];

    public function addNewCustomer($firstName, $lastName, $email, $vip = '')
    {
        $validator = Validator::make(['first_name' => $firstName,
                                      'last_name'  => $lastName,
                                      'email'      => $email], $this->customerValidationRules, $this->validationMessages);

        $validator->setAttributeNames($this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();
            $errors->add('error', 1);

            return $errors;
        }

        return 'ok';

//        $customer = new Customer;
//        $customer->first_name = $firstName;
//        $customer->last_name = $lastName;
//        $customer->email = $email;
//        $customer->vip = $vip;
//        $customer->id_status = config('const.status.enabled');
//        $customer->save();
//
//        return $customer;
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

    public function addCustomerProduct($customerId, $productId)
    {

        $product = Product::find($productId);
        if ($product == null)
        {
            Log::debug('addCustomerProduct(): product not found. id=' . $productId);

            return false;
        }

        $expiration = $this->getTimeToAdd($product->frequency);
        $expires = null;

        if ($expiration != null)
        {
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

    public function getNextProductExpirationTimestamp(Product $product)
    {
//        $product->frequency
        $timeToAdd = array('annual'        => 'first day of next year',
                           'monthly'       => 'first day of next month',
                           'onetime'       => 'first day of next month',
                           'Included'      => 'first day of next month',
                           'included'      => 'first day of next month',
                           'complimentary' => null
        );

        return $timeToAdd[$type];
    }

    public function getCustomerByEmail($emailAddress)
    {

        return Customer::where('email', $emailAddress)->orderBy('id_status', 'ASC')->first();
    }

    public function getActiveCustomerByPhoneNumber($phoneNumber)
    {

        $contact = Contact::where('value', $phoneNumber)->first();
        if ($contact != null)
        {
            return Customer::find($contact->id_customers);
        }

        return null;

    }

    public function getActiveCustomerByLocUnitNumber($locCode, $unitNumber)
    {

        $address = Address::where('code', $locCode)
            ->where('unit', $unitNumber)
            ->first();
        if ($address != null)
        {
            return Customer::find($address->id_customers);
        }

        return null;
    }

    public function getAdminUserByEmail($emailAddress)
    {

        return User::where('email', $emailAddress)
            ->where('id_status', config('const.status.active'))->first();
    }

    public function getAdminUserIdByEmail($email)
    {
        $adminUserId = 0;
        $sipAdminMatch = preg_match('/^.*\@silverip\.com/', $email);

        if ($sipAdminMatch !== 0 && $sipAdminMatch !== false)
        {
            $adminUser = $this->getAdminUserByEmail($email);
            if ($adminUser != null)
            {
                $adminUserId = $adminUser->id;
            }
        }

        return $adminUserId;
    }
}