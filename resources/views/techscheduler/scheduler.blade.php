<head>


<script src='https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/js/bootstrap.min.js'></script>
<script src='https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/js/bootstrap-datepicker.min.js'></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.js"></script>
<link id="bs-css" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.2/css/bootstrap.css" rel="stylesheet">
<link id="bsdp-css" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.6.4/css/bootstrap-datepicker.css" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.css">
  
  <!--script src="https://code.jquery.com/jquery-1.12.4.js"></script-->


<script>


   function updatesearch(){
    //ajax get available codes
 //   console.log(event);
var search = $('#bcode').val();

$.ajax({
    type: "GET",
    url: '/tech-schedule/bcodesearch',
    data: {search: search},
    success: updatesearchsuccess
 });
};

   
  
function updatesearchsuccess(data) {
var availableTags=[];
//console.log(data);
for(var i=0;i<data.length;i++){
availableTags.push(data[i]['code']);
}

$( "#bcode" ).autocomplete({
      source: availableTags
    });
};


function populatetable(data) {
   // console.log(data);

$('.scheduletable').html("");
$('.scheduletable').append('<tbody>');
var row='';
var offset=data['offset'];
var hour;
var filler;
var appointmentcolor={};
appointmentcolor['completed']='green';
appointmentcolor['pending']='red';
appointmentcolor['onsite']='powerderblue';


row += '<thead><tr><th></th>';
for(var hit=0;hit<data['colums'];hit++) {
row +='<th>' + data['header'][hit] + '</th>';
}
row += '</tr></thead>';
$('.scheduletable').prepend(row);
row='';

	for(var it1 = 0;it1<data['rows'];it1++) {

if(offset < 12) {
hour = offset + ' a.m.';
} else if(offset!=12) {
	hour = (offset - 12) + ' p.m.';
} else {
	hour = '12 p.m.';
}

		row += '<tr><td>' + hour + '</td>';
    	for(var it2=0;it2<data['colums'];it2++) {  //for each row in table
		//	console.log(data[it1][it2]['appointment']);
    		switch(data[it1][it2]['type']) {
    			case 'closed':
    			filler = 'closed';
    			break;
    			case 'free':	
    			filler = '<input type="checkbox" class="techtimeselect" name="selected[]" value=' + JSON.stringify(data[it1][it2]) + '>';
    			break;
    			case 'completed':
    			filler = '<a style="color:' + appointmentcolor['completed'] +';" href=' + data[it1][it2]['appointment']['htmlLink'] +'>' + data[it1][it2]['appointment']['summary']+'</a>';
    			break;
    			case 'pending':
				filler = '<a style="color:' + appointmentcolor['pending'] +';" href=' + data[it1][it2]['appointment']['htmlLink'] +'>' + data[it1][it2]['appointment']['summary']+'</a>';
    			break;
                case 'onsite':
                filler = '<a style="color:' + appointmentcolor['onsite'] +';" href=' + data[it1][it2]['appointment']['htmlLink'] +'>' + data[it1][it2]['appointment']['summary']+'</a>';
                break;
    			default:
    			filler='';
    			break;
    		}

    		row += '<td>' + filler + '</td>';
			
			}
			row += '</tr>';
			$('.scheduletable').find('tbody:last').append(row);
			row='';
			offset++;
	}


//$('.scheduletable').find('tbody:last').append(newtable);

};



function renewtable(a) {
//console.log(a);
$('.datepicker').val(a);
$.ajax({
    type: "GET",
    url: '/tech-schedule/generatetable',
    data: {date: a},
    success: populatetable,
 });

};



$(document).ready(function() {


$('.datepicker').datepicker({
                    autoclose : true,
                    todayHighlight : true,
                    clearBtn: false,
                    format: 'mm-dd-yyyy', 
                    onSelect: renewtable,
                    todayBtn: "linked",
                    startView: 0, maxViewMode: 0,minViewMode:0

                    }).on('changeDate',renewtable);




var date;
var olddate="{{ old('date') }}";
if( olddate ) {
    console.log(olddate);
    date = new Date( olddate );
} else {
    date = new Date();
}

$('.datepicker').val( (date.getMonth() + 1) + '/' + date.getDate() + '/' +  date.getFullYear() );
$.ajax({
    type: "GET",
    url: '/tech-schedule/generatetable',
    data: {date: date},
    success: populatetable
 });
 $( "#service" ).selectmenu();
 $( "#action").selectmenu();

});









</script>



<style>
textarea {
    border: solid 1px black;
    width: 100%;
    height: 100%;

    -webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
       -moz-box-sizing: border-box; /* FF1+ */
            box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
}

textarea.form-control {
  height: 100%;
}

table {
    border-collapse: separate;
    border-spacing: 5px 4;

}

input[type='checkbox'] {
    -webkit-appearance:none;
    width:2em;
    height:2em;
    background:white;
    border-radius:1.2em;
    border:2px solid #555;
}
input[type='checkbox']:checked {
    background: #2980B9;
}

.scheduletable td {
text-align:center;
border: 2px solid grey;
}

.scheduletable th {
text-align: center;
}



</style>
</head>
<html>
<body>
    <div class="flash-message">
    @foreach (['danger', 'warning', 'success', 'info'] as $msg)
      @if(Session::has('alert-' . $msg))

      <p class="alert alert-{{ $msg }}">{{ Session::get('alert-' . $msg) }} <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a></p>
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

<div class="form-group">
<form method="get" action="/tech-schedule/setappointment">
<table id='sheader'>
 <tr><td><label for="dpick">Date</label></td><td><input id="dpick" type="text" class="datepicker form-control" name="date" tabindex="0"></input>
<input type="hidden" name="username" value="{{Auth::user()->first_name . ' ' . Auth::user()->last_name}}"></input>
</td><td><label for="adesc">Appointment Description</label></td></tr>

<tr><td><label for="cname">Customer Name</label></td><td><input type="text" id='cname' name="customername" class="form-control" value="{{old('customername')}}" tabindex="1"></input></td>
    <td rowspan="7" style="width: 600px; max-width: 100%; height: 100%"><textarea type="text" id="adesc" name="appointmentdescription" class="form-control" tabindex="8">{{old('appointmentdescription')}}</textarea><!--should be textarea--></td></tr>
<tr><td><label for="cphone">Customer Phone</label></td><td><input type="text" id='cphone' name="customerphone" class="form-control" value="{{old('customerphone')}}" tabindex="2"></input></td></tr>
<tr><td><label for="bcode">Building Code</label></td><td><input type="text" onkeyup="updatesearch()" id="bcode" name="buildingcode" class="form-control" value="{{old('buildingcode')}}" tabindex="3"></input></td></tr>
<tr><td><label for="unit">Unit</label></td><td><input type="text" id="unit" name="unit" class="form-control" value="{{old('unit')}}" tabindex="4"></input></td></tr>
<tr><td><label for="service">Service</label></td><td><select id="service" name="service" value="{{old('service')}}" tabindex="5">
    <option disabled selected>Service</option><option>TV</option><option>INT</option></select><!--should be dropdown--></td></tr>
<tr><td><label for="action">Action</label></td><td><select id="action" name="action" value="{{old('action')}}" tabindex="6">
    <option disabled selected>Action</option><option>Connect</option><option>Repair</option><option>Other</option></select><!--should be dropdown--></td></tr>
<tr><td><label for="dtvaccount">DTV Account</label></td><td><input type="text" id="dtvaccount" name="dtvaccount" class="form-control" value="{{old('dtvaccount')}}" tabindex="7"></input></td></tr>

<!--$username,$tech,$buildingcode,$unit,$service,$action,$customername,$customerphone,$appointmentdescription,$startTime,$endTime,$dtvaccount-->
<table class='scheduletable table table-hover'><tbody></tbody></table>
<input type="submit" value="Submit">
</form>
</div>
</body>
</html>