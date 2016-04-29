@extends('main.main')
@section('bodyContent')
  <div class="col-lg-12">
    <div class="panel panel-default">
      <div class="panel-heading font-bold">Basic Building form</div>
      <div class="panel-body">
        {!! $form !!}
      </div>
    </div>
  </div>
@stop