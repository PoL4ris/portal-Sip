<head>


    <!--script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js'></script-->
    <!--script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js'></script-->

    <script src='/js/jquery-ui-1.12.1.custom/jquery.js' type="text/javascript"></script>
    <script src='/js/jquery-ui-1.12.1.custom/jquery-ui.min.js' type="text/javascript"></script>
    <script src='/js/bootstrap/js/bootstrap.min.js'></script>
    <link rel="stylesheet" href="/js/jquery-ui-1.12.1.custom/jquery-ui.css">
    <link rel="stylesheet" href="/js/bootstrap/css/bootstrap.css">


    <script src="/js/silveripJS/techScheduler.js" type="text/javascript"></script>
    <link rel="stylesheet" type="text/css" href="/css/plugins/tech-schedule.css">


    <script type="text/javascript">
        //provides old date for techScheduler.js from laravel old
        var olddate = "{{ old('date') }}";
    </script>




</head>
<html>
<body>
<div class="flash-message">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
        @if(Session::has('alert-' . $msg))

            <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close"
                                                                                     data-dismiss="alert"
                                                                                     aria-label="close">&times;</a></p>
        @endif
    @endforeach
</div> <!-- end .flash-message -->

@if (count($errors) > 0)
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="form-group schedule-wrapper">
    <form method="get" name="scheduleform" action="/tech-schedule/setappointment" onsubmit="preparesubmit()">
        <table id='sheader'>
            <tr>
                <td><label for="dpick">Date</label></td>
                <td><input id="dpick" type="text" class="datepicker form-control" name="date" tabindex="0"></input>
                    <input type="hidden" name="username"
                           value="{{Auth::user()->first_name . ' ' . Auth::user()->last_name}}"></input>
                </td>
                <td><label for="adesc">Appointment Description</label></td>
            </tr>

            <tr>
                <td><label for="cname">Customer Name</label></td>
                <td><input type="text" id='cname' name="customername" class="form-control"
                           value="{{old('customername')}}" tabindex="1"></input></td>
                <td rowspan="7" style="width: 600px; max-width: 100%; height: 100%"><textarea type="text" id="adesc"
                                                                                              name="appointmentdescription"
                                                                                              class="form-control"
                                                                                              tabindex="8">{{old('appointmentdescription')}}</textarea>
                    <!--should be textarea--></td>
            </tr>
            <tr>
                <td><label for="cphone">Customer Phone</label></td>
                <td><input type="text" id='cphone' name="customerphone" class="form-control"
                           value="{{old('customerphone')}}" tabindex="2"></input></td>
            </tr>
            <tr>
                <td><label for="bcode">Building Code</label></td>
                <td><input type="text" onkeyup="updatesearch()" id="bcode" name="buildingcode" class="form-control"
                           value="{{old('buildingcode')}}" tabindex="3"></input></td>
            </tr>
            <tr>
                <td><label for="unit">Unit</label></td>
                <td><input type="text" id="unit" name="unit" class="form-control" value="{{old('unit')}}"
                           tabindex="4"></input></td>
            </tr>
            <tr>
                <td><label for="service">Service</label></td>
                <td><select id="service" name="service" value="{{old('service')}}" tabindex="5">
                        <option disabled selected>Service</option>
                        <option>TV</option>
                        <option>INT</option>
                    </select><!--should be dropdown--></td>
            </tr>
            <tr>
                <td><label for="action">Action</label></td>
                <td><select id="action" name="action" value="{{old('action')}}" tabindex="6">
                        <option disabled selected>Action</option>
                        <option>Connect</option>
                        <option>Repair</option>
                        <option>Other</option>
                    </select><!--should be dropdown--></td>
            </tr>
            <tr>
                <td><label for="dtvaccount">DTV Account</label></td>
                <td><input type="text" id="dtvaccount" name="dtvaccount" class="form-control"
                           value="{{old('dtvaccount')}}" tabindex="7"></input></td>
            </tr>


            <table class='scheduletable table-sm table-striped'>
                <tbody></tbody>
            </table>
            <input type="submit" value="Submit">
    </form>
</div>
</body>
</html>
