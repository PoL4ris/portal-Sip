<?php

namespace App\Extensions;

use App\Models\NetworkNode;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\InvoiceLog;
use App\Models\Address;
use App\Models\CustomerProduct;
use App\Extensions\SIPBilling;
use DB;
use Hash;
use Illuminate\Support\MessageBag;
use Mail;
use Carbon\Carbon;

class BillingHelper {

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

    public function generateResidentialChargeRecords() {

        // Get residential buildings
        $buildings = Building::with(['properties' => function ($query) {
            $query->where('id_building_properties', config('const.3')
                ->where('value','Retail')
                ->orWhere('value','Bulk');
        }])
            ->where('id', 28)->get();

        $count = 0;

        foreach($buildings as $building){
            $invoiceDataTable = $this->generateBuildingInvoiceDataTable($building->id);
            $count = $this->addInvoicesToDatabase($invoiceDataTable);
            error_log('BillingHelper::generateInvoiceRecords(): Added invoices for '.$building->nickname.' to DB');
        }

        return 'Generated '.$count.' invoices and added them to the DB.';

    }

    public function generateResidentialInvoiceRecords() {

        // Get residential buildings
        $buildings = Building::with(['properties' => function ($query) {
            $query->where('id_building_properties', '3')
                ->where('value','Retail')
                ->orWhere('value','Bulk');
        }])
            ->where('id', 28)->get();

        $count = 0;

        foreach($buildings as $building){
            $invoiceDataTable = $this->generateBuildingInvoiceDataTable($building->id);
            $count = $this->addInvoicesToDatabase($invoiceDataTable);
            error_log('BillingHelper::generateInvoiceRecords(): Added invoices for '.$building->nickname.' to DB');
        }

        return 'Generated '.$count.' invoices and added them to the DB.';

    }

    public function processAutopayInvoices() {

        $nowMysql = date("Y-m-d H:i:s");
        $invoices = Invoice::where('status', 'pending')
            ->where('processing_type', '13')
            //            ->where('due_date', 'is', 'NULL')
            //            ->orWhere('due_date', '<=', $nowMysql)
            //            ->orWhere('due_date', '')            
            ->chunk(100, function ($invoices) {
                foreach ($invoices as $invoice) {
                    $this->processInvoice($invoice);
                    dd('Done');
                    break;
                }
            });
    }

    protected function processInvoice(Invoice $invoice){

        if(isset($invoice) == false){
            error_log('BillingHelper::processInvoice(): $invoice is not set!');
            return false;
        }

        if($invoice->amount <= 0){
            error_log('BillingHelper::processInvoice(): ERROR: Invalid invoice amount: '. $invoice->amount);
            return false;
        }

        // Charge the invoice
        $billingService = new SIPBilling();
        $chargeResult = $billingService->chargeCC($invoice->id_customers, $invoice->amount, 'invoice_id: '. $invoice->id, 'SilverIP Data', $invoice->details);

        $customer = Customer::find($invoice->id_customers);
        $invoiceDetails = ($invoice->details != '') ? json_decode($invoice->details, true) : null;              
        $customerProductIds = ($invoiceDetails != null) ? array_column($invoiceDetails, 'customer_product_id') : null;
        $transactionId = isset($chargeResult['TRANSACTIONID']) ? $chargeResult['TRANSACTIONID'] : NULL;

        if ($chargeResult['RESPONSETEXT'] == 'APPROVED') {

            error_log('BillingHelper::processInvoice(): INFO: id: '.$invoice->id_customers . ', ' . trim($customer->first_name) . ' ' . trim($customer->last_name) . ', $' . $invoice->amount . ', ' . 'invoice: ' . $invoice->id  . " ... Approved\n");

            if($customerProductIds != null){
                // Update the customer product/service's expiration and charge timestamps
                $updateCount = $this->updateCustomerProductDates($customerProductIds);
                error_log('BillingHelper::processInvoice(): INFO: Updated expiration dates for '.$updateCount.' products of invoice: ' . $invoice->id);
            } else {
                error_log('BillingHelper::processInvoice(): ERROR: Could not update expiration dates for invoice: ' . $invoice->id);
            }

            $this->logInvoice($invoice, 'processed', $transactionId);
            $this->sendInvoiceReceiptEmail($invoice, $chargeResult);
            Invoice::destroy($invoice->id);

        } else {

            error_log('BillingHelper::processInvoice(): INFO: id: '.$invoice->id_customers . ', ' . trim($customer->first_name) . ' ' . trim($customer->last_name) . ', $' . $invoice->amount . ', ' . 'invoice: ' . $invoice->id  . " ... Declined\n");

            $invoice->failed_charges_count++;
            $invoice->save();
            $this->logInvoice($invoice, 'failed', $transactionId);

            if($customerProductIds != null){
                // Update the customer product/service's charge timestamp only
                $updateCount = $this->updateCustomerProductDates($customerProductIds, true);
                error_log('BillingHelper::processInvoice(): INFO: Updated failed count for '.$updateCount.' product(s) of invoice: ' . $invoice->id);
            } else {
                error_log('BillingHelper::processInvoice(): ERROR: Could not update failed counts for invoice: ' . $invoice->id);
            }
            /*** Create a ticket ***/

            $this->sendChargeDeclienedEmail($invoice, $chargeResult);

            //            if ($testRun == false) {
            //                $ticketReason = 'Credit Card Declined for ' . date("M-Y") . ' (' . getFormattedPrice($amountToCharge) . ') --- TransID: ' . $chargeResult['TRANSACTIONID'] . ' - Action: ' . $chargeResult['ACTIONCODE'] . ' - Approval: ' . $chargeResult['APPROVAL'] . '- Response: ' . $chargeResult['RESPONSETEXT'];
            //                createTicket($cid, '18', $ticketReason, 'escalated', 0, false);
            //                addCustomerComment($cid, $ticketReason);
            //                flagCustomerAccount($cid, '1', 'CC Declined');
            //                foreach ($customer['LineItems'] as $lineItem) {
            //                    updateCustomerProductBillingFlag($lineItem['CSID'], $lineItem['BillingFlag'] - 1, date("M-Y") . ' Charge Delined', false);
            //                }
            //                if ($skipEmails == false) {
            //                    sendMonthlyDeclinedEmail($customer, $testRun);
            //                }
            //            }
        }
    }

    protected function logInvoice(Invoice $invoice, $status = NULL, $transactionId = NULL, $comment = NULL){
        // Log the invoice event
        $invoiceLog = new InvoiceLog;
        $invoiceLog->id_invoices = $invoice->id;
        $invoiceLog->id_customers = $invoice->id_customers;
        $invoiceLog->status = $status;
        $invoiceLog->id_transactions = $transactionId;
        $invoiceLog->comment = $comment;
        $invoiceLog->invoice_record = $invoice->toJson();
        $invoiceLog->save();
        return $invoiceLog->id;
    }

    protected function generateBuildingInvoiceDataTable($buildingId){

        // Create an invoice info table
        $customerInvoiceTable = array();

        // Get list of invoiceable products/services for the requested building
        $recordList = $this->getInvoiceableCustomerProductsByBuildingId($buildingId);

        // Go through the list and process the info
        foreach ($recordList as $record) {

            $cid = $record->customer_id;

            if (!isset($customerInvoiceTable[$cid])) {
                // This is the first time we are seeing this customer so create a new record in the table for them
                $customerInvoiceTable[$cid] = array();
                $customerInvoiceTable[$cid]['details'] = array();
                $customerInvoiceTable[$cid]['amount'] = 0;
                $customerInvoiceTable[$cid]['record'] = $record;
            }

            $customerInvoiceTable[$cid]['amount'] += $record->product_amount;
            $customerInvoiceTable[$cid]['details'][] = array(
                'customer_product_id' => $record->id,
                'product_id' => $record->id_products,
                'product_name' => $record->product_name,
                'product_desc' => $record->product_desc,
                'product_amount' => $record->product_amount,
                'product_frequency' => $record->product_frequency,
                'product_type' => $record->product_type
            );
        }
        return $customerInvoiceTable;
    }

    protected function addInvoicesToDatabase($invoiceDataTable){

        $count = 0;
        foreach($invoiceDataTable as $cid => $data){

            $customer = Customer::find($cid);
            $address_id = $data['record']->address_id;
            $address = Address::find($address_id);

            $firstDayofNextMonthTime = strtotime("first day of next month  00:00:00");
            $firstDayofNextMonthMysql = date("Y-m-d H:i:s", $firstDayofNextMonthTime);

            // Create a new invoice model and fill it up with data then save it
            $invoice = new Invoice;
            $invoice->name = trim($customer->first_name).' '.trim($customer->last_name);
            $invoice->address = trim($address->address);
            if(trim($address->unit != '')){
                $invoice->address .= "\n" . 'Apt '.$address->unit;
            }
            $invoice->address .= "\n" . trim($address->city) . ', ' . trim($address->state) . ' ' .trim($address->zip);
            $invoice->description = "New Invoice";
            $invoice->details = json_encode($data['details']);
            $invoice->amount = (strpos($data['amount'], '.') === false) ? $data['amount'].'.00' : $data['amount'];
            $invoice->id_customers = $cid;
            $invoice->id_address = $address_id;
            $invoice->status = 'pending';
            $invoice->failed_charges_count = 0;
            $invoice->bill_cycle_day = '1';
            $invoice->processing_type = '13';  // 13 = Autopay, 14 = Manual Pay
            $invoice->due_date = $firstDayofNextMonthMysql;
            $invoice->save();

            // Log this event in the invoice log table
            $this->logInvoice($invoice, 'generated');

            // Create a list of all the customer products that were just invoiced
            $lineItems = $data['details'];
            $customerProductIds = array();
            foreach($lineItems as $item){
                $customerProductIds[] = $item['customer_product_id'];
            }

            // Update the invoice status and invoice date of the customer products that were just invoiced
            $this->updateCustomerProductInvoiceStatus($customerProductIds);

            $count++;
        }

        return $count;
    }

    protected function updateCustomerProductInvoiceStatus($customerProductIds = array()){

        // Default to the first day of the month
        $firstDayofMonthTime = strtotime("first day of this month 00:00:00");
        $firstDayofMonthMysql = date("Y-m-d H:i:s", $firstDayofMonthTime);

        foreach($customerProductIds as $customerProductId){
            $customerProduct = CustomerProduct::find($customerProductId);            
            if($customerProduct->product->frequency == 'annual'){
                // Set the next invoiceable date to next year for annual products
                $timestampMysql = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayofMonthTime));
            }elseif($customerProduct->product->frequency == 'monthly'){
                // Set the next invoiceable date to next month for monthly products
                $timestampMysql = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayofMonthTime));
            } else {
                $timestampMysql = '0000-00-00 00:00:00';
            }

            $customerProduct->next_invoice_date = $timestampMysql;
            $customerProduct->invoice_status = '1';
            $customerProduct->amount_owed += $customerProduct->product->amount;
            $customerProduct->save();
        }
    }

    protected function updateCustomerProductDates($customerProductIds = array(), $updateChargeTimestampOnly = false){

        $firstDayOfMonth = mktime(0, 0, 0, date("m"), '01', date("Y"));
        $update_count = 0;
        foreach ($customerProductIds as $customerProductId) {    

            $customerProduct = CustomerProduct::find($customerProductId);

            if($updateChargeTimestampOnly == false){

                $dateExpires = '';
                $chargeFreq = $customerProduct->product->frequency;

                if ($chargeFreq == 'monthly') {
                    // Set the next expiration date to next month for monthly plans
                    $dateExpires = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayOfMonth));
                } else if ($chargeFreq == 'annual') {
                    // Set the next expiration date to next year for annual plans
                    $dateExpires = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayOfMonth));
                } else {
                    // Mark "onetime" purchases with a 2 to indicate "paid" status so they won't be charged again
                    $customerProduct->invoice_status = '2';
                    $dateExpires = date('Y-m-d H:i:s');
                }

                $customerProduct->expires = $dateExpires;
                $customerProduct->renewed_at = date('Y-m-d H:i:s');
                $customerProduct->amount_owed -= $customerProduct->product->amount;
            }

            $customerProduct->last_charged = date('Y-m-d H:i:s');
            $customerProduct->save();
            $update_count++;
        }
        return $update_count;
    }

    protected function sendInvoiceReceiptEmail(Invoice $invoice, $chargeResult) {
        $subject = 'Service Charge Receipt: ' . date('F') . ' ' . date('Y');
        $template = 'email.template_customer_invoice_receipt';
        $this->sendInvoiceResponseEmail($invoice, $chargeResult, $subject, $template);
    }

    protected function sendChargeDeclienedEmail(Invoice $invoice, $chargeResult) {
        $template = 'email.template_customer_charge_declined';
        if($chargeResult['PaymentType'] == 'Credit Card'){
            $subject = 'NOTICE: Credit Card Declined';
        } elseif($chargeResult['PaymentType'] == 'Checking Account'){
            $subject = 'NOTICE: ACH Declined';
        } else {
            $subject = 'NOTICE: Charge Declined';
        }
        $this->sendInvoiceResponseEmail($invoice, $chargeResult, $subject, $template);
    }

    protected function sendInvoiceResponseEmail(Invoice $invoice, $chargeResult, $subject, $template) {

        $customer = Customer::find($invoice->id_customers);
        // If no customer was found create a Customer model populate it from the name col and send it in

        $address = Address::find($invoice->id_address);
        // If no address was found create an Address model populate it from the address col and send it in

        $toAddress = ($this->testMode) ? 'peyman@silverip.com' : $customer->email;

        $lineItems = ($invoice->details != '') ? json_decode($invoice->details, true) : array();
        $chargeDetails = array();
        $chargeDetails['TRANSACTIONId'] = $chargeResult['TRANSACTIONID'];
        $chargeDetails['PaymentType'] = $chargeResult['PaymentType'];
        $chargeDetails['PaymentTypeDetails'] = $chargeResult['PaymentTypeDetails'];

        Mail::send(array('html' => $template), ['customer' => $customer, 'address' => $address, 'lineItems' => $lineItems, 'chargeDetails' => $chargeDetails], function($message) use($toAddress, $subject, $customer, $address, $lineItems, $chargeDetails) {
            $message->from('help@silverip.com', 'SilverIP Customer Care');
            $message->to($toAddress, trim($customer->first_name).' '.trim($customer->last_name))->subject($subject);
        });        
    }

    public function getInvoiceableCustomerProductsByBuildingId($building_id) {
        return $this->getInvoiceableCustomerProducts(null, $building_id);
    }

    public function getInvoiceableCustomerProductsByCustomerId($customer_id) {
        return $this->getInvoiceableCustomerProducts($customer_id);
    }

    protected function getInvoiceableCustomerProducts($customerId = null, $buildingId = null) {

        // First day of next month to check expirations against
        $firstDayofNextMonthMysql = date("Y-m-d H:i:s", strtotime("first day of next month 00:00:00"));

        $queryBuilder = Customer::join('customer_products', 'id_customers', '=', 'customers.id')
            ->join('products', 'customer_products.id_products', '=', 'products.id')
            ->join('address', 'address.id_customers', '=', 'customers.id')
            ->join('buildings', 'address.id_buildings', '=', 'buildings.id')
            // customer MUST be active
            ->where('customers.id_status', '=', '2')
            // the customer's product/service MUST be active
            ->where('customer_products.id_status', '=', '3')
            // customer's product/service MUST be expiring before the first day of next month
            ->where('customer_products.expires', '<=', $firstDayofNextMonthMysql)
            // skip customer's product/service that are complimentary
            ->where('products.amount', '>', 0);

        if($customerId != null){
            $queryBuilder = $queryBuilder->where('customers.id', '=', $customerId);
        }

        if($buildingId != null){
            $queryBuilder = $queryBuilder->where('buildings.id', '=', $buildingId);
        }

        $queryBuilder = $queryBuilder->where(function($query) use ($firstDayofNextMonthMysql) {
            $query->where(function($query2) {
                // Get 'onetime' products that have not been invoiced (status = 0)
                $query2->where('customer_products.invoice_status', '<', 1)
                    ->where('products.frequency', '=', 'onetime');
            })->orWhere(function($query3) use ($firstDayofNextMonthMysql) {
                // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
                $query3->where('customer_products.invoice_status', '<', 2)
                    ->where('products.frequency', '<>', 'onetime')
                    ->where('products.frequency', '<>', 'included')
                    ->where('customer_products.next_invoice_date', '<', $firstDayofNextMonthMysql);
            });    
        });

        return $queryBuilder
            ->get(array('customers.id as customer_id'
                        ,'buildings.id as building_id'
                        , 'address.id as address_id'
                        , 'buildings.nickname as building_code'
                        , 'products.name as product_name'
                        , 'products.description as product_desc'
                        , 'products.amount as product_amount'
                        , 'products.frequency as product_frequency'
                        , 'products.id_types as product_type'
                        , 'customer_products.*'));
    }

    protected function createTicket($newTicketCID, $newTicketIssue, $newTicketDetails, $newTicketStatus, $AdminUser_ID, $sendEmail = false, $vendorTID = '')
    {

        $issue = $newTicketIssue;

        if ($issue != '' && $newTicketDetails != '' && $newTicketStatus != '') {

            //SQL INSERT TICKET
            $ticketItemArray = array();
            $ticketItemArray[] = "`CID` = '" . $newTicketCID . "'";
            $ticketNumber = '';

            //         $ticketNumberSql = "SELECT max(TicketNumber) AS ticket_id FROM supportTickets";
            $ticketNumberSql = "SELECT TicketNumber AS ticket_id FROM supportTickets where TID in (SELECT max(TID) FROM supportTickets)";
            $maxTnumberRes = mysql_query($ticketNumberSql) or die(mysql_error());
            $rowTnumber = mysql_fetch_array($maxTnumberRes);
            if ($rowTnumber['ticket_id']) {
                $maxTNumberArr = explode("-", $rowTnumber['ticket_id']);
                $maxTNumber = $maxTNumberArr[1] + 1;
                $ticketNumber = 'ST-' . $maxTNumber;
            }

            $ticketItemArray[] = "`TicketNumber` = '" . $ticketNumber . "'";
            if ($vendorTID != '') {
                $ticketItemArray[] = "`VendorTID` = '" . $vendorTID . "'";
            }
            $ticketItemArray[] = "`RID` = '" . $issue . "'";
            $ticketItemArray[] = "`Comment` = '" . sanitize($newTicketDetails, true) . "'";
            $ticketItemArray[] = "`Status` = '" . $newTicketStatus . "'";

            // Get the Staff ID from the left pane
            $ticketItemArray[] = "`StaffID` = '" . $AdminUser_ID . "'";

            $date = new DateTime();
            $currTimestamp = $date->format('Y-m-d H:i:s');
            $ticketItemArray[] = "`DateCreated` = '" . $currTimestamp . "'";

            $imploded_array = implode(",", $ticketItemArray);
            $strSQL = "INSERT INTO `supportTickets` SET " . $imploded_array;

            //         echo 'Ticket Insertion SQL: '.$strSQL .'<br/>';

            $result = mysql_query($strSQL);
            $ticketID = mysql_insert_id();

            if ($sendEmail) {
                $reasonInfoSql = "SELECT * FROM supportTicketReasons WHERE `RID` = '" . $issue . "'";
                $reasonInfoRes = mysql_query($reasonInfoSql) or die(mysql_error());
                $reasonInfoRow = mysql_fetch_array($reasonInfoRes);
                $customerInfo = getCustomerWithLocInfoByCID($newTicketCID);
                $adminUserInfo = getAdminUserByID($AdminUser_ID);

                $mail_config ['emailHeader'] = 'New Support Ticket';
                $mail_config ['fields']['Ticket Status'] = ucfirst($newTicketStatus);

                if ($newTicketCID == '0') {
                    $mail_config ['fields']['Name'] = 'Unknown';
                } else {
                    $mail_config ['fields']['Name'] = $customerInfo['Firstname'] . ' ' . $customerInfo['Lastname'];
                }

                $mail_config ['fields']['Ticket'] = '<a href="https://admin.silverip.net/customerinfo/browser_detect.php?tid=' . $ticketID . '">' . $ticketNumber . '</a>';
                $mail_config ['fields']['Timestamp'] = date("g:i a M j, Y ", strtotime($currTimestamp));
                if(trim($customerInfo['Address']) != ''){
                    $mail_config ['fields']['Address'] = $customerInfo['Address'] . ', #' . $customerInfo['Unit'];
                }
                if(trim($customerInfo['Tel']) != ''){
                    $mail_config ['fields']['Phone'] = $customerInfo['Tel'];
                }
                if(trim($customerInfo['Email']) != ''){
                    $mail_config ['fields']['Email'] = $customerInfo['Email'];
                }

                $mail_config ['fields']['Call Taker'] = $adminUserInfo['Name'];
                $mail_config ['fields']['Reason For Calling'] = $reasonInfoRow['ReasonName'];
                $mail_config ['fields']['Call Details'] = $newTicketDetails;
                $mail_config ['Unit'] = $customerInfo['Unit'];
                $mail_config ['ReasonCode'] = $reasonInfoRow['ReasonCategory'];

                //            $serviceLocationInfoSql = "SELECT * FROM serviceLocation WHERE `LocID` = '" . $customerInfo['LocID'] . "'";
                //            $serviceLocationInfoRes = mysql_query($serviceLocationInfoSql) or die(mysql_error());
                //            $serviceLocationInfoRow = mysql_fetch_array($serviceLocationInfoRes);

                $mail_config ['LocCode'] = $customerInfo['ShortName'];
                $mail_config ['recipient'] = array('Silver Support Portal' => 'help@silverip.com');
                $mail_config['senderName'] = 'New Ticket';
                $mail_config['serverSenderName'] = $mail_config['senderName'];
                $mail_config['serverSenderEmail'] = 'noreply@silverip.net';
                $mail_config['emailServerHostname'] = 'mail.silverip.net';


                sendSipEmail($mail_config);
            }

            return $ticketNumber;
        }

        return false;
    }
}
