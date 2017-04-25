<html>

<body>

{{--<div class="border3 text-center">--}}
{{--{{$customer}}--}}
{{--{{$customer->id}}--}}
{{--{{$customer->first_name}}--}}
{{--{{$customer->last_name}}--}}

{{--<div>--}}
{{--@foreach ($customer as $item)--}}
{{--{{ $customer[$item] }}<br />--}}
{{--@endforeach--}}
{{--</div>--}}

{{--<p>{{$address}}</p>--}}
{{--<p>{{$data}}</p>--}}
{{--<p>{{$data->uno}}</p>--}}
{{----}}
{{--</div>--}}


<div>
  <div class="col-lg-12">
    <div class="border bg-content-white">

      <div class="header">
        <h2 class="text-center t-white">New UC Signup</h2>
      </div>


      {{--MAIN INFO--}}
      <div class="block">
        <label>Customer Information</label>
        <p><span class="lef">First Name:</span>          <span class="rig">demetrius</span></p>
        <p><span class="lef">Last Name:</span>           <span class="rig">grant</span></p>
        <p><span class="lef">E-mail:</span>              <span class="rig">degrant11@yahoo.com</span></p>
        <p><span class="lef">Phone Number:</span>        <span class="rig">7737599520</span></p>
      </div>
      {{--MAIN Location info--}}
      <div class="block">
        <label>Location Information</label>
        <p><span class="lef">Street Address:</span>      <span class="rig">1150 W 15th Street</span></p>
        <p><span class="lef">Apartment/Unit:</span>      <span class="rig">304</span></p>
        <p><span class="lef">City:</span>                <span class="rig">Chicago</span></p>
        <p><span class="lef">State:</span>               <span class="rig">IL</span></p>
        <p><span class="lef">Zip:</span>                 <span class="rig">60608</span></p>
      </div>
      {{--MAIN Products--}}
      <div class="block">
        <label>Product Description</label>
        <p><span class="lef">Service Plan:</span>        <span class="rig">30 Mbps - Included ($0.00)</span></p>
        <p><span class="lef">Wireless Router:</span>     <span class="rig">Fast WiFi</span></p>
        <p><span class="lef">Total Charges:</span>       <span class="rig">$99.00</span></p>
        <p><span class="lef">Recurring Charges:</span>   <span class="rig">$0.00</span></p>
        <p><span class="lef">Authorized Charges:</span>  <span class="rig">$0.00</span></p>
      </div>
      {{--MAIN network INFO--}}
      <div class="block">
        <label>Network Data</label>
        <p><span class="lef">IP Address:</span>          <span class="rig">100.64.54.10</span></p>
        <p><span class="lef">MAC Address:</span>         <span class="rig">AC:87:A3:0E:70:F7</span></p>
        <p><span class="lef">Switch ID:</span>           <span class="rig">UC5-3</span></p>
        <p><span class="lef">Switch IP:</span>           <span class="rig">10.10.36.53</span></p>
        <p><span class="lef">Switch Port:</span>         <span class="rig">12</span></p>
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
  .border3 {
    border: 1px solid crimson;
  }
  .col-lg-12 {
    font-family: "Open Sans",Arial,Helvetica,sans-serif;
    margin: 0 auto;
    width: 700px;
  }
  .container {
  }
  .bg-chicago {
    height: 60%;
    left: 0;
    overflow: hidden;
    position: fixed;
    right: 0;
    top: 0;
    width: 100%;
    z-index: 0;
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
  .t-blue {
    color: royalblue;
  }
  .t-orange {
    color: #ff871e;
  }
  .t-white{
    color: white;
  }
  .logo {
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
  }
  .block .rig {
    display: inline-block;
    padding-left: 5px;
    text-align: left;
    width: 300px;
  }
  .block p {
    color: #333;
    height: 18px;
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
