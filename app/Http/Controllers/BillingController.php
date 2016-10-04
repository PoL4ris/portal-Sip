<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Models\Customer;
use App\Models\PaymentMethod;


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


      $sipBilling = new SIPBilling;
      $result = $sipBilling->chargeCC($cid, $amountToCharge, $chargeDesc);
      if (isset($result['RESPONSETEXT']) == false || $result['RESPONSETEXT'] != 'APPROVED') {
          error_log('billing_hanlder.php: action[charge] Failed: ' . print_r($result, true));
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

      $input = $request->all();

      $pm = new PaymentMethod;
      $pm->id_customers = $input['id_customers'];
      $pm->id_address = '33';
      $pm->types = 'Credit Card';
      $pm->card_type = $input['card_type'];
      $pm->account_number = $input['account_number'];
      $pm->CCscode = $input['CCscode'];
      $pm->exp_month = $input['exp_month'];
      $pm->exp_year = $input['exp_year'];
      $pm->billing_phone = $input['billing_phone'];
      $pm->save();

      if($pm->account_number == 'ERROR')
        return 'ERROR';
      else
        return 'OK';

    }
}
