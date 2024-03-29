<?php

namespace App\Models;

use ClassPreloader\Config;
use Illuminate\Database\Eloquent\Model;
use App\Models\NetworkNodes;
use App\Models\Building;
use App\Models\Address;
use App\Models\Ticket;
use App\Models\Reason;


class Customer extends Model {

    public function address()
    {
        return $this->hasOne('App\Models\Address', 'id_customers');
    }

    public function addresses()
    {
        return $this->hasMany('App\Models\Address', 'id_customers');
    }

    public function allPaymentMethods()
    {
        return $this->hasMany('App\Models\PaymentMethod', 'id_customers')
            ->where('account_number', '!=', 'ERROR')
            ->orderBy('priority', 'DESC');
    }

    public function building()
    {
        return $this->belongsToMany('App\Models\Building', 'address', 'id_customers', 'id_buildings');
    }

    public function contact()
    {
        return $this->hasOne('App\Models\Contact', 'id_customers')->where('id_types', config('const.contact_type.mobile_phone'));
    }

    public function contacts()
    {
        return $this->hasMany('App\Models\Contact', 'id_customers');
    }

    public function emailAddress()
    {
        return $this->hasOne('App\Models\Contact', 'id_customers')->where('id_types', config('const.contact_type.email'));
    }

    public function phone()
    {
        return $this->hasOne('App\Models\Contact', 'id_customers')->where('id_types', config('const.contact_type.mobile_phone'));
    }

    public function customerProducts()
    {
        return $this->hasMany('App\Models\CustomerProduct', 'id_customers', 'id');
    }

    public function activeCustomerProducts()
    {
        return $this->hasMany('App\Models\CustomerProduct', 'id_customers', 'id')
            ->where('id_status', config('const.status.active'));
    }

    public function defaultPaymentMethod()
    {
        return $this->hasOne('App\Models\PaymentMethod', 'id_customers')
            ->where('priority', 1);
    }

    public function openTickets()
    {
        return $this->hasMany('App\Models\Ticket', 'id_customers')->where('status', '!=', config('const.ticket_status.closed'));
    }

    public function port()
    {
        return $this->hasMany('App\Models\Port', 'id_customers', 'id');
    }

    public function ports()
    {
        return $this->belongsToMany('App\Models\Port');
    }

    public function customerPort()
    {
        return $this->hasMany('App\Models\CustomerPort', 'customer_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'id_customers', 'id', 'App\Models\CustomerProduct');
//        return $this->belongsToMany('App\Models\Product', 'customer_products', 'id_customers', 'id_products');
    }

    public function services()
    {
        return $this->hasMany('App\Models\CustomerProduct', 'id_customers', 'id')
            ->orderBy('id_status', 'asc');
    }

    public function activeServices()
    {
        return $this->hasMany('App\Models\CustomerProduct', 'id_customers', 'id')
            ->where('id_status', config('const.status.active'))
            ->orderBy('id_status', 'asc');
    }

    public function status()
    {
        return $this->hasOne('App\Models\Status', 'id', 'id_status');
    }

    public function tickets()
    {
        return $this->hasMany('App\Models\Ticket', 'id_customers');
    }

    public function ticketHistory()
    {
        return $this->hasMany('App\Models\Ticket', 'id_customers', 'id');
    }

    public function type()
    {
        return $this->hasOne('App\Models\Type', 'id', 'id_types');
    }

    public function pendingManualCharges()
    {
        return $this->hasMany('App\Models\Charge', 'id_customers')
            ->where('status', config('const.charge_status.pending_approval'))
            ->where('processing_type', config('const.type.manual_pay'));

    }

    public function invoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers');
    }

    public function pendingInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.status', config('const.invoice_status.pending'));
    }

    public function pendingManualPayInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.processing_type', config('const.type.manual_pay'))
            ->where('invoices.status', config('const.invoice_status.pending'));
    }

    public function pendingAutoPayInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.processing_type', config('const.type.auto_pay'))
            ->where('invoices.status', config('const.invoice_status.pending'));
    }

    public function pendingAutoPayInvoicesOnOrAfterTimestamp($timestamp)
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.processing_type', config('const.type.auto_pay'))
            ->where('invoices.due_date', '>=', $timestamp)
            ->where(function ($query) {
                $query->where('invoices.status', config('const.invoice_status.pending'))
                    ->orWhere('invoices.status', config('const.invoice_status.open'));
            })
            ->get();
    }

    public function user()
    {
        return $this->hasOne('App\Models\User', 'id', 'id_users');
    }

    /**
     * Charge and Invoice relations
     */
    public function activeAutoPayCharges()
    {
        return $this->hasMany('App\Models\Charge', 'id_customers')
            ->where('charges.processing_type', config('const.type.auto_pay'))
            ->where(function ($query) {
                $query->where('charges.status', config('const.charge_status.pending'))
                    ->orWhere('charges.status', config('const.charge_status.invoiced'));
            })
            ->get();
    }

    public function activeManualCharges()
    {
        return $this->hasMany('App\Models\Charge', 'id_customers')
            ->where('charges.processing_type', config('const.type.manual_pay'))
            ->where(function ($query) {
                $query->where('charges.status', config('const.charge_status.pending'))
                    ->orWhere('charges.status', config('const.charge_status.invoiced'));
            })
            ->get();
    }

    public function allActiveCharges()
    {
        return $this->hasMany('App\Models\Charge', 'id_customers')
            ->where(function ($query) {
                $query->where('charges.status', config('const.charge_status.pending'))
                    ->orWhere('charges.status', config('const.charge_status.invoiced'));
            })
            ->get();
    }

    public function activeAutoPayInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.processing_type', config('const.type.auto_pay'))
            ->where(function ($query) {
                $query->where('invoices.status', config('const.invoice_status.open'))
                    ->orWhere('invoices.status', config('const.invoice_status.pending'));
            })
            ->get();
    }

    public function activeManualInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where('invoices.processing_type', config('const.type.manual_pay'))
            ->where(function ($query) {
                $query->where('invoices.status', config('const.invoice_status.open'))
                    ->orWhere('invoices.status', config('const.invoice_status.pending'));
            })
            ->get();
    }

    public function allActiveInvoices()
    {
        return $this->hasMany('App\Models\Invoice', 'id_customers')
            ->where(function ($query) {
                $query->where('invoices.status', config('const.invoice_status.open'))
                    ->orWhere('invoices.status', config('const.invoice_status.pending'));
            })
            ->get();
    }

//    public function payment() {
//        return $this->hasOne('App\Models\PaymentMethod', 'id_customers')
//                ->where('priority', 1);
//    }


    public function getNetworkNodes($id = null)
    {
        if ($id == null)
        {
            $id = $this->attributes['id'];
        }

        return NetworkNode::join('ports', 'ports.id_network_nodes', '=', 'network_nodes.id')
            ->join('customers', 'ports.id_customers', '=', 'customers.id')
            ->where('ports.id_customers', '=', $id)
            ->select('*')
            ->get();
    }

    public function getNetworkInfo($id = null)
    {
        if ($id == null)
        {
            $id = $this->attributes['id'];
        }

//        return $this->port()->with('networkNode')->get();
        $modelCollection = $this->customerPort()->with('portWithNetworkNode')->get();

        return $modelCollection->pluck('portWithNetworkNode');
    }

    public function getTickets($id = null)
    {
        if ($id == null)
        {
            $id = $this->attributes['id'];
        }

        return Ticket::join('reasons', 'tickets.id_reasons', '=', 'reasons.id')
            ->join('customers', 'tickets.id_customers', '=', 'customers.id')
            ->join('categories', 'reasons.id_categories', '=', 'categories.id')
            ->where('tickets.id_customers', '=', $id)
            ->select(['tickets.*', 'reasons.name as reason', 'reasons.short_description as short_reason', 'categories.name as category'])
            ->get();
    }

    public function log()
    {
        return $this->hasMany('App\Models\ActivityLog', 'id_type')->orderBy('id', 'desc');
    }

    /**
     * invoice_status:
     * 0 = not invoiced yet
     * 1 = invoiced and waiting to be charged
     * 2 = charged successfully
     * -1 = charge failed
     */

    //    How do I say WHERE (a=1 OR b=1) AND (c=1 OR d=1)
    //        
    //    Model::where(function ($query) {
    //    $query->where('a', '=', 1)
    //          ->orWhere('b', '=', 1);
    //    })->where(function ($query) {
    //        $query->where('c', '=', 1)
    //          ->orWhere('d', '=', 1);
    //    });

    public function getActiveCustomerProductsByBuildingID($building_id)
    {

        return Customer::join('customer_products', 'id_customers', '=', 'customers.id')
            ->join('products', 'customer_products.id_products', '=', 'products.id')
            ->join('address', 'address.id_customers', '=', 'customers.id')
            ->join('buildings', 'address.id_buildings', '=', 'buildings.id')
            ->where('customers.id_status', '=', '2')
            ->where('buildings.id', '=', $building_id)
            ->get(array('buildings.id as building_id'
            , 'address.id as address_id'
            , 'buildings.nickname as building_code'
            , 'products.name as product_name'
            , 'products.description as product_desc'
            , 'products.amount as product_amount'
            , 'products.frequency as product_frequency'
            , 'products.id_types as product_type'
            , 'customer_products.*'));

    }

    public function getActiveCustomerProductsByCustomerID($customer_id)
    {

        return Customer::join('customer_products', 'id_customers', '=', 'customers.id')
            ->join('products', 'customer_products.id_products', '=', 'products.id')
            ->where('customers.id_status', '=', '2')
            ->where('customers.id', '=', $customer_id)
            ->get(array('products.name as product_name'
            , 'products.description as product_desc'
            , 'products.amount as product_amount'
            , 'products.frequency as product_frequency'
            , 'products.id_types as product_type'
            , 'customer_products.*'));
    }
}
