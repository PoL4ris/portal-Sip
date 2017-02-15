<?php

namespace App\Extensions;

use App\Extensions\IpPay;
use App\Models\Customer;
use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\BillingTransactionLog;
use Hash;
use DB;

class SIPBilling {

    private $testMode = true;
    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    public function __construct() {
        DB::connection()->enableQueryLog();
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
            error_log('SIPBilling::updatePaymentMethod(): calling tokenize');
            return $this->tokenize($pm);
        }
        error_log('SIPBilling::updatePaymentMethod(): no cc number detected');
        return $pm;
    }

    public function tokenize(PaymentMethod $pm) {

        // Use the customer and address associated with this payment method
        $customer = Customer::find($pm->id_customers);
        $address = Address::find($pm->id_address);

        if($customer == null || $address == null){
            error_log('SIPBilling::tokenize(): did not find a customer or an address associated with the payment method you supplied');
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
            $pm->properties = json_encode($pmPropertiesArr);

        } else {
            $pm->account_number = 'ERROR';
        }

        // Store the transaction log in the database
        $ippayresult['TransactionType'] = $request['TransactionType'];
        $ippayresult['Comment'] = $customer->Comment;
        $this->storeXaction($customer, $ippayresult, $address, $pm);

        return $pm;
    }

    protected function storeXaction(Customer $customer, $xactionResult, Address $address = null, PaymentMethod $pm = null, $details = false) {

        $xactionLog = new BillingTransactionLog;
        $xactionLog->transaction_id = $xactionResult['TRANSACTIONID'];
        $xactionLog->payment_mode = 'CC';
        $xactionLog->username = isset($customer->email) ? $customer->email : '';
        $xactionLog->name = $customer->first_name.' '.$customer->last_name;        
        $xactionLog->id_customers = $customer->id;
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

    public function chargeCC($customer_id, $amount, $reason, $orderNumber = false, $details = false, $payment_id = null) {
        $xactionRequest = array();
        if ($orderNumber != false) {
            $xactionRequest['OrderNumber'] = $orderNumber;
        }
        return $this->processCC($customer_id, $xactionRequest, false, $amount, $reason, $details, $payment_id);
    }

    public function authCC($customer_id, $amount, $reason, $details = false, $payment_id = null) {
        return $this->processCC($customer_id, array(), true, $amount, $reason, $details, $payment_id);
    }

    public function captCC($transaction_id){

        $transactionLog = BillingTransactionLog::where('transaction_id',$transaction_id)
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
            $result['ERRMSG'] = 'Transaction not found';
            return $result;
        }

        $customer = Customer::find($transactionLog->id_customers)
            ->get();

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
            $result['ERRMSG'] = 'Customer not found';
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();

        $request['TransactionType'] = 'CAPT';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['TransactionID'] = $transaction_id;

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

        $this->storeXaction($customer, $result, $details);
        $this->storeXaction($customer, $ippayresult, $address, $pm);

        return $result;
    }

    public function forceCC($transaction_id, PaymentMethod $pm, $amount){

        $transactionLog = BillingTransactionLog::where('transaction_id',$transaction_id)
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
            $result['ERRMSG'] = 'Transaction not found';
            return $result;
        }

        $customer = Customer::find($transactionLog->id_customers)
            ->get();

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
            $result['ERRMSG'] = 'Customer not found';
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
            $result['ERRMSG'] = 'Payment method not found';
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();

        $request['TransactionType'] = 'FORCE';
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['TransactionID'] = $transaction_id;
        $request['Token'] = $transaction_id;
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

        $this->storeXaction($customer, $result, $details);
        return $result;
    }

    public function refundCC($customer_id, $amount, $desc, $details = false, $payment_id = null) {
        $xactionRequest = array('TransactionType' => 'CREDIT');
        return $this->processCC($customer_id, $xactionRequest, false, $amount, $desc, $details, $payment_id);
    }

    public function refundCCByTransID($transaction_id, $desc, $details = false) {
        $transaction = BillingTransactionLog::where('transaction_id', $transaction_id)->first();
        $customer_id = ($transaction == null) ? null : $transaction->id_customers;
        $xactionRequest = array('TransactionType' => 'CREDIT');
        return $this->processCC($customer_id, $xactionRequest, false, $transaction->Amount, $desc, $details);
    }

    //Type of transaction sent to the system. Options: SALE, AUTHONLY, CAPT, FORCE, VOID, CREDIT, CHECK, REVERAL, VOIDACH
    protected function processCC($customer_id, $xactionRequest, $authOnly = false, $totAmount = 0, $desc = 'SilverIP Comm', $details = false, $payment_id = null) {

        // Gather the IPPay response in the following array
        $result = array();

        $customer = Customer::find($customer_id);
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
            $result['ERRMSG'] = 'Customer not found';
            return $result;
        }

        $pm = null;
        if($payment_id == null){

            // Get customer's default payment method
            $pm = PaymentMethod::with('address')
                ->where('id_customers',$customer->id)
                ->where('priority',1)
                ->first();
        } else {
            // Use the requested payment method
            $pm = PaymentMethod::with('address')
                ->where('id_customers',$customer->id)
                ->get();
        }

        if ($pm == null || $pm->account_number == '') {
            $result['ERRMSG'] = 'Missing CC Token. We can not process this request without a CC token.';
            return $result;
        }

        $xactionType = isset($xactionRequest['TransactionType']) ? $xactionRequest['TransactionType'] : ($authOnly ? 'AUTHONLY' : 'SALE');

        if ($xactionType != 'SALE' && $xactionType != 'AUTHONLY' && $xactionType != 'CREDIT') {
            $result['ERRMSG'] = 'Unsupported TransactionType. processCC only supports: SALE, AUTHONLY, and CREDIT.';
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = $xactionType;
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id
        $request['Token'] = $pm->account_number;
        $request['UDField1'] = $desc;
        $request['TotalAmount'] = $totAmount;
        $request['OrderNumber'] = isset($xactionRequest['OrderNumber']) ? substr($xactionRequest['OrderNumber'], 0, 20) : date('My') . ' Charges';   //20 Charachtar Max- Appears on CC Statement - Default: SilverIP Comm

        $request['UDField2'] = isset($xactionRequest['UDField2']) ? $xactionRequest['UDField2'] : ''; // User defied feild - descripton of the charge, i.e. Signup
        $request['UDField3'] = isset($xactionRequest['UDField3']) ? $xactionRequest['UDField3'] :'';


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
        $result['CVV2'] = $ippayresult['CVV2'];
        $result['VERIFICATIONRESULT'] = $ippayresult['VERIFICATIONRESULT'];
        $result['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $result['ADDRESSMATCH'] = $ippayresult['ADDRESSMATCH'];
        $result['ZIPMATCH'] = $ippayresult['ZIPMATCH'];
        $result['AVS'] = $ippayresult['AVS'];
        $result['ERRMSG'] = $ippayresult['ERRMSG'];
        $result['TransactionType'] = $request['TransactionType'];
        $result['Comment'] = $customer->comment;
        $result['TotalAmount'] = $totAmount;

        $this->storeXaction($customer, $result, $pm->address, $pm,  $details);
        return $result;
    }

    public function chargeRawCC(Customer $customer, PaymentMethod $pm, Address $address = null, $amount, $desc, $details = false) {
        return self::processRawCC($customer, $pm, $address, null, false, $amount, $desc, $details);
    }

    public function authRawCC(Customer $customer, PaymentMethod $pm, Address $address = null, $amount, $desc, $details = false) {
        return $this->processRawCC($customer, $pm, $address, null, true, $amount, $desc, $details);
    }

    //Type of transaction sent to the system. Options: SALE, AUTHONLY, CAPT, FORCE, VOID, CREDIT, CHECK, REVERAL, VOIDACH
    protected function processRawCC(Customer $customer, PaymentMethod $pm, Address $address = null, $xactionRequest = null, $authOnly = false, $totAmount = 0, $desc = 'SilverIP Comm', $details = false) {

        // Gather the IPPay response in the following array
        $result = array();

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
            $result['ERRMSG'] = 'Customer not found';
            return $result;
        }

        $xactionType = isset($xactionRequest['TransactionType']) ? $xactionRequest['TransactionType'] : ($authOnly ? 'AUTHONLY' : 'SALE');

        if ($xactionType != 'SALE' && $xactionType != 'AUTHONLY' && $xactionType != 'CREDIT') {
            $result['ERRMSG'] = 'Unsupported TransactionType. processCC only supports: SALE, AUTHONLY, and CREDIT.';
            return $result;
        }

        // Create an array to pass to IPPay for processing
        $request = array();
        $request['TransactionType'] = $xactionType;
        $request['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id

        if ($pm == null || $pm->account_number == '') {
            $result['ERRMSG'] = 'Payment method is null or CC number is missing. We can not process this request without CC info.';
            return $result;
        }

        if (isset($pm->account_number) == false || trim($pm->account_number) == '') {
            $result['ERRMSG'] = 'Missing Card number. We can not process this request without a CC number.';
            return $result;
        }

        if (isset($pm->CCscode) == false) {
            $result['ERRMSG'] = 'Missing CVV2 number. We can not process this request without a CVV2 number.';
            return $result;
        }

        if (isset($pm->exp_month) == false) {
            $result['ERRMSG'] = 'Missing expiration month. We can not process this request without an expiration date.';
            return $result;
        }

        if (isset($pm->exp_year) == false) {
            $result['ERRMSG'] = 'Missing expiration year. We can not process this request without an expiration date.';
            return $result;
        }

        if (strlen($pm->exp_year) == 4) {
            $request['CardExpYear'] = substr($pm->exp_year, 2);    // customer CC expire year - YY
        } else {
            $request['CardExpYear'] = $pm->exp_year;    // customer CC expire year - YY
        }

        $request['CardNum'] = $customer->CCnumber;
        $request['CardExpMonth'] = $customer->Expmo;
        $request['CVV2'] = $customer->CCscode;      // CC security code
        $request['CardName'] = $customer->Firstname.' '.$customer->Lastname;

        if($address != null){
            $request['BillingAddress'] = $address->address; // customer billing address
            $request['BillingCity'] = $address->city;   // customer billing city
            $request['BillingStateProv'] = $address->state; // customer billing state
            $request['BillingPostalCode'] = $address->zip; // customer zip code
            $request['BillingCountry'] = 'USA'; // customer country - USA
            $request['BillingPhone'] = $pm->billing_phone;      // customer phone number 
        }

        $request['Origin'] = 'RECURRING';
        $request['UDField1'] = $desc;
        $request['TotalAmount'] = $totAmount;
        $request['OrderNumber'] = isset($xactionRequest['OrderNumber']) ? substr($xactionRequest['OrderNumber'], 0, 20) : date('My') . ' Charges';   //20 Charachtar Max- Appears on CC Statement - Default: SilverIP Comm
        $request['UDField2'] = isset($xactionRequest['UDField2']) ? $xactionRequest['UDField2'] : ''; // User defied feild - descripton of the charge, i.e. Signup
        $request['UDField3'] = isset($xactionRequest['UDField3']) ? $xactionRequest['UDField3'] :'';

        $ipPayHandle = new IpPay();
        $ippayresult = array();
        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($request, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($request, 1);  //process card - 0 is for test server, 1 for live server
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
        $result['Comment'] = $customer->comment;

        if (isset($customer->id) == false) {
            $customer->id = 1;
        }

        unset($pm->account_number);
        unset($pm->CCscode);

        $this->storeXaction($pm, $customer, $result, $details);
        return $result;
    }

    public function hasCCExpired(PaymentMethod $pm) {
        $expDate = $pm->exp_year. '-' . $pm->exp_month;
        return ($expDate < time()) ? true : false;
    }

}

?>