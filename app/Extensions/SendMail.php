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

    public function test()
    {
        return 'this is the test of the sendMailProvideer.';
    }

    public function ticketMail($request, $status)
    {

        $fromName = '';
        $fromAddress = 'noreply@silverip.net';
        $toAddress = config('mail.support-ticket-recipient'); //['pablo@silverip.com', 'pol.laris@gmail.com', 'peyman@silverip.com'];
        $template = 'email.template_support_ticket';
        $ticket = null;

        switch ($status)
        {
            case config('const.ticket_status.new'):
                $fromName = 'New Ticket';
                $ticket = Ticket::with('customer', 'address', 'reason', 'contacts', 'user')->find($request->id);
                $ticket->mailType = 'New Support Ticket';
                break;
            case config('const.ticket_status.escalated'):
                $fromName = 'Ticket Update';
                $ticket = Ticket::with('customer', 'address', 'contacts', 'user', 'lastTicketHistory.reason')->find($request->id_tickets);
                $ticket->mailType = 'Ticket Update';
                break;
            case config('const.ticket_status.closed'):
                $fromName = 'Ticket Closed';
                $ticket = Ticket::with('customer', 'address', 'contacts', 'user', 'lastTicketHistory.reason')->find($request->id_tickets);
                $ticket->mailType = 'Ticket Closed';
                break;
        }

//        $ticket->type = $type;

        if ($ticket != null)
        {
            $subject = $ticket->address->code .
                ' #' .
                $ticket->address->unit .
                ', ' .
                $ticket->customer->first_name .
                ' ' .
                $ticket->customer->last_name;

            Mail::send(array('html' => $template), ['data' => $ticket, 'status' => $status],
                function ($message) use (
                    $fromName,
                    $fromAddress,
                    $toAddress,
                    $subject,
                    $ticket
                )
                {
                    $message->from($fromAddress, $fromName);
                    $message->to($toAddress, trim($ticket->customer->first_name) . ' ' . trim($ticket->customer->last_name))
                        ->subject($subject);
                }
            );

            return 'ticketMail():: Ticket email sent';
        }

        return 'ticketMail():: Ticket not found';

    }

    public function signupMail($request)
    {
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

    public function generalEmail($messageInfo, $template, $templateData, $attachement= null)
    {

        $fromName = $messageInfo['fromName'];
        $fromAddress = $messageInfo['fromAddress'];
        $toName = $messageInfo['toName'];
        $toAddress = $messageInfo['toAddress'];
        $subject = $messageInfo['subject'];
        $template = $template;

        Mail::send(array('html' => $template), $templateData,
            function ($message) use (
                $fromName,
                $fromAddress,
                $toName,
                $toAddress,
                $subject,
                $attachement
            )
            {
                $message->from($fromAddress, $fromName);
                $message->to($toAddress, $toName)
                    ->subject($subject);
                if($attachement != ''){
                    $message->attach($attachement);
                }
            }
        );

        return 'generalEmail():: email sent';
    }
}

?>