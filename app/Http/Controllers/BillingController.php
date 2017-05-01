<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Models\Customer;
use App\Models\Address;
use App\Models\PaymentMethod;
use Log;

class BillingController extends Controller
{

    public function __construct(){
        $this->middleware('auth');
    }

    public function charge(Request $request){

        $input = $request->all();
        $customerId = $input['cid'];
        $amountToCharge = $input['amount'];
        $chargeDesc = $input['desc'];

        $pm = PaymentMethod::where('id_customers', $customerId)->where('priority', 1)->first();
        if($pm == null){
            Log::notice('BillingController::charge(): PaymentMethod not found for customer with id: ' . $customerId);
            return 'ERROR';
        }

        $sipBilling = new SIPBilling();
        $result = $sipBilling->chargePaymentMethod($pm->id, $amountToCharge, $chargeDesc);

        return $result;
    }

    public function refund(Request $request){

        $input = $request->all();
        $customerId = $input['cid'];
        $amountToCharge = $input['amount'];
        $chargeDesc = $input['desc'];

        $pm = PaymentMethod::where('id_customers', $customerId)->where('priority', 1)->first();
        if($pm == null){
            Log::notice('BillingController::refund(): PaymentMethod not found for customer with id: ' . $customerId);
            return 'ERROR';
        }

        $sipBilling = new SIPBilling;
        $result = $sipBilling->refundPaymentMethod($pm->id, $amountToCharge, $chargeDesc);

        return $result;
    }

    public function insertPaymentMethod(Request $request){


        //    CC VALIDATE IMPORTANT!!!
        //      $cardResult = $this->validateCard($request->account_number);
        //
        //      if(!$cardResult)
        //        return 'ERROR: CARD INVALID';


        if($request->id) {
            $pm = PaymentMethod::find($request->id);
            $pm->exp_month = $request->exp_month;
            $pm->exp_year  = $request->exp_year;
            $pm->save();
        }
        else{

            $address = Address::where('id_customers', $request->id_customers)->first();
            if($address == null){
                return 'ERROR';
            }

            $pm = new PaymentMethod;
            $pm->account_number = $request->account_number;
            $pm->exp_month      = $request->exp_month;
            $pm->exp_year       = $request->exp_year;
            $pm->types          = 'Credit Card';
            $pm->billing_phone  = $request->billing_phone;
            $pm->priority       = 1; // MEANS DEFAULT
            $pm->card_type      = $request->card_type;
            $pm->id_address     = $address->id;
            $pm->id_customers   = $request->id_customers;
            $pm->save();
        }

        //ACTIVITY LOG HERE

        return 'OK';
    }


    public function validateCard($number) {

        global $type;

        $cardtype = array(
            "visa"       => "/^4[0-9]{12}(?:[0-9]{3})?$/",
            "mastercard" => "/^5[1-5][0-9]{14}$/",
            "amex"       => "/^3[47][0-9]{13}$/",
            "discover"   => "/^6(?:011|5[0-9]{2})[0-9]{12}$/",
        );

        if (preg_match($cardtype['visa'],$number))
        {
            $type = "visa";
            return 'visa';
        }
        else if (preg_match($cardtype['mastercard'],$number))
        {
            $type = "mastercard";
            return 'mastercard';
        }
        else if (preg_match($cardtype['amex'],$number))
        {
            $type = "amex";
            return 'amex';

        }
        else if (preg_match($cardtype['discover'],$number))
        {
            $type = "discover";
            return 'discover';
        }
        else
        {
            return false;
        }
    }
}
