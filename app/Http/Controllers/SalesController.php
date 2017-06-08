<?php

namespace App\Http\Controllers;



use Illuminate\Http\Request;
use Route;
use DB;
use Auth;




class SalesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        DB::connection()->enableQueryLog();

    }

    public function uploadSalesFiles(Request $request)
    {
        dd('WARPOLIN');
    }

    public function renderViewUno(Request $request)
    {
        return view('sales.templateA')->render();












        die();
        $toAddress = $ticketData->customer->email;
        $template  = 'email.template_support_ticket';
        $ticketData->mailType  = $type == 1 ? 'New Support Ticket' : 'Ticket Update';
        $ticketData->type      = $type ;

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





    }


}
