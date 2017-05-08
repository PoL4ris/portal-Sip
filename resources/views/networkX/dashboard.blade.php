

@extends('main.main')
@section('bodyContent')


  <div class="bg-light lter b-b wrapper-md">
    <h2 class="m-n font-thin h3">Sales Pipeline</h2>
  </div>

  <div class="panel panel-default">
    <div class="table-responsive">
      <table ui-jq="dataTable" class="table table-striped b-t b-b" ui-options="{aaSorting:[[0,'asc']]}">
        <thead>
        <tr>
          <th>Location</th><th>Address</th><th>Core</th><th>Dist</th><th>Queue-set</th><th>Primary</th><th>Backup</th><th>Mgmt Subnet</th><th>segment #</th>
        </tr>
        </thead>
        <tbody>
        @foreach($networkdata as $data)
          <tr>
            <td>{{ $data->location }}</td>
            <td>{{ $data->address }}</td>
            <td><a href="" class="SwitchStatusLink btn-link" loc="{{ $data->location }}" ip="{{ $data->core }}">  {{ $data->core }} </a></td>
            <td><a href="" class="SwitchStatusLink btn-link" loc="{{ $data->location }}" ip="{{ $data->dist }}">{{ $data->dist }} </a></td>
            <td>{{ $data->access_up_to_data }}</td>

            <p style="display: none;"> {{ preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $data->primary, $str) }}</p>
            @if (preg_match('/((https?|ftp)\:\/\/)/', $data->primary, $http))
              <td><a href="{{$http[0] . $str[0]}}" target="_blank" class="btn-link"> {{ $str[0] }}</a></td>
            @else
              <td><a href="http://{{$str[0]}}" target="_blank"  class="btn-link">{{ $str[0] }}</a></td>
            @endif

            @if(!empty($data->backup))
              <p style="display: none;"> {{ preg_match('/\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}/', $data->backup, $str) }}</p>
              @if (preg_match('/((https?|ftp)\:\/\/)/', $data->backup, $http))
                <td><a href="{{$http[0] . $str[0]}}" target="_blank" class="btn-link"> {{ $str[0] }}</a></td>
              @else
                <td><a href="http://{{$str[0]}}" target="_blank"  class="btn-link">{{ $str[0] }}</a></td>
              @endif
            @else
              <td>{{ $data->backup }}</td>
            @endif
            <td>{{ $data->mgmtnet }}</td>
            <td>{{ $data->segment }}</td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>
  </div>


  <script>

    $('.SwitchStatusLink').click(function (event) {
      event.preventDefault();
      var ipAddress = $(this).attr('IP');
      var location = $(this).attr('LOC');
      var formDataLoadUrl = "assets/includes/network_switch_handler.php";
      //        console.error('IP = '+ipAddress);
      $('#switchInfoDialog').html('');
//      displayAjaxLoader('#switchInfoDialog','<center><span>Loading</span><br><img src="assets/images/ajax-loader-bar.gif" alt=""></center>');
      $('#switchInfoDialog').load(formDataLoadUrl, {
        'action': 'get-core-switch-info-page',
        'ipAddress': '"'+ipAddress+'"',
        'location' : location
      }, function(){
//        hideAjaxLoader('#switchInfoDialog');
      }); //, function(responseText){
      $('#switchInfoDialog').dialog('open');
      //        $('#ticketInfoDialog').css('display','block');
      return false;
    });


  </script>

@stop