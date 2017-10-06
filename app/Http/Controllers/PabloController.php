<?php

namespace App\Http\Controllers;

use App\Models\BillingTransactionLog;
use Auth;
use Config;
use DB;
use Log;
use Mail;
use SendMail;
use View;

use App\Models\BuildingTicket;
use App\Models\TicketHistory;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Extensions\SIPBilling;
use App\Extensions\SIPSignup;
use App\Extensions\SIPNetwork;
use App\Extensions\SIPCustomer;
use App\Extensions\BillingHelper;
use App\Extensions\CiscoSwitch;
use App\Extensions\MtikRouter;
use App\Extensions\DataMigrationUtils;

//use App\User;
use App\Models\Customer;
use App\Models\Charge;
use App\Models\Invoice;
use App\Models\Ticket;
use App\Models\CustomerProduct;
use App\Models\CustomerPort;
use App\Models\DataMigration;
use App\Models\Address;
use App\Models\BuildingPropertyValue;
use App\Models\Building;
use App\Models\Product;
use App\Models\User;
use App\Models\Port;
use App\Models\NetworkNode;
use App\Models\ContactType;
use App\Models\PaymentMethod;
use App\Models\ActivityLog;
use App\Models\RetailRevenue;
use App\Http\Controllers\TechScheduleController;
use App\Extensions\GoogleCalendar;
use DateTime;
use App\Http\Controllers\Lib\UtilsController;

//use Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Html2Text\Html2Text;


//use ActivityLogs;
use Symfony\Component\Console\Helper\ProgressBar;

class PabloController extends Controller
{
    //This tests Blade files Direct.
    public function bladeView()
    {
        $billingHelper = new BillingHelper();
        $customer = Customer::find(14187);

        $invoices = Invoice::where('id_customers', $customer->id)
            ->where('status', config('const.invoice_status.pending'))
            ->where('processing_type', config('const.type.auto_pay'))
            ->orderBy('due_date', 'asc')->get();

        $address = $customer->address;

        $charges = [];
        $total = 0;

        foreach ($invoices as $invoice)
        {
            $charges = array_merge($charges, $invoice->details());
            $total += $invoice->amount;
        }

        return view('email.template_customer_declined_billing_reminder',
            ['customer' => $customer, 'address' => $address, 'charges' => $charges, 'total' => $total]);


//        $billingHelper->sendDeclinedChargeReminderByInvoiceCollection($customer, $invoices);

        dd('done');
    }
    public function supportTest(Request $request)
    {



        $loadResults = Building::whereNotNull('id_status')->get();




        print '<pre>';
        dd($loadResults);
        die();







//CHANGE ROUTE

        return Customer::find($request->id);

        dd(Product::with('type')->orderBy('frequency', 'asc')->get()->take(10)->toArray());
        die();
    }
}
