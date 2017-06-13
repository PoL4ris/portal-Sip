<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Extensions\BillingHelper;
use App\Models\Customer;
use App\Models\Charge;
use App\Models\Address;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use Log;
use Auth;

use ActivityLogs;

class BillingController extends Controller {

    protected $logType;

    public function __construct()
    {
        $this->middleware('auth');
        $this->logType = 'billing';
    }

    public function charge(Request $request)
    {

        $input = $request->all();
        $customerId = $input['cid'];
        $amountToCharge = $input['amount'];
        $chargeDesc = $input['desc'];

        $pm = PaymentMethod::where('id_customers', $customerId)->where('priority', 1)->first();
        if ($pm == null)
        {
            Log::notice('BillingController::charge(): PaymentMethod not found for customer with id: ' . $customerId);

            return 'ERROR';
        }

        $sipBilling = new SIPBilling();
        $result = $sipBilling->chargePaymentMethod($pm->id, $amountToCharge, $chargeDesc);

        return $result;
    }

    public function refund(Request $request)
    {

        $input = $request->all();
        $customerId = $input['cid'];
        $amountToCharge = $input['amount'];
        $chargeDesc = $input['desc'];

        $pm = PaymentMethod::where('id_customers', $customerId)->where('priority', 1)->first();
        if ($pm == null)
        {
            Log::notice('BillingController::refund(): PaymentMethod not found for customer with id: ' . $customerId);

            return 'ERROR';
        }

        $sipBilling = new SIPBilling;
        $result = $sipBilling->refundPaymentMethod($pm->id, $amountToCharge, $chargeDesc);

        return $result;
    }

    public function manualCharge(Request $request)
    {

        $input = $request->all();
        $customerId = $input['cid'];
        $amount = $input['amount'];
        $chargeDesc = $input['desc'];
        $user = Auth::user();

        $customer = Customer::find($customerId);
        if ($customer == null)
        {
            Log::notice('BillingController::manualCharge(): Customer not found with id: ' . $customerId);

            return 'ERROR';
        }
        $billingHelper = new BillingHelper();
        $result = $billingHelper->createManualChargeForCustomer($customer, $amount, $chargeDesc, $user->id);

        return 'OK';
    }

    public function manualRefund(Request $request)
    {

        $input = $request->all();
        $customerId = $input['cid'];
        $amount = $input['amount'];
        $chargeDesc = $input['desc'];
        $user = Auth::user();

        $customer = Customer::find($customerId);
        if ($customer == null)
        {
            Log::notice('BillingController::manualRefund(): Customer not found with id: ' . $customerId);

            return 'ERROR';
        }
        $billingHelper = new BillingHelper();
        $result = $billingHelper->createManualRefundForCustomer($customer, $amount, $chargeDesc, $user->id);

        return 'OK';
    }

    public function updateManualCharge(Request $request)
    {

        $input = $request->all();
        $chargeId = $input['id'];
        $amount = $input['amount'];
        $comment = $input['desc'];
        $user = Auth::user();

        $charge = Charge::find($chargeId);
        if ($charge == null)
        {
            Log::notice('BillingController::updateCharge(): Charge not found with id: ' . $chargeId);

            return 'ERROR';
        }
        $billingHelper = new BillingHelper();
        $result = $billingHelper->updateManualChargeAmount($charge, $amount, $comment, $user->id);

        return ['response'=>'OK', 'updated_data' => $this->getPendingManualCharges()];
    }

    public function approveManualCharge(Request $request)
    {
        $input = $request->all();
        $chargeId = $input['id'];

        $charge = Charge::find($chargeId);
        if ($charge == null)
        {
            Log::notice('BillingController::approveManualCharge(): Charge not found with id: ' . $chargeId);

            return 'ERROR';
        }
        $billingHelper = new BillingHelper();
        $result = $billingHelper->approveManualCharge($charge);

        return $this->getPendingManualCharges();
    }

    public function denyManualCharge(Request $request)
    {

        $input = $request->all();
        $chargeId = $input['id'];

        $charge = Charge::find($chargeId);
        if ($charge == null)
        {
            Log::notice('BillingController::denyManualCharge(): Charge not found with id: ' . $chargeId);

            return 'ERROR';
        }
        $billingHelper = new BillingHelper();
        $result = $billingHelper->denyManualCharge($charge);

        return $this->getPendingManualCharges();
    }

    public function getPendingManualChargesByCustomer(Request $request)
    {
        $input = $request->all();
        $customerId = $input['cid'];
        $customer = Customer::find($customerId);
        if ($customer == null)
        {
            Log::notice('BillingController::getPendingManualCharges(): Customer not found with id: ' . $customerId);

            return 'ERROR';
        }
        return $customer->pendingManualCharges;
    }

    public function getPendingManualCharges()
    {

        return Charge::where('status', config('const.charge_status.pending_approval'))
                      ->where('processing_type', config('const.type.manual_pay'))
                      ->get();
    }

    public function insertPaymentMethod(Request $request)
    {


        //    CC VALIDATE IMPORTANT!!!
        //      $cardResult = $this->validateCard($request->account_number);
        //
        //      if(!$cardResult)
        //        return 'ERROR: CARD INVALID';


        if ($request->id)
        {

            $pm = PaymentMethod::find($request->id);

            // Copy the old model so we can use it to log this update
            $oldModel = $pm->replicate();
            $oldModel->id = $pm->id;

            // Update the payment method with the info from the request
            $pm->exp_month = $request->exp_month;
            $pm->exp_year = $request->exp_year;
            $pm->save();

            $data = $pm->getProperty('last four');

            $newData = array();
            $newData['exp_month'] = $pm->exp_month;
            $newData['exp_year'] = $pm->exp_year;

            ActivityLogs::add($this->logType, $request->id_customers, 'update', 'insertPaymentMethod', $oldModel, $newData, $data, ('update-payment'));

        } else
        {

            $address = Address::where('id_customers', $request->id_customers)->first();
            if ($address == null)
            {
                return 'ERROR';
            }

            $pm = new PaymentMethod;
            $pm->account_number = $request->account_number;
            $pm->exp_month = $request->exp_month;
            $pm->exp_year = $request->exp_year;
            $pm->types = 'Credit Card';
            $pm->billing_phone = $request->billing_phone;
            $pm->priority = 1; // MEANS DEFAULT
            $pm->card_type = $request->card_type;
            $pm->id_address = $address->id;
            $pm->id_customers = $request->id_customers;
            $pm->save();

            // Set other cards to 0 (not default)
            PaymentMethod::where('id_customers', $request->id_customers)
                ->where('id', '!=', $pm->id)
                ->update(['priority' => 0]);

            $newData = array();
            $newData['account_number'] = $pm->account_number;
            $newData['exp_month'] = $pm->exp_month;
            $newData['exp_year'] = $pm->exp_year;
            $newData['types'] = 'Credit Card';
            $newData['card_type'] = $pm->card_type;
            ActivityLogs::add($this->logType, $request->id_customers, 'update', 'insertPaymentMethod', null, $newData, null, ('update-payment'));
        }


        //ACTIVITY LOG HERE

        return 'OK';
    }

    public function validateCard($number)
    {

        global $type;

        $cardtype = array(
            "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "mastercard" => "/^5[1-5][0-9]{14}$/",
            "amex"       => "/^3[47][0-9]{13}$/",
            "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
        );

        if (preg_match($cardtype['visa'], $number))
        {
            $type = "visa";

            return 'visa';
        } else if (preg_match($cardtype['mastercard'], $number))
        {
            $type = "mastercard";

            return 'mastercard';
        } else if (preg_match($cardtype['amex'], $number))
        {
            $type = "amex";

            return 'amex';

        } else if (preg_match($cardtype['discover'], $number))
        {
            $type = "discover";

            return 'discover';
        } else
        {
            return false;
        }
    }
}
