<?php

namespace App\Extensions;

/***************************************************************
  This Module was created to send and receive information from the IPPay credit card processing servers

  It uses two associative arrays to conduct the transaction
  $ippay = The sending array with all valid fields for the transaction
  $xactionResult = The recieving array with all valid fields for the response

  The system also uses a global XML creation string called $ipPayXML where the XML envelope is constructed.

  The Sending mechanism is an SSL Secured HTTP POST transaction facilitated by CURL

  The module is installed by adding the following code to the processing page.
  require('ippay.php');

  The module is used by doing the following
  1: Populating the CASE-SENSITIVE $ippay array with the correct information as described below
  2: Invoking the ippay function to build and send the array
  3: Referencing the $xactionResult array to determine the success or failure of the transaction.

  NOTE:
  GLOBAL VARIABLES $ippay, $ipPayXML, and $xactionResult MAY NOT BE REFERENCED OR
  RECREATED IN ANY WAY THAT WOULD TAMPER WITH THE STRUCTURE OF THE CODE BELOW!!!!!!!!!!!!
 ***************************************************************/

// KEYS
// key named jptest1
define('IPPAY_CRYPTOPAN_KEY_TEST', 'jptest1');
define('IPPAY_CRYPTOPAN_KEY_TEST_PUBLIC_KEY', <<<KEYDATA
-----BEGIN PUBLIC KEY-----
MIIBIDANBgkqhkiG9w0BAQEFAAOCAQ0AMIIBCAKCAQEAqI7dBsRc29MHpf97DJ2g
AGkOF5w5fh2Yk0mc8xLJkexzwQsgnG+1hSycATFArDaAisXqbDplWcYD/nJAigW4
85FVYGgDZohV++iH9SKaCt8sFY3dlibrO7Py/K7xWbaMw0x7fmaSZyLTeheZmEYz
b5RQ641w2xf8fTPUDYb+CRpPzg+H51dM7U8tBmeBKB2/AYTV+jHW8sDYhVVxrQap
w6CbHBhT/3iC2bpdEV3vXaC9C6Y915uZTR6uzfKWRqBgCTZhKkdu0G+qbppW52PR
cG5SmlZGpLYEASSSLXHMTWNo0mtroO7KKIJS4RFuZV93ZsCcBHlEsX+0ll34ybUF
dQIBEQ==
-----END PUBLIC KEY-----
KEYDATA
      );

// key named jp_rsa_prod_01
define('IPPAY_CRYPTOPAN_KEY_PRODUCTION', 'jp_rsa_prod_01');
define('IPPAY_CRYPTOPAN_KEY_PRODUCTION_PUBLIC_KEY', <<<KEYDATA
-----BEGIN PUBLIC KEY-----
MIIBIDANBgkqhkiG9w0BAQEFAAOCAQ0AMIIBCAKCAQEAtfJxg396JIXBVE2BAF9T
9MIb73XWO6+CIHt0xRgy6xAOai9ryE741lTD7jz71CMLRc+IZwE+UtybeYcGdZCj
pN5HQHqcy3/nmmm131dCAuN2AzZ2j+Fqffrxvn1Y3AeyLLxCkhaeTaMmEP4VDdBe
jOKgmKi+iIsaYr+1eUy4NQq3Z3Y7UIWE4uSgsiyyd8PpS5MdmeYRzORIyIRovx5R
DR/vo78nvEWM3sZUCOWBCJyhBxyagtcYXulYhVOZ5RfN8E4pWHdyS84Bil2VlVmd
AsKygnE5pFA34deCBeWfOid/I3OL1cfhQ+DCvm37nnZ032ouJ69TX8/tiadenQYi
xQIBEQ==
-----END PUBLIC KEY-----
KEYDATA
      );

class IpPay {

    protected $xactionInfo;
    protected $xactionResult;
    protected $ipPayXML;

    public function __construct() {


        /***************************************************************
          Setup TransactionID.
          TransactionID is exactly 18 characters in length and is alphanumeric
          Current random value based around data and time then random characters
         ***************************************************************/
        $dtest = "IP" . date('dmhs');
        $alph = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        for ($i = 0; $i < 8; $i++) {
            $dtest = $dtest . $alph[rand(0, 25)];
        }

        /***************************************************************
          Removing certain fields that should never be NULL in the system
          This allows for better interchange qualification on the credit card side
         *****************************************************************/
        //Generating a random phone number (the first and 4th digits cannot be 0)
        $phonenull = NULL;
        for ($i = 0; $i < 10; $i++) {
            switch ($i) {
                case 0:
                case 3:
                    $phonenull = $phonenull . mt_rand(1, 9);
                    break;
                default:
                    $phonenull = $phonenull . mt_rand(0, 9);
            }
        }
        //Gennerating a randome invoice / order number from five digits.  (The first digit cannot be 0)
        $ordernull = NULL;
        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0:
                    $ordernull = $ordernull . mt_rand(1, 9);
                    break;
                default:
                    $ordernull = $ordernull . mt_rand(0, 9);
            }
        }

        /***************************************************************
          Generating global arrays for the sending and recption of information from the IPPay system
          $xactionInfo is an associative array with the correct XML fields assigned to each element in the array
          $xactionResult is an associative array witht he correct fields for the XMLresponse from the IPPay system
         *****************************************************************/

        $this->ccMask = NULL;
        $this->ccvMask = NULL;
        $this->cleanXML = NULL;

        //Setup IPPay Sending Array with NULL Keys
        $this->xactionInfo = array(
            'TransactionType' => NULL, //Type of transaction sent to the system. Options: SALE, AUTHONLY, CAPT, FORCE, VOID, CREDIT, CHECK, REVERAL, VOIDACH
            'TerminalID' => "SILVERIPC001", //Customer terminal identifier provided by the IPPay system.  Test TID: TESTMERCHANT
            'TransactionID' => $dtest, //Transaction identifier.  18 alpha-numeric characters $dtest is generated above to fill this value.
            'Approval' => NULL, //Requred on FORCE transactions.  Will match a approval code from a previous AUTHONLY response
            'RoutingCode' => NULL, //Value will be NULL unless pre-arranged with IPPay
            //            'Origin' => "INTERNET", //Type of system comminicating with IPPay.  Options: INTERNET (Default), RECURRING, POS, PHONE ORDER, MAIL ORDER
            'Origin' => NULL, //Type of system comminicating with IPPay.  Options: INTERNET (Default), RECURRING, POS, PHONE ORDER, MAIL ORDER
            'Password' => NULL, //Value will be NULL unless pre-arranged with IPPay
            'OrderNumber' => "SilverIP", //Internal Order number assigned by the billing system.  Value is generated above if no order number is given  - $ordernull
            'Token' => NULL, //Credit card token
            'Tokenize' => NULL, //Credit card token
            'CardNum' => NULL, //Credit card number with no spaced
            'CryptopanKey' => NULL, // NULL to disable encryption (DEFAULT). Otherwise, the key name from IPPAY_CRYPTOPAN_KEY_* above.
            'CVV2' => NULL, //CVV code with no spaces
            'Issue' => NULL, //Issue code is only used with European credit cards as an additional CVV style protection
            'CardExpMonth' => NULL, //Two-Digit expiration month (e.g. 02 = Feb, 07 = July, 12 = Dec)
            'CardExpYear' => NULL, //Two-Digit expiration year (e.g. 08 = 2008)
            'CardStartMonth' => NULL, //Value not currently used
            'CardStartYear' => NULL, //Vlaue not currently used
            'Track1' => NULL, //Track 1 data for swiped cards
            'Track2' => NULL, //Track 2 data for swiped cards
            'AccountType' => NULL, //Type of account for ACH transaction. Options: Checking (default), Savings, BusinessCK
            'SEC' => NULL, //Three letter code that identifies the nature of the ACH entry. REF: http://en.wikipedia.org/wiki/Automated_Clearing_House#Standard_entry_class_code
            'AccountNumber' => NULL, //The customer bank account number
            'ABA' => NULL, //The ABA routing code of the customer's bank
            'CheckNumber' => NULL, //The customer's check number for the transaction
            'Scrutiny' => NULL, //Level of ACH checking performed on the transaction
            'CardName' => NULL, //Name of the customer for credit and check transactions
            'DispositionType' => NULL, //Value will remain NULL unless instructed to change by IPPay
            'TotalAmount' => NULL, //Total amount of transaction. All decimal and symbols should be removed (e.g. 100 = $1.00 2378 = $23.78)
            'FeeAmount' => NULL, //Additional Fee amount included in transaction.  Typically used for a tip or similar charge.  FeeAmount is assumed to be included in TotalAmount and will NOT add to the total transaction
            'TaxAmount' => NULL, //Total Taxes that are included in transaction.  TaxAmount is assumed to be included in TotalAmount and will NOT add to the total transaction
            'BillingAddress' => NULL, //Customer's Billing street address
            'BillingCity' => NULL, //Customer's Billing City
            'BillingStateProv' => NULL, //Customer's Billing State
            'BillingPostalCode' => NULL, //Customer's Billing ZIP or Postal Code
            'BillingCountry' => NULL, //Customer's Billing Country.  Country codes follow the three-character ISO standard.  (e.g. USA is correct.  US is not)
            //            'BillingPhone' => $phonenull, //Customer's Billing phone number.  Randome phone number is generated above if this field is blank
            'BillingPhone' => NULL, //Customer's Billing phone number.  Randome phone number is generated above if this field is blank
            'Email' => NULL, //Customer's Email address
            'UserIPAddress' => NULL, //IP address of the unit sending the XML
            'UserHost' => NULL, //Host Name of the unit sending the XML
            'UDField1' => NULL, //User-Defined field.  Will the reported back to customer for internal reporting purposes
            'UDField2' => NULL, //User-Defined field.  Will the reported back to customer for internal reporting purposes
            'UDField3' => NULL, //User-Defined field.  Will the reported back to customer for internal reporting purposes
            'ActionCode' => NULL, //Value currently not used
            'IndustryType' => NULL, //General Marketing category of the business sending the transaction. Options: ECOMMERCE, RETAIL, MOTO, HOTEL, RESTAURANT, AUTORENTAL, AIRLINE, PARKING, QUASICASH
            'VerificationType' => NULL, //Field should be set to VbV for Verified by Visa / SecureCode transaction checking
            'CAVV' => NULL,
            'XID' => NULL,
            'ECI' => NULL,
            //            'CustomerPO' => $ordernull, //Customer Purchase Order number.  Value will be randomly generated above if NULL
            'CustomerPO' => NULL, //Customer Purchase Order number.  Value will be randomly generated above if NULL
            'ShippingMethod' => NULL, //Method of delivery to customer. OPTIONS: SAME DAY, OVERNIGHT, PRIORITY, GROUND, ELECTORNIC
            'ShippingName' => NULL, //Name of person taking delivery of item
            'Address' => NULL, //Customer's Billing street address
            'City' => NULL, //Customer's Billing City
            'StateProv' => NULL, //Customer's Billing State
            'Country' => NULL, //Customer's Billing Country.  Country codes follow the three-character ISO standard.  (e.g. USA is correct.  US is not)
            //            'Phone' => $phonenull                     //Customer's Billing phone number.  Randome phone number is generated above if this field is blank
            'Phone' => NULL                     //Customer's Billing phone number.  Randome phone number is generated above if this field is blank
        );

        //Setup IPPay reception Array with NULL keys
        $this->xactionResult = Array(
            'TRANSACTIONID' => NULL, //Transaction identifier returned from IPPay.  Should match previous transaction
            'ACTIONCODE' => NULL, //Decline code if the transaction is unsuccessful.  000 if approved
            'APPROVAL' => NULL, //Approval code returned from IPPay
            'CVV2' => NULL, //CVV2 result returned from IPPay.  M = CVV Match
            'VERIFICATIONRESULT' => NULL, //Verified by Visa / SecureCode Response
            'RESPONSETEXT' => NULL, //Error messege returned for debugging purposes
            'ADDRESSMATCH' => NULL, //Textual response descibing result. Options: Approved, Declined
            'ZIPMATCH' => NULL, //AVS address match code returned with respect to the ZIP code
            'AVS' => NULL, //AVS address match code returned with respect to the complete address
            'ERRMSG' => NULL                   //Error messege returned for debugging purposes. Only returned in the event of XML transaction failure
        );

        $this->rawXactionResult = array();

        /***************************************************************
         *     Setup base XML Build variable
         *         Tag <JetPay> is needed to start the XML and IS case sensitive.
         ****************************************************************/
        $this->ipPayXML = "<JetPay>";
    }

    /**
     * Encrypt data for cryptopan
     *
     * @param string The key to use one of IPPAY_CRYPTOPAN_KEY_PRODUCTION or IPPAY_CRYPTOPAN_KEY_TEST
     * @return string The encrypted data, in base64.
     */
    protected function cryptopan_encrypt($keyName, $dataToEncrypt) {
        switch ($keyName) {
            case IPPAY_CRYPTOPAN_KEY_TEST:
                $key = IPPAY_CRYPTOPAN_KEY_TEST_PUBLIC_KEY;
                break;
            case IPPAY_CRYPTOPAN_KEY_PRODUCTION:
                $key = IPPAY_CRYPTOPAN_KEY_PRODUCTION_PUBLIC_KEY;
                break;
            default:
                throw new Exception("Unknown key name: {$keyName}");
        }
        $key = openssl_pkey_get_public($key);
        if (!$key)
            throw new Exception("Key error.");
        $cleartext = <<<TPL
$dataToEncrypt
TPL;
        $ok = openssl_public_encrypt($cleartext, $crypttext, $key, OPENSSL_PKCS1_OAEP_PADDING);
        if (!$ok) {
            $msg = '';
            while ($str = openssl_error_string()) {
                $msg .= "$str\n";
            }

            throw new Exception("Error encrypting data: {$msg}");
        }
        $crypttext = base64_encode($crypttext);
        return $crypttext;
    }

    /***************************************************************
      Function to send data to IPPay Gateway
      Data is sent via SSL encapsulated POST transaction.  In this code CURL is used to facilitate transaction
      $url = Destination of POST transaction.  Either test or production gateways
      $data = Formed XML to send to appropriate gateway
     ***************************************************************/

    protected function sendHttp($url, $data) {

        // verify that the URL uses a supported protocol.
        if ((strpos($url, "http://") === 0) || (strpos($url, "https://") === 0)) {

            // Set the Header values
            $header[0] = "Content-type: text/xml";
            $header[1] = "Content-length: " . strlen($data);

            //Get info for UserAgent
            $defined_vars = get_defined_vars();

            //Set url and parameters
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
            } else {
                //                      curl_setopt($ch, CURLOPT_USERAGENT, 'Batch Job');
                curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7; rv:7.0.1) Gecko/20100101 Firefox/7.0.1');
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, "30");

            //Connect to url
            $result = curl_exec($ch);

            //Verify result or display connection problems
            if ($result == NULL) {
                error_log('Error:');
                error_log(curl_errno($ch) . " - " . curl_error($ch));
                return false;
                //                print "Error:<br>";
                //                print curl_errno($ch) . " - " . curl_error($ch) . "<br>";
                //                return "Error:<br>" . curl_errno($ch) . " - " . curl_error($ch);
            }

            curl_close($ch);

            return $result;
        }
    }

    /***************************************************************
      Function to build the XML string for sending to IPPay
      At certain points in the XML extra tags must be added to account for nested XML
      $key = Associative array key value & XML tag value
      $item = Contents of array field
     ***************************************************************/

    protected function buildxml($key, $item) {

        //        //Include ShippingInfo tag to account for nesting
        //        if ($key == "CustomerPO" && $item != NULL) {
        //            $this->ipPayXML .= "<ShippingInfo>";
        //        }
        //        //Add ShippingAddr tag to account for nesting
        //        if ($key == "Address" && $item != NULL) {
        //            $this->ipPayXML .= "<ShippingAddr>";
        //        }
        //Set up ACH transaction if the applicable ACH transaction type is detected.
        if ($key == "AccountType") {
            switch ($this->xactionInfo['TransactionType']) {
                case "CHECK":
                case "REVERSAL":
                case "VOIDACH":
                    $this->ipPayXML.= "<ACH Type=\"" . $item . "\"";
                    return;
                    break;
            }
        }
        //Add SEC code and close ACH Tag
        if ($key == "SEC") {
            switch ($this->xactionInfo['TransactionType']) {
                case "CHECK":
                case "REVERSAL":
                case "VOIDACH":
                    if ($item != NULL) {
                        $this->ipPayXML .= " SEC=\"" . $item . "\">";
                    } else {
                        $this->ipPayXML .= ">";
                    }
                    return;
                    break;
            }
        }
        //Add Industry Type as a descriptor inside "IndustryType" Tag
        if ($key == "IndustryType" && $item != NULL) {
            $this->ipPayXML .= "<IndustryInfo Type=\"" . $item . "\"></IndustryInfo>";
            return;
        }
        //Add VbV as descriptor if Verfied by Visa / Secure Code is being utilized
        if ($key == "VerificationType" && $item != NULL) {
            $this->ipPayXML .= "<Verification Type=\"" . $item . "\">";
            return;
        }
        if ($key == "CardNum" && $item != NULL) {
            $cryptoAttrs = NULL;
            if ($this->xactionInfo['CryptopanKey']) {
                $cryptoAttrs = ' Encrypted="true" KeyName="' . $this->xactionInfo['CryptopanKey'] . '" Encoding="BASE64"';
            } else if (isset($this->xactionInfo['Tokenize']) && $this->xactionInfo['Tokenize']) {
                $cryptoAttrs = ' Tokenize="true"';
            }
            $this->ipPayXML .= "<CardNum{$cryptoAttrs}>{$item}</CardNum>";
            $this->ccMask = 'XXXX-XXXX-XXXX-' . substr($item, -4);
            return;
        }
        if ($key == "CVV2" && $item != NULL) {
            $this->ccvMask = 'XXX';
        }
        if ($key == "CryptopanKey") {
            return; // skip this one, it's only used in context of CardNum above
        }

        //Standard add XML statment.  
        //If $key = one and $item = two then statement is <one>Two</one>
        if ($item != NULL) {
            $this->ipPayXML .= "<" . $key . ">" . $item . "</" . $key . ">";
        }
        //Close Verification tag if VbV / SecureCode are used.
        if ($key == "ECI" && $this->xactionInfo['VerificationType'] != NULL) {
            $this->ipPayXML .= "</Verification>";
        }
        //Close ACH tag if transaction is a check type transaction
        if ($key == "CheckNumber") {
            switch ($this->xactionInfo['TransactionType']) {
                case "CHECK":
                case "REVERSAL":
                case "VOIDACH":
                    $this->ipPayXML .= "</ACH>";
                    return;
                    break;
            }
        }
        //        //Close shipping tags upone completion of shipping info inclusion
        //        if ($key == "Phone") {
        //            $this->ipPayXML .= "</ShippingAddr></ShippingInfo>";
        //        }
    }

    public function tokenize($ccInfo, $testbol = 1, $fields = NULL) {

        $ccInfoRequest = array();
        $ccInfoRequest['TransactionType'] = 'TOKENIZE';
        $ccInfoRequest['CardNum'] = $ccInfo['CardNum'];
        $ccInfoRequest['CardExpMonth'] = $ccInfo['CardExpMonth'];
        $ccInfoRequest['CardExpYear'] = $ccInfo['CardExpYear'];

        return $this->rawProcess($ccInfoRequest, $testbol, $fields);
    }

    public function updateToken($ccInfo, $testbol = 1, $fields = NULL) {

        $ccInfoRequest = array();
        $ccInfoRequest['TransactionType'] = 'TOKENIZE';
        $ccInfoRequest['Token'] = $ccInfo['Token'];
        $ccInfoRequest['CardExpMonth'] = $ccInfo['CardExpMonth'];
        $ccInfoRequest['CardExpYear'] = $ccInfo['CardExpYear'];

        return $this->rawProcess($ccInfoRequest, $testbol, $fields);
    }

    public function rawProcess($ccInfo, $testbol = 1, $fields = NULL) {

        foreach ($ccInfo as $key => $value) {
            $this->xactionInfo[$key] = $value;
        }

        //Toggle between Test and Production Server
        if ($testbol == 0) {
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
            //            $url = 'https://test1.jetpay.com/jetpay';
            $url = 'https://testgtwy.ippay.com/ippay';
            $this->xactionInfo['TerminalID'] = 'TESTTERMINAL';
            $ccInfo['TerminalID'] = 'TESTTERMINAL';
        } else {
            $url = 'https://gtwy.ippay.com/ippay';
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
            $ccInfo['TerminalID'] = $this->xactionInfo['TerminalID'];
        }

        //        $this->ipPayXML = "<JetPay>";
        $this->ipPayXML = "<ippay>";
        //Step through array in order to build the XML.  See function buildXML
        foreach ($ccInfo as $key => $value) {
            if ($key == 'Tokenize') {
                continue;
            }
            $this->buildxml($key, $value);
        }
        //Finish XML with End tag once the XML has been populated by the array
        //        $this->ipPayXML .= "</JetPay>";
        $this->ipPayXML .= "</ippay>";

        $this->cleanXML = $this->ipPayXML;

        if ($this->ccMask != NULL) {
            $pattern = '/<CardNum\s*[^>*]>(\d+)<\/CardNum>/';
            $replacement = '<CardNum>' . $this->ccMask . '</CardNum>';
            $this->cleanXML = preg_replace($pattern, $replacement, $this->cleanXML);
        }

        if ($this->ccvMask != NULL) {
            $pattern = '/<CVV2>(\d+)<\/CVV2>/';
            $replacement = '<CVV2>' . $this->ccvMask . '</CVV2>';
            $this->cleanXML = preg_replace($pattern, $replacement, $this->cleanXML);
        }

        //        if ($testbol == 0) {
        error_log('IPPay:rawProcess(): $this->ipPayXML: ' . print_r($this->cleanXML, true));
        //        }
        $result = $this->sendHTTP($url, $this->ipPayXML);

        $this->rawParsing($result);

        error_log('IPPay:rawProcess(): $this->xactionResult: ' . print_r($this->xactionResult, true));

        return $this->rawXactionResult;
    }

    /***************************************************************
      //Function to coordinate the following
      1: Error checking to ensure proper values are passed
      2: XML Build
      3: Sending of XML
      4: Reception of information
      5: Reporting back to sender

      $this->xactionInfo: IPPay XML sending array
      $testbol: 0 = Test server, 1 = Production Server
     ***************************************************************/

    public function process($ccInfo, $testbol, $fields = NULL) {

        foreach ($ccInfo as $key => $value) {
            $this->xactionInfo[$key] = $value;
        }

      //Toggle between Test and Production Server
        if ($testbol == 0) {
            //        $url = 'https://test1.jetpay.com/jetpay'; 
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
            //            $url = 'https://test1.jetpay.com/jetpay';
            $url = 'https://testgtwy.ippay.com/ippay';
            $this->xactionInfo['TerminalID'] = 'TESTTERMINAL';
        } else {
            //          $url = 'https://gateway17.jetpay.com/jetpay';
            $url = 'https://gtwy.ippay.com/ippay';
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
        }

        //     $this->ippay['TransactionType'] = strtoupper($ccInfo['TransactionType']);
        //Error Checking on the Array for All transaction types
        if (is_null($this->xactionInfo['TransactionID'])) {
            $this->xactionResult['ERRMSG'] = "TransactionID required for ALL transactions.";
            return $this->xactionResult;
        }
        if (is_null($this->xactionInfo['TerminalID'])) {
            $this->xactionResult['ERRMSG'] = "TerminalID required for ALL transactions.";
            return $this->xactionResult;
        }

        //Error Checking on the Array for transaction type specific information
        switch ($this->xactionInfo['TransactionType']) {
                //Return error if not transaction type is supplied
            case NULL:
                $this->xactionResult['ERRMSG'] = "No TransactionType supplied.";
                return $this->xactionResult;
                //Return error if not approval code or card number is supplied
            case "VOID":
                if (is_null($this->xactionInfo['Approval'])) {
                    $this->xactionResult['ERRMSG'] = "No Approval Code supplied with VOID Transaction.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['CardNum'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number required with all VOID Transactions.";
                    return $this->xactionResult;
                }
                break;
                //Return error if no approval code is suppplied
            case "FORCE":
                if (is_null($this->xactionInfo['Approval'])) {
                    $this->xactionResult['ERRMSG'] = "No Approval Code supplied with FORCE Transaction.";
                    return $this->xactionResult;
                }
                //Return error if no Card Number or Expiration Date is supplied
            case "SALE":
            case "AUTHONLY":
            case "CREDIT":
                if (is_null($this->xactionInfo['CardNum']) && is_null($this->xactionInfo['Token'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number or Token required with SALE, AUTHONLY, and FORCE Transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['Token']) && (is_null($this->xactionInfo['CardExpMonth']) or is_null($this->xactionInfo['CardExpYear']))) {
                    $this->xactionResult['ERRMSG'] = "Card Expiration Date required with SALE, AUTHONLY, and FORCE Transactions without a TOKEN present.";
                    return $this->xactionResult;
                }
                break;
                //Return error if no Name, Account Type, SEC Code, Account Number, Routing Number or Check number is supplied
            case "CHECK":
            case "REVERSAL":
            case "VOIDACH":
                if (is_null($this->xactionInfo['CardName'])) {
                    $this->xactionResult['ERRMSG'] = "Customer Name is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['AccountType'])) {
                    $this->xactionResult['ERRMSG'] = "Account Type is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['SEC'])) {
                    $this->xactionResult['ERRMSG'] = "SEC Code is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['AccountNumber'])) {
                    $this->xactionResult['ERRMSG'] = "Account Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['ABA'])) {
                    $this->xactionResult['ERRMSG'] = "ABA Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['CheckNumber'])) {
                    $this->xactionResult['ERRMSG'] = "Check Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return $this->xactionResult;
                }
                break;
            case "TOKENIZE":
                if (is_null($this->xactionInfo['CardNum']) && is_null($this->xactionInfo['Token'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number or Token required with TOKENIZE Transactions.";
                    return $this->xactionResult;
                }
                if (is_null($this->xactionInfo['Token']) && (is_null($this->xactionInfo['CardExpMonth']) or is_null($this->xactionInfo['CardExpYear']))) {
                    $this->xactionResult['ERRMSG'] = "Card Expiration Date required with TOKENIZE Transactions.";
                    return $this->xactionResult;
                }
                break;
        }


        if ($this->xactionInfo['TransactionType'] != 'TOKENIZE') {
            if (is_null($this->xactionInfo['TotalAmount'])) {
                $this->xactionResult['ERRMSG'] = "TotalAmount required for all non-TOKENIZE transactions.";
                return $this->xactionResult;
            } else {
                //              $this->xactionInfo['TotalAmount'] = str_replace(".", "", $this->xactionInfo['TotalAmount']);
                $pos = strpos($this->xactionInfo['TotalAmount'], '.');
                if ($pos === false) {
                    $this->xactionInfo['TotalAmount'] .= '00';
                } else {
                    $this->xactionInfo['TotalAmount'] = str_replace('.', '', $this->xactionInfo['TotalAmount']);
                }

                if ($this->xactionInfo['TotalAmount'] <= 1) {
                    $this->xactionResult['ERRMSG'] = "Bad dollar amount. TotalAmount must be greater than 0.";
                    return $this->xactionResult;
                }
            }
        } else {
            $this->xactionInfo['OrderNumber'] = NULL;
            $this->xactionInfo['TransactionID'] = NULL;
        }


        //        $this->ipPayXML = "<JetPay>";
        $this->ipPayXML = "<ippay>";
        //Step through array in order to build the XML.  See function buildXML
        foreach ($this->xactionInfo as $key => $value) {
            if ($key == 'Tokenize') {
                continue;
            }
            $this->buildxml($key, $value);
        }
        //Finish XML with End tag once the XML has been populated by the array
        //        $this->ipPayXML .= "</JetPay>";
        $this->ipPayXML .= "</ippay>";
        $this->cleanXML = $this->ipPayXML;
        //Send XML and URL to processing function and recieve response. See function sendHTTP
        //        if ($testbol == 0) {

        if ($this->ccMask != NULL) {
            $pattern = '/(<CardNum\s*[^>]*>)(\d+)<\/CardNum>/';
            $replacement = '${1}' . $this->ccMask . '</CardNum>';
            $this->cleanXML = preg_replace($pattern, $replacement, $this->cleanXML);
        }

        if ($this->ccvMask != NULL) {
            $pattern = '/<CVV2>(\d+)<\/CVV2>/';
            $replacement = '<CVV2>' . $this->ccvMask . '</CVV2>';
            $this->cleanXML = preg_replace($pattern, $replacement, $this->cleanXML);
        }


        error_log('IPPay:process(): $this->ipPayXML: ' . print_r($this->cleanXML, true));
        //        }
        $result = $this->sendHTTP($url, $this->ipPayXML);


      $this->parsing($result);

        //        if ($testbol == 0) {
        error_log('IPPay:process(): $this->xactionResult: ' . print_r($this->xactionResult, true));
        //        }

        return $this->xactionResult;
    }

    /***************************************************************
      //Function to coordinate the following
      1: Error checking to ensure proper values are passed
      2: XML Build
      3: Sending of XML
      4: Reception of information
      5: Reporting back to sender

      $this->xactionInfo: IPPay XML sending array
      $testbol: 0 = Test server, 1 = Production Server
     ***************************************************************/

    public function charge($ccInfo, $testbol, $fields = NULL) {

        foreach ($ccInfo as $key => $value) {
            $this->xactionInfo[$key] = $value;
        }


        //Toggle between Test and Production Server
        if ($testbol == 0) {
            //        $url = 'https://test1.jetpay.com/jetpay'; 
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
            //            $url = 'https://test1.jetpay.com/jetpay';
            $url = 'https://testgtwy.ippay.com/ippay';
            $this->xactionInfo['TerminalID'] = 'TESTTERMINAL';
        } else {
            //          $url = 'https://gateway17.jetpay.com/jetpay';
            $url = 'https://gtwy.ippay.com/ippay';
            $this->xactionInfo['TransactionID'] = $this->GenerateTransactionID();  //added by farzad          
        }


        // DEBUG ONLY - Uncomment to see what's being passed
        //    $errors = array();
        //    $errors['field18'] = 'Here is the arraey that is passed to ippay: <br/>'.print_r($this->xactionInfo, true);
        //    $errors = array('error' => 1) + $errors;
        //    throw new FormValidateException($errors);
        //     $this->ippay['TransactionType'] = strtoupper($ccInfo['TransactionType']);
        //Error Checking on the Array for All transaction types
        if (is_null($this->xactionInfo['TransactionID'])) {
            $this->xactionResult['ERRMSG'] = "TransactionID required for ALL transactions.";
            return $this->xactionResult;
        }
        if (is_null($this->xactionInfo['TerminalID'])) {
            $this->xactionResult['ERRMSG'] = "TerminalID required for ALL transactions.";
            return $this->xactionResult;
        }



        //Error Checking on the Array for transaction type specific information
        switch ($this->xactionInfo['TransactionType']) {
                //Return error if not transaction type is supplied
            case NULL:
                $this->xactionResult['ERRMSG'] = "No TransactionType supplied.";
                return;
                break;
                //Return error if not approval code or card number is supplied
            case "VOID":
                if (is_null($this->xactionInfo['Approval'])) {
                    $this->xactionResult['ERRMSG'] = "No Approval Code supplied with VOID Transaction.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['CardNum'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number required with all VOID Transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['TotalAmount']) || $this->xactionInfo['TotalAmount'] < 1) {
                    $this->xactionResult['ERRMSG'] = "TotalAmount required for ALL transactions.";
                    return $this->xactionResult;
                } else {
                    $this->xactionInfo['TotalAmount'] = str_replace(".", "", $this->xactionInfo['TotalAmount']);
                }
                break;
                //Return error if no approval code is suppplied
            case "FORCE":
                if (is_null($this->xactionInfo['Approval'])) {
                    $this->xactionResult['ERRMSG'] = "No Approval Code supplied with FORCE Transaction.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['TotalAmount']) || $this->xactionInfo['TotalAmount'] < 1) {
                    $this->xactionResult['ERRMSG'] = "TotalAmount required for ALL transactions.";
                    return $this->xactionResult;
                } else {
                    $this->xactionInfo['TotalAmount'] = str_replace(".", "", $this->xactionInfo['TotalAmount']);
                }
                //Return error if no Card Number or Expiration Date is supplied
            case "SALE":
            case "AUTHONLY":
            case "CREDIT":
                if (is_null($this->xactionInfo['CardNum'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number required with SALE, AUTHONLY, and FORCE Transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['CardExpMonth']) or is_null($this->xactionInfo['CardExpYear'])) {
                    $this->xactionResult['ERRMSG'] = "Card Expiration Date required with SALE, AUTHONLY, and FORCE Transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['TotalAmount']) || $this->xactionInfo['TotalAmount'] < 1) {
                    $this->xactionResult['ERRMSG'] = "TotalAmount required for ALL transactions.";
                    return $this->xactionResult;
                } else {
                    $this->xactionInfo['TotalAmount'] = str_replace(".", "", $this->xactionInfo['TotalAmount']);
                }
                break;
                //Return error if no Name, Account Type, SEC Code, Account Number, Routing Number or Check number is supplied
            case "CHECK":
            case "REVERSAL":
            case "VOIDACH":
                if (is_null($this->xactionInfo['CardName'])) {
                    $this->xactionResult['ERRMSG'] = "Customer Name is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['AccountType'])) {
                    $this->xactionResult['ERRMSG'] = "Account Type is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['SEC'])) {
                    $this->xactionResult['ERRMSG'] = "SEC Code is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['AccountNumber'])) {
                    $this->xactionResult['ERRMSG'] = "Account Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['ABA'])) {
                    $this->xactionResult['ERRMSG'] = "ABA Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['CheckNumber'])) {
                    $this->xactionResult['ERRMSG'] = "Check Number is required for all CHECK, REVERSAL, VOIDACH transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['TotalAmount']) || $this->xactionInfo['TotalAmount'] < 1) {
                    $this->xactionResult['ERRMSG'] = "TotalAmount required for ALL transactions.";
                    return $this->xactionResult;
                } else {
                    $this->xactionInfo['TotalAmount'] = str_replace(".", "", $this->xactionInfo['TotalAmount']);
                }
                break;
            case "TOKENIZE":
                if (is_null($this->xactionInfo['CardNum'])) {
                    $this->xactionResult['ERRMSG'] = "Card Number required with SALE, AUTHONLY, and FORCE Transactions.";
                    return;
                    break;
                }
                if (is_null($this->xactionInfo['CardExpMonth']) or is_null($this->xactionInfo['CardExpYear'])) {
                    $this->xactionResult['ERRMSG'] = "Card Expiration Date required with SALE, AUTHONLY, and FORCE Transactions.";
                    return;
                    break;
                }
                break;
        }

        $this->ipPayXML = "<JetPay>";
        //    $this->ipPayXML = "<ippay>";   
        //Step through array in order to build the XML.  See function buildXML
        foreach ($this->xactionInfo as $key => $value) {
            $this->buildxml($key, $value);
        }
        //Finish XML with End tag once the XML has been populated by the array
        $this->ipPayXML .= "</JetPay>";
        //     $this->ipPayXML .= "</ippay>";
        //    error_log('$this->ipPayXML: '.print_r($this->ipPayXML,true));
        //    
        // DEBUG ONLY - Uncomment to see what's being passed
        //    $errors = array();
        //    $errors['field18'] = 'Here is the array that is passed to ippay: <br>'.print_r($this->xactionInfo, true). '<br>Here is the XML: <br>'.$this->ipPayXML;
        //    $errors = array('error' => 1) + $errors;
        //    throw new FormValidateException($errors);
        //     return array($url,$this->ipPayXML);
        //Send XML and URL to processing function and recieve response. See function sendHTTP
        //        error_log('IPPay:charge(): $this->ipPayXML: '.print_r($this->ipPayXML,true));
        $result = $this->sendHTTP($url, $this->ipPayXML);
        //        error_log('IPPay:charge(): $result: '.print_r($result,true));
        // DEBUG ONLY - Uncomment to see what's being passed
        //    $errors = array();
        //    $errors['field18'] = "Here is the result from ippay: \n".$result." \nAnd the XML that we pass to ippay: \n".$this->ipPayXML;
        //    $errors = array('error' => 1) + $errors;
        //    throw new FormValidateException($errors);
        //Send result to Parsing function
        //        error_lo(print_r($result, true));

        $this->parsing($result);
        return $this->xactionResult;
    }

    /**
     * Function to parse XML result from IPPay into the ippayresult array
     * Called from the IPPay function
     * $result = Response XML from IPPay 
     * */
    protected function rawParsing($result) {

        //Parse IPPay Response to Vals Array
        $xml_parser = xml_parser_create();
        $vals = array();
        xml_parse_into_struct($xml_parser, $result, $vals);

        //        error_log(print_r($vals,true));

        xml_parse($xml_parser, $result);

        xml_parser_free($xml_parser);

        //Parse VALS Array into IPPayResult Array
        for ($i = 0; $i < count($vals); $i++) {
            if ($vals[$i]['tag'] != "IPPayResponse") {
                if (!isset($vals[$i]['value'])) {
                    //                    error_log('IPPay:parsing(): $vals[$i][\'value\'] not set: ' . print_r($result, true));
                    //                    $this->xactionResult[$vals[$i]['tag']] = '';
                    continue;
                } else {
                    $this->rawXactionResult[$vals[$i]['tag']] = $vals[$i]['value'];
                }
            }
        }
    }

    /***************************************************************
      Function to parse XML result from IPPay into the ippayresult array
      Called from the IPPay function
      $result = Response XML from IPPay
     ***************************************************************/

    protected function parsing($result) {

        //Parse IPPay Response to Vals Array
        $xml_parser = xml_parser_create();
        $vals = array();
        xml_parse_into_struct($xml_parser, $result, $vals);

        //        error_log(print_r($vals,true));

        xml_parse($xml_parser, $result);

        xml_parser_free($xml_parser);

        //Parse VALS Array into IPPayResult Array
        for ($i = 0; $i < count($vals); $i++) {
            if ($vals[$i]['tag'] != "IPPayResponse") {
                //            if ($vals[$i]['tag'] != "JETPAYRESPONSE") {
                if (!isset($vals[$i]['value'])) {
                    //                    error_log('IPPay:parsing(): $vals[$i][\'value\'] not set: ' . print_r($result, true));
                    $this->xactionResult[$vals[$i]['tag']] = '';
                    continue;
                } else {
                    $this->xactionResult[$vals[$i]['tag']] = $vals[$i]['value'];
                }
            }
        }
    }

    // Added by Farzad
    protected function GenerateTransactionID() {
        $dtest = "IP" . date('dmhs');
        $alph = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
        for ($i = 0; $i < 8; $i++) {
            $dtest = $dtest . $alph[rand(0, 25)];
        }
        return $dtest;
    }

    protected function GenerateInvoiceNum() {
        $ordernull = NULL;
        for ($i = 0; $i < 5; $i++) {
            switch ($i) {
                case 0:
                    $ordernull = $ordernull . mt_rand(1, 9);
                    break;
                default:
                    $ordernull = $ordernull . mt_rand(0, 9);
            }
        }
        return $ordernull;
    }

    /***************************************************************
      Test variables to run against the IPPay system

      $ippay[TransactionType] = "sale";
      $ippay[TerminalID] = "TESTTERMINAL";  //SILVERIPC001
      $ippay[CardNum]  = "4000300020001000";
      $ippay[CardExpMonth] = "11";
      $ippay[CardExpYear] = "10";
      $ippay[TotalAmount] = "100";
      $ippay[CVV2] = "411";
      $ippay[BillingAddress] = "123 Anywhere Dr";
      $ippay[BillingCity] = "Anytown";
      $ippay[BillingStateProv] = "IL";
      $ippay[BillingPostalCode] = "60141";
      $ippay[BillingCountry] = "USA";
      print_r($ippay);
      ippay($ippay,0);
      echo $ipPayXML;
      print_r($xactionResult);
     ***************************************************************/
}

?>