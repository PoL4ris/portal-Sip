<html>
  <body>
    <div>
      <div class="col-lg-12">
        <div class="border bg-content-white">

          <div class="header">
            <h2 class="text-center t-white">{{$data->mailType}}</h2>
          </div>


          {{--MAIN INFO--}}
          <div class="block">
            <label>Customer Information</label>
            <p><span class="lef">First Name:</span>          <span class="rig">{{$data->customer->first_name}}</span></p>
            <p><span class="lef">Last Name:</span>           <span class="rig">{{$data->customer->last_name}}</span></p>
            <p><span class="lef">E-mail:</span>              <span class="rig">{{$data->customer->email}}</span></p>
            <p><span class="lef">Contact:</span>             <span class="rig">{{$data->contacts->value}}</span></p>
          </div>
          {{--MAIN Location info--}}
          <div class="block">
            <label>Location Information</label>
            <p><span class="lef">Street Address:</span>      <span class="rig">{{$data->address->address}}</span></p>
            <p><span class="lef">Apartment/Unit:</span>      <span class="rig">#{{$data->address->unit}}</span></p>
            <p><span class="lef">City:</span>                <span class="rig">{{$data->address->city}}</span></p>
            <p><span class="lef">State:</span>               <span class="rig">{{$data->address->country}}</span></p>
            <p><span class="lef">Zip:</span>                 <span class="rig">{{$data->address->zip}}</span></p>
          </div>
          {{--MAIN Ticket--}}
          <div class="block">
            <label>Ticket Description</label>
            <p><span class="lef">Ticket:</span>              <span class="rig">{{$data->ticket_number}}</span></p>
            <p><span class="lef">Call Taker:</span>          <span class="rig">Auto System</span></p>
            <p><span class="lef">Reason:</span>              <span class="rig">{{$status == config('const.ticket_status.new') ? $data->reason->name : $data->lastTicketHistory->reason->name}}</span></p>
            <p><span class="lef">Ticket Status:</span>       <span class="rig">{{$status == config('const.ticket_status.new') ? $data->status : $data->lastTicketHistory->status}}</span></p>
            <p><span class="lef">Timestamp:</span>           <span class="rig">{{$status == config('const.ticket_status.new') ? $data->created_at : $data->lastTicketHistory->created_at}}</span></p>
            <p><span class="lef">Details:</span>             <span class="rig">{{$status == config('const.ticket_status.new') ? $data->comment : $data->lastTicketHistory->comment}}</span></p>
          </div>

        </div>
      </div>
      <div class="col-lg-12 footer">
        <p>SilverIP Communications © 2017</p>
      </div>
    </div>


    <style>

      .text-center {
        text-align: center;
      }
      h2 {
        font-weight: normal;
        margin-bottom: 0;
        margin-top: 0;
      }
      .col-lg-12 {
        font-family: "Open Sans",Arial,Helvetica,sans-serif;
        margin: 0 auto;
        width: 700px;
      }
      .bg-chicago img {
        position: absolute;
        top: -80%;
        width: 100%;
      }
      .bg-content-white::after {
        background: transparent none repeat scroll 0 0;
        border-left: medium none transparent;
        border-radius: 50%;
        border-right: medium none transparent;
        border-top: medium none transparent;
        bottom: -2px;
        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.5);
        content: " ";
        height: 2px;
        left: 0;
        margin: 0 10%;
        overflow: hidden;
        position: absolute;
        width: 80%;
        z-index: -1;
      }
      .bg-content-white {
        border: 1px solid #ddd;
        box-shadow: 0 2px 5px 0 rgba(0, 0, 0, 0.26);
        padding-bottom: 10px;
        width: 700px;
      }
      .header {
        background: #166bb4 none repeat scroll 0 0;
        padding: 5px 0;
        position: relative;
      }
      .header img {
        height: 40px;
        left: calc(50% - 20px);
        position: absolute;
        top: -70px;
      }
      .block {
        border-top: 1px solid #ddd;
        width: 700px;
      }
      .t-white{
        color: white;
      }
      .logo img {
        width: 100%;
      }
      .block label {
        background: #fafafa none repeat scroll 0 0;
        border-bottom: 1px solid #ddd;
        color: #afafaf;
        display: block;
        font-weight: normal;
        padding: 5px 0;
        text-align: center;
        text-transform: uppercase;
        width: 700px;
      }
      .block span {
        width: 50%;
      }
      .block .lef {
        display: inline-block;
        font-weight: bold;
        padding-right: 5px;
        text-align: right;
        width: 300px;
        vertical-align: top;
      }
      .block .rig {
        display: inline-block;
        padding-left: 5px;
        text-align: left;
        width: 300px;
      }
      .block p {
        color: #333;
        min-height: 18px;
        text-align: center;
      }
      .footer {
        bottom: 0;
        position: fixed;
        text-align: center;
        width: 100%;
      }

    </style>

  </body>
</html>
