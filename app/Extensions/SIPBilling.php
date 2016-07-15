<?php

namespace App\Extensions;

use App\Extensions\IpPay;
use App\Models\Customer;
use App\Models\PaymentMethod;
use App\Models\Billing\billingTransactionLog;
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

    public function updatePaymentMethod(PaymentMethod $pm){
        if (is_numeric($pm->account_number) && strlen($pm->account_number) >= 14) {
            error_log('SIPBilling::updatePaymentMethod(): calling tokenizeCustomerCC');
            return $this->tokenizeCustomerCC($pm);
        }
        error_log('SIPBilling::updatePaymentMethod(): no cc number detected');
        return $pm;
    }

    public function tokenizeCC(PaymentMethod $pm) {

        $ipPayHandle = new IpPay();
        $customer = Customer::find($pm->id_customers);
        $address = Address::find($pm->id_address);
        
        // Create an array to pass to IP Pay for processing
        $ccInfo = array();
        $ippayresult = array();
        $resultArr = array();

        $ccInfo['TransactionType'] = 'TOKENIZE';
        $ccInfo['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id
        $ccInfo['CardNum'] = str_replace(' ', '', trim($pm->account_number));
        $ccInfo['CVV2'] = $pm->CCscode;
        $ccInfo['CardExpMonth'] = $pm->exp_month;
        $ccInfo['CardExpYear'] = (strlen($pm->exp_year) == 4) ? substr($customer->exp_year, 2) : $customer->exp_year;    // customer CC expire year - YY
        $ccInfo['CardName'] = $customer->first_name.' '.$customer->last_name;
        $ccInfo['BillingCity'] = $customer->City;
        $ccInfo['BillingStateProv'] = $customer->State;
        $ccInfo['BillingPostalCode'] = $customer->Zip;
        $ccInfo['BillingPhone'] = $customer->Tel;
        $ccInfo['BillingAddress'] = $customer->Address;
        $ccInfo['BillingCountry'] = 'USA';        

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($ccInfo, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($ccInfo, 1);  //process card - 0 is for test server, 1 for live server
        }

        //            error_log(print_r($ippayresult,true));

        if(isset($ippayresult['TOKEN'])){
            $customer->CCtoken = $ippayresult['TOKEN'];
            $customer->CCnumber =  'XXXX-XXXX-XXXX-' . substr($customer->CCnumber, -4);        
        } else {
            $customer->CCnumber = 'ERROR';
        }
        $customer->CCscode = '';

        $ippayresult['TransactionType'] = $ccInfo['TransactionType'];


        $ippayresult['Comment'] = $customer->Comment;

        $this->storeXaction($customer, $ippayresult);

        return $customer;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    public function updateCustomerCC(PaymentMethod $pm, $customer_id){

        if (is_numeric($pm->CCnumber) && strlen($customer->CCnumber) >= 14) {
            error_log('SIPBilling::updateCustomerCC(): calling tokenizeCustomerCC');
            return $this->tokenizeCustomerCC($customer);
        }

        error_log('SIPBilling::updateCustomerCC(): no cc number detected');
        return $customer;
    }

    public function tokenizeCustomerCC(Customer $customer) {

        $ipPayHandle = new IpPay();

        // Create an array to pass to IP Pay for processing
        $ccInfo = array();
        $ippayresult = array();
        $resultArr = array();

        $ccInfo['TransactionType'] = 'TOKENIZE';
        $ccInfo['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id
        $ccInfo['CardNum'] = str_replace(' ', '', trim($customer->CCnumber));
        $ccInfo['CVV2'] = $customer->CCscode;
        $ccInfo['CardExpMonth'] = $customer->Expmo;
        $ccInfo['CardExpYear'] = (strlen($customer->Expyr) == 4) ? substr($customer->Expyr, 2) : $customer->Expyr;    // customer CC expire year - YY
        $ccInfo['CardName'] = $customer->Firstname.' '.$customer->Lastname;
        $ccInfo['BillingCity'] = $customer->City;
        $ccInfo['BillingStateProv'] = $customer->State;
        $ccInfo['BillingPostalCode'] = $customer->Zip;
        $ccInfo['BillingPhone'] = $customer->Tel;
        $ccInfo['BillingAddress'] = $customer->Address;
        $ccInfo['BillingCountry'] = 'USA';        

        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($ccInfo, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($ccInfo, 1);  //process card - 0 is for test server, 1 for live server
        }

        //            error_log(print_r($ippayresult,true));

        if(isset($ippayresult['TOKEN'])){
            $customer->CCtoken = $ippayresult['TOKEN'];
            $customer->CCnumber =  'XXXX-XXXX-XXXX-' . substr($customer->CCnumber, -4);        
        } else {
            $customer->CCnumber = 'ERROR';
        }
        $customer->CCscode = '';

        $ippayresult['TransactionType'] = $ccInfo['TransactionType'];


        $ippayresult['Comment'] = $customer->Comment;

        $this->storeXaction($customer, $ippayresult);

        return $customer;
    }

    protected function storeXaction(Customer $customer, $xactionResult, $details = false) {

        $xactionLog = new billingTransactionLog;
        $xactionLog->TransactionID = $xactionResult['TRANSACTIONID'];
        $xactionLog->PaymentMode = 'CC';
        $xactionLog->Username = isset($customer->Email) ? $customer->Email : '';
        $xactionLog->Name = $customer->Firstname.' '.$customer->Lastname;        
        $xactionLog->CID = $customer->CID;
        $xactionLog->Unit = $customer->Unit;
        $xactionLog->TransType = $xactionResult['TransactionType'];
        $xactionLog->OrderNumber = isset($xactionResult['OrderNumber']) ? $xactionResult['OrderNumber'] : 'N/A';
        $xactionLog->ChargeDescription = isset($xactionResult['UDField1']) ? $xactionResult['UDField1'] : 'N/A';
        if ($details != false) {
            $xactionLog->ChargeDetails = $details;
        }
        $xactionLog->ActionCode = $xactionResult['ACTIONCODE'];
        $xactionLog->Approval = ($xactionResult['ACTIONCODE'] == '900') ? 'ERROR' : $xactionResult['APPROVAL'];
        $xactionLog->Responsetext = ($xactionResult['ACTIONCODE'] == '900') ? 'ERROR' : $xactionResult['RESPONSETEXT'];    
        $xactionLog->Address = $customer->Address . ', ' . $customer->City . ', ' . $customer->State . ' ' . $customer->Zip;
        $xactionLog->Unit = $customer['Unit'];
        $xactionLog->Responseerror = $xactionResult['ERRMSG'];
        if (isset($xactionResult['Comment'])) {
            $xactionLog->Comment = $xactionResult['Comment'] . "\nCCtoken: " . $customer->CCtoken;
        } else {
            $xactionLog->Comment = "CCtoken: ".$customer->CCtoken;
        }

        if (isset($xactionResult['TotalAmount']) == false) {
            $xactionLog->Amount = '0.00';
        } else {
            $xactionLog->Amount = strstr($xactionResult['TotalAmount'], '.') ?  $xactionResult['TotalAmount'] : $xactionResult['TotalAmount'].'.00';
        }
        $xactionLog->save();
        return $xactionLog->id;
    }

    public function chargeCC($amount, $desc, $customer, $details = false) {
        return $this->processCC($customer, array(), false, $amount, $desc, $details);
    }

    public function chargeCCByCID($CID, $amount, $reason, $orderNumber = false, $details = false) {
        $customer = Customer::find($CID);
        $xactionRequest = array();
        if ($orderNumber != false) {
            $xactionRequest['OrderNumber'] = $orderNumber;
        }
        return $this->processCC($customer, $xactionRequest, false, $amount, $reason, $details);
    }

    public function authCCByCID($CID, $amount, $reason, $details = false) {
        $customer = Customer::find($CID);
        return $this->processCC($customer, array(), true, $amount, $reason, $details);
    }

    public function authCC($amount, $desc, $customer, $details = false) {
        return $this->processCC($customer, array(), true, $amount, $desc, $details);
    }

    public function refundCCByCID($CID, $amount, $desc, $details = false) {
        $customer = Customer::find($CID);
        $xactionRequest = array('TransactionType' => 'CREDIT');
        return $this->processCC($customer, $xactionRequest, false, $amount, $desc, $details);
    }

    public function refundCC($amount, $desc, Customer $customer, $details = false) {
        $xactionRequest = array('TransactionType' => 'CREDIT');
        return $this->processCC($customer, $xactionRequest, false, $amount, $desc, $details);
    }

    public function refundCCByTransID($transID, $desc, $details = false) {
        $transaction = billingTransactionLog::where('TransactionID', $transID)->first();
        $customer = ($transaction == null) ? null : Customer::find($transaction->CID);
        $xactionRequest = array('TransactionType' => 'CREDIT');
        return $this->processCC($customer, $xactionRequest, false, $transaction->Amount, $desc, $details);
    }

    public function tokenizeCCByCID($CID, $cardNum, $expMo, $expYr, $ccv = 0) {
        $customer = Customer::find($CID);
        if($customer == null){
            return $customer;
        }
        $customer->CCnumber = $cardNum;
        $customer->Expmo = $expMo;
        $customer->Expyr = $expYr;
        $customer->CCscode = $ccv;
        return $this->tokenizeCustomerCC($customer, false);
    }

    public function tokenizeCC($customer, $cardNum, $expMo, $expYr, $ccv = 0) {
        if($customer == null){
            return $customer;
        }
        $customer->CCnumber = $cardNum;
        $customer->Expmo = $expMo;
        $customer->Expyr = $expYr;
        $customer->CCscode = $ccv;
        return $this->tokenizeCustomerCC($customer, false);
    }

    //Type of transaction sent to the system. Options: SALE, AUTHONLY, CAPT, FORCE, VOID, CREDIT, CHECK, REVERAL, VOIDACH
    protected function processCC($customer, $xactionRequest, $authOnly = false, $totAmount = 0, $desc = 'SilverIP Comm', $details = false) {

        $ipPayHandle = new IpPay();

        // Create an array to pass to IPPay for processing
        $ccInfo = array();

        // Gather the IPPay response in the following array
        $resultArr = array();

        if($customer == null){
            $resultArr['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $resultArr['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $resultArr['APPROVAL'] = 'ERROR';
            $resultArr['CVV2'] = '';
            $resultArr['VERIFICATIONRESULT'] = '';
            $resultArr['RESPONSETEXT'] = 'ERROR';
            $resultArr['ADDRESSMATCH'] = '';
            $resultArr['ZIPMATCH'] = '';
            $resultArr['AVS'] = '';
            $resultArr['ERRMSG'] = 'Customer not found';
            return $resultArr;
        }

        $xactionType = isset($xactionRequest['TransactionType']) ? $xactionRequest['TransactionType'] : ($authOnly ? 'AUTHONLY' : 'SALE');

        if ($xactionType != 'SALE' && $xactionType != 'AUTHONLY' && $xactionType != 'CREDIT') {
            $resultArr['ERRMSG'] = 'Unsupported TransactionType. processCC only supports: SALE, AUTHONLY, and CREDIT.';
            return $resultArr;
        }

        $ccInfo['TransactionType'] = $xactionType;
        $ccInfo['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';  // silverip unique account id

        if ($customer->CCtoken == '') {
            $resultArr['ERRMSG'] = 'Missing CC Token. We can not process this request without a CC token.';
            return $resultArr;
        }

        $ccInfo['Token'] = $customer->CCtoken;
        $ccInfo['UDField1'] = $desc;
        $ccInfo['TotalAmount'] = $totAmount;

        $ccInfo['OrderNumber'] = isset($xactionRequest['OrderNumber']) ? substr($xactionRequest['OrderNumber'], 0, 20) : date('My') . ' Charges';   //20 Charachtar Max- Appears on CC Statement - Default: SilverIP Comm

        $ccInfo['UDField2'] = isset($xactionRequest['UDField2']) ? $xactionRequest['UDField2'] : ''; // User defied feild - descripton of the charge, i.e. Signup
        $ccInfo['UDField3'] = isset($xactionRequest['UDField3']) ? $xactionRequest['UDField3'] :'';

        $ippayresult = array();
        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($ccInfo, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($ccInfo, 1);  //process card - 0 is for test server, 1 for live server
        }

        $resultArr['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $resultArr['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $resultArr['APPROVAL'] = $ippayresult['APPROVAL'];
        $resultArr['CVV2'] = $ippayresult['CVV2'];
        $resultArr['VERIFICATIONRESULT'] = $ippayresult['VERIFICATIONRESULT'];
        $resultArr['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $resultArr['ADDRESSMATCH'] = $ippayresult['ADDRESSMATCH'];
        $resultArr['ZIPMATCH'] = $ippayresult['ZIPMATCH'];
        $resultArr['AVS'] = $ippayresult['AVS'];
        $resultArr['ERRMSG'] = $ippayresult['ERRMSG'];

        $ippayresult['TransactionType'] = $ccInfo['TransactionType'];
        $ippayresult['Comment'] = $customer->Comment;
        $ippayresult['TotalAmount'] = $totAmount;

        $this->storeXaction($customer, $ippayresult, $details);
        return $resultArr;
    }

    public function chargeRawCC($amount, $desc, $customer, $details = false) {
        return self::processRawCC($customer, false, $amount, $desc, $details);
    }

    public function authRawCC($amount, $desc, $customer, $details = false) {
        return $this->processRawCC($customer, true, $amount, $desc, $details);
    }

    //Type of transaction sent to the system. Options: SALE, AUTHONLY, CAPT, FORCE, VOID, CREDIT, CHECK, REVERAL, VOIDACH
    protected function processRawCC($customer, $authOnly = false, $totAmount = 0, $desc = 'SilverIP Comm', $details = false) {

        $ipPayHandle = new IpPay();

        // Create an array to pass to IPPay for processing
        $ccInfo = array();

        // Gather the IPPay response in the following array
        $resultArr = array();

        if($customer == null){
            $resultArr['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $resultArr['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $resultArr['APPROVAL'] = 'ERROR';
            $resultArr['CVV2'] = '';
            $resultArr['VERIFICATIONRESULT'] = '';
            $resultArr['RESPONSETEXT'] = 'ERROR';
            $resultArr['ADDRESSMATCH'] = '';
            $resultArr['ZIPMATCH'] = '';
            $resultArr['AVS'] = '';
            $resultArr['ERRMSG'] = 'Customer not found';
            return $resultArr;
        }

        $xactionType = isset($xactionRequest['TransactionType']) ? $xactionRequest['TransactionType'] : ($authOnly ? 'AUTHONLY' : 'SALE');

        if ($xactionType != 'SALE' && $xactionType != 'AUTHONLY' && $xactionType != 'CREDIT') {
            $resultArr['ERRMSG'] = 'Unsupported TransactionType. processCC only supports: SALE, AUTHONLY, and CREDIT.';
            return $resultArr;
        }

        $ccInfo['TransactionType'] = $xactionType;
        $ccInfo['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id

        if (isset($customer->CCnumber) == false || trim($customer->CCnumber) == '') {
            $resultArr['ERRMSG'] = 'Missing Card number. We can not process this request without a CC number.';
            return $resultArr;
        }

        if (isset($customer->CCscode) == false) {
            $resultArr['ERRMSG'] = 'Missing CVV2 number. We can not process this request without a CVV2 number.';
            return $resultArr;
        }

        if (isset($customer->Expmo) == false) {
            $resultArr['ERRMSG'] = 'Missing expiration month. We can not process this request without an expiration date.';
            return $resultArr;
        }

        if (isset($customer->Expyr) == false) {
            $resultArr['ERRMSG'] = 'Missing expiration year. We can not process this request without an expiration date.';
            return $resultArr;
        }

        if (strlen($customer->Expyr) == 4) {
            $ccInfo['CardExpYear'] = substr($customer->Expyr, 2);    // customer CC expire year - YY
        } else {
            $ccInfo['CardExpYear'] = $customer->Expyr;    // customer CC expire year - YY
        }

        $ccInfo['CardNum'] = $customer->CCnumber;
        $ccInfo['CardExpMonth'] = $customer->Expmo;
        $ccInfo['CVV2'] = $customer->CCscode;      // CC security code
        $ccInfo['CardName'] = $customer->Firstname.' '.$customer->Lastname;

        if (isset($customer->Address)) {
            $ccInfo['BillingAddress'] = $customer->Address; // customer billing address
        }

        if (isset($customer->City)) {
            $ccInfo['BillingCity'] = $customer->City;   // customer billing city
        }

        if (isset($customer->State)) {
            $ccInfo['BillingStateProv'] = $customer->State; // customer billing state
        }

        if (isset($customer->Zip)) {
            $ccInfo['BillingPostalCode'] = $customer->Zip; // customer zip code
            $ccInfo['BillingCountry'] = 'USA'; // customer country - USA
        }

        if (isset($customer->Tel)) {
            $ccInfo['BillingPhone'] = $customer->Tel;      // customer phone number
        }

        $ccInfo['Origin'] = 'RECURRING';
        $ccInfo['UDField1'] = $desc;
        $ccInfo['TotalAmount'] = $totAmount;
        $ccInfo['OrderNumber'] = isset($xactionRequest['OrderNumber']) ? substr($xactionRequest['OrderNumber'], 0, 20) : date('My') . ' Charges';   //20 Charachtar Max- Appears on CC Statement - Default: SilverIP Comm
        $ccInfo['UDField2'] = isset($xactionRequest['UDField2']) ? $xactionRequest['UDField2'] : ''; // User defied feild - descripton of the charge, i.e. Signup
        $ccInfo['UDField3'] = isset($xactionRequest['UDField3']) ? $xactionRequest['UDField3'] :'';

        $ippayresult = array();
        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($ccInfo, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($ccInfo, 1);  //process card - 0 is for test server, 1 for live server
        }

        $resultArr['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $resultArr['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $resultArr['APPROVAL'] = $ippayresult['APPROVAL'];
        $resultArr['CVV2'] = $ippayresult['CVV2'];
        $resultArr['VERIFICATIONRESULT'] = $ippayresult['VERIFICATIONRESULT'];
        $resultArr['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $resultArr['ADDRESSMATCH'] = $ippayresult['ADDRESSMATCH'];
        $resultArr['ZIPMATCH'] = $ippayresult['ZIPMATCH'];
        $resultArr['AVS'] = $ippayresult['AVS'];
        $resultArr['ERRMSG'] = $ippayresult['ERRMSG'];

        $ippayresult['TransactionType'] = $ccInfo['TransactionType'];
        $ippayresult['Comment'] = $customer->Comment;

        if (isset($customer->CID) == false) {
            $customer->CID = 0;
        }

        $this->storeXaction($customer, $ippayresult, $details);
        return $resultArr;
    }

    public function updateToken($customer, $cardNum, $expMo, $expYr, $ccv = 0) {

        $ipPayHandle = new IpPay();

        // Create an array to pass to IPPay for processing
        $ccInfo = array();

        // Gather the IPPay response in the following array
        $resultArr = array();

        if($customer == null){
            $resultArr['TRANSACTIONID'] = '';   //Returns the unique tranaction ID
            $resultArr['ACTIONCODE'] = '900';     // 000 = Approved, else Denied
            $resultArr['APPROVAL'] = 'ERROR';
            $resultArr['CVV2'] = '';
            $resultArr['VERIFICATIONRESULT'] = '';
            $resultArr['RESPONSETEXT'] = 'ERROR';
            $resultArr['ADDRESSMATCH'] = '';
            $resultArr['ZIPMATCH'] = '';
            $resultArr['AVS'] = '';
            $resultArr['ERRMSG'] = 'Customer not found';
            return $resultArr;
        }

        $ccInfo['TransactionType'] = 'TOKENIZE';
        $ccInfo['TerminalID'] = $this->testMode ? 'TESTTERMINAL' : 'SILVERIPC001';   // silverip unique account id

        if ($customer->CCtoken == '') {
            $resultArr['ERRMSG'] = 'Missing CC Token. We can not process this request without a CC token.';
            return $resultArr;
        }

        $ccInfo['Token'] = $customer->CCtoken;
        if (isset($customer->CCnumber) == false || trim($customer->CCnumber) == '') {
            $resultArr['ERRMSG'] = 'Missing Card number. We can not process this request without a CC number.';
            return $resultArr;
        }

        if (isset($customer->CCscode) == false) {
            $resultArr['ERRMSG'] = 'Missing CVV2 number. We can not process this request without a CVV2 number.';
            return $resultArr;
        }

        if (isset($customer->Expmo) == false) {
            $resultArr['ERRMSG'] = 'Missing expiration month. We can not process this request without an expiration date.';
            return $resultArr;
        }

        if (isset($customer->Expyr) == false) {
            $resultArr['ERRMSG'] = 'Missing expiration year. We can not process this request without an expiration date.';
            return $resultArr;
        }

        if (strlen($customer->Expyr) == 4) {
            $ccInfo['CardExpYear'] = substr($customer->Expyr, 2);    // customer CC expire year - YY
        } else {
            $ccInfo['CardExpYear'] = $customer->Expyr;    // customer CC expire year - YY
        }

        $ccInfo['CardNum'] = $customer->CCnumber;
        $ccInfo['CardExpMonth'] = $customer->Expmo;
        $ccInfo['CVV2'] = $customer->CCscode;      // CC security code
        $ccInfo['CardName'] = $customer->Firstname.' '.$customer->Lastname;

        $ippayresult = array();
        if ($this->testMode == true) {
            $ippayresult = $ipPayHandle->process($ccInfo, 0);  //process card - 0 is for test server, 1 for live server	   		
        } else {
            $ippayresult = $ipPayHandle->process($ccInfo, 1);  //process card - 0 is for test server, 1 for live server
        }

        $resultArr['TRANSACTIONID'] = $ippayresult['TRANSACTIONID'];   //Returns the unique tranaction ID
        $resultArr['ACTIONCODE'] = $ippayresult['ACTIONCODE'];     // 000 = Approved, else Denied
        $resultArr['APPROVAL'] = $ippayresult['APPROVAL'];
        $resultArr['CVV2'] = $ippayresult['CVV2'];
        $resultArr['VERIFICATIONRESULT'] = $ippayresult['VERIFICATIONRESULT'];
        $resultArr['RESPONSETEXT'] = $ippayresult['RESPONSETEXT'];    // Approved or Denied
        $resultArr['ADDRESSMATCH'] = $ippayresult['ADDRESSMATCH'];
        $resultArr['ZIPMATCH'] = $ippayresult['ZIPMATCH'];
        $resultArr['AVS'] = $ippayresult['AVS'];
        $resultArr['ERRMSG'] = $ippayresult['ERRMSG'];

        $ippayresult['UDField1'] = 'CC Updated';
        $ippayresult['TransactionType'] = $ccInfo['TransactionType'];
        $ippayresult['Comment'] = $customer->Comment;

        $this->storeXaction($customer, $ippayresult, $details);
        return $resultArr;
    }

    public function hasCCExpired(Customer $customer) {
        $expDate = $customer->Expyr. '-' . $customer->Expmo;
        return ($expDate < time()) ? true : false;
    }


    //    ChargeCard, with customer id and amount and reason  - actual charge card
    //    ManualChargeCard - pass in a cc number and all info
    //    Charge Card to test server - use IPpay test server
    //    AuthCard - AuthCard to ensure its a valid card with credit
    //    RefundCard - Refund card with CustomerID and amount and reason
    //    ManualRefundCard - Refund a card with all the ccnumber passed along
    //    RefundCard-with TransactionID (refund an existing transaction)
    //    Add-to-TransactionLog
    //    CheckExpirationDate (check to see if card is expired or expiring)
    //    CheckCard-Format  (validate real card)
    //    Show-Last10-DeclinedCards
    //    Show-DeclinedCards-For-EachMonth
    //    Show-Last10-Transactions
    //    ShowAll-Transactions-For-User
    //    Show-Last10-Refunds
    //    Show-Last10-VoipCharges
    //    Email-Expiration-Notice
    //    Email-Monthly-Recipte
    //    Email-Annual-Renewal-recipt
    //    Email-Voip-Recipt
}

?>