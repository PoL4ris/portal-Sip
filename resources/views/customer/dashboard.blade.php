@extends('main.main')
@section('bodyContent')
  <div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">Customers Dashboard</h1>
  </div>
  <div class="col-lg-10 bld-search-main">
    <br />



    <div class="panel-body">
      <form class="form-horizontal" method="get" id="complexSearch">
        <div class="form-group">
          <label class="col-sm-1 control-label">Code</label>
          <div class="col-md-2"><input  index="4" id="id-customers-searchAddress" type="text" name="ShortName" placeholder="Search Address ..." class="input-search id-search form-control"></div>
          <label class="col-sm-1 control-label">Unit</label>
          <div class="col-md-2"><input  index="3" id="id-customers-searchUnit" type="text" name=Unit" placeholder="Search Unit ..." class="input-search id-search form-control"></div>
          <label class="col-sm-1 control-label">Contact</label>
          <div class="col-md-2"><input index="1" id="id-customers-searchTel" type="text" name="Tel" placeholder="Search Contact Number ..." class="input-search id-search form-control"></div>
          <label class="col-sm-1 control-label">Email</label>
          <div class="col-md-2"><input index="2"  id="id-customers-searchEmail" type="text" name="Email" placeholder="Search Email Adress..." class="input-search id-search form-control"></div>
        </div>
      </form>
    </div>

    {{-- Building Search/Simple --}}
    <div class="search-content">
      {{--<input type="text" id="id-customers-search" class="input-search id-search" placeholder="Search Customer..."><i class="is-lupa fa fa-search"></i>--}}
      <label class="col-sm-4 control-label input-search" style="background: none;">Default Search by Customers Location Code</label>

      <div id="id-customers-search-result" class="search-result anim resultadosComplex"></div>
    </div>
    {{-- END Building Search/Simple --}}


  </div>


@stop