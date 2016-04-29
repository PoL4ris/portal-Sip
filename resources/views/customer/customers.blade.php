@extends('main.main')
@section('bodyContent')


<div class="bg-box-black display-ticket" id="bg-black-window" style="display: none;" typeoff="close"></div>
{{-- TITULO --}}
<div class="bg-light lter b-b wrapper-md">
  <h2 class="m-n font-thin h3">Customer Information</h2>
</div>


{{-- Customer Info --}}
<div class="col-lg-3 display-content-main-box anim">

  <div class="color-name-header bg-dark">
    Customer :  {{ $customer['customer']->Firstname . ' ' . $customer['customer']->Lastname }}
    <div class="customer-edit-btn"><button class="btn m-b-xs w-xs btn-info btn-edit" id="block-a">Edit</button></div>
  </div>

  <form action="updateCustomerData" class="customer-customer-form" id="a-form-{{ $customer['customer']->CID }}" dbtable="customers">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="field-data bg-white col-lg-12">
     <label class="bold-type col-lg-4">VIP</label><label class="col-lg-8 block-a-label" id="a-VIP">{{ $customer['customer']->VIP }}</label>
     <input type="text" value="{{ $customer['customer']->VIP }}" name="VIP" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Firstname</label><label class="col-lg-8 block-a-label" id="a-Firstname">{{ $customer['customer']->Firstname }}</label>
      <input type="text" value="{{ $customer['customer']->Firstname }}" name="Firstname" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Lastname</label><label class="col-lg-8 block-a-label" id="a-Lastname">{{ $customer['customer']->Lastname }}</label>
      <input type="text" value="{{ $customer['customer']->Lastname }}" name="Lastname" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Address</label><label class="col-lg-8 block-a-label" id="a-Address">{{ $customer['customer']->Address }}</label>
      <input type="text" value="{{ $customer['customer']->Address }}" name="Address" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Unit</label><label class="col-lg-8 block-a-label" id="a-Unit">{{ $customer['customer']->Unit }}</label>
      <input type="text" value="{{ $customer['customer']->Unit }}" name="Unit" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">City</label><label class="col-lg-8 block-a-label" id="a-City">{{ $customer['customer']->City }}</label>
      <input type="text" value="{{ $customer['customer']->City }}" name="City" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">State</label><label class="col-lg-8 block-a-label" id="a-State">{{ $customer['customer']->State }}</label>
      <input type="text" value="{{ $customer['customer']->State }}" name="State" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
     <label class="bold-type col-lg-4">Zip</label><label class="col-lg-8 block-a-label" id="a-Zip">{{ $customer['customer']->Zip }}</label>
      <input type="text" value="{{ $customer['customer']->Zip }}" name="Zip" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
     <label class="bold-type col-lg-4">Username</label><label class="col-lg-8 block-a-label" id="a-Username">{{ $customer['customer']->Username }}</label>
      <input type="text" value="{{ $customer['customer']->Username }}" name="Username" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Tel</label><label class="col-lg-8 block-a-label" id="a-Tel">{{ $customer['customer']->Tel }}</label>
      <input type="text" value="{{ $customer['customer']->Tel }}" name="Tel" class="block-a-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
     <label class="bold-type col-lg-4">DateSignup</label><label class="col-lg-8 ">{{ $customer['customer']->DateSignup }}</label>
    </div>
    <div class="field-data bg-white col-lg-12">
     <label class="bold-type col-lg-4">AccountStatus</label> <label class="col-lg-8 block-a-label" id="a-AccountStatus">{{ $customer['customer']->AccountStatus }}</label>
      <input type="text" value="{{ $customer['customer']->AccountStatus }}" name="AccountStatus" class="block-a-edit form-control editclass">
    </div>
    {{--<div class="field-data bg-white col-lg-12">--}}
      {{--<label class="bold-type col-lg-4">Comments</label><label class="col-lg-8  h250h">{{ $customer['customer']->Comments }}</label>--}}
    {{--</div>--}}
    <button class="btn m-b-xs w-xs btn-success save-btn" idType="CID"  CID="{{ $customer['customer']->CID }}" bloque="a" id="save-block-a" onclick="return false;">Save</button>
  </form>

</div>


{{-- Services --}}
<div class="col-lg-6 display-content-main-box anim" id="customer-box-c">

  <div class="color-name-header btn-dark">
    Services Info
    <div class="customer-edit-btn"><button class="btn m-b-xs w-xs btn-info " id="add-service-btn">Add Service</button></div>
  </div>

  @if($customer['services'])
    <div class="panel panel-default">
      <table id="myTable" class="tablesorter table" >
        <thead>
        <tr><th>Service</th><th>Amount</th><th>Cycle</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        @foreach($customer['services'] as $serviceData)
          <tr id="serviceno-{{ $serviceData->CSID }}" class="{{ $serviceData->Status=='active'?'':$serviceData->Status . ' ital' }}">
            <td>{{ $serviceData->ProdName }}</td>
            <td>{{ $serviceData->Amount }}</td>
            <td>{{ $serviceData->ChargeFrequency }}</td>
            <td id="serviceinfo-status-{{ $serviceData->CSID }}">{{ $serviceData->Status }}</td>
            <td>
              <button class="btn fa btn-danger fa-pencil modif-service-btn" tipo="{{ $serviceData->ProdType }}" type="button" tipoid="{{ $serviceData->ProdID }}" kind="update" serviceid="{{ $serviceData->CSID }}" displaystatus="{{ $serviceData->Status }}"></button>
              <button class="btn btn-dark fa fa-times transparent-btn" type="button" ></button>
              <button id="xservice-btn-id-{{ $serviceData->CSID }}" class="btn fa {{ $serviceData->Status=='active'?'btn-dark fa-times':'btn-success fa-check' }} action-confirm" tipo="{{ $serviceData->ProdType }}" type="button" tipoid="{{ $serviceData->ProdID }}" kind="cancel" serviceid="{{ $serviceData->CSID }}" displaystatus="{{ $serviceData->Status }}" route="0"></button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  @endif

  <div id="addservicecontentbox" class="addservicecontentbox" style="display:none;">
    <div class="color-name-header">Service Information</div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-2">Service/Plan</label>
      <select class="col-lg-3 block-a-label">
        <option value="#" class="prod-id-select-option">Please select a product</option>
        @foreach($customer['addservices'] as $addingService)
          <option value="{{ $addingService->ProdID }}" class="prod-id-select-option">{{ $addingService->ProdName }}</option>
        @endforeach
      </select>
      <div class="col-lg-7 ">
        @foreach($customer['addservices'] as $addingService)
          <div id="addServiceId-{{ $addingService->ProdID }}" class="options-content">
            <label class="bold-type col-lg-6">Amount</label><label class="col-lg-6 block-a-label">{{ $addingService->Amount }}</label>
            <label class="bold-type col-lg-6">Cycle</label><label class="col-lg-6 block-a-label">{{ $addingService->ChargeFrequency }}</label>
            <label class="bold-type col-lg-6">Status</label><label class="col-lg-6 block-a-label">{{ $addingService->Status }}</label>
            <label class="bold-type col-lg-6">Comments</label><textarea name='name' class="col-lg-6 block-a-label common-textarea"></textarea>
          </div>
        @endforeach
      </div>
      <div class="col-lg-1 addServiceBoton" >
        <button class="btn m-b-xs w-xs btn-primary">Add</button>
      </div>
    </div>
  </div>

  {{--{{ dd($customer['servicesactiveinfo']) }}--}}
  {{--{{ dd($customer['services'] )  . 'aaaaaa'}}--}}


@if($customer['servicesactiveinfo'])

    <div id="updateservicecontentbox" class="addservicecontentbox" style="display: none;">
      <div class="color-name-header">Service Information</div>
      <div class="field-data bg-white col-lg-12">
        <label class="bold-type col-lg-2">Service/Plan</label>
        <select class="col-lg-3 block-a-label" name="serviceID" id="select-csiu">
          @foreach($customer['servicesactiveinfo'] as $servtmp)
            @foreach($servtmp as $avServices)
              <option value="{{ $avServices->ProdID }}" class="modif-servicios type-{{ $avServices->ProdType }}" >{{ $avServices->ProdName }}</option>
            @endforeach
          @endforeach
        </select>



        <div class="col-lg-7 ">
          @foreach($customer['servicesactiveinfo'] as $servtmpdos)
            @foreach($servtmpdos as $servtmptres)
              <div id="updateServiceId-{{ $servtmptres->ProdID }}" class="options-content modif-servicios">
                <label class="bold-type col-lg-6">Amount</label>        <label class="col-lg-6 block-a-label" id="amount-{{ $servtmptres->ProdID }}">{{ $servtmptres->Amount }}</label>
                <label class="bold-type col-lg-6">Signed Up</label>     <label class="col-lg-6 block-a-label" id="signup-{{ $servtmptres->ProdID }}">{{ $servtmptres->DateSignup }}</label>
                <label class="bold-type col-lg-6">Cycle</label>         <label class="col-lg-6 block-a-label" id="cycle-{{ $servtmptres->ProdID }}">{{ $servtmptres->ChargeFrequency }}</label>
                <label class="bold-type col-lg-6">Renewed:</label>      <label class="col-lg-6 block-a-label" id="renewed-{{ $servtmptres->ProdID }}">{{ $servtmptres->DateRenewed }}</label>
                <label class="bold-type col-lg-6">Status</label>        <label class="col-lg-6 block-a-label" id="status-{{ $servtmptres->ProdID }}">{{ $servtmptres->Status }}</label>
                <label class="bold-type col-lg-6">Expires</label>       <label class="col-lg-6 block-a-label" id="expires-{{ $servtmptres->ProdID }}">{{ $servtmptres->CProdDateExpires }}</label>
                <label class="bold-type col-lg-6">Last Updated</label>  <label class="col-lg-6 block-a-label" id="lastupdate-{{ $servtmptres->ProdID }}">{{ $servtmptres->CProdDateUpdated }}</label>
                <label class="bold-type col-lg-6">Comments</label>      <textarea class="col-lg-6 block-a-label common-textarea" id="comment-{{ $servtmptres->ProdID }}"></textarea>
              </div>
            @endforeach
          @endforeach
        </div>


        <div class="col-lg-1 addServiceBoton">
          @foreach($customer['services'] as $serviceData)
          <button class="btn m-b-xs w-xs btn-primary btn-display-service-{{ $serviceData->CSID }} btn-display-service" onclick="updateActiveServiceInfo({{ $serviceData->CSID }},{{ $serviceData->ProdID }});return false;" style="display: none;">Add</button>
          @endforeach

        </div>

      </div>
    </div>
  @endif
</div>


{{-- CC --}}
<div class="col-lg-3 display-content-main-box anim">

  <div class="color-name-header bg-dark">
    Credit Card Information
    <div class="customer-edit-btn"><button class="btn m-b-xs w-xs btn-info btn-edit" id="block-b">Edit</button></div>
  </div>

  <form action="updateCustomerData" class="customer-customer-form" id="b-form-{{ $customer['customer']->CID }}" dbtable="customers">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-6">Card Number</label><label class="col-lg-6 block-b-label" id="b-CCnumber">{{ $customer['customer']->CCnumber }}</label>
      <input type="text" value="{{ $customer['customer']->CCnumber }}" name="CCnumber" class="block-b-edit form-control editclass">
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-6">Card Type</label><label class="col-lg-6 block-b-label" id="b-CCtype">{{ $customer['customer']->CCtype }}</label>
      <select class="block-b-edit form-control editclass " name="CCtype">
        <option value="{{ $customer['customer']->CCtype }}">{{ $customer['customer']->CCtype }}</option>
        <option value="VS">VISA</option>
        <option value="MC">MASTER CARD</option>
        <option value="AX">AMERICAN EXPRESS</option>
        <option value="DS">DISCOVERY</option>
      </select>
      {{--<input type="text" value="{{ $customer['customer']->CCtype }}" name="CCtype" class="block-b-edit form-control editclass">--}}
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-6">Expiration Month</label><label class="col-lg-6 block-b-label" id="b-Expmo">{{ $customer['customer']->Expmo }}</label>
      {{--<input type="text" value="{{ $customer['customer']->Expmo }}" name="Expmo" class="block-b-edit form-control editclass">--}}
      <select class="block-b-edit form-control editclass " name="Expmo">
        <option value="{{ $customer['customer']->Expmo }}">{{ $customer['customer']->Expmo }}</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
        <option value="7">7</option>
        <option value="8">8</option>
        <option value="9">9</option>
        <option value="10">10</option>
        <option value="11">11</option>
        <option value="12">12</option>
      </select>
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-6">Expiration Year</label><label class="col-lg-6 block-b-label" id="b-Expyr">{{ $customer['customer']->Expyr }}</label>
      {{--<input type="text" value="{{ $customer['customer']->Expyr }}" name="Expyr" class="block-b-edit form-control editclass">--}}
      <select class="block-b-edit form-control editclass " name="Expyr">
        <option value="{{ $customer['customer']->Expyr }}">{{ $customer['customer']->Expyr }}</option>
        <option value="2020">2020</option>
        <option value="2019">2019</option>
        <option value="2018">2018</option>
        <option value="2017">2017</option>
        <option value="2016">2016</option>
        <option value="2015">2015</option>
      </select>
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-6">CCV</label><label class="col-lg-6 block-b-label" id="b-CCscode"></label>
      <input type="text" value="" name="CCscode" class="block-b-edit form-control editclass">
    </div>
    <button class="btn m-b-xs w-xs btn-success save-btn" idType="CID"  CID="{{ $customer['customer']->CID }}" bloque="b" id="save-block-b" onclick="return false;">Save</button>
  </form>
  <div class="btn-group btn-group-justified">
    <a class="btn btn-danger" href="">Manual Charge</a>
    <a class="btn btn-warning" href="">Manual Refound</a>
  </div>

</div>


  {{-- BOTONES --}}
<div class="col-lg-12 display-content-main-box" style="text-align: center;">
  <button class="btn m-b-xs w-xs btn-dark customer-seccion" window="subseccion-network">Network</button>
  <button class="btn m-b-xs w-xs btn-info customer-seccion" window="subseccion-newticket">New Ticket</button>
  <button class="btn m-b-xs w-xs btn-dark customer-seccion" window="subseccion-tickets">Tickets</button>
  <button class="btn m-b-xs w-xs btn-info customer-seccion" window="subseccion-billing">Billing</button>
  <button class="btn m-b-xs w-xs btn-dark customer-seccion" window="subseccion-notices">Notices</button>
  <button class="btn m-b-xs w-xs btn-info customer-seccion" window="subseccion-building">Building</button>
</div>


{{-- Network --}}
<div class="col-lg-12 display-content-main-box bg-white no-padding-sides subseccion subseccion-network">

  <div class="color-name-header btn-info">
    Port and Network Info
  </div>
  <div class="col-lg-4 no-padding-sides">
  @if($customer['network'])
  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Name</div><div class="col-lg-8">{{ $customer['network']['Name'] }}</div></div>
  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">IP</div><div class="col-lg-8">{{ $customer['network']['IP'] }}</div></div>
  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Port</div><div class="col-lg-8">{{ $customer['network']['Port'] }}</div></div>
  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Access</div><div class="col-lg-8" id="acces-network-id">{{ $customer['network']['Access']}}</div></div>
  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Vendor</div><div class="col-lg-8">{{ $customer['network']['Vendor'] }}</div></div>

  <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Model</div><div class="col-lg-8">{{ $customer['network']['Model'] }}</div></div>
  @else
  <div>EMPTY</div>
  @endif
  </div>

  <div class="col-lg-4 no-padding-sides">
    <div class="color-name-header btn-default nav" id="basic-info-net">
      Basic Information
    </div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Switch Uptime</div>    <div class="col-lg-6 anim" id="switch-uptime"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Status</div>           <div class="col-lg-6 anim" id="oper-status"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Admin Status</div>     <div class="col-lg-6 anim" id="admin-status"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Speed</div>            <div class="col-lg-6 anim" id="port-speed"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Last Change</div>      <div class="col-lg-6 anim" id="last-change"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Vlan</div>             <div class="col-lg-6 anim" id="vlan"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">IP</div>               <div class="col-lg-6" id="IPs"></div></div>
  </div>
  <div class="col-lg-4 no-padding-sides">
    <div class="color-name-header btn-default nav">
      Advanced Information
    </div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Portfast</div>          <div class="col-lg-6" id="portfast"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">Portfast Mode</div>     <div class="col-lg-6" id="portfast-mode"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">BPDU Guard</div>        <div class="col-lg-6" id="bpdu-guard"></div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-6">BPDU Filter</div>       <div class="col-lg-6" id="bpdu-filter"></div></div>
  </div>
  {{--<div class="customer-edit-btn"><button class="btn m-b-xs w-xs btn-info btn-edit" id="block-a">Edit</button></div>--}}
  <div class="btn-group btn-group-justified">
    <p class="btn btn-success network-functions" onclick="networkServices(0,' {{ $customer['customer']->PortID }} ')" type="0" portid="{{ $customer['customer']->PortID }}">Check Status</p>{{-- getSwitchPortStatus(); --}}
    <p class="btn btn-dark network-functions action-confirm"     type="3" portid="{{ $customer['customer']->PortID }}">Recycle Port</p>
    <p class="btn btn-success network-functions"  type="4" portid="{{ $customer['customer']->PortID }}">Check IP's</p>
    @if ($customer['network']['Access'] == 'signup')
      <p class="btn btn-info network-functions action-confirm access-type-net"   type="6" portid="{{ $customer['customer']->PortID }}">Activate</p>
    @else
      <p class="btn btn-danger network-functions action-confirm access-type-net"   type="5" portid="{{ $customer['customer']->PortID }}">Send to Signup</p>
    @endif

    <p class="btn btn-success network-functions"  type="" portid="{{ $customer['customer']->PortID }}">Building IP Stats</p>
  </div>

</div>


{{-- Create Ticket --}}
<div class="col-lg-12 display-content-main-box subseccion subseccion-newticket">

  <div class="color-name-header btn-dark">
    Create Ticket
  </div>
  <form action="" class="customer-customer-form" id="newticketform">
    <input type="hidden" name="_token" value="{{ csrf_token() }}">
    <input type="hidden" name="CID" value="{{ $customer['customer']->CID }}">
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Issue</label>
      <select class="form-control select-control" name="RID">
        <option value="err">Select an issue</option>
        @foreach($customer['ticketreasone'] as $reasoneIndex => $topic)
          <option value="{{ $reasoneIndex }}">{{ $topic->ReasonShortDesc }}</option>
        @endforeach
      </select>
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Status</label>
      <select class="form-control select-control" name="Status">
        <option value="err">Select an ticket status</option>
        <option value="escalated">Escalate</option>
        <option value="closed">Close</option>
      </select>
    </div>
    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Details</label>
      <textarea name="Comment" class="col-lg-4 common-textarea" placeholder="Ticket Info..."></textarea>
      <div class="field-data bg-white col-lg-12">
        <button class="btn btn-dark col-lg-2" id="create-customer-ticket" onclick="return false;">Create Ticket</button>
      </div>
    </div>

  </form>
</div>


{{-- Notices --}}
<div class="col-lg-12 display-content-main-box subseccion subseccion-notices">

  <div class="color-name-header btn-info">
    Notices
  </div>

  <form action="" class="customer-customer-form">

    <div class="field-data bg-white col-lg-12">
      <div class="bold-type col-lg-4">From</div><div class="col-lg-8">Silver Customer Service < help@silverip.com > </div>
    </div>
    <div class="field-data bg-white col-lg-12">
      <div class="bold-type col-lg-4">To</div>
      <input type="text" value="{{ $customer['customer']->Username }}" name="Expyr" class="block-b-edit form-control ">
    </div>
    <div class="field-data bg-white col-lg-12">
      <div class="bold-type col-lg-4">CC</div>
      <input type="text" value="help@silverip.com" name="Expyr" class="block-b-edit form-control ">
    </div>
    <div class="field-data bg-white col-lg-12">
      <div class="bold-type col-lg-4">Subject</div>
      <input type="text" value="" name="Expyr" class="block-b-edit form-control ">
    </div>
    <div class="field-data bg-white col-lg-12">
      <div class="bold-type col-lg-4">Template</div>
      <select class="form-control select-control" name="issue">
        <option value="cc-expire">CC Expiring</option>
        <option value="voip-loa">VoIP LOA</option>
      </select>
    </div>


    <div class="field-data bg-white col-lg-12">
      <label class="bold-type col-lg-4">Details</label>
      <textarea name="comment" class="col-lg-4 common-textarea"></textarea>
      <div class="field-data bg-white col-lg-12">
        <button class="btn btn-success col-lg-1">Send</button>
      </div>
    </div>

  </form>
</div>


{{-- Tickets --}}
<div class="col-lg-12 display-content-main-box subseccion subseccion-tickets" id="customer-box-b">

  <div id="customer-view-0">

    <div class="color-name-header btn-dark">
      Tickets History:
    </div>
    <div class="panel panel-default">
      <table id="myTable" class="tablesorter table" >
        <thead>
        <tr><th>ID</th><th>Created</th><th>Reasone</th><th>Details</th><th>Status</th><th>Update</th></tr>
        </thead>
        <tbody>
        @if($customer['tickethistory'])
          @foreach($customer['tickethistory'] as $tickethistory)
            <tr>
              <td>{{ $tickethistory->TicketNumber }}</td>
              <td>{{ $tickethistory->DateCreated }}</td>
              <td>{{ $tickethistory->ReasonShortDesc }}</td>
              <td class="special-td">{!! $tickethistory->Comment !!}</td>
              <td>{{ $tickethistory->Status }}</td>
              <td>{{ $tickethistory->LastUpdate }}</td>
            </tr>
          @endforeach
        @else
          <tr><td></td></tr>
        @endif
        </tbody>
      </table>

    </div>

  </div>

</div>


{{-- Billing --}}
<div class="col-lg-12 display-content-main-box subseccion subseccion-billing" id="customer-box-c">

  <div id="customer-view-0">

    <div class="color-name-header btn-dark">
      Billing History:
    </div>
    <div class="panel panel-default">
      <table id="myTable" class="tablesorter table" ui-jq="dataTable">
        <thead>
          <tr><th>Date</th><th>Type</th><th>Amount</th><th>PaymentMode</th><th>Oder Number</th><th>Charge Desc</th><th>ActCode</th><th>Trans ID</th><th>Trans Status</th></tr>
        </thead>
        <tbody>
          @foreach($customer['billing'] as $billingItem)
            <tr>
              <td>{{ $billingItem->DateTime }}</td>
              <td>{{ $billingItem->TransType }}</td>
              <td>{{ $billingItem->Amount }}</td>
              <td>{{ $billingItem->PaymentMode }}</td>
              <td>{{ $billingItem->OrderNumber }}</td>
              <td>{{ $billingItem->ChargeDescription }}</td>
              <td>{{ $billingItem->ActionCode }}</td>
              <td>{{ $billingItem->TransactionID }}</td>
              <td>{{ $billingItem->Responsetext }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>

    </div>

  </div>

</div>


{{-- Building --}}
<div class="col-lg-12 display-content-main-box subseccion subseccion-building bg-white no-padding-sides"  id="customer-box-a">

  <div id="customer-view-0">

    <div class="color-name-header btn-info">
      Customer Building:  {{ $customer['building']->Name . ' ' . $customer['building']->ShortName }}
    </div>

    <div class="field-data bg-white col-lg-4"><div class="col-lg-12" style="text-align: center;"><img src="/img/buildings/{{ $customer['building']->fnImage }}" alt="" class="img-customer"></div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Address</div><div class="col-lg-8">{{ $customer['building']->Address }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Name</div><div class="col-lg-8">{{ $customer['building']->Name }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Building Code</div><div class="col-lg-8">{{ $customer['building']->ShortName }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Service(s)</div><div class="col-lg-8">{{ $customer['building']->Description }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Dedicated Number</div><div class="col-lg-8">{{ $customer['building']->SupportNumber }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Units</div><div class="col-lg-8">{{ $customer['building']->Units }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Management</div><div class="col-lg-8">{{ $customer['building']->MgrCompany }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Site Manager</div><div class="col-lg-8">{{ $customer['building']->MgrName }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Front Desk</div><div class="col-lg-8">{{ $customer['building']->ShortName }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">Backhaul Links</div><div class="col-lg-8">{{ $customer['building']->ShortName }}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">IP</div><div class="col-lg-8">{!! $customer['building']->IP !!}</div></div>
    <div class="field-data bg-white col-lg-8"><div class="bold-type col-lg-4">DNS</div><div class="col-lg-8">{!! $customer['building']->DNS !!}</div></div>

    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">Gateway</div><div class="col-lg-8">{!! $customer['building']->Gateway !!}</div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">INT Speed/Price</div><div class="col-lg-8">{!! $customer['building']->Speeds !!}</div></div>

    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">INIT Setup</div><div class="col-lg-8">{!! $customer['building']->HowToConnect !!}</div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">TV</div><div class="col-lg-8">{{ $customer['building']->DirectTV }}</div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">VoIP</div><div class="col-lg-8">{!! $customer['building']->VoIP !!}</div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">WiFi</div><div class="col-lg-8">{{ $customer['building']->ShortName }}</div></div>
    <div class="field-data bg-white col-lg-12"><div class="bold-type col-lg-4">BuildingSetup</div><div class="col-lg-8">{{ $customer['building']->ShortName }}</div></div>

  </div>

</div>







@stop