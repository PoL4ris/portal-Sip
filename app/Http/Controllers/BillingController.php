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

      $cid = $input['cid'];
      $amountToCharge = $input['amount'];
      $chargeDesc = $input['desc'];

Log::notice('BillingController::charge(): creating new SIPBilling ...');
      $sipBilling = new SIPBilling;
      $result = $sipBilling->chargeCC($cid, $amountToCharge, $chargeDesc);
      if (isset($result['RESPONSETEXT']) == false || $result['RESPONSETEXT'] != 'APPROVED') {
          Log::notice('BillingController::charge(): action[charge] Failed: ' . print_r($result, true));
      } else {
          Log::notice('BillingController::charge(): charge was successful');
      }
      return $result;
    }
    
     public function refund(Request $request){
      $input = $request->all();

      $cid = $input['cid'];
      $amountToCharge = $input['amount'];
      $chargeDesc = $input['desc'];

      $sipBilling = new SIPBilling;
      $result = $sipBilling->refundCC($cid, $amountToCharge, $chargeDesc);
      if (isset($result['RESPONSETEXT']) == false || $result['RESPONSETEXT'] != 'RETURN ACCEPTED') {
          error_log('billing_hanlder.php: action[refund] Failed: ' . print_r($result, true));
      }
      return $result;
    }
    public function insertPaymentMethod(Request $request)
    {


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


  public function validateCard($number)
  {
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
