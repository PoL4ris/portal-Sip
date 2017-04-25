<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use DB;
use Auth;
use Mail;
use SendMail;

use App\Models\Customer;



class MailController extends Controller
{
  public function __construct() {
    $this->middleware('auth');
  }

  public function sendCustomerMail(Request $request)
  {

  $tmp = SendMail::test();

  print '<pre>';
  print_r($tmp);
  die();

  return;
    //MAIL

    $customer  = Customer::with('address')->find(501);
    $address   = $customer->address;
    //$toAddress = ['pablo@silverip.com', 'pol.laris@gmail.com', 'peyman@silverip.com'];
    $toAddress = ['pablo@silverip.com'];
    $template  = 'mail.signup';
    $subject   = 'dummy Test Mail';

    $data = array();
    $data['uno']  = '111';
    $data['dos']  = '222';
    $data['tres'] = '333';

    Mail::send(array('html'      => $template),
                    ['customer'  => $customer,
                      'address'  => $address,
                      'data'     => $data
                    ], function($message)
                        use($toAddress,
                            $subject,
                            $customer,
                            $address,
                            $data) {
                                    $message->from('pablo@silverip.com', 'SilverIP');
                                    $message->to($toAddress,
                                                 trim($customer->first_name).' '.trim($customer->last_name)
                                                )->subject($subject);
                                    }
              );

    return 'MAIL SENT';
  }

}
