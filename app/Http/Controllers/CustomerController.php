<?php

namespace App\Http\Controllers;

//use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Http\Request;
/*
 *  Models
 */

use App\Models\Note;
use App\Models\Product;
use App\Models\PaymentMethod;
use App\Models\Customer;
use App\Models\TicketNote;
use App\Models\Reason;
use App\Models\CustomerProduct;
use App\Models\Ticket;
use App\Models\Address;
use App\Models\Contact;
use App\Models\BillingTransactionLog;
use App\Models\Invoice;
use App\Models\Building;
//use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\ActivityLog;
use App\Models\Charge;
use App\Extensions\SIPCustomer;
use App\Extensions\BillingHelper;

/*
 * Extensions
 */

use DB;
use Log;
use Schema;
use Auth;
use ActivityLogs;
use SendMail;

/**
 * Class CustomerController
 * @package App\Http\Controllers
 * Auth::user()->id = Logged User id.
 */
class CustomerController extends Controller {

    protected $logType;
    protected $sipCustomer;

    public function __construct()
    {
        $this->middleware('auth');
        $this->logType = 'customer';
        $this->sipCustomer = new SIPCustomer();
    }

    public function addCustomer(Request $request)
    {
        dd($request);
    }

    /**
     * @param Request $request
     * querySearch = String to search on:
     * Code, Unit, First name, Last name, email,
     * @return Array of Customers that match criteria.
     */
    public function getCustomersSearch(Request $request)
    {
        $string = $request->querySearch;
        $select = "SELECT * FROM address INNER JOIN customers ON address.id_customers = customers.id ";
        $limit = ' limit 50';
        $arrX = array();
        $arrY = ' ';
        $whereFlag = false;
        $pattern = '/([0-9])\w+/';
        $stringArray = explode(' ', $string);

        foreach ($stringArray as $index => $item)
        {
            preg_match($pattern, $item, $patternItemResult);

            if (count($patternItemResult) == 0)
                $arrX[$index] = " AND ( address.code like '%" . $item . "%' or address.unit like '%" . $item . "%' or customers.first_name like '%" . $item . "%' or customers.last_name like '%" . $item . "%' or customers.email like '%" . $item . "%')";
            else
            {
                if ($whereFlag)
                    $arrX['where'] .= " AND (address.code like '%" . $patternItemResult[0] . "%' OR address.unit like '%" . $patternItemResult[0] . "%') ";
                else
                {
                    $whereFlag = true;
                    $arrX['where'] = " where (address.code like '%" . $patternItemResult[0] . "%' OR address.unit like '%" . $patternItemResult[0] . "%') ";
                }
            }
        }

        if ($whereFlag)
        {
            $tmpWhere = $arrX['where'];
            unset($arrX['where']);
        }

        $arrY .= $whereFlag ? $tmpWhere : '';

        foreach ($arrX as $idx => $or)
            $arrY .= $or;

        return DB::select($select . $arrY . $limit);
    }

    /**
     * @param Request $request
     * id = id_customers to get data.
     * @return Customer collection with relations.
     * RENAME TO getCustomerData at the very end.
     */
    public function customersData(Request $request)
    {
        return Customer::with('addresses',
            'contacts',
            'type',
            'address.buildings',
            'address.buildings.neighborhood',
            'status',
            'status.type',
            'openTickets',
            'log',
            'log.user')
            ->find($request->id);
    }

    /**
     * @param Request $request
     * id = id_customers to get Status.
     * @return Customer Status.
     */
    public function getCustomerStatus(Request $request)
    {
        return Customer::with('status')->find($request->id)['status'];
    }

    /**
     * @param Request $request .
     * id = id_customers
     * note = Note to add to customer;
     * @return Notes attached to this customer.
     */
    public function insertCustomerNote(Request $request)
    {
        $note = new Note;
        $note->comment = $request->note;
        $note->created_by = Auth::user()->id;
        $note->id_customers = $request->id;
        $note->save();

        return Note::where('id_customers', $request->id)->get();
    }

    /**
     * @param Request $request
     * id = id_customers to get all his notes.
     * @return Customer Notes.
     */
    public function getCustomerNotes(Request $request)
    {
        return Note::where('id_customers', $request->id)->get();
    }

    /**
     * @param Request $request
     * id = id_customers to search info and reset password
     * Take the Customer Contact Value(phone number) and bcrypts the value as a new password
     * @return response OK and New password.
     */
    public function resetCustomerPassword(Request $request)
    {
        $customer = Customer::with('contact')->find($request->id);
        $match = preg_split('/[^0-9]+/', $customer->contact->value);

        foreach ($match as $item)
        {
            if (isset($result))
                $result .= $item;
            else
                $result = $item;
        }

        $customer->password = bcrypt($result);
        $customer->password_updated = 1;
        $customer->save();

        //ADD ACTIVITY LOG HERE
        return ['response' => 'OK', 'password' => $result];
    }

    /**
     * @param Request $request
     * id = id_customers to get all contacts related to this customer.
     * @return Customer contacts.
     */
    public function getCustomerContactData(Request $request)
    {
        return Customer::with('contacts')->find($request->id);
    }

    /**
     * @return Types of Contact:
     * Mobile Phone
     * Home Phone
     * Fax
     * Work Phone
     * Email
     */
    public function getContactTypes()
    {
        return ContactType::get();
    }

    /**
     * @param Request $request
     * id = id_customers to get Default = 1,  Payment Method.
     * @return array(default payment method , properties of default payment method).
     */
    public function getDefaultPaymentMethod(Request $request)
    {
        $customer = Customer::find($request->id);

        return ($customer->defaultPaymentMethod != null) ? [$customer->defaultPaymentMethod, $customer->defaultPaymentMethod->getProperties()] : [];
    }

    /**
     * @param Request $request
     * id = id_customers to get all Payment Methods of this Customer.
     * @return Array of Payment Methods of the selected Customer.
     */
    public function getAllPaymentMethods(Request $request)
    {
        $customer = Customer::find($request->id);

        return $customer->allPaymentMethods;
    }

    /**
     * @param Request $request
     * id = Payment method id to set this as a default payment method
     * customerID = id_customers to find and update payment methods.
     * @return Payment methods of the Customer selected.
     */
    public function setDefaultPaymentMethod(Request $request)
    {
        $customer = Customer::find($request->customerID);
        $oldDefaultPm = $customer->defaultPaymentMethod;

        // Set new default payment method
        PaymentMethod::where('id', $request->id)->update(['priority' => 1]);

        // Deactivate other payment methods
        PaymentMethod::where('id_customers', $request->customerID)
            ->where('id', '!=', $request->id)
            ->update(['priority' => 0]);

        $newDefaultPm = $customer->defaultPaymentMethod;

        $data = $newDefaultPm->getProperty('last four');

        $newData = array();
        $newData['priority'] = 1;

        ActivityLogs::add($this->logType, $request->customerID, 'update', 'updatePaymentMethods', $oldDefaultPm, $newDefaultPm, $data, ('update-payment'));

        return $customer->allPaymentMethods;
    }

    /**
     * @param Request $request
     * id = id_customers to find all tickets related to.
     * @return Customer with all his tickets.
     * REFACTOR NAME getCustomerTickets
     */
    public function getNewTicketData(Request $request)
    {
        return Customer::with('tickets')->find($request->id);
    }

    /**
     * @param Request $request
     * id = id_customers to get all ticket history.
     * @return Customer with all his ticket history.
     */
    public function getTicketHistory(Request $request)
    {
        $customer = new Customer;

        return $customer->getTickets($request->id);
    }

    /**
     * getTicketHistoryNotes:
     */
    public function getTicketHistoryNotes(Request $request)
    {
        return TicketNote::find($request->id);
    }//REMOVE FROM ROUTES TO BE ABLE TO REMOVE FROM HERE NOT IN USE ANYMORE.VERIFY THIS.

    /**
     * @param Request $request
     * id = id_reason to find in reasons table.
     * @return Reason requested.
     */
    public function getTicketHistoryReason(Request $request)
    {
        return Reason::find($request->id);
    }

    /**
     * @param Request $request
     * id = id_customers to get invoice huistory records.
     * @return Invoice history of requested customer.
     */
    public function getInvoiceHistory(Request $request)
    {
        $invoiceStatusArrayMap = array_flip(config('const.invoice_status'));
        $invoices = Invoice::with('address')->where('id_customers', $request->id)->get();

        $invoices->transform(function ($invoice, $key) use ($invoiceStatusArrayMap) {
            $invoice->status = $invoiceStatusArrayMap[$invoice->status];

            return $invoice;
        });

        return $invoices;
    }

    /**
     * @param Request $request
     * id = id_customers to get all billing transaction log.
     * @return Billing transaction log of requested Customer.
     */
    public function getBillingHistory(Request $request)
    {
        return billingTransactionLog::where('id_customers', $request->id)->get();
    }

    /**
     * @param Request $request
     * id = id_customers to get Customer services.
     * @return Customer services of requested Customer
     */
    public function getCustomerServices(Request $request)//FIX IDCUSTOMER TO ID ON HTTP REQUEST.
    {
        $customer = Customer::with('services', 'services.product')->find($request->id ? $request->id : $request->idCustomer);
        if ($customer == null)
        {
            return $customer;
        }

        $customerArray = $customer->toArray();
        $updatedCustomerProducts = collect($customerArray['services'])->map(function ($customerProduct, $key) {
            $customerProduct['created_at'] = date('c', strtotime($customerProduct['created_at']));
            $customerProduct['expires'] = ($customerProduct['expires'] != null && $customerProduct['expires'] != '') ? date('c', strtotime($customerProduct['expires'])) : $customerProduct['expires'];

            return $customerProduct;
        });

        $customerArray['services'] = $updatedCustomerProducts->toArray();

        return $customerArray;
    }

    /**
     * @param Request $request
     * id = id of customer product to get data of.
     * @return Products of customer product.
     */
    public function getCustomerProduct(Request $request)
    {
        return CustomerProduct::with('product')->find($request->id);
    }

    /**
     * @param Request $request
     * id = id product to get status.
     * @return Status of requested product.
     */
    public function getCustomerProductType(Request $request)
    {
        return CustomerProduct::with('status')->find($request->id);
    }

    /**
     * getCustomerBuilding
     */
    public function getCustomerBuilding(Request $request)//REMOVE FROM ROUTES TO BE ABLE TO REMOVE FROM HERE NOT IN USE ANYMORE.VERIFY THIS.
    {

        print '<pre>';
        print_r(Building::find($request->id)->toArray());
        die();


        return CustomerProduct::with('status')->find($request->id);
    }

    /**
     * @param Request $request
     * id = id_customers to get network info
     * @return network info of requested Customer.
     */
    public function getCustomerNetwork(Request $request)// VERIFY  " $customer = new Customer; " IF ITS NEEDED OR ITS EXTRA.
    {
        $customer = new Customer;
        $customer = Customer::find($request->id);
        $netInfo = $customer->getNetworkInfo();

        return $netInfo;
    }

    /**
     * @param $id
     * id = id product to get Port attached to the customer.
     * @return port id of the requested Customer-Product.
     */
    public function getPortID($id)
    {
        return CustomerProduct::with('port')->where('id_customers', $id)->get()[0]->id;
    }

    /**
     * getCustomerList
     */
    public function getCustomerList()//VERIFY USAGE, AND REMOVE FROM ROUTES, CUSTOMERCONTROLLER.PHP AND CUSTOMERCONTROLLER.JS->NG-CONTROLLER. VERIFY THIS.
    {
        return Ticket::with('customer', 'address')->orderBy('created_at', 'asc')->where('id_customers', '!=', 1)->groupBy('id_customers')->take(100)->get();

        return Customer::all()->take(100);
    }

    /**
     * getCustomerList
     */
    public function getAddress()//VERIFY USAGE, AND REMOVE FROM ROUTES, CUSTOMERCONTROLLER.PHP. VERIFY THIS.
    {
        return Address::groupBy('id_buildings')->get();
    }

    /**
     * @param Request $request
     * id = id_customers to update.
     * id_table = id of record table.
     * value = value to insert on (name, value...)
     * field = direct field of the table DB --> Unit
     * table = table name to update on the DB
     * @return OK if any errors Occured.
     */
    public function updateAddressTable(Request $request)
    {
        $newData = array();
        $hasHashtag = explode('#', $request->value);

        $newData[$request->field] = (count($hasHashtag) == 1) ? $hasHashtag[0] : $hasHashtag[1];

        $addressExist = Address::find($request->id_table);
        $addressExist->unit = (count($hasHashtag) == 1) ? $hasHashtag[0] : $hasHashtag[1];
        $addressExist->save();

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateAddressTable', $addressExist, $newData, null, 'update-unit');

        return 'OK';
    }

    /**
     * @param Request $request
     * id = id_customers to update.
     * field = name of the field direct on the DB
     * value = value of the field.
     * @return string OK
     */
    public function updateCustomersTable(Request $request)
    {
        $params = $request->all();
        $newData = array();
        $newData[$params['field']] = $params['value'];

        // Get current customer data
        $currrentData = Customer::find($request->id)->toArray();

        // Update customer data
        Customer::where('id', $request->id)->update($newData);

        // Log this activity
        ActivityLogs::add($this->logType, $request->id, 'update', 'updateCustomersTable', $currrentData, $newData, null, ('update-' . $params['field']));

        return 'OK';
    }

    /**
     * @param Request $request
     * id = id contact to find and update.
     * value = new value of the contact.
     * @return OK for updated record.
     */
    public function updateContactInfo(Request $request)
    {
        if (empty($request->value))
            return 'ERROR';

        $record = Contact::find($request->id);
        $record->value = $request->value;
        $record->save();

        return 'OK';

    }

    /**
     * @param Request $request
     * id = id_customer to update record.
     * id_table = record to find and update.
     * table = table name
     * value = value to set on the update
     * field = name of the field on the DB
     * @return OK for updated record.
     */
    public function updateContactsTable(Request $request)
    {
        $newData = array();
        $newData[$request->field] = $request->value;

        $contactExist = Contact::find($request->id_table);
        $contactExist->value = $request->value;
        $contactExist->save();

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateContactsTable', $contactExist, $newData, null, 'update-contact');

        return 'OK';

    }

    /**
     * @param Request $request
     * id = id_customers to find
     * @return customer data.
     */
    public function getCustomerDataTicket(Request $request)
    {
        return Customer::find($request->id);
    }

    /**
     * @param Request $request
     * idProduct = id product to get frequency.
     * idCustomer = id_customers to add new service.
     * Status 1 = Active Product.
     * @return Customer Services list.
     */
    public function insertCustomerService(Request $request)
    {
        $when = $this->getTimeToAdd(Product::find($request->idProduct)->frequency);
        $expires = null;

        if ($when != null)
        {
            $expires = date("Y-m-d H:i:s", strtotime($when . ' 00:00:00'));
        }

        $customer = Customer::find($request->idCustomer);
        $address = $customer->address;

        $newProduct = new CustomerProduct();
        $newProduct->id_customers = $request->idCustomer;
        $newProduct->id_products = $request->idProduct;
        $newProduct->id_status = config('const.status.active');
        $newProduct->id_address = ($address != null) ? $address->id : 0;
        $newProduct->signed_up = date("Y-m-d H:i:s");
        $newProduct->expires = $expires;
        $newProduct->id_users = Auth::user()->id;
        $newProduct->save();

        $relationData = Product::find($request->idProduct);

        ActivityLogs::add($this->logType, $request->idCustomer, 'insert', 'insertCustomerService', null, $newProduct, $relationData, 'insert-service');

        return $this->getCustomerServices($request);

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
    public function disableCustomerServices(Request $request)
    {
        $customer = Customer::find($request->id);
        $customerProduct = CustomerProduct::find($request->idService);
        $customerProduct->id_status = config('const.status.disabled');
        $customerProduct->save();

        $sipCustomer = new SIPCustomer();
        $sipCustomer->cancelActiveChargesForCustomerProduct($customerProduct);
//        $this->cancelActiveChargesForCustomerProduct($activeService);
//        $this->cancelActiveInvoicesForCustomer($customer);

        $newData = array();
        $newData['id_status'] = config('const.status.disabled');

        $relationData = Product::find($customerProduct->id_products);

        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $customerProduct, $newData, $relationData, 'disable-service');

        return $this->getCustomerServices($request);

    }

//    protected function cancelActiveChargesForCustomerProduct(CustomerProduct $customerProduct)
//    {
//
//        $charge = $customerProduct->activeCharges;
//        if ($charge == null)
//        {
//            Log::info('cancelActiveChargesForCustomerProduct(): CustomerProduct id=' . $customerProduct->id . ' has no active charges.');
//
//            return false;
//        }
//        $billingHelper = new BillingHelper();
//
//        return $billingHelper->removeChargeFromInvoice($charge);
//    }

    protected function cancelActiveInvoicesForCustomer(Customer $customer)
    {

        $firstDayOfMonthTime = strtotime("first day of this month 00:00:00");
        $timestampMysql = date('Y-m-d H:i:s', $firstDayOfMonthTime);

        $pendingInvoices = $customer->pendingAutoPayInvoicesOnOrAfterTimestamp($timestampMysql);
        $billingHelper = new BillingHelper();

        $count = 0;
        foreach ($pendingInvoices as $invoice)
        {
            $billingHelper->cancelInvoice($invoice);
            $count ++;
        }
        Log::info('cancelActiveInvoicesForCustomer(): Cancelled ' . $count . ' invoices for customer id=' . $customer->id);

        return true;
    }


    /**
     * @param Request $request
     * id = id_customers.
     * idService to find and update (activate) record/Service.
     * Status
     * 1 = active
     * 2 = disabled
     * 3 = decommissioned
     * 4 = pending
     * 5 = admin
     * @return Customer
     */
    public function activeCustomerServices(Request $request)
    {
        $activeService = CustomerProduct::find($request->idService);
        $activeService->id_status = config('const.status.active');
        $activeService->save();

        $newData = array();
        $newData['id_status'] = config('const.status.active');

        $relationData = Product::find($activeService->id_products);

        ActivityLogs::add($this->logType, $request->id, 'update', 'disableCustomerServices', $activeService, $newData, $relationData, 'active-service');

        return $this->getCustomerServices($request);
    }

    /**
     * @param Request $request
     * newId = new id product to update TO.
     * oldID = id record to find and update
     * id = id_customers.
     * Status 1 = Active.
     * @return OK to updated record.
     */
    public function updateCustomerServices(Request $request)
    {
        $when = $this->getTimeToAdd(Product::find($request->newId)->frequency);
        $expires = date("Y-m-d H:i:s", strtotime('first day of next ' . $when));

        $updateService = CustomerProduct::find($request->oldId);
        $updateService->id_products = $request->newId;
        $updateService->signed_up = date("Y-m-d H:i:s");
        $updateService->expires = $expires;
        $updateService->id_users = Auth::user()->id;
        $updateService->id_status = config('const.status.active');
        $updateService->save();

        $newData = array();
        $newData['id_products'] = $request->newId;

        $relationData = Product::find($request->newId);

        ActivityLogs::add($this->logType, $request->id, 'update', 'updateCustomerServices', $updateService, $newData, $relationData, 'update-service');

        return 'OK';

    }

    /**
     * @param Request $request
     * customerId = id_customers to insert new Contact Info.
     * typeId = contact type.
     * contactInfoVal = Value of the new Contact.
     * @return Customer with contacts, list.
     */
    public function insertContactInfo(Request $request)
    {
        $newContact = new Contact;
        $newContact->id_customers = $request->id_customers;
        $newContact->id_types = $request->id_types;
        $newContact->value = $request->value;
        $newContact->save();

        return Customer::with('contacts')->find($request->id_customers);
    }

    /**
     * @param Request $request
     * id_customers = id_customers to add new ticket TO.
     * id_reasons = id_reason of the ticket.
     * comment = comment of the ticket, Description.
     * status = status of the ticket, Escalated/Closed.
     * @return OK after sending Mail of new ticket created.
     */
    public function insertCustomerTicket(Request $request)
    {
        $lastTicketId = Ticket::max('id');
        $lastTicketNumber = Ticket::find($lastTicketId)->ticket_number;
        $ticketNumber = explode('ST-', $lastTicketNumber);
        $ticketNumberCast = (int) $ticketNumber[1] + 1;

        $newTicket = new Ticket;

        $newTicket->id_customers = $request->id_customers;
        $newTicket->ticket_number = 'ST-' . $ticketNumberCast;
        $newTicket->id_reasons = $request->id_reasons;
        $newTicket->comment = $request->comment;
        $newTicket->status = $request->status;
        $newTicket->id_users = Auth::user()->id;
        $newTicket->save();

        //1 = new ticket
        //2 = update ticket
        SendMail::ticketMail($newTicket, config('const.ticket_status.new'));

        return 'OK';
    }

    /**
     * @param Request $request
     * type = pending to set.
     * id_type = id customer to get records.
     * @return Customers activity log.
     */
    public function getCustomerLog(Request $request)
    {
        //RECHECK
        return ActivityLog::with('user')
            ->where('type', $request->type)
            ->where('id_type', $request->id_type)
            ->orderBy('id', 'desc')
            ->get();
    }

//    public function refundAmountAction(Request $request)
//    {
//
//
//        $customerInfo = Customer::find($request->cid);
//        $customerAddress = Address::where('id_customers', $request->cid)->first();
//        $customerInvoice = Invoice::where('id_customers', $request->cid)->first();
//
//        $newCharge = new Charge;
//        $newCharge->name = $customerInfo->first_name . ' ' . $customerInfo->last_name;
//        $newCharge->address = $customerAddress->address;
////        $newCharge->description = 'New Charge';       //?? always the same?
////        $newCharge->description = 'Current Amount owed : $ ' . $customerInvoice->amount;//?? always the same?
////        $newCharge->details
//        $newCharge->amount = $request->amount;
////        $newCharge->qty
//        $newCharge->id_customers = $request->cid;
////        $newCharge->id_customer_products
//        $newCharge->id_address = $customerAddress->id;
//        $newCharge->id_invoices = $customerInvoice->id;
//        $newCharge->id_users = Auth::user()->id;
//        $newCharge->status = config('const.charge_status.pending_approval');
//        $newCharge->type = 'Refund';
//        $newCharge->comment = $request->desc;
//
//        $newCharge->save();
//
//        return 'OK';
//    }
//
//    public function chargeAmountAction(Request $request)
//    {
//
//
//        $customerInfo = Customer::find($request->cid);
//        $customerAddress = Address::where('id_customers', $request->cid)->first();
//        $customerInvoice = Invoice::where('id_customers', $request->cid)->first();
//
//        $newCharge = new Charge;
//        $newCharge->name = $customerInfo->first_name . ' ' . $customerInfo->last_name;
//        $newCharge->address = $customerAddress->address;
////        $newCharge->description = 'New Charge';       //?? always the same?
//        $newCharge->description = 'Current Amount owed : $ ' . $customerInvoice->amount;//?? always the same?
////        $newCharge->details
//        $newCharge->amount = $request->amount;
////        $newCharge->qty
//        $newCharge->id_customers = $request->cid;
////        $newCharge->id_customer_products
//        $newCharge->id_address = $customerAddress->id;
//        $newCharge->id_invoices = $customerInvoice->id;
//        $newCharge->id_users = Auth::user()->id;
//        $newCharge->status = config('const.charge_status.pending_approval');
//        $newCharge->type = 'Charge';
//        $newCharge->comment = $request->desc;
//        $newCharge->bill_cycle_day = 1; //Default for 1fay of the month
////        $newCharge->processing_type // ??? ---> NO IDEA *******
////        $newCharge->start_date    //??? ---> First day of the next month????
////        $newCharge->end_date    //??? ---> Last day of the next month????
////        $newCharge->due_date      //??? ---> NO IDEA *******
//        $newCharge->save();
//
//        return 'OK';
//    }

    public function insertNewCustomer(Request $request)
    {

        Log::info('insertNewCustomer called', $request->all());

        // Add the customer
        $response = $this->sipCustomer->addNewCustomer($request->customers_first_name, $request->customers_last_name, $request->customers_email, $request->customers_vip);

        Log::info('insertNewCustomer: ' . json_encode($response));

        if (isset($response['error']))
        {
            return $response;
        }

        $customer = $response['response'];
        $response = $this->sipCustomer->addCustomerContact($customer->id, config('const.contact_type.mobile_phone'), $request->contacts_value);
        if (isset($response['error']))
        {
            $customer->delete();

            return $response;
        }
        $customerPhone = $response['response'];

        $response = $this->sipCustomer->addCustomerContact($customer->id, config('const.contact_type.email'), $request->customers_email);
        if (isset($response['error']))
        {
            $customerPhone->delete();
            $customer->delete();

            return $response;
        }
        $customerEmail = $response['response'];

        $response = $this->sipCustomer->addCustomerAddressByBuilding($customer->id, $request->building_id, $request->address_unit);
        if (isset($response['error']))
        {
            $customerEmail->delete();
            $customer->delete();

            return $response;
        }

        $response = $this->sipCustomer->addCustomerProduct($customer->id, $request->product_id);
        if (isset($response['error']))
        {
            $customerPhone->delete();
            $customerEmail->delete();
            $customer->delete();

            return $response;
        }
        $customerProduct = $response['response'];

        $response = $this->sipCustomer->addCustomerPortBySwitchAndPort($customer->id, $request->switch_id, $request->port_id);
        if (isset($response['error']))
        {
            $customerPhone->delete();
            $customerEmail->delete();
            $customer->delete();
            $customerProduct->delete();

            return $response;
        }

        ActivityLogs::add($this->logType, $customer->id, 'insert', 'insertNewCustomer', '', $customer, json_encode(['customer_id' => $customer->id]), 'insert-customer');

        return ['ok' => $customer];
    }

    public function updateCustomerStatus(Request $request)
    {
        dd($request->all());

        /*
         * status-service-check: "on",
         * status-invoice-check: "on",
         * status-network-check: "on",
         * id: 4667
         * */

    }

    public function getCustomerById(Request $request)
    {
        return Customer::find($request->id);
    }
}



































