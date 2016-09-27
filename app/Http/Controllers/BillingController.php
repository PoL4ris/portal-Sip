<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Models\Customer\Customers;

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
}
