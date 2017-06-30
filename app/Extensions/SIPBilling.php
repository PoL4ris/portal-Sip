<?php

namespace App\Extensions;

use App\Extensions\IpPay;
use App\Models\Customer;
use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\BillingTransactionLog;
use Hash;
use DB;
use Log;

class SIPBilling {

    private $testMode = true;
    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    public function __construct() {
        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        //        DB::connection()->enableQueryLog();
        $configPasscode = config('billing.ippay.passcode');    
        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    public function getMode(){
        return ($this->testMode) ? 'development' : 'production';
    }

    public function pingProcessor() {

        // Create a request array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = 'PING';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id

        $ipPayHandle = new IpPay();
        $ippayresult = array();

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
        }


        if(isset($ippayresult['ResponseText']) && $ippayresult['ResponseText'] == 'APPROVED'){
            return true;
        }
        return false;
    }

    public function queryTransaction($transaction_id) {

        // Create a request array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = 'ENQ';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id
        $request['TransactionID'] = $transaction_id;

        $ipPayHandle = new IpPay();
        $ippayresult = array();

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
        }

        return $ippayresult;
    }

    public function updatePaymentMethod(PaymentMethod $pm){

        if (is_numeric($pm->account_number) && strlen($pm->account_number) >= 14) {
            return $this->tokenize($pm);
        }
        return $pm;
    }

    protected function getPaymentMethod($paymentMethodId){

        $pm = null;
        if($paymentMethodId != null){
            // Use the requested payment method
            $pm = PaymentMethod::find($paymentMethodId);
            if ($pm == null) {
                Log::info('PaymentMethod: id='.$paymentMethodId.' not found.');
                return $pm;
            }
            if($pm->account_number == ''){
                Log::info('PaymentMethod: id='.$paymentMethodId.' is missing CC Token. We can not process this request without a CC token.');
                return $pm;
            }
        }
        return $pm;
    }

    public function hasPaymentMethodExpired(PaymentMethod $pm) {
        $expDate = $pm->exp_year. '-' . $pm->exp_month;
        return ($expDate < time()) ? true : false;
    }

    public function tokenize(PaymentMethod $pm) {

        // Use the customer and address associated with this payment method
        $customer = Customer::find($pm->id_customers);
        $address = Address::find($pm->id_address);

        if($customer == null){
            Log::info('SIPBilling::tokenize(): ERROR: Could not find a customer associated with the payment method you supplied');
            $pm->account_number = 'ERROR';
            return $pm;
        }

        if($address == null){
            Log::info('SIPBilling::tokenize(): ERROR: Could not find an address associated with the payment method you supplied');
            $pm->account_number = 'ERROR';
            return $pm;
        }

        // Create a request array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = 'TOKENIZE';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id
        $request['CardNum'] = str_replace(' ', '', trim($pm->account_number));
        $request['CVV2'] = $pm->CCscode;
        $request['CardExpMonth'] = $pm->exp_month;
        $request['CardExpYear'] = (strlen($pm->exp_year) == 4) ? substr($pm->exp_year, 2) : $pm->exp_year;    // customer CC expire year - YY
        $request['CardName'] = $customer->first_name.' '.$customer->last_name;
        $request['BillingCity'] = $address->city;
        $request['BillingStateProv'] = $address->state;
        $request['BillingPostalCode'] = $address->zip;
        $request['BillingPhone'] = $pm->billing_phone;
        $request['BillingAddress'] = $address->address;
        $request['BillingCountry'] = 'USA';        

        $ipPayHandle = new IpPay();
        $ippayresult = array();


        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
        }

        // Clear the unencrypted cc number from the payment method object
        unset($pm->CCscode);

        // Check if IPPay was able to tokenize the cc number
        if(isset($ippayresult['TOKEN'])){
            $pm->account_number = $ippayresult['TOKEN'];
            $pmPropertiesArr = json_decode($pm->properties);
            $pmPropertiesArr['last four'] = 'XXXX-XXXX-XXXX-'.substr($pm->account_number, -4);
            $pmPropertiesArr['card type'] = $pm->card_type;
            $pmPropertiesArr['exp month'] = $pm->exp_month;
            $pmPropertiesArr['exp year'] = $pm->exp_year;
            $pm->properties = json_encode($pmPropertiesArr);

        } else {
            $pm->account_number = 'ERROR';
        }

        // Store the transaction log in the database
        $ippayresult['TransactionType'] = $request['TransactionType'];
        $ippayresult['Comment'] = $customer->Comment;
        $this->storeXaction($ippayresult, $customer, $address, $pm);

        return $pm;
    }

    protected function storeXaction($xactionResult, Customer $customer = null, Address $address = null, PaymentMethod $pm = null, $details = false) {

        $xactionLog = new BillingTransactionLog;
        $xactionLog->transaction_id = $xactionResult['TRANSACTIONID'];
        $xactionLog->payment_mode = 'CC';

        if($customer != null){
            $xactionLog->username = isset($customer->email) ? $customer->email : '';
            $xactionLog->name = $customer->first_name.' '.$customer->last_name;
            $xactionLog->id_customers = $customer->id;
        }

        $xactionLog->transaction_type = $xactionResult['TransactionType'];
        $xactionLog->order_number = isset($xactionResult['OrderNumber']) ? $xactionResult['OrderNumber'] : 'N/A';
        $xactionLog->charge_description = isset($xactionResult['UDField1']) ? $xactionResult['UDField1'] : 'N/A';
        if ($details != false) {
            $xactionLog->charge_details = $details;
        }
        $xactionLog->action_code = $xactionResult['ACTIONCODE'];
        $xactionLog->approval = ($xactionResult['ACTIONCODE'] == '900') ? 'ERROR' : $xactionResult['APPROVAL'];
        $xactionLog->response_text = ($xactionResult['ACTIONCODE'] == '900') ? 'ERROR' : $xactionResult['RESPONSETEXT'];    
        if($address != null){
            $xactionLog->address = $address->address . ', ' . $address->city . ', ' . $address->state . ' ' . $address->zip;
            $xactionLog->unit = $address->unit;    
        }
        $xactionLog->response_error = $xactionResult['ERRMSG'];
        if($pm != null){
            if (isset($xactionResult['Comment'])) {
                $xactionLog->comment = $xactionResult['Comment'] . "\nCCtoken: " . $pm->account_number;
            } else {
                $customer = $pm->customer;
                $xactionLog->comment = "CCtoken: ".$customer->account_number;
            }
        }

        if (isset($xactionResult['TotalAmount']) == false) {
            $xactionLog->amount = '0.00';
        } else {
            $xactionLog->amount = strstr($xactionResult['TotalAmount'], '.') ?  $xactionResult['TotalAmount'] : $xactionResult['TotalAmount'].'.00';
        }
        $xactionLog->save();
        return $xactionLog->id;
    }

    public function updateXactionWithCustomer($xactionLogId, Customer $customer = null, Address $address = null, PaymentMethod $pm = null) {

        $xactionLog = BillingTransactionLog::find($xactionLogId);
        if($xactionLog == null){
            return false;
        }

        if($customer != null){
            $xactionLog->username = isset($customer->email) ? $customer->email : '';
            $xactionLog->name = $customer->first_name.' '.$customer->last_name;
            $xactionLog->id_customers = $customer->id;
        }

        if($address != null){
            $xactionLog->address = $address->address . ', ' . $address->city . ', ' . $address->state . ' ' . $address->zip;
            $xactionLog->unit = $address->unit;
        }

        if($pm != null){
            $xactionLog->comment = $xactionLog->comment . "\nCCtoken: " . $pm->account_number;
        }

        $xactionLog->save();
        return true;
    }

    protected function processCC($request, $authOnly = false, $totAmount = 0, $desc = 'SilverIP Comm', PaymentMethod $pm = null, Address $address = null) {

        // Gather the IPPay response in the following array
        $result = array();

        // Check if a PaymentMethod id is passed in and find it in the DB
        if($pm != null){
            // Use the requested payment method
            if($pm->account_number == ''){
                $result['FAILED'] = 'PaymentMethod: id='.$pm->id.' is missing CC Token. We can not process this request without a CC token.';
            }

            $request['Token'] = $pm->account_number;
            $result['PaymentType'] = $pm->types;
            $result['PaymentTypeDetails'] = ($pm->properties != '') ? json_decode($pm->properties, true)[0] : '';
        } else {
            // Use the card number and details in the request
            if (isset($request['CardName']) == false) {
                $result['FAILED'] = 'Missing card holder name. We can not process this request without a name.';
                $this->logChargeResult($result);
                return $result;
            }

            if (isset($request['CardNum']) == false || trim($request['CardNum']) == '') {
                $result['FAILED'] = 'Missing Card number. We can not process this request without a CC number.';
                $this->logChargeResult($result);
                return $result;
            }

            if (isset($request['CVV2']) == false) {
                $result['FAILED'] = 'Missing CVV2 number. We can not process this request without a CVV2 number.';
                $this->logChargeResult($result);
                return $result;
            }

            if (isset($request['CardExpMonth']) == false) {
                $result['FAILED'] = 'Missing expiration month. We can not process this request without an expiration date.';
                $this->logChargeResult($result);
                return $result;
            }

            if (isset($request['CardExpYear']) == false) {
                $result['FAILED'] = 'Missing expiration year. We can not process this request without an expiration date.';
                $this->logChargeResult($result);
                return $result;
            }

            if (strlen($request['CardExpYear']) == 4) {
                $request['CardExpYear'] = substr($request['CardExpYear'], 2);    // customer CC expire year - YY
            }

            $request['Tokenize'] = true;
            $result['PaymentType'] = 'Credit Card';
            $pmPropertiesArr = array();
            $pmPropertiesArr['last four'] = 'XXXX-XXXX-XXXX-'.substr($request['CardNum'], -4);
            $pmPropertiesArr['exp month'] = $request['CardExpMonth'];
            $pmPropertiesArr['exp year'] = $request['CardExpYear'];
            if (isset($request['CardType'])) {
                $pmPropertiesArr['card type'] = $request['CardType'];
                unset($request['CardType']);
            }
            $result['PaymentTypeDetails'] = json_encode($pmPropertiesArr);
        }

        $xactionType = isset($request['TransactionType']) ? $request['TransactionType'] : ($authOnly ? 'AUTHONLY' : 'SALE');

        if ($xactionType != 'SALE' && $xactionType != 'AUTHONLY' && $xactionType != 'CREDIT') {
            $result['FAILED'] = 'Unsupported TransactionType. processCC only supports: SALE, AUTHONLY, and CREDIT.';
            $this->logChargeResult($result);
            return $result;
        }

        // Create an array to pass to IPPay for processing
        //        $request = array();
        $request['TransactionType'] = $xactionType;
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['UDField1'] = $desc;
        $request['TotalAmount'] = $totAmount;
        //20 Charachtar Max - Appears on CC Statement - Default: SilverIP Comm
        $request['OrderNumber'] = isset($request['OrderNumber']) ? substr($request['OrderNumber'], 0, 20) : date('My') . ' Charges';
        // User defied feild - descripton of the charge, i.e. Signup
        $request['UDField2'] = isset($request['UDField2']) ? $request['UDField2'] : '';
        $request['UDField3'] = isset($request['UDField3']) ? $request['UDField3'] :'';

        if($address != null){
            $request['BillingAddress'] = $address->address; // customer billing address
            $request['BillingCity'] = $address->city;   // customer billing city
            $request['BillingStateProv'] = $address->state; // customer billing state
            $request['BillingPostalCode'] = $address->zip; // customer zip code
            $request['BillingCountry'] = 'USA'; // customer country - USA
            if($pm != null){
                $request['BillingPhone'] = $pm->billing_phone;    // customer phone number
            }
        }

        $ippayresult = array();
        $ipPayHandle = new IpPay();

        //process card - 0 is for test server, 1 for live server
        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  // IPPay test server
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  // IPPay live server
        }

        $result['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $result['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $result['APPROVAL'] = $ippayresult['APPROVAL'];
        $result['CVV2'] = $ippayresult['CVV2'];
        $result['VERIFICATIONRESULT'] = $ippayresult['VERIFICATIONRESULT'];
        $result['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $result['ADDRESSMATCH'] = $ippayresult['ADDRESSMATCH'];
        $result['ZIPMATCH'] = $ippayresult['ZIPMATCH'];
        $result['AVS'] = $ippayresult['AVS'];
        $result['ERRMSG'] = $ippayresult['ERRMSG'];
        $result['TransactionType'] = $request['TransactionType'];
        $result['TotalAmount'] = $totAmount;
        if(isset($ippayresult['TOKEN'])){
            $result['TOKEN'] = $ippayresult['TOKEN'];
        }
        $this->logChargeResult($result);
        return $result;
    }

    protected function logChargeResult($chargeResult){

        $response = 'failed';
        if(isset($chargeResult['FAILED']) == true){
            $response = 'failed';
        }

        else if (!isset($chargeResult['RESPONSETEXT']) && !isset($chargeResult['ACTIONCODE'])) {
            $response = 'failed';
        }

        else if ($chargeResult['RESPONSETEXT'] == 'DECLINED') {
            $response = 'declined';
        }

        else if ($chargeResult['RESPONSETEXT'] == 'APPROVED' && $chargeResult['ACTIONCODE'] == '000') {
            $response = 'approved';
        }

        Log::info('SIPBilling: Charge '.$response.': ' . "\n" . print_r($chargeResult,true));
        return $response;
    }

    public function chargeCustomer(Customer $customer, $amount = 0, $desc = 'SilverIP Comm', $orderNumber = false, $details = false) {

        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        $paymentMethod = $customer->defaultPaymentMethod;

        if($paymentMethod == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Payment method not found';
            $this->logChargeResult($result);
            return $result;
        }

        $xactionRequest = array();
        if ($orderNumber != false) {
            $xactionRequest['OrderNumber'] = $orderNumber;
        }
        $result = $this->processCC($xactionRequest, false, $amount, $desc, $paymentMethod);

        $result['Comment'] = $customer->comment;
        if(isset($result['FAILED']) == false){
            $this->storeXaction($result, $customer, $paymentMethod->address, $paymentMethod,  $details);
        }
        return $result;
    }

    public function chargePaymentMethod($paymentMethodId, $amount = 0, $desc = 'SilverIP Comm', $orderNumber = false, $details = false) {

        $paymentMethod = $this->getPaymentMethod($paymentMethodId);

        if($paymentMethod == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Payment method not found';
            $this->logChargeResult($result);
            return $result;
        }

        $customer = $paymentMethod->customer;
        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        $xactionRequest = array();
        if ($orderNumber != false) {
            $xactionRequest['OrderNumber'] = $orderNumber;
        }
        $result = $this->processCC($xactionRequest, false, $amount, $desc, $paymentMethod);

        $result['Comment'] = $customer->comment;
        if(isset($result['FAILED']) == false){
            $this->storeXaction($result, $customer, $paymentMethod->address, $paymentMethod,  $details);
        }
        return $result;
    }

    public function chargeCreditCard($cardInfo, $amount = 0, $desc = 'SilverIP Comm',  $orderNumber = false, $details = false, $address = null){

        if ($orderNumber != false) {
            $cardInfo['OrderNumber'] = $orderNumber;
        }
        $result = $this->processCC($cardInfo, false, $amount, $desc, null, $address);
        if(isset($result['FAILED']) == false){
            $result['TransactionLogId'] = $this->storeXaction($result, null, $address, null,  $details);
        }
        return $result;
    }

    public function authCreditCard($cardInfo, $amount = 0, $desc = 'SilverIP Comm', $orderNumber = false, $details = false, $address = null){

        if ($orderNumber != false) {
            $cardInfo['OrderNumber'] = $orderNumber;
        }
        $result = $this->processCC($cardInfo, true, $amount, $desc, null, $address);
        if(isset($result['FAILED']) == false){
            $result['TransactionLogId'] = $this->storeXaction($result, null, $address, null,  $details);
        }
        return $result;
    }

    public function refundCustomer(Customer $customer, $amount = 0, $desc = 'SilverIP Comm', $details = false) {

        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        $paymentMethod = $customer->defaultPaymentMethod;

        if($paymentMethod == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Payment method not found';
            $this->logChargeResult($result);
            return $result;
        }

        $xactionRequest = ['TransactionType' => 'CREDIT'];
        $result = $this->processCC($xactionRequest, false, $amount, $desc, $paymentMethod);

        $result['Comment'] = $customer->comment;
        if(isset($result['FAILED']) == false){
            $this->storeXaction($result, $customer, $paymentMethod->address, $paymentMethod,  $details);
        }
        return $result;
    }

    public function refundPaymentMethod($paymentMethodId, $amount, $desc = 'SilverIP Comm', $details = false) {

        $paymentMethod = $this->getPaymentMethod($paymentMethodId);

        if($paymentMethod == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Payment method not found';
            $this->logChargeResult($result);
            return $result;
        }

        $customer = $paymentMethod->customer;
        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        $xactionRequest = ['TransactionType' => 'CREDIT'];
        $result = $this->processCC($xactionRequest, false, $amount, $desc, $paymentMethod);

        $result['Comment'] = $customer->comment;
        if(isset($result['FAILED']) == false){
            $this->storeXaction($result, $customer, $paymentMethod->address, $paymentMethod,  $details);
        }
        return $result;
    }

    public function refundCreditCard($cardInfo, $amount = 0, $desc = 'SilverIP Comm', $details = false, $address = null){

        $cardInfo['TransactionType'] ='CREDIT';
        $result = $this->processCC($cardInfo, false, $amount, $desc, null, $address);
        $result['TransactionLogId'] = $this->storeXaction($result, null, $address, null,  $details);

        return $result;
    }

    public function captTransaction($transactionId){

        $transactionLog = BillingTransactionLog::where('transaction_id',$transactionId)
            ->get();

        // Gather the IPPay response in the following array
        $result = array();

        if($transactionLog == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Transaction not found';
            $this->logChargeResult($result);
            return $result;
        }

        $customer = Customer::find($transactionLog->id_customers);

        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = 'CAPT';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['TransactionID'] = $transactionId;

        $ippayresult = array();
        $ipPayHandle = new IpPay();

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
        }

        $result['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $result['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $result['APPROVAL'] = $ippayresult['APPROVAL'];
        $result['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $result['TransactionType'] = $request['TransactionType'];
        $result['Comment'] = $customer->comment;
        $result['TotalAmount'] = strstr($transactionLog->amount, '.') ?  str_replace('.', '', $transactionLog->amount) : $transactionLog->amount.'00';

        if(isset($result['ERRMSG']) && $result['ERRMSG'] != null){
            $this->storeXaction($result, $customer, $transactionLog->address, null,  $transactionLog->$details);
        }
        $this->logChargeResult($result);
        return $result;
    }

    public function forceCreditCard($transactionId, PaymentMethod $pm, $amount){

        $transactionLog = BillingTransactionLog::where('transaction_id',$transactionId)
            ->get();

        // Gather the IPPay response in the following array
        $result = array();

        if($transactionLog == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Transaction not found';
            $this->logChargeResult($result);
            return $result;
        }

        $customer = Customer::find($transactionLog->id_customers);

        if($customer == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Customer not found';
            $this->logChargeResult($result);
            return $result;
        }

        if($pm == null){
            $result['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $result['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $result['APPROVAL'] = 'ERROR';
            $result['CVV2'] = '';
            $result['VERIFICATIONRESULT'] = '';
            $result['RESPONSETEXT'] = 'ERROR';
            $result['ADDRESSMATCH'] = '';
            $result['ZIPMATCH'] = '';
            $result['AVS'] = '';
            $result['FAILED'] = 'Payment method not found';
            $this->logChargeResult($result);
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();

        $request['TransactionType'] = 'FORCE';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['TransactionID'] = $transactionId;
        $request['Token'] = $pm->account_number;
        $request['CardExpMonth'] = $pm->exp_month;
        $request['CardExpYear'] = $pm->exp_year;
        $request['TotalAmount'] = $amount;
        $request['Approval'] = $transactionLog->approval;

        $ippayresult = array();
        $ipPayHandle = new IpPay();

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
        }

        $result['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $result['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $result['APPROVAL'] = $ippayresult['APPROVAL'];
        $result['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $result['TransactionType'] = $request['TransactionType'];
        $result['Comment'] = $customer->comment;
        $result['TotalAmount'] = $transactionLog->amount;

        if(isset($result['ERRMSG']) && $result['ERRMSG'] != null){
            $this->storeXaction($result, $customer, $transactionLog->address, null,  $transactionLog->$details);
        }
        $this->logChargeResult($result);
        return $result;
    }

}

?>
