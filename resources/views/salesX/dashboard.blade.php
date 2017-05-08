@extends('main.main')
@section('bodyContent')


  <div class="bg-light lter b-b wrapper-md">
    <h2 class="m-n font-thin h3">Sales Pipeline</h2>
  </div>

  <div class="panel panel-default">
    <div class="table-responsive">
      <table ui-jq="dataTable" class="table table-striped b-t b-b" >
        <thead>
          <tr>
            <th>Piority</th><th>Status</th><th>City</th><th>Neighborhood</th><th>Code</th><th>Wiring</th><th>Short Name</th><th>Person of contact</th><th>Phone</th><th>Email Address</th><th>Management Co</th><th>Built</th><th>Units</th><th>Floors</th>
          </tr>
        </thead>
        <tbody>
          @foreach($salesdata as $data)
          <tr>
            <td>{{ $data->Priority }}</td>
            <td>{{ $data->Status }}</td>
            <td>{{ $data->City }}</td>
            <td>{{ $data->Neighborhood }}</td>
            <td>{{ $data->Code }}</td>
            <td>{{ $data->INT_Wiring }}</td>
            <td>{{ $data->ShortName }}</td>
            <td>{{ $data->ContactName }}</td>
            <td>{{ $data->ContactPhone }}</td>
            <td>{{ $data->ContactEmail }}</td>
            <td>{{ $data->MgmtCo }}</td>
            <td>{{ $data->BuiltDate }}</td>
            <td>{{ $data->Units }}</td>
            <td>{{ $data->Floors }}</td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

@stop