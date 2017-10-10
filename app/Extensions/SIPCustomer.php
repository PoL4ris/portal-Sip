<?php

namespace App\Extensions;

use App\Models\Address;
use App\Models\Contact;
use App\Models\Customer;
use App\Models\Product;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\NetworkNode;
use App\Models\Port;
use App\Models\User;
use App\Extensions\SIPNetwork;
use Validator;
use Log;
use Auth;

class SIPCustomer {

    protected $validationRules = [
        'Customer' => ['first_name' => 'required|alpha_dash',
                       'last_name'  => 'required|alpha_dash',
                       'email'      => 'required|e-mail',
                       'vip'        => 'alpha_dash'],
        'Contact'  => ['customer_id'  => 'required|numeric|min:2',
                       'contact_type' => 'required|numeric',
                       'mobile_phone' => 'regex:/[0-9]{3}-[0-9]{3}-[0-9]{4}/',
                       'home_phone'   => 'regex:/[0-9]{3}-[0-9]{3}-[0-9]{4}/',
                       'work_phone'   => 'regex:/[0-9]{3}-[0-9]{3}-[0-9]{4}/',
                       'fax'          => 'regex:/[0-9]{3}-[0-9]{3}-[0-9]{4}/',
                       'email'        => 'e-mail'],

        'Address' => ['customer_id' => 'required|numeric|min:2',
                      'building_id' => 'required|numeric|min:2',
                      'unit'        => 'required'],

//        'Address' => ['address'     => 'required',
//                      'code'        => 'required',
//                      'unit'        => 'required',
//                      'city'        => 'required|alpha',
//                      'zip'         => 'required|numeric|size:5',
//                      'state'       => 'required|size:2|alpha|size:2',
//                      'customer_id' => 'required|numeric|min:2',
//                      'building_id' => 'required|numeric|min:2'],

        'CustomerProduct' => ['customer_id' => 'required|min:2',
                              'product_id'  => 'required|min:1'],

        'CustomerPort' => ['customer_id' => 'required|numeric|min:2',
                           'switch_id'   => 'required|numeric|min:2',
                           'port_id'     => 'required|numeric|min:2']


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
        'alpha_dash'                   => ':attribute: Please use letters and numbers only',
        'alpha'                        => ':attribute: Please use letters only',
        'size'                         => 'The :attribute length must be exactly :size.',
        'cc_type.required_unless'      => 'Please select your card type',
        'cc_number.required_unless'    => 'Please enter your card number',
        'cc_exp_month.required_unless' => 'Please select an expiration month',
        'cc_exp_year.required_unless'  => 'Please select an expiration year',
        'cc_sec_code.required_unless'  => 'Please enter your card\'s security code',
    ];

    protected $validationAttributes = ['first_name'      => 'First name',
                                       'last_name'       => 'Last name',
                                       'email'           => 'Email',
                                       'vip'             => 'vip',
                                       'phone_number'    => 'Phone number',
                                       'street_address'  => 'Address',
                                       'unit'            => 'Unit number',
                                       'city'            => 'City',
                                       'state'           => 'State',
                                       'zip'             => 'Zip code',
                                       'service_plan'    => 'Plan',
                                       'wireless_router' => 'Wireless router',
                                       'cc_type'         => 'Card type',
                                       'cc_number'       => 'Card number',
                                       'cc_exp_month'    => 'Expiration month',
                                       'cc_exp_year'     => 'Expiration year',
                                       'cc_sec_code'     => 'Security code'];

    public function addNewCustomer($firstName, $lastName, $email, $vip = '')
    {
        $validator = Validator::make(['first_name' => $firstName,
                                      'last_name'  => $lastName,
                                      'email'      => $email,
                                      'vip'        => $vip], $this->validationRules['Customer'], $this->validationMessages, $this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();

            return ['error' => true, 'messages' => ['customer' => $errors]];
        }

        $customer = new Customer;
        $customer->first_name = $firstName;
        $customer->last_name = $lastName;
        $customer->email = $email;
        $customer->id_status = config('const.status.active');
        $customer->signedup_at = $nowMysqlDate = date("Y-m-d H:i:s");
        $customer->id_users = Auth::user()->id;

        $vip = trim($vip);
        if ($vip != null && $vip != '')
        {
            $customer->vip = $vip;
        }

        $customer->save();

        return ['response' => $customer];
    }

    public function addCustomerContact($customerId, $contactType, $value)
    {
        $validator = Validator::make(['customer_id'                                  => $customerId,
                                      'contact_type'                                 => $contactType,
                                      $this->getContactTypeNameByValue($contactType) => $value], $this->validationRules['Contact'], $this->validationMessages, $this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();

            return ['error' => true, 'messages' => ['customer' => $errors]];
        }

        $contact = new Contact;
        $contact->id_customers = $customerId;
        $contact->id_types = $contactType;
        $contact->value = $value;
        $contact->save();

        return ['response' => $contact];
    }

    public function addCustomerAddressByBuilding($customerId, $buildingId, $unit)
    {
        $validator = Validator::make(['customer_id' => $customerId,
                                      'building_id' => $buildingId,
                                      'unit'        => $unit], $this->validationRules['Address'], $this->validationMessages, $this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();

            return ['error' => true, 'messages' => ['location' => $errors]];
        }

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
        $address->country = $locationAddress->country;
        $address->id_customers = $customerId;
        $address->id_buildings = $locationAddress->id_buildings;
        $address->save();

        return ['response' => $address];
    }

    public function addCustomerProduct($customerId, $productId)
    {
        $validator = Validator::make(['customer_id' => $customerId,
                                      'product_id'  => $productId], $this->validationRules['CustomerProduct'], $this->validationMessages, $this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();

            return ['error' => true, 'messages' => ['service' => $errors]];
        }

        $product = Product::find($productId);
        if ($product == null)
        {
            Log::debug('addCustomerProduct(): product not found. id=' . $productId);

            return ['error' => true, 'messages' => ['service' => ['product' => ['The product you specified was not found in the database']]]];
        }

        $customerProduct = new CustomerProduct;
        $customerProduct->id_customers = $customerId;
        $customerProduct->id_products = $productId;
        $customerProduct->id_status = config('const.status.active');
        $customerProduct->signed_up = date("Y-m-d H:i:s");
        $customerProduct->id_users = Auth::user()->id;
        $customerProduct->save();

        return ['response' => $customerProduct];
    }

    public function getContactTypeNameByValue($value)
    {
        $contactTypeArray = array_flip(config('const.contact_type'));
        if (isset($contactTypeArray[$value]))
        {
            return $contactTypeArray[$value];
        }

        return null;
    }

    public function addCustomerPortBySwitchAndPort($customerId, $switchId, $portIndex)
    {
        $validator = Validator::make(['customer_id' => $customerId,
                                      'switch_id'   => $switchId,
                                      'port_id'     => $portIndex], $this->validationRules['CustomerPort'], $this->validationMessages, $this->validationAttributes);

        if ($validator->fails())
        {
            $errors = $validator->getMessageBag();

            return ['error' => true, 'messages' => ['service' => $errors]];
        }

        $switch = NetworkNode::find($switchId);
        if ($switch == null)
        {
            Log::debug('addCustomerPortBySwitchAndPort(): switch not found. id=' . $switchId);

            return ['error' => true, 'messages' => ['service' => ['switch' => ['The switch you specified was not found in the database']]]];
        }

        $switchIp = $switch->ip_address;

        $sipNetwork = new SIPNetwork();
        $portNumber = $sipNetwork->getPortNumberFromInfoTableIndex($switchIp, $portIndex);

        if ($portNumber == '')
        {
            Log::debug('addCustomerPortBySwitchAndPort(): port not found. index=' . $portIndex);

            return ['error' => true, 'messages' => ['service' => ['port' => ['The port you specified was not found on the switch']]]];
        }

        $port = Port::firstOrNew(['id_network_nodes' => $switch->id, 'port_number' => $portNumber]);
        $port->access_level = 'yes';
        $port->save();

        $customerPort = new CustomerPort();
        $customerPort->customer_id = $customerId;
        $customerPort->port_id = $port->id;
        $customerPort->save();

        return ['response' => $customerPort];
    }

    public function getRecentManuallyAddedCustomers()
    {
        return Customer::with(['address', 'user', 'emailAddress', 'phone'])
            ->where('id_users', '!=', 0)
            ->orderBy('signedup_at', 'desc')
            ->take(15)
            ->get();
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
            // Search for any status not just active otherwiese emails from help@silverip.com will not clear the "read" flag on tickets
//            ->where('id_status', config('const.status.active'))
            ->first();
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

    /**
     * @param Request $request
     * id = id_customers.
     * idService to find and update (disable) record/Service.
     * Status
     * 1 = active
     * 2 = disabled
     * 3 = decommissioned
     * 4 = pending
     * 5 = admin
     * @return Customer services list.
     */
//    public function disableCustomerServices($customerId)
//    {
//        $customer = Customer::find($customerId);
//        $activeService = CustomerProduct::find($request->idService);
//        $activeService->id_status = config('const.status.disabled');
//        $activeService->save();
//
//        $this->cancelActiveChargesForCustomerProduct($activeService);
//        $this->cancelActiveInvoicesForCustomer($customer);
//
//        $newData = array();
//        $newData['id_status'] = config('const.status.disabled');
//
//        $relationData = Product::find($activeService->id_products);
//
//        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $activeService, $newData, $relationData, 'disable-service');
//
//        return $this->getCustomerServices($request);
//
//    }

// TODO: Complete the following functions
//    public function disableCustomer($customerId)
//    {
//        $customer = Customer::find($customerId);
//        $activeService = CustomerProduct::find($request->idService);
//        $activeService->id_status = config('const.status.disabled');
//        $activeService->save();
//
//        $this->cancelActiveChargesForCustomerProduct($activeService);
//        $this->cancelActiveInvoicesForCustomer($customer);
//
//        $newData = array();
//        $newData['id_status'] = config('const.status.disabled');
//
//        $relationData = Product::find($activeService->id_products);
//
//        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $activeService, $newData, $relationData, 'disable-service');
//
//        return $this->getCustomerServices($request);
//
//    }
//


    public function cancelActiveChargesForCustomerProduct(CustomerProduct $customerProduct)
    {

        $charges = $customerProduct->activeCharges;
        if ($charges == null)
        {
            Log::info('cancelActiveChargesForCustomerProduct(): CustomerProduct id=' . $customerProduct->id . ' has no active charges.');

            return false;
        }
        $billingHelper = new BillingHelper();

        foreach ($charges as $charge)
        {
            $billingHelper->removeChargeFromInvoice($charge);
        }

        return true;
    }

    protected function cancelActiveChargesForCustomer(Customer $customer)
    {

        $charges = $customer->allActiveCharges;
        if ($charges == null)
        {
            Log::info('cancelActiveChargesForCustomer(): Customer id=' . $customer->id . ' has no active charges.');

            return false;
        }

        $billingHelper = new BillingHelper();
        foreach ($charges as $charge)
        {
            $billingHelper->removeChargeFromInvoice($charge);
        }

        return true;
    }

    protected function cancelActiveInvoicesForCustomer(Customer $customer)
    {

        $invoices = $customer->allActiveInvoices;
        if ($invoices == null)
        {
            Log::info('cancelActiveInvoicesForCustomer(): Customer id=' . $customer->id . ' has no active invoices.');

            return false;
        }

        $billingHelper = new BillingHelper();
        $count = 0;
        foreach ($invoices as $invoice)
        {
            $billingHelper->cancelInvoice($invoice);
            $count ++;
        }
        Log::info('cancelActiveInvoicesForCustomer(): Cancelled ' . $count . ' invoices for customer id=' . $customer->id);

        return true;
    }
}