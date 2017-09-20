<?php

namespace App\Extensions;

use App\Models\NetworkNode;
use App\Models\Building;
use App\Models\Customer;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\InvoiceLog;
use App\Models\Address;
use App\Models\CustomerProduct;
use App\Extensions\SIPBilling;
use App\Extensions\SIPTicket;
use DB;
use Hash;
use Illuminate\Support\MessageBag;
use Mail;
use Log;
use Carbon\Carbon;

class BillingHelper {

    private $testMode = true;
    private $passcode = '$2y$10$igbvfItrwUkvitqONf4FkebPyD0hhInH.Be4ztTaAUlxGQ4yaJd1K';

    public function __construct()
    {
        // DO NOT ENABLE QUERY LOGGING IN PRODUCTION
        //        DB::connection()->enableQueryLog();
        $configPasscode = config('billing.ippay.passcode');
        $this->testMode = (Hash::check($configPasscode, $this->passcode)) ? false : true;
    }

    public function getMode()
    {
        return ($this->testMode) ? 'development' : 'production';
    }

    public function generateResidentialChargeRecords()
    {
        // Get residential buildings
        $buildings = $this->getRetailAndBulkBuildings();

        $count = 0;
        foreach ($buildings as $building)
        {
            // Get list of chargeable products/services for the requested building
            $customerProducts = $this->getChargeableCustomerProductsByBuildingId($building->id);
            $count = $this->addChargesForCustomerProducts($customerProducts);
            Log::info('BillingHelper::generateResidentialChargeRecords(): Added ' . $count . ' charges for ' . $building->nickname . ' to DB');
        }

        return 'Generated ' . $count . ' charges and added them to the DB.';
    }

    public function generateResidentialChargeRecordsByMonth($monthString = '', $skipOneTime = false, $testMode = false)
    {
        if ($monthString == '')
        {
            Log::info('BillingHelper::generateResidentialChargeRecordsByMonth(): ERROR: You need to specify a month. e.g. August 2017 or just August.');

            return false;
        }

        $expiresBeforeMysqlDate = date("Y-m-d H:i:s", strtotime('first day of ' . $monthString . ' 00:00:00'));

        // Get residential buildings
        $buildings = $this->getRetailAndBulkBuildings();

        $count = 0;
        $logVerb = ($testMode) ? 'Found' : 'Added';
        foreach ($buildings as $building)
        {
            // Get list of chargeable products/services for the requested building
            $customerProducts = $this->getChargeableCustomerProductsQuery('first day of ' . $monthString . ' 00:00:00', $skipOneTime)
                ->where('customer_products.signed_up', '<', $expiresBeforeMysqlDate)
                ->whereNull('customer_products.last_charged')
                ->where('buildings.id', '=', $building->id)
                ->get(['customer_products.*']);

            if ($testMode)
            {
                $productCount = $customerProducts->count();

            } else
            {
                $productCount = $this->addChargesForCustomerProducts($customerProducts, $monthString);
            }

            if ($productCount == 0)
            {
                continue;
            }
            $count += $productCount;
            Log::info('BillingHelper::generateResidentialChargeRecordsByMonth(): ' . $logVerb . ' ' . $productCount . ' charges for ' . $building->nickname);
        }

        Log::info('BillingHelper::generateResidentialChargeRecordsByMonth(): ' . $logVerb . ' ' . $count . ' total products that need to be charged for ' . $monthString);

        return true;
    }

    protected function getRetailAndBulkBuildings()
    {
        // Get residential bulk+retail buildings
        return Building::with(['properties' => function ($query) {
            $query->where('id_building_properties', config('const.building_property.service_type'))
                ->where('value', 'LIKE', '%Retail%')
                ->orWhere('value', 'LIKE', '%Bulk%');
        }])->get();
    }

    public function generateChargeRecordsForCustomer($customerId)
    {
        // Get list of chargeable products/services for the requested customer
        $customerProducts = $this->getChargeableCustomerProductsByCustomerId($customerId);
        $count = $this->addChargesForCustomerProducts($customerProducts);
        Log::info('BillingHelper::generateChargeRecordsForCustomer(): Added charges for customer id=' . $customerId . ' to DB');
    }

    public function getChargeableCustomerProductsByBuildingId($buildingId)
    {
        return $this->getChargeableCustomerProductsQuery()
            ->where('buildings.id', '=', $buildingId)
            ->get(['customer_products.*']);
    }

    public function getChargeableCustomerProductsByCustomerId($customerId)
    {
        return $this->getChargeableCustomerProductsQuery()
            ->where('customers.id', '=', $customerId)
            ->get(['customer_products.*']);
    }

    public function getChargeableCustomerProductsQuery($expiresBefore = 'first day of next month 00:00:00', $skipOneTime = false)
    {
        // Date to check product expirations against
        $expiresBeforeMysqlDate = date("Y-m-d H:i:s", strtotime($expiresBefore));

        $query = CustomerProduct::join('customers', 'customer_products.id_customers', '=', 'customers.id')
            ->join('products', 'customer_products.id_products', '=', 'products.id')
            ->join('address', 'address.id_customers', '=', 'customers.id')
            ->join('buildings', 'address.id_buildings', '=', 'buildings.id')
            // customer MUST be active
            ->where('customers.id_status', '=', config('const.status.active'))
            // the customer's product/service MUST be active
            ->where('customer_products.id_status', '=', config('const.status.active'))
            // customer's product/service MUST be expiring before the first day of next month
            ->where(function ($query) use ($expiresBeforeMysqlDate) {
                $query->whereNull('customer_products.expires')
                    ->orWhere('customer_products.expires', '<=', $expiresBeforeMysqlDate);
            })
            // skip customer's product/service that are complimentary
            ->where('products.amount', '>', 0);

        if ($skipOneTime)
        {
            $query->where(function ($query3) use ($expiresBeforeMysqlDate) {
                // Get 'monthly' and/or 'annual' products that can be charged (status != paid or failed, freq is monthly or annual, and next_charge_date has expired)
                $query3->where('customer_products.charge_status', '<>', config('const.customer_product_charge_status.paid'))
                    ->where('customer_products.charge_status', '<>', config('const.customer_product_charge_status.failed'))
                    ->where('products.frequency', '<>', 'onetime')
                    ->where('products.frequency', '<>', 'included')
                    ->whereNull('customer_products.next_charge_date')
                    ->orWhere('customer_products.next_charge_date', '<', $expiresBeforeMysqlDate);
            });
        } else
        {
            $query->where(function ($query) use ($expiresBeforeMysqlDate) {
                $query->where(function ($query2) {
                    // Get 'onetime' products that have not been charged (status = none or active)
                    $query2->where('products.frequency', '=', 'onetime')
                        ->where('customer_products.charge_status', '=', config('const.customer_product_charge_status.none'))
                        ->orWhere('customer_products.charge_status', '=', config('const.customer_product_charge_status.active'));
                })->orWhere(function ($query3) use ($expiresBeforeMysqlDate) {
                    // Get 'monthly' and/or 'annual' products that can be charged (status != paid or failed, freq is monthly or annual, and next_charge_date has expired)
                    $query3->where('customer_products.charge_status', '<>', config('const.customer_product_charge_status.paid'))
                        ->where('customer_products.charge_status', '<>', config('const.customer_product_charge_status.failed'))
                        ->where('products.frequency', '<>', 'onetime')
                        ->where('products.frequency', '<>', 'included')
                        ->whereNull('customer_products.next_charge_date')
                        ->orWhere('customer_products.next_charge_date', '<', $expiresBeforeMysqlDate);
                });
            });
        }

        return $query;
    }

    protected function addChargesForCustomerProducts($customerProducts, $monthString = 'next month')
    {
        $count = 0;
        foreach ($customerProducts as $customerProduct)
        {
            $result = $this->createChargeForCustomerProduct($customerProduct, 0, $monthString);
            if ($result == false)
            {
                continue;
            }
            // Uncomment this when ready to run a real or production test
            $this->markCustomerProductAsCharged($customerProduct, $monthString);
            $count ++;
        }

        return $count;
    }

    public function createChargeForCustomerProduct($customerProduct, $userId = 0, $monthString = 'next month')
    {
        $product = $customerProduct->product;
        $dateRange = $this->getProductChargeDatesByMonth($product, $monthString);

        $charge = Charge::where('id_customers', $customerProduct->id_customers)
            ->where('id_customer_products', $customerProduct->id)
            ->where('id_address', $customerProduct->id_address)
            ->where('start_date', $dateRange['startDate'])
            ->where('end_date', $dateRange['endDate'])->first();

        if ($charge != null)
        {
            Log::info('Charge already exists. Customer id: ' . $customerProduct->id_customers .
                ' for product id: ' . $customerProduct->id_products .
                ' for date range: ' . $dateRange['startDate'] . ' - ' . $dateRange['endDate']);

            return false;
        }

        Charge::create([
            'description'          => 'New Charge',
            'details'              => json_encode(array('customer_product_id'     => $customerProduct->id,
                                                        'customer_product_status' => $customerProduct->id_status,
                                                        'product_id'              => $customerProduct->id_products,
                                                        'product_name'            => $product->name,
                                                        'product_desc'            => $product->description,
                                                        'product_amount'          => $product->amount,
                                                        'product_frequency'       => $product->frequency,
                                                        'product_type'            => $product->type
            )),
            'amount'               => number_format($product->amount, 2),
            'qty'                  => 1,
            'id_customers'         => $customerProduct->id_customers,
            'id_customer_products' => $customerProduct->id,
            'id_address'           => $customerProduct->id_address,
            'id_users'             => $userId,
            'status'               => config('const.charge_status.pending'),
            'type'                 => config('const.charge_type.charge'),
            'bill_cycle_day'       => '1',
            'processing_type'      => config('const.type.auto_pay'),  // auto_pay, manual_pay
            'start_date'           => $dateRange['startDate'],
            'end_date'             => $dateRange['endDate'],
            'due_date'             => date("Y-m-d H:i:s", strtotime("first day of $monthString  00:00:00"))
        ]);

        return true;
    }

    public function createManualChargeForCustomer(Customer $customer, $amount, $comment, $userId = 0)
    {
        $address = $customer->address;

        Charge::create(['description'     => 'Manual Charge',
                        'amount'          => number_format($amount, 2),
                        'qty'             => 1,
                        'id_customers'    => $customer->id,
                        'id_address'      => $address->id,
                        'id_users'        => $userId,
                        'status'          => config('const.charge_status.pending_approval'),
                        'type'            => config('const.charge_type.charge'),
                        'comment'         => $comment,
                        'bill_cycle_day'  => '1',
                        'processing_type' => config('const.type.manual_pay'),  // auto_pay, manual_pay
                        'due_date'        => date("Y-m-d H:i:s", strtotime("first day of next month  00:00:00"))
        ]);

        return true;
    }

    public function createManualRefundForCustomer(Customer $customer, $amount, $comment, $userId = 0)
    {
        $address = $customer->address;

        Charge::create(['description'     => 'Manual Refund',
                        'amount'          => number_format($amount, 2),
                        'qty'             => 1,
                        'id_customers'    => $customer->id,
                        'id_address'      => $address->id,
                        'id_users'        => $userId,
                        'status'          => config('const.charge_status.pending_approval'),
                        'type'            => config('const.charge_type.credit'),
                        'comment'         => $comment,
                        'bill_cycle_day'  => '1',
                        'processing_type' => config('const.type.manual_pay'),  // auto_pay, manual_pay
                        'due_date'        => date("Y-m-d H:i:s", strtotime("first day of next month  00:00:00"))
        ]);

        return true;
    }

    /**
     *  Manual charge and refund request and approval functions
     */

    /**
     * @param Charge $charge
     * @param $amount
     * @param $comment
     * @param int $userId
     * @return bool
     */
    public function updateManualChargeAmount(Charge $charge, $amount, $comment, $userId = 0)
    {
        $charge->amount = number_format($amount, 2);
        $charge->comment = $comment;
        $charge->id_users = $userId;
        $charge->save();

        return true;
    }

    public function approveManualChargeList($chargeIds, $notifyViaEmail = false)
    {

        $resultsArray = array();
        foreach ($chargeIds as $chargeId)
        {
            $charge = Charge::find($chargeId);
            if ($charge == null)
            {
                $resultsArray[$chargeId] = ['ERRMSG' => 'Charge not found'];
                Log::notice('BillingController::approveManualChargeList(): Charge not found with id: ' . $chargeId);
                continue;
            }
            $resultsArray[$chargeId] = $this->approveManualCharge($charge, $notifyViaEmail);
        }

        return $resultsArray;
    }

    public function approveManualCharge(Charge $charge, $notifyViaEmail = false)
    {
        $charge->status = config('const.charge_status.pending');
        /**
         *  We will leave the processing type as manual pay
         */
        // $charge->processing_type = config('const.type.auto_pay');
        $charge->save();

        $invoice = $this->invoiceManualCharge($charge);

        return $this->processInvoice($invoice, false); //$notifyViaEmail);

//        return true;
    }

    public function denyManualChargeList($chargeIds)
    {

        $resultsArray = array();
        foreach ($chargeIds as $chargeId)
        {
            $charge = Charge::find($chargeId);
            if ($charge == null)
            {
                $resultsArray[$chargeId] = ['ERRMSG' => 'Charge not found'];
                Log::notice('BillingController::denyManualChargeList(): Charge not found with id: ' . $chargeId);
                continue;
            }
            $resultsArray[$chargeId] = $this->denyManualCharge($charge);
        }

        return $resultsArray;
    }

    public function denyManualCharge(Charge $charge)
    {
        $charge->status = config('const.charge_status.denied');
        $charge->save();

        return true;
    }

    public function getPendingManualCharges(Customer $customer)
    {

    }

    protected function getProductChargeDatesByMonth($product, $monthString = 'next month')
    {
        if ($product->frequency == 'monthly')
        {
            return ['startDate' => date("Y-m-d H:i:s", strtotime("first day of $monthString  00:00:00")),
                    'endDate'   => date("Y-m-d H:i:s", strtotime("last day of $monthString  00:00:00"))];

        } elseif ($product->frequency == 'annual')
        {
            return ['startDate' => date("Y-m-d H:i:s", strtotime("first day of $monthString  00:00:00")),
                    'endDate'   => date("Y-m-d H:i:s", strtotime("first day of $monthString next year  00:00:00"))];
        } else
        {
            // Onetime products
            return ['startDate' => date("Y-m-d H:i:s", strtotime("first day of $monthString  00:00:00")),
                    'endDate'   => null];
        }
    }

    protected function calculateCustomerProductDateRange()
    {

    }

    protected function getFormattedAddress(Address $address)
    {

        $formattedAddress = $address->address;
        if (trim($address->unit != ''))
        {
            $formattedAddress .= "\n" . 'Apt ' . $address->unit;
        }

        return $formattedAddress . "\n" . trim($address->city) . ', ' . trim($address->state) . ' ' . trim($address->zip);
    }

    /**
     *  Customer product timestamp and status update functions
     */

    protected function markCustomerProductAsCharged($customerProduct, $monthString = 'this month')
    {

        // Default to the first day of the month
        $firstDayOfMonthTime = strtotime("first day of $monthString 00:00:00");

        if ($customerProduct->charge_status != 0)
        {
            $customerProduct->amount_owed += number_format($customerProduct->product->amount, 2, '.', '');
        }

        // Default charge status is active. We set one-time products to paid after their invoice is processed
        $customerProduct->charge_status = config('const.customer_product_charge_status.active');

        $customerProductNextChargeTime = strtotime($customerProduct->next_charge_date);

        $timestampMysql = null;
        if ($customerProduct->product->frequency == 'annual')
        {
            // Set the next chargeable date to next year for annual products
            $nextChargeTime = strtotime('+1 year', $firstDayOfMonthTime);
            $nextChargeDateMysql = date('Y-m-d H:i:s', $nextChargeTime);
        } elseif ($customerProduct->product->frequency == 'monthly')
        {
            // Set the next chargeable date to next month for monthly products
            $nextChargeTime = strtotime('+1 month', $firstDayOfMonthTime);
            $nextChargeDateMysql = date('Y-m-d H:i:s', $nextChargeTime);
        } else
        {
            $nextChargeDateMysql = null;
        }

        if ($customerProduct->next_charge_date == null || $nextChargeTime > $customerProductNextChargeTime)
        {
            $customerProduct->next_charge_date = $nextChargeDateMysql;
        }

        $customerProduct->save();
    }

    protected function markCustomerProductAsPaid($customerProductId, $monthString = 'this month')
    {
        $customerProduct = CustomerProduct::find($customerProductId);
        if ($customerProduct == null)
        {
            Log::info('BillingHelper::markCustomerProductAsPaid(): customer product id=' . $customerProductId . ' not found.');

            return false;
        }

        $firstDayOfMonthTime = strtotime("first day of $monthString 00:00:00");

        // Default charge status is active. We set one-time products to paid down below
        $customerProduct->charge_status = config('const.customer_product_charge_status.active');

        $timestampMysql = null;
        if ($customerProduct->product->frequency == 'annual')
        {
            // Set the next expiration date to next year for annual plans
            $customerProduct->expires = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayOfMonthTime));;
            $customerProduct->renewed_at = date('Y-m-d H:i:s');
        } elseif ($customerProduct->product->frequency == 'monthly')
        {
            // Set the next expiration date to next month for monthly plans
            $customerProduct->expires = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayOfMonthTime));
            $customerProduct->renewed_at = date('Y-m-d H:i:s');
        } else
        {
            // This is for one-time products
            $customerProduct->charge_status = config('const.customer_product_charge_status.paid');
        }

        $customerProduct->last_charged = date('Y-m-d H:i:s');
        $customerProduct->amount_owed -= number_format($customerProduct->product->amount, 2);
        $customerProduct->save();

        return true;
    }

    protected function markCustomerProductAsFailedToCharge($customerProductId)
    {
        $customerProduct = CustomerProduct::find($customerProductId);
        if ($customerProduct == null)
        {
            Log::info('BillingHelper::markCustomerProductAsPaid(): customer product id=' . $customerProductId . ' not found.');

            return false;
        }
        $customerProduct->last_charged = date('Y-m-d H:i:s');
        $customerProduct->save();

        return true;
    }

    /**
     *  Invoice generation functions
     */

    public function invoiceManualCharge(Charge $charge)
    {
        $invoice = $this->createNewManualInvoice($charge->id_customers, $charge->id_address);
        $this->addChargeToInvoice($charge, $invoice);

        return $invoice;
    }

    public function invoicePendingAutoPayCharges()
    {
        while (true)
        {
            $charges = Charge::where('status', config('const.charge_status.pending'))
                ->where('processing_type', config('const.type.auto_pay'))
                ->take(100)
                ->get();

            if ($charges->count() == 0)
            {
                break;
            }

            foreach ($charges as $charge)
            {
                $invoice = $this->findOrCreateOpenInvoice($charge->id_customers, $charge->id_address);
                $this->addChargeToInvoice($charge, $invoice);
            }
        }
    }

    public function invoicePendingAutoPayChargesByMonth($monthString)
    {
        // Default to the first day of the month
        $startDateTime = strtotime("first day of $monthString 00:00:00");
        $startDateMysql = date("Y-m-d H:i:s", $startDateTime);

        while (true)
        {
            $charges = Charge::where('status', config('const.charge_status.pending'))
                ->where('processing_type', config('const.type.auto_pay'))
                ->where('start_date', $startDateMysql)
                ->take(100)
                ->get();

            if ($charges->count() == 0)
            {
                break;
            }

            foreach ($charges as $charge)
            {
                $invoice = $this->findOrCreateOpenInvoice($charge->id_customers, $charge->id_address, $monthString);
                $this->addChargeToInvoice($charge, $invoice);
            }
        }
    }

    public function findOrCreateOpenInvoice($customerId, $addressId, $monthString = 'next month')
    {
        $firstDayOfMonthTime = strtotime("first day of $monthString 00:00:00");
        $description = date('M-Y', $firstDayOfMonthTime) . ' Charges';

        $invoice = Invoice::firstOrCreate(['id_customers' => $customerId, 'id_address' => $addressId, 'status' => config('const.invoice_status.open')]);

        if ($invoice->description == null)
        {
            $invoice->description = $description;
        }
        if ($invoice->bill_cycle_day == null)
        {
            $invoice->bill_cycle_day = '1';
        }
        if ($invoice->processing_type == null)
        {
            $invoice->processing_type = config('const.type.auto_pay'); // 13 = Autopay, 14 = Manual Pay
        }

        if ($invoice->due_date == null)
        {
            $dueDateTime = strtotime("first day of $monthString  00:00:00");
            $dueDateMysql = date("Y-m-d H:i:s", $dueDateTime);
            $invoice->due_date = $dueDateMysql;
        }

        return $invoice;
    }

    public function createNewManualInvoice($customerId, $addressId)
    {
        $invoice = Invoice::create(['id_customers' => $customerId, 'id_address' => $addressId, 'status' => config('const.invoice_status.open')]);

        if ($invoice->description == null)
        {
            $invoice->description = 'New Manual Invoice';
        }
        if ($invoice->bill_cycle_day == null)
        {
            $invoice->bill_cycle_day = '1';
        }
        if ($invoice->processing_type == null)
        {
            $invoice->processing_type = config('const.type.manual_pay'); // 13 = Autopay, 14 = Manual Pay
        }

        if ($invoice->due_date == null)
        {
            $firstDayOfNextMonthTime = strtotime("first day of next month  00:00:00");
            $firstDayOfNextMonthMysql = date("Y-m-d H:i:s", $firstDayOfNextMonthTime);
            $invoice->due_date = $firstDayOfNextMonthMysql;
        }

        $invoice->status = config('const.invoice_status.pending');
        $invoice->save();

        return $invoice;
    }

    protected function getCustomerName($customerId)
    {
        $customer = Customer::find($customerId);
        if ($customer != null)
        {
            return trim($customer->first_name, "\x20,\xC2,\xA0") . ' ' . trim($customer->last_name, "\x20,\xC2,\xA0");
        }

        return null;
    }

    protected function getCustomerAddress($addressId)
    {
        $address = Address::find($addressId);
        if ($address != null)
        {
            $addressString = trim($address->address);
            if (trim($address->unit != ''))
            {
                $addressString .= "\n" . 'Apt ' . $address->unit;
            }
            $addressString .= "\n" . trim($address->city) . ', ' . trim($address->state) . ' ' . trim($address->zip);

            return $addressString;
        }

        return null;
    }

    protected function addChargeToInvoice(Charge $charge, Invoice $invoice)
    {

        $charge->id_invoices = $invoice->id;
        $charge->status = config('const.charge_status.invoiced');
        $charge->save();

        if ($charge->type == config('const.charge_type.charge'))
        {
            $invoice->amount += number_format($charge->amount, 2);
        } else
        {
            $invoice->amount -= number_format($charge->amount, 2);
        }
        $invoice->save();
    }

    public function removeChargeFromInvoice(Charge $charge)
    {

        $invoice = $charge->invoice;
        $charge->delete();

        if ($invoice != null)
        {
            $this->updateInvoiceAmount($invoice);
        }

        return true;
    }

    protected function updateInvoiceAmount(Invoice $invoice)
    {
        $charges = $invoice->charges;

        $total = 0;
        foreach ($charges as $charge)
        {
            if ($charge->id_types != config('const.charge_type.charge'))
            {
                $total -= $charge->amount;
            } else
            {
                $total += $charge->amount;
            }
        }
        $invoice->amount = number_format($total, 2);

        if ($invoice->amount == 0)
        {
            $invoice->delete();
        } else
        {
            $invoice->save();
        }
    }


    /**
     * Invoice processing functions
     */

    public function processPendingAutopayInvoices($records = 200)
    {

        $nowMysql = date("Y-m-d H:i:s");
        Invoice::where('status', config('const.invoice_status.pending'))
            ->where('processing_type', config('const.type.auto_pay'))
            ->where('failed_charges_count', 0)
            ->where(function ($query) use ($nowMysql) {
                $query->where('due_date', 'is', 'NULL')
                    ->orWhere('due_date', '<=', $nowMysql)
                    ->orWhere('due_date', '');
            })->chunk($records, function ($invoices) use ($records) {
                foreach ($invoices as $invoice)
                {
                    $this->processInvoice($invoice, true);
                }

                if ($records > 0)
                {
                    return true;
                }
            });

        return true;
    }

    public function processPendingAutopayInvoicesByMonth($monthString = '', $notifyViaEmail = true, $records = 200)
    {

        if ($monthString == '')
        {
            Log::info('BillingHelper::processPendingAutopayInvoicesByMonth(): ERROR: You need to specify a month. e.g. August 2017 or just August.');

            return false;
        }

        $dueDateMysqlDate = date("Y-m-d H:i:s", strtotime('first day of ' . $monthString . ' 00:00:00'));

        $nowMysql = date("Y-m-d H:i:s");
        Invoice::where('status', config('const.invoice_status.pending'))
            ->where('processing_type', config('const.type.auto_pay'))
            ->where('failed_charges_count', 0)
            ->where('due_date', $dueDateMysqlDate)
            ->chunk($records, function ($invoices) use ($notifyViaEmail, $records) {
                foreach ($invoices as $invoice)
                {
                    $this->processInvoice($invoice, $notifyViaEmail);
                }

                if ($records > 0)
                {
                    return true;
                }
            });

        return true;
    }

    /**
     * TODO: Change pagination to use record IDs not LIMIT
     */
    public function processPendingAutopayInvoicesThatHaveUpdatedPaymentMethods()
    {
        $perPage = 15;
        $totalInvoicesProcessed = 0;

        $paginatedInvoices = $this->paginatePendingInvoices();
        dd($paginatedInvoices);
        while (true)
        {
            $invoices = $this->filterPendingInvoicesByUpdatedPaymentMethods($paginatedInvoices);

            foreach ($invoices as $invoice)
            {
                $totalInvoicesProcessed ++;
//                $this->processInvoice($invoice, true, true);
            }

            if ($paginatedInvoices->hasMorePages() == false)
            {
                break;
            }
            $paginatedInvoices = $this->paginatePendingInvoices($perPage, $paginatedInvoices->currentPage() + 1);
        }

        echo 'Processed ' . $totalInvoicesProcessed . ' invoices.' . "\n";

        return true;
    }

    public function rerunPendingAutopayInvoices()
    {

        $nowMysql = date("Y-m-d H:i:s");
        Invoice::where('status', config('const.invoice_status.pending'))
            ->where('processing_type', config('const.type.auto_pay'))
            ->where(function ($query) use ($nowMysql) {
                $query->where('due_date', 'is', 'NULL')
                    ->orWhere('due_date', '<=', $nowMysql)
                    ->orWhere('due_date', '');
            })->chunk(200, function ($invoices) {
                foreach ($invoices as $invoice)
                {
                    $this->processInvoice($invoice, true);
//                    break;
                }
                dd('Done');
            });
    }

    public function processInvoice(Invoice $invoice, $notifyViaEmail = true, $notifyViaEmailOnlyIfPassed = false, $createTicketOnFailure = false)
    {
        if (isset($invoice) == false)
        {
            Log::info('BillingHelper::processInvoice(): $invoice is not set!');

            return ['ERRMSG' => 'Invalid is empty!'];
        }

        if ($invoice->amount == 0)
        {
            Log::info('BillingHelper::processInvoice(): ERROR: Invalid invoice amount: ' . $invoice->amount);

            return ['ERRMSG' => 'Invalid invoice amount: ' . $invoice->amount];
        }

        // Charge the invoice
        $billingService = new SIPBilling();
        $charges = $invoice->charges;
        $details = $charges->pluck('details');

        $chargeDetailsArray = array();
        foreach ($details as $chargeDetails)
        {
            $chargeDetailsArray[] = json_decode($chargeDetails, true);
        }


        $customer = Customer::find($invoice->id_customers);

        if ($invoice->amount > 0)
        {
            $chargeResult = $billingService->chargeCustomer($customer, $invoice->amount, $invoice->description, $invoice->description, json_encode($chargeDetailsArray));
        } else
        {
            $chargeResult = $billingService->refundCustomer($customer, - 1 * $invoice->amount, $invoice->description, json_encode($chargeDetailsArray));
        }

        $transactionId = isset($chargeResult['TRANSACTIONID']) ? $chargeResult['TRANSACTIONID'] : null;

        if ($chargeResult['RESPONSETEXT'] == 'APPROVED' || $chargeResult['RESPONSETEXT'] == 'RETURN ACCEPTED')
        {
            Log::info('BillingHelper::processInvoice(): INFO: id: ' . $invoice->id_customers . ', ' . trim($customer->first_name) . ' ' . trim($customer->last_name) . ', $' . $invoice->amount . ', ' . 'invoice: ' . $invoice->id . " ... processed\n");
            $this->updateInvoiceChargeStatus($invoice, config('const.charge_status.paid'));
            $this->markInvoiceAsPaid($invoice);
            $this->logInvoice($invoice, 'processed', $transactionId);

            if ($notifyViaEmail && $notifyViaEmailOnlyIfPassed)
            {
                $this->sendInvoiceReceiptEmail($invoice, $chargeResult);
            }

        } else
        {

            Log::info('BillingHelper::processInvoice(): INFO: id: ' . $invoice->id_customers . ', ' . trim($customer->first_name) . ' ' . trim($customer->last_name) . ', $' . $invoice->amount . ', ' . 'invoice: ' . $invoice->id . " ... failed\n");
            $this->markInvoiceAsFailedToCharge($invoice);
            $this->logInvoice($invoice, 'failed', $transactionId);

            /*** Create a ticket ***/
            if ($createTicketOnFailure)
            {

                $sipTicket = new SIPTicket();
                $ticketComment = 'Failed to charge invoice: ' . $invoice->id . ' for $' . $invoice->amount . ' --- TransID: ' . $chargeResult['TRANSACTIONID'] . ' - Action: ' . $chargeResult['ACTION'] . ' - Approval: ' . $chargeResult['APPROVAL'] . ' - Response: ' . $chargeResult['RESPONSETEXT'];
                $sipTicket->createTicket($invoice->id_customers, config('const.reason.billing'), config('const.ticket_status.escalated'), $ticketComment, 0, '', false);
            }

            /*** Notify customer ***/
            if ($notifyViaEmail && $notifyViaEmailOnlyIfPassed == false)
            {
                $this->sendChargeDeclienedEmail($invoice, $chargeResult);
            }
        }

        return $chargeResult;
    }

    protected function logInvoice(Invoice $invoice, $status = null, $transactionId = null, $comment = null)
    {
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

    protected function markInvoiceAsPaid(Invoice $invoice)
    {
        $invoice->status = config('const.invoice_status.paid');
        $invoice->save();

        $invoiceDetails = $invoice->details();

        if (count($invoiceDetails) == 0)
        {
            Log::notice('BillingHelper::markInvoiceAsPaid(): NOTICE: invoice: ' . $invoice->id . ' has no products.');

            return false;
        }

        $detailsCollection = collect($invoiceDetails);
        $customerProductIds = $detailsCollection->pluck('customer_product_id');

        $updateCount = 0;
        foreach ($customerProductIds as $customerProductId)
        {

            $this->markCustomerProductAsPaid($customerProductId);
            $updateCount ++;
        }

        Log::notice('BillingHelper::markInvoiceAsPaid(): INFO: Updated expiration and charge dates for ' . $updateCount . ' products of invoice: ' . $invoice->id);

        return $updateCount;
    }

    protected function markInvoiceAsFailedToCharge(Invoice $invoice)
    {
        $invoice->failed_charges_count ++;
        $invoice->save();
        $invoiceDetails = $invoice->details();

        if (count($invoiceDetails) == 0)
        {
            Log::notice('BillingHelper::markInvoiceAsFailedToCharge(): NOTICE: invoice: ' . $invoice->id . ' has no products.');

            return false;
        }

        $detailsCollection = collect($invoiceDetails);
        $customerProductIds = $detailsCollection->pluck('customer_product_id');

        $updateCount = 0;
        foreach ($customerProductIds as $customerProductId)
        {

            $this->markCustomerProductAsFailedToCharge($customerProductId);
            $updateCount ++;
        }

        Log::notice('BillingHelper::markInvoiceAsFailedToCharge(): INFO: Updated charge dates for ' . $updateCount . ' products of invoice: ' . $invoice->id);

        return $updateCount;
    }

    public function markInvoiceAsCancelled(Invoice $invoice)
    {
        $this->updateInvoiceChargeStatus($invoice, config('const.charge_status.cancelled'));
        $invoice->status = config('const.invoice_status.cancelled');
        $invoice->save();

        return true;
    }

    protected function updateInvoiceChargeStatus(Invoice $invoice, $status)
    {
        $charges = $invoice->charges;

        if (count($charges) == 0)
        {
            Log::notice('BillingHelper::updateInvoiceChargeStatus(): NOTICE: invoice: ' . $invoice->id . ' has no charges.');

            return false;
        }

        $updateCount = 0;
        foreach ($charges as $charge)
        {
            $charge->status = $status;
            $charge->save();
            $updateCount ++;
        }

        Log::notice('BillingHelper::updateInvoiceChargeStatus(): INFO: Updated the status of ' . $updateCount . ' charge(s) in invoice: ' . $invoice->id . ' to ' . $status);

        return $updateCount;
    }

    public function paginatePendingInvoices($perPage = 15, $page = null)
    {
        // Get pending invoices
        $invoices = Invoice::where('processing_type', config('const.type.auto_pay'))
            ->where('status', config('const.invoice_status.pending'));

        if ($page == null)
        {
            $invoices = $invoices->paginate($perPage);
        } else
        {
            $invoices = $invoices->paginate($perPage, array('*'), 'page', $page);
        }

        return $invoices;
    }

    public function filterPendingInvoicesByUpdatedPaymentMethods($invoices)
    {
        if ($invoices->isEmpty())
        {
            Log::notice('No pending invoices found.');

            return $invoices;
        }
        // Remove invoices that don't have a valid customer
        $invoices = $invoices->reject(function ($invoice, $key) {
            return $invoice->customer == null;
        });

        // Remove invoices that don't have a valid payment method
        $invoices = $invoices->reject(function ($invoice, $key) {
            return $invoice->customer->defaultPaymentMethod == null || $invoice->customer->defaultPaymentMethod->updated_at == null;
        });

        // Remove invoices that don't have an updated payment method
        $invoices = $invoices->reject(function ($invoice, $key) {
            $invoiceUpdatedAtCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $invoice->updated_at, 'America/Chicago');
            $pmUpdatedAtCarbon = Carbon::createFromFormat('Y-m-d H:i:s', $invoice->customer->defaultPaymentMethod->updated_at, 'America/Chicago');
            $invoiceUpdatedAtUnixTimestamp = $invoiceUpdatedAtCarbon->timestamp;
            $pmUpdatedAtCarbonUnixTimestamp = $pmUpdatedAtCarbon->timestamp;

            return $invoiceUpdatedAtUnixTimestamp > $pmUpdatedAtCarbonUnixTimestamp;
        });

        return $invoices;
    }


//    protected function updateInvoiceProductDates(Invoice $invoice, $updateChargeTimestampOnly = false)
//    {
//
//        $invoiceDetails = $invoice->details();
//
//        if (count($invoiceDetails) == 0)
//        {
//            Log::notice('BillingHelper::processInvoice(): NOTICE: invoice: ' . $invoice->id . ' has no products.');
//
//            return false;
//        }
//
//        $detailsCollection = collect($invoiceDetails);
//        $customerProductIds = $detailsCollection->pluck('customer_product_id');
//
//        $updateCount = 0;
//        foreach ($customerProductIds as $customerProductId)
//        {
//
//            $this->updateCustomerProductAttributes($customerProductId, $updateChargeTimestampOnly);
//            $updateCount ++;
//        }
//
//        Log::notice('BillingHelper::processInvoice(): INFO: Updated expiration dates for ' . $updateCount . ' products of invoice: ' . $invoice->id);
//
//        return $updateCount;
//    }
//
//    protected function updateCustomerProductAttributes($customerProductId, $updateChargeTimestampOnly = false)
//    {
//        $customerProduct = CustomerProduct::find($customerProductId);
//        $firstDayOfMonthTime = strtotime("first day of this month 00:00:00");
//
//        if ($updateChargeTimestampOnly == false)
//        {
//            $timestampMysql = null;
//            if ($customerProduct->product->frequency == 'annual')
//            {
//                // Set the next expiration date to next year for annual plans
//                $dateExpires = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayOfMonthTime));
//            } elseif ($customerProduct->product->frequency == 'monthly')
//            {
//                // Set the next expiration date to next month for monthly plans
//                $dateExpires = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayOfMonthTime));
//            } else
//            {
//                // Mark "onetime" purchases with a 2 to indicate "paid" status so they won't be charged again
//                $customerProduct->invoice_status = config('const.invoice_status.paid');
//                $dateExpires = date('Y-m-d H:i:s');
//            }
//
//            $customerProduct->expires = $dateExpires;
//            $customerProduct->renewed_at = date('Y-m-d H:i:s');
//            $customerProduct->amount_owed -= $customerProduct->product->amount;
//        }
//
//        $customerProduct->last_charged = date('Y-m-d H:i:s');
//        $customerProduct->save();
//    }


//    protected function changeOpenInvoicesToPending()
//    {
//
//        DB::table('invoices')
//            ->where('id_status', config('const.invoice_status.open'))
//            ->update(['id_status' => config('const.invoice_status.pending')]);
//
//    }
//
//    public function processPendingInvoices()
//    {
//        while (true)
//        {
//            $invoices = Invoice::where('status', config('const.invoice_status.pending'))
//                ->take(100)
//                ->get();
//
//            if ($invoices->count() == 0)
//            {
//                break;
//            }
//
//            foreach ($invoices as $invoice)
//            {
//                $invoice = $this->chargePendingInvoice($invoice);
////                $this->addChargeToInvoice($charge, $invoice);
//            }
//        }
//    }
//
//    public function chargePendingInvoice(Invoice $invoice){
//
//
//    }


//    protected function getChargeableCustomerProducts($customerId = null, $buildingId = null)
//    {
//
//        // First day of next month to check expirations against
//        $firstDayofNextMonthMysql = date("Y-m-d H:i:s", strtotime("first day of next month 00:00:00"));
//
//        $queryBuilder = Customer::join('customer_products', 'id_customers', '=', 'customers.id')
//            ->join('products', 'customer_products.id_products', '=', 'products.id')
//            ->join('address', 'address.id_customers', '=', 'customers.id')
//            ->join('buildings', 'address.id_buildings', '=', 'buildings.id')
//            // customer MUST be active
//            ->where('customers.id_status', '=', config('const.status.active'))
//            // the customer's product/service MUST be active
//            ->where('customer_products.id_status', '=', config('const.status.active'))
//            // customer's product/service MUST be expiring before the first day of next month
//            ->where('customer_products.expires', '<=', $firstDayofNextMonthMysql)
//            // skip customer's product/service that are complimentary
//            ->where('products.amount', '>', 0);
//
//        if ($customerId != null)
//        {
//            $queryBuilder = $queryBuilder->where('customers.id', '=', $customerId);
//        }
//
//        if ($buildingId != null)
//        {
//            $queryBuilder = $queryBuilder->where('buildings.id', '=', $buildingId);
//        }
//
//        $queryBuilder = $queryBuilder->where(function ($query) use ($firstDayofNextMonthMysql)
//        {
//            $query->where(function ($query2)
//            {
//                // Get 'onetime' products that have not been invoiced (status = 0)
//                $query2->where('customer_products.charge_status', '<', 1)
//                    ->where('products.frequency', '=', 'onetime');
//            })->orWhere(function ($query3) use ($firstDayofNextMonthMysql)
//            {
//                // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
//                $query3->where('customer_products.charge_status', '<', 2)
//                    ->where('products.frequency', '<>', 'onetime')
//                    ->where('products.frequency', '<>', 'included')
//                    ->where('customer_products.next_charge_date', '<', $firstDayofNextMonthMysql);
//            });
//        });
//
//        return $queryBuilder
//            ->get(array('customers.id as customer_id'
//            , 'buildings.id as building_id'
//            , 'address.id as address_id'
//            , 'products.id as product_id'
//            , 'customers.first_name'
//            , 'customers.last_name'
//            , 'address.address as address'
//            , 'address.unit as unit'
//            , 'address.city as city'
//            , 'address.zip as zip'
//            , 'address.state as state'
//            , 'buildings.nickname as building_code'
//            , 'products.name as product_name'
//            , 'products.description as product_desc'
//            , 'products.amount as product_amount'
//            , 'products.frequency as product_frequency'
//            , 'products.id_types as product_type'
//            , 'customer_products.*'));
//    }

//    protected function addChargesToDatabase($chargeTable)
//    {
//
//        $firstDayofThisMonthTime = strtotime("first day of this month  00:00:00");
//        $lastDayofThisMonthTime = strtotime("last day of this month  00:00:00");
//        $firstDayofThisMonthMysql = date("Y-m-d H:i:s", $firstDayofThisMonthTime);
//        $lastDayofThisMonthMysql = date("Y-m-d H:i:s", $lastDayofThisMonthTime);
//
//        $firstDayofNextMonthTime = strtotime("first day of next month  00:00:00");
//        $firstDayofNextMonthMysql = date("Y-m-d H:i:s", $firstDayofNextMonthTime);
//        $lastDayofNextMonthTime = strtotime("last day of next month  00:00:00");
//        $lastDayofNextMonthMysql = date("Y-m-d H:i:s", $lastDayofNextMonthTime);
//        $startDate = '';
//        $endDate = '';
//        $count = 0;
//        foreach ($chargeTable as $chargeable)
//        {
//
//            $address = $chargeable['address'];
//            if (trim($chargeable['unit'] != ''))
//            {
//                $chargeable['address'] .= "\n" . 'Apt ' . $chargeable['unit'];
//            }
//            $address .= "\n" . trim($chargeable['city']) . ', ' . trim($chargeable['state']) . ' ' . trim($chargeable['zip']);
//
//            if ($chargeable['product_frequency'] == 'monthly')
//            {
//
//                $startDate = $firstDayofNextMonthMysql;
//                $endDate = $lastDayofNextMonthMysql;
//
//            } elseif ($chargeable['product_frequency'] == 'annual')
//            {
//                $startDate = $firstDayofNextMonthMysql;
//                $endDate = $lastDayofNextMonthMysql;
//            } else
//            {
//
//            }
//
//
//            Charge::create(['name'            => trim($chargeable['first_name']) . ' ' . trim($chargeable['last_name']),
//                            'address'         => $address,
//                            'description'     => 'New Charge',
//                            'details'         => json_encode(array('customer_product_id' => $chargeable['id'],
//                                                                   'product_id'          => $chargeable['id_products'],
//                                                                   'product_name'        => $chargeable['product_name'],
//                                                                   'product_desc'        => $chargeable['product_desc'],
//                                                                   'product_amount'      => $chargeable['product_amount'],
//                                                                   'product_frequency'   => $chargeable['product_frequency'],
//                                                                   'product_type'        => $chargeable['product_type']
//                            )),
//                            'amount'          => (strpos($chargeable['product_amount'], '.') === false) ? $chargeable['product_amount'] . '.00' : $chargeable['product_amount'],
//                            'qty'             => 1,
//                            'id_customers'    => $chargeable['customer_id'],
//                            'id_products'     => $chargeable['product_id'],
//                            'id_address'      => $chargeable['address_id'],
//                            'status'          => 'pending',
//                            'type'            => 'charge',
//                            'bill_cycle_day'  => '1',
//                            'processing_type' => config('const.type.auto_pay'),  // auto_pay, manual_pay
//                            'start_date'      => $startDate,
//                            'end_date'        => $endDate,
//                            'due_date'        => $firstDayofNextMonthMysql,
//
//            ]);
//
//
////            if ( ! isset($customerChargeTable[$cid]))
////            {
////                // This is the first time we are seeing this customer so create a new record in the table for them
////                $customerChargeTable[$cid] = array();
////                $customerChargeTable[$cid]['details'] = array();
////                $customerChargeTable[$cid]['amount'] = 0;
////                $customerChargeTable[$cid]['record'] = $record;
////            }
////
////            $customerChargeTable[$cid]['amount'] += $record->product_amount;
////            $customerChargeTable[$cid]['details'][] = array(
////                'customer_product_id' => $record->id,
////                'product_id'          => $record->id_products,
////                'product_name'        => $record->product_name,
////                'product_desc'        => $record->product_desc,
////                'product_amount'      => $record->product_amount,
////                'product_frequency'   => $record->product_frequency,
////                'product_type'        => $record->product_type
////            );
////
////
////            $customer = Customer::find($cid);
////            $address_id = $data['record']->address_id;
////            $address = Address::find($address_id);
////
////            $firstDayofNextMonthTime = strtotime("first day of next month  00:00:00");
////            $firstDayofNextMonthMysql = date("Y-m-d H:i:s", $firstDayofNextMonthTime);
////
////            // Create a new invoice model and fill it up with data then save it
////            $invoice = new Invoice;
////            $invoice->name = trim($customer->first_name) . ' ' . trim($customer->last_name);
////            $invoice->address = trim($address->address);
////            if (trim($address->unit != ''))
////            {
////                $invoice->address .= "\n" . 'Apt ' . $address->unit;
////            }
////            $invoice->address .= "\n" . trim($address->city) . ', ' . trim($address->state) . ' ' . trim($address->zip);
////            $invoice->description = "New Invoice";
////            $invoice->details = json_encode($data['details']);
////            $invoice->amount = (strpos($data['amount'], '.') === false) ? $data['amount'] . '.00' : $data['amount'];
////            $invoice->id_customers = $cid;
////            $invoice->id_address = $address_id;
////            $invoice->status = 'pending';
////            $invoice->failed_charges_count = 0;
////            $invoice->bill_cycle_day = '1';
////            $invoice->processing_type = '13';  // 13 = Autopay, 14 = Manual Pay
////            $invoice->due_date = $firstDayofNextMonthMysql;
////            $invoice->save();
////
////            // Log this event in the invoice log table
////            $this->logInvoice($invoice, 'generated');
////
////            // Create a list of all the customer products that were just invoiced
////            $lineItems = $data['details'];
////            $customerProductIds = array();
////            foreach ($lineItems as $item)
////            {
////                $customerProductIds[] = $item['customer_product_id'];
////            }
////
////            // Update the invoice status and invoice date of the customer products that were just invoiced
////            $this->updateCustomerProductInvoiceStatus($customerProductIds);
////
////            $count ++;
//        }
//
//        return $count;
//    }

//    protected function generateBuildingChargeDataTable($buildingId)
//    {
//
//        // Create a table with charge information
//        $customerChargeTable = array();
//
//        // Get list of chargeable products/services for the requested building
//        $recordList = $this->getChargeableCustomerProductsByBuildingId($buildingId);
//
//        // Go through the list and process the info
//        foreach ($recordList as $record)
//        {
//
//            $cid = $record->customer_id;
//
//            if ( ! isset($customerChargeTable[$cid]))
//            {
//                // This is the first time we are seeing this customer so create a new record in the table for them
//                $customerChargeTable[$cid] = array();
//                $customerChargeTable[$cid]['details'] = array();
//                $customerChargeTable[$cid]['amount'] = 0;
//                $customerChargeTable[$cid]['record'] = $record;
//            }
//
//            $customerChargeTable[$cid]['amount'] += $record->product_amount;
//            $customerChargeTable[$cid]['details'][] = array(
//                'customer_product_id' => $record->id,
//                'product_id'          => $record->id_products,
//                'product_name'        => $record->product_name,
//                'product_desc'        => $record->product_desc,
//                'product_amount'      => $record->product_amount,
//                'product_frequency'   => $record->product_frequency,
//                'product_type'        => $record->product_type
//            );
//        }
//
//        return $customerChargeTable;
//    public function getCustomersWithChargableProducts($buildingId = null)
//    {
//        // First day of next month to check expirations against
//        $firstDayOfNextMonthMysql = date("Y-m-d H:i:s", strtotime("first day of next month 00:00:00"));
//
////        $customers = Customer::with('customerProducts', 'product', 'address')
//        // customer MUST be active
//        $customers = Customer::where('id_status', config('const.status.active'))
//            ->whereHas('customerProducts', function ($query) use ($firstDayOfNextMonthMysql)
//            {
//                // the customer's product/service MUST be active
//                $query->where('id_status', '=', config('const.status.active'))
//                    // customer's product/service MUST be expiring before the first day of next month
//                    ->where('expires', '<=', $firstDayOfNextMonthMysql);
//            })
//            ->whereHas('product', function ($query)
//            {
//                // skip customer's product/service that are complimentary
//                $query->where('amount', '>', 0);
//            })
//            ->whereHas('address', function ($query) use ($buildingId)
//            {
//                // skip customer's product/service that are complimentary
//                $query->where('id_buildings', $buildingId);
//            });
////            ->where(function ($query) use ($firstDayOfNextMonthMysql)
////            {
////                $query->where(function ($query2)
////                {
////                    // Get 'onetime' products that have not been invoiced (status = 0)
////                    $query2->whereHas('product', function ($productQuery)
////                    {
////                        $productQuery->where('frequency', '=', 'onetime');
////                    })
////                        ->whereHas('customerProducts', function ($customerProductQuery)
////                        {
////                            $customerProductQuery->where('charge_status', '<', 1);
////                        });
////
////                })->orWhere(function ($query3) use ($firstDayOfNextMonthMysql)
////                {
////                    // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
////                    $query3->whereHas('product', function ($productQuery)
////                    {
////                        $productQuery->where('frequency', '<>', 'onetime')
////                            ->where('frequency', '<>', 'included');
////                    })
////                        ->whereHas('customerProducts', function ($customerProductQuery) use ($firstDayOfNextMonthMysql)
////                        {
////                            $customerProductQuery->where('charge_status', '<', 2)
////                                ->where('next_charge_date', '<', $firstDayOfNextMonthMysql);
////                        });
////                });
////            });
//        return $customers->get();
//    }

//    }

//    public function getChargeableCustomerProductsByBuildingId($buildingId)
//    {
//        return $this->getChargeableCustomerProducts(null, $buildingId);
//    }
//
//    public function getChargeableCustomerProductsByCustomerId($customerId)
//    {
//        return $this->getChargeableCustomerProducts($customerId);
//    }

//    protected function getChargeableCustomerProductsOld($customerId = null, $buildingId = null)
//    {
//
//        // First day of next month to check expirations against
//        $firstDayofNextMonthMysql = date("Y-m-d H:i:s", strtotime("first day of next month 00:00:00"));
//
//        $queryBuilder = Customer::join('customer_products', 'id_customers', '=', 'customers.id')
//            ->join('products', 'customer_products.id_products', '=', 'products.id')
//            ->join('address', 'address.id_customers', '=', 'customers.id')
//            ->join('buildings', 'address.id_buildings', '=', 'buildings.id')
//            // customer MUST be active
//            ->where('customers.id_status', '=', config('const.status.active'))
//            // the customer's product/service MUST be active
//            ->where('customer_products.id_status', '=', config('const.status.active'))
//            // customer's product/service MUST be expiring before the first day of next month
//            ->where('customer_products.expires', '<=', $firstDayofNextMonthMysql)
//            // skip customer's product/service that are complimentary
//            ->where('products.amount', '>', 0);
//
//        if ($customerId != null)
//        {
//            $queryBuilder = $queryBuilder->where('customers.id', '=', $customerId);
//        }
//
//        if ($buildingId != null)
//        {
//            $queryBuilder = $queryBuilder->where('buildings.id', '=', $buildingId);
//        }
//
//        $queryBuilder = $queryBuilder->where(function ($query) use ($firstDayofNextMonthMysql)
//        {
//            $query->where(function ($query2)
//            {
//                // Get 'onetime' products that have not been invoiced (status = 0)
//                $query2->where('customer_products.charge_status', '<', 1)
//                    ->where('products.frequency', '=', 'onetime');
//            })->orWhere(function ($query3) use ($firstDayofNextMonthMysql)
//            {
//                // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
//                $query3->where('customer_products.charge_status', '<', 2)
//                    ->where('products.frequency', '<>', 'onetime')
//                    ->where('products.frequency', '<>', 'included')
//                    ->where('customer_products.next_charge_date', '<', $firstDayofNextMonthMysql);
//            });
//        });
//
//        return $queryBuilder
//            ->get(array('customers.id as customer_id'
//            , 'buildings.id as building_id'
//            , 'address.id as address_id'
//            , 'products.id as product_id'
//            , 'customers.first_name'
//            , 'customers.last_name'
//            , 'address.address as address'
//            , 'address.unit as unit'
//            , 'address.city as city'
//            , 'address.zip as zip'
//            , 'address.state as state'
//            , 'buildings.nickname as building_code'
//            , 'products.name as product_name'
//            , 'products.description as product_desc'
//            , 'products.amount as product_amount'
//            , 'products.frequency as product_frequency'
//            , 'products.id_types as product_type'
//            , 'customer_products.*'));
//    }

//    public function getChargeableCustomerProducts2($customerId = null, $buildingId = null)
//    {
//
//        // First day of next month to check expirations against
//        $firstDayOfNextMonthMysql = date("Y-m-d H:i:s", strtotime("first day of next month 00:00:00"));
//
////        $building = Building::find($buildingId);
////        $buildingAddress = $building->address;
//
//        CustomerProduct::with('customer', 'product', 'address')
//            ->whereHas('customer', function ($query)
//            {
//                // customer MUST be active
//                $query->where('id_status', config('const.status.active'));
//            })
//            ->whereHas('product', function ($query)
//            {
//                // skip customer's product/service that are complimentary
//                $query->where('amount', '>', 0);
//            })
//            ->whereHas('address', function ($query) use ($buildingId)
//            {
//                // skip customer's product/service that are complimentary
//                $query->where('id_buildings', $buildingId);
//            })
//            // the customer's product/service MUST be active
//            ->where('id_status', '=', config('const.status.active'))
//            // customer's product/service MUST be expiring before the first day of next month
//            ->where('expires', '<=', $firstDayOfNextMonthMysql)
//            ->where(function ($query) use ($firstDayOfNextMonthMysql)
//            {
//                $query->where(function ($query2)
//                {
//                    // Get 'onetime' products that have not been invoiced (status = 0)
//                    $query2->whereHas('product', function ($productQuery)
//                    {
//                        $productQuery->where('frequency', '=', 'onetime');
//                    })
//                        ->where('charge_status', '<', 1);
//
//                })->orWhere(function ($query3) use ($firstDayOfNextMonthMysql)
//                {
//                    // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
//                    $query3->whereHas('product', function ($productQuery)
//                    {
//                        $productQuery->where('frequency', '<>', 'onetime')
//                            ->where('frequency', '<>', 'included');
//                    })
//                        ->where('charge_status', '<', 2)
//                        ->where('next_charge_date', '<', $firstDayOfNextMonthMysql);
//                });
//            });
//
//
////        CustomerProduct::with([
////            'customer' => function ($query)
////            {
////                // customer MUST be active
////                $query->where('id_status', config('const.status.active'));
////            },
////            'product'  => function ($query)
////            {
////                // skip customer's product/service that are complimentary
////                $query->where('amount', '>', 0);
////            },
////            'building' => function ($query) use ($buildingId)
////            {
////                // skip customer's product/service that are complimentary
////                $query->where('id', $buildingId);
////            },
////            'address'
////        ])
////            // the customer's product/service MUST be active
////            ->where('id_status', '=', config('const.status.active'))
////            // customer's product/service MUST be expiring before the first day of next month
////            ->where('expires', '<=', $firstDayofNextMonthMysql);
//
//    }


//    public function generateResidentialChargeRecordsOLD()
//    {
//
//        // Get residential buildings
//        $buildings = Building::with(['properties' => function ($query)
//        {
//            $query->where('id_building_properties', config('const.building_property.service_type'))
//                ->where('value', 'Retail')
//                ->orWhere('value', 'Bulk');
//        }])
//            ->where('id', 28)->get();
//
//        $count = 0;
//
//        foreach ($buildings as $building)
//        {
//            $invoiceDataTable = $this->generateBuildingInvoiceDataTable($building->id);
//            $count = $this->addInvoicesToDatabase($invoiceDataTable);
//            error_log('BillingHelper::generateInvoiceRecords(): Added invoices for ' . $building->nickname . ' to DB');
//        }
//
//        return 'Generated ' . $count . ' invoices and added them to the DB.';
//
//    }

//    public function generateResidentialInvoiceRecords()
//    {
//
//        // Get residential buildings
//        $buildings = Building::with(['properties' => function ($query)
//        {
//            $query->where('id_building_properties', config('const.building_property.service_type'))
//                ->where('value', 'Retail')
//                ->orWhere('value', 'Bulk');
//        }])
//            ->where('id', 28)->get();
//
//        $count = 0;
//
//        foreach ($buildings as $building)
//        {
//            $invoiceDataTable = $this->generateBuildingInvoiceDataTable($building->id);
//            $count = $this->addInvoicesToDatabase($invoiceDataTable);
//            error_log('BillingHelper::generateInvoiceRecords(): Added invoices for ' . $building->nickname . ' to DB');
//        }
//
//        return 'Generated ' . $count . ' invoices and added them to the DB.';
//
//    }


    protected function generateBuildingInvoiceDataTable($buildingId)
    {

        // Create an invoice info table
        $customerInvoiceTable = array();

        // Get list of invoiceable products/services for the requested building
        $recordList = $this->getInvoiceableCustomerProductsByBuildingId($buildingId);

        // Go through the list and process the info
        foreach ($recordList as $record)
        {

            $cid = $record->customer_id;

            if ( ! isset($customerInvoiceTable[$cid]))
            {
                // This is the first time we are seeing this customer so create a new record in the table for them
                $customerInvoiceTable[$cid] = array();
                $customerInvoiceTable[$cid]['details'] = array();
                $customerInvoiceTable[$cid]['amount'] = 0;
                $customerInvoiceTable[$cid]['record'] = $record;
            }

            $customerInvoiceTable[$cid]['amount'] += $record->product_amount;
            $customerInvoiceTable[$cid]['details'][] = array(
                'customer_product_id' => $record->id,
                'product_id'          => $record->id_products,
                'product_name'        => $record->product_name,
                'product_desc'        => $record->product_desc,
                'product_amount'      => $record->product_amount,
                'product_frequency'   => $record->product_frequency,
                'product_type'        => $record->product_type
            );
        }

        return $customerInvoiceTable;
    }

    protected function addInvoicesToDatabase($invoiceDataTable)
    {

        $count = 0;
        foreach ($invoiceDataTable as $cid => $data)
        {

            $customer = Customer::find($cid);
            $address_id = $data['record']->address_id;
            $address = Address::find($address_id);

            $firstDayofNextMonthTime = strtotime("first day of next month  00:00:00");
            $firstDayofNextMonthMysql = date("Y-m-d H:i:s", $firstDayofNextMonthTime);

            // Create a new invoice model and fill it up with data then save it
            $invoice = new Invoice;
            $invoice->name = trim($customer->first_name) . ' ' . trim($customer->last_name);
            $invoice->address = trim($address->address);
            if (trim($address->unit != ''))
            {
                $invoice->address .= "\n" . 'Apt ' . $address->unit;
            }
            $invoice->address .= "\n" . trim($address->city) . ', ' . trim($address->state) . ' ' . trim($address->zip);
            $invoice->description = "New Invoice";
            $invoice->details = json_encode($data['details']);
            $invoice->amount = (strpos($data['amount'], '.') === false) ? $data['amount'] . '.00' : $data['amount'];
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
            foreach ($lineItems as $item)
            {
                $customerProductIds[] = $item['customer_product_id'];
            }

            // Update the invoice status and invoice date of the customer products that were just invoiced
            $this->updateCustomerProductInvoiceStatus($customerProductIds);

            $count ++;
        }

        return $count;
    }

    protected function updateCustomerProductInvoiceStatus($customerProductIds = array())
    {

        // Default to the first day of the month
        $firstDayofMonthTime = strtotime("first day of this month 00:00:00");
        $firstDayofMonthMysql = date("Y-m-d H:i:s", $firstDayofMonthTime);

        foreach ($customerProductIds as $customerProductId)
        {
            $customerProduct = CustomerProduct::find($customerProductId);
            if ($customerProduct->product->frequency == 'annual')
            {
                // Set the next invoiceable date to next year for annual products
                $timestampMysql = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayofMonthTime));
            } elseif ($customerProduct->product->frequency == 'monthly')
            {
                // Set the next invoiceable date to next month for monthly products
                $timestampMysql = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayofMonthTime));
            } else
            {
                $timestampMysql = '0000-00-00 00:00:00';
            }

            $customerProduct->next_invoice_date = $timestampMysql;
            $customerProduct->invoice_status = '1';
            $customerProduct->amount_owed += $customerProduct->product->amount;
            $customerProduct->save();
        }
    }


    protected function updateCustomerProductDates($customerProductIds = array(), $updateChargeTimestampOnly = false)
    {

        $firstDayOfMonth = mktime(0, 0, 0, date("m"), '01', date("Y"));
        $update_count = 0;
        foreach ($customerProductIds as $customerProductId)
        {

            $customerProduct = CustomerProduct::find($customerProductId);

            if ($updateChargeTimestampOnly == false)
            {

                $dateExpires = '';
                $chargeFreq = $customerProduct->product->frequency;

                if ($chargeFreq == 'monthly')
                {
                    // Set the next expiration date to next month for monthly plans
                    $dateExpires = date('Y-m-d H:i:s', strtotime('+1 month', $firstDayOfMonth));
                } else if ($chargeFreq == 'annual')
                {
                    // Set the next expiration date to next year for annual plans
                    $dateExpires = date('Y-m-d H:i:s', strtotime('+1 year', $firstDayOfMonth));
                } else
                {
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
            $update_count ++;
        }

        return $update_count;
    }

    protected function sendInvoiceReceiptEmail(Invoice $invoice, $chargeResult)
    {
        $subject = 'Service Charge Receipt: ' . date('F') . ' ' . date('Y');
        $template = 'email.template_customer_invoice_receipt';
        $this->sendInvoiceResponseEmail($invoice, $chargeResult, $subject, $template);
    }

    protected function sendChargeDeclienedEmail(Invoice $invoice, $chargeResult)
    {
        $template = 'email.template_customer_charge_declined';
        if ($chargeResult == null || isset($chargeResult['PaymentType']) == false)
        {
            Log::info('BillingHelper::sendChargeDeclienedEmail(): INFO: Did not send a declined email for invoice id=' . $invoice->id . ' due to missing credit card info.');

            return false;
        }
        if ($chargeResult['PaymentType'] == 'Credit Card')
        {
            $subject = 'NOTICE: Credit Card Declined';
        } elseif ($chargeResult['PaymentType'] == 'Checking Account')
        {
            $subject = 'NOTICE: ACH Declined';
        } else
        {
            $subject = 'NOTICE: Charge Declined';
        }
        $this->sendInvoiceResponseEmail($invoice, $chargeResult, $subject, $template);
    }

    protected function sendInvoiceResponseEmail(Invoice $invoice, $chargeResult, $subject, $template)
    {

        $customer = Customer::find($invoice->id_customers);
        // If no customer was found create a Customer model populate it from the name col and send it in

        $address = Address::find($invoice->id_address);
        // If no address was found create an Address model populate it from the address col and send it in

        $toAddress = ($this->testMode) ? 'peyman@silverip.com' : $customer->email;

        $lineItems = $invoice->details(); //($invoice->details != null && $invoice->details != '') ? json_decode($invoice->details, true) : array();
        $chargeDetails = array();
        $chargeDetails['TRANSACTIONId'] = $chargeResult['TRANSACTIONID'];
        $chargeDetails['PaymentType'] = $chargeResult['PaymentType'];
        $chargeDetails['PaymentTypeDetails'] = $chargeResult['PaymentTypeDetails'];

        Mail::send(array('html' => $template), ['invoice' => $invoice, 'customer' => $customer, 'address' => $address, 'lineItems' => $lineItems, 'chargeDetails' => $chargeDetails], function ($message) use ($toAddress, $subject, $customer, $address, $lineItems, $chargeDetails) {
            $message->from('help@silverip.com', 'SilverIP Customer Care');
            $message->to($toAddress, trim($customer->first_name) . ' ' . trim($customer->last_name))->subject($subject);
        });
    }

    public function getInvoiceableCustomerProductsByBuildingId($building_id)
    {
        return $this->getInvoiceableCustomerProducts(null, $building_id);
    }

    public function getInvoiceableCustomerProductsByCustomerId($customer_id)
    {
        return $this->getInvoiceableCustomerProducts($customer_id);
    }

    protected function getInvoiceableCustomerProducts($customerId = null, $buildingId = null)
    {

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

        if ($customerId != null)
        {
            $queryBuilder = $queryBuilder->where('customers.id', '=', $customerId);
        }

        if ($buildingId != null)
        {
            $queryBuilder = $queryBuilder->where('buildings.id', '=', $buildingId);
        }

        $queryBuilder = $queryBuilder->where(function ($query) use ($firstDayofNextMonthMysql) {
            $query->where(function ($query2) {
                // Get 'onetime' products that have not been invoiced (status = 0)
                $query2->where('customer_products.invoice_status', '<', 1)
                    ->where('products.frequency', '=', 'onetime');
            })->orWhere(function ($query3) use ($firstDayofNextMonthMysql) {
                // Get 'monthly' and/or 'annual' products that have not been invoiced (status = 0 or 1 and an expired invoice date)
                $query3->where('customer_products.invoice_status', '<', 2)
                    ->where('products.frequency', '<>', 'onetime')
                    ->where('products.frequency', '<>', 'included')
                    ->where('customer_products.next_invoice_date', '<', $firstDayofNextMonthMysql);
            });
        });

        return $queryBuilder
            ->get(array('customers.id as customer_id'
            , 'buildings.id as building_id'
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

        if ($issue != '' && $newTicketDetails != '' && $newTicketStatus != '')
        {

            //SQL INSERT TICKET
            $ticketItemArray = array();
            $ticketItemArray[] = "`CID` = '" . $newTicketCID . "'";
            $ticketNumber = '';

            //         $ticketNumberSql = "SELECT max(TicketNumber) AS ticket_id FROM supportTickets";
            $ticketNumberSql = "SELECT TicketNumber AS ticket_id FROM supportTickets where TID in (SELECT max(TID) FROM supportTickets)";
            $maxTnumberRes = mysql_query($ticketNumberSql) or die(mysql_error());
            $rowTnumber = mysql_fetch_array($maxTnumberRes);
            if ($rowTnumber['ticket_id'])
            {
                $maxTNumberArr = explode("-", $rowTnumber['ticket_id']);
                $maxTNumber = $maxTNumberArr[1] + 1;
                $ticketNumber = 'ST-' . $maxTNumber;
            }

            $ticketItemArray[] = "`TicketNumber` = '" . $ticketNumber . "'";
            if ($vendorTID != '')
            {
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

            if ($sendEmail)
            {
                $reasonInfoSql = "SELECT * FROM supportTicketReasons WHERE `RID` = '" . $issue . "'";
                $reasonInfoRes = mysql_query($reasonInfoSql) or die(mysql_error());
                $reasonInfoRow = mysql_fetch_array($reasonInfoRes);
                $customerInfo = getCustomerWithLocInfoByCID($newTicketCID);
                $adminUserInfo = getAdminUserByID($AdminUser_ID);

                $mail_config ['emailHeader'] = 'New Support Ticket';
                $mail_config ['fields']['Ticket Status'] = ucfirst($newTicketStatus);

                if ($newTicketCID == '0')
                {
                    $mail_config ['fields']['Name'] = 'Unknown';
                } else
                {
                    $mail_config ['fields']['Name'] = $customerInfo['Firstname'] . ' ' . $customerInfo['Lastname'];
                }

                $mail_config ['fields']['Ticket'] = '<a href="https://admin.silverip.net/customerinfo/browser_detect.php?tid=' . $ticketID . '">' . $ticketNumber . '</a>';
                $mail_config ['fields']['Timestamp'] = date("g:i a M j, Y ", strtotime($currTimestamp));
                if (trim($customerInfo['Address']) != '')
                {
                    $mail_config ['fields']['Address'] = $customerInfo['Address'] . ', #' . $customerInfo['Unit'];
                }
                if (trim($customerInfo['Tel']) != '')
                {
                    $mail_config ['fields']['Phone'] = $customerInfo['Tel'];
                }
                if (trim($customerInfo['Email']) != '')
                {
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
