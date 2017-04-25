<?php

namespace App\Extensions;

use Auth;
use Log;
use Mail;
use Illuminate\Http\Request;
//Models
use App\Models\Customer;
use App\Models\Ticket;

class SendMail {

  public function test(){
    return'this is the test of the sendMailProvideer.';
  }
  public function newTicketMail($request){

    $ticketData = Ticket::with('customer', 'address', 'reason', 'contacts', 'user')->find($request->id);

    //$toAddress = ['pablo@silverip.com', 'pol.laris@gmail.com', 'peyman@silverip.com'];
    $toAddress = $ticketData->customer->email;
    $template  = 'email.template_support_new_ticket';

    $subject   = $ticketData->address->code .
                 ' #' .
                 $ticketData->address->unit .
                 ', ' .
                 $ticketData->customer->first_name .
                 ' ' .
                 $ticketData->customer->last_name;

    Mail::send(array('html'  => $template), ['data'  => $ticketData],
                                             function($message) use($toAddress,
                                                                    $subject,
                                                                    $ticketData)
                                             {
                                                $message->from('noreply@silverip.net', 'SilverIP');
                                                $message->to($toAddress,trim($ticketData->customer->first_name).' '.trim($ticketData->customer->last_name))
                                                ->subject($subject);
                                             }
    );
    return 'MAIL SENT';

  }

  public function signupMail($request){
  /*FIX CONTENT TO RESPECTIVE MAIL

    $signupData = //relation();

    $toAddress = ['pablo@silverip.com', 'pol.laris@gmail.com', 'peyman@silverip.com'];
    $template  = 'email.template_';

    $subject   = $signupData->address->code .
      ' #' .
      $signupData->address->unit .
      ', ' .
      $signupData->customer->first_name .
      ' ' .
      $signupData->customer->last_name;

    Mail::send(array('html'  => $template), ['data'  => $signupData],
      function($message) use($toAddress,
        $subject,
        $signupData)
      {
        $message->from('noreply@silverip.net', 'SilverIP');
        $message->to($toAddress,trim($signupData->customer->first_name).' '.trim($signupData->customer->last_name))
          ->subject($subject);
      }
    );
    return 'MAIL SENT';
  */

  }
}

?>