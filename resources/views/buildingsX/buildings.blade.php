@extends('main.main')
@section('bodyContent')


  {{-- COL --}}
  <div class="col w-lg lter b-r b-tabs anim" ng-controller="CustomTabController">

    <div id="bld-tabs-cover" class="anim">
      <div class="font-thin h2">B<br />u<br />i<br />l<br />d<br />i<br />n<br />g<br />s</div>
    </div>

    <div class="bg-light lter b-b wrapper-md">
      <h2 class="m-n font-thin h3">Buildings</h2>
    </div>

    <div class="vbox">

      <div class="b-b">
        {{-- Building Search/Simple --}}
        <div class="search-content">
          <input type="text" id="id-buildings-search" class="input-search id-search" placeholder="Search Buildings..."><i class="is-lupa fa fa-search"></i>
          <div id="id-buildings-search-result" class="search-result anim"></div>
        </div>
        {{-- END Building Search/Simple --}}
      </div>

      {{--TABS--}}
      <div class="nav-tabs-alt ntas-tmp" id="ntas-tmp">
        <ul class="nav nav-tabs nav-justified">
          <li class="active anim">
            <a data-target="#tab-1" role="tab" data-toggle="tab">Buildings</a>
          </li>
          <li class=" anim">
            <a data-target="#tab-2" role="tab" data-toggle="tab">Retail</a>
          </li>
          <li class=" anim">
            <a data-target="#tab-3" role="tab" data-toggle="tab">Comercial</a>
          </li>
        </ul>
      </div>
      {{--END TABS--}}

      <div class="row-row ntas-tmp" >
        <div class="tabs-content">
          <div class="cell-inner">
            <div class="tab-content">
              {{-- OFFSET LIMIT btn's --}}
              <div class="tab-pane active" id="tab-1">
                <div id="bldlist-result-limits" class="lesmoreinfo">
                  <div offset="{{ $offset }}" limit="{{ $limit }}" position="0" class="newdata left anim left-btn-trigg bldlist-result-limits-0" ></div>
                  <div offset="{{ $offset }}" limit="{{ $limit }}" position="1" class="newdata right anim right-btn-trigg bldlist-result-limits-1" ></div>
                </div>
              {{-- END OFFSET LIMIT btn's --}}

                {{--CONTENT TABS--}}
                <div id="bldlist-result">
                  @if($buildingList)
                    @foreach($buildingList as $bldGral)
                       <p><a href="/buildings/{{ $bldGral->LocID }}">{{ $bldGral->Name?$bldGral->Name:$bldGral->name }}</a></p>
                    @endforeach
                  @endif
                </div>
              </div>

              {{--<div class="tab-pane" id="tab-2">--}}
                {{--<div class="wrapper-md">--}}
                {{--@if($retail)--}}
                  {{--@foreach($retail as $bldRetail)--}}
                    {{--<p><a href="/buildings/{{ $bldRetail->id?$bldRetail->id:$bldRetail->LocID }}">{{ $bldRetail->name?$bldRetail->name:$bldRetail->Name }}</a></p>--}}
                  {{--@endforeach--}}
                {{--@endif--}}
                {{--</div>--}}
              {{--</div>--}}

              {{--<div class="tab-pane" id="tab-3">--}}
                {{--<div class="wrapper-md">--}}
                {{--@if($comer)--}}
                  {{--@foreach($comer as $bldComercial)--}}
                    {{--<p><a href="/buildings/{{ $bldComercial->id?$bldComercial->id:$bldComercial->LocID }}">{{ $bldComercial->name?$bldComercial->name:$bldComercial->Name }}</a></p>--}}
                  {{--@endforeach--}}
                {{--@endif--}}
                {{--</div>--}}
              {{--</div>--}}
              {{-- BUILDING TABLE --}}
              {{--END CONTENT TABS--}}

            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
  {{-- END COL --}}

  {{-- BUILDING HEADER NAME --}}
  <div class="bg-light lter b-b wrapper-md">
    <h2 class="m-n font-thin h3">Building : {{ $building->name?$building->name:$building->Name }}</h2>
  </div>
  {{-- END BUILDING HEADER NAME --}}

  {{-- COL --}}
  <div class="col-lg-12">
    <!-- Building -->

    <div id="building" class="building header-img">

      <div class="bld block-a">

        <div class="img block-a-label"><img src="{{ '/img/buildings/' . $building->fnImage }}" alt=""></div>

        <div class="block-a-edit form-group ">
          <input type='file' name='img_building' class='text  form-control inp-img-form block-a-edit' data-classinput='form-control inline v-middle input-s'data-classbutton='btn btn-default'data-icon='false'ui-jq='filestyle' tabindex='-1'/>
          <img  src='#' alt='' class='prvw-img-form'/>
        </div>

      </div>

      {{--<div>--}}
        {{--<button class="btn m-b-xs w-xs btn-info btn-edit" id="block-a">Edit</button>--}}
      {{--</div>--}}

      <form action="{{ url('/buildingupdate') }}" class="xeditforms validation-form" method="POST">
        <div class="bld block-b">
          <div class="bld-info-data">
            <label class="bold-type">Name</label>           <label class="block-a-label" id="name">{{ $building->name?$building->name:$building->Name }}</label>
              <input type="text" value="{{ $building->name?$building->name:$building->Name }}" name="name" class="block-a-edit form-control">

            <label class="bold-type">Address</label>        <label class="block-a-label" id="address">{{ $building->address?$building->address:$building->Address }}</label>
              <input type="text" value="{{ $building->address?$building->address:$building->Address }}" name="address" class="block-a-edit form-control">

            <label class="bold-type">Alias</label>          <label class="block-a-label" id="alias">{{ $building->alias?$building->alias:'' }}</label>
              <input type="text" value="{{ $building->alias?$building->alias:'NO ALIAS' }}" name="alias" class="block-a-edit form-control">

            <label class="bold-type">Nickname</label>       <label class="block-a-label" id="nickname">{{ $building->nickname?$building->nickname:'' }}</label>
              <input type="text" value="{{ $building->nickname?$building->nickname:'NO NICKNAME' }}" name="nickname" class="block-a-edit form-control">

            <label class="bold-type">Neighborhood</label>   <label class="block-a-label" id="id_neighborhood"> {{ $building->neighborhoodname?$building->neighborhoodname:'' }}</label>

              <select name="id_neighborhood" class="form-control block-a-edit">
              </select>

            <label class="bold-type">Code</label>           <label class="block-a-label" id="code">{{ $building->code?$building->code:$building->ShortName }}</label>
             <input type="text" value="{{ $building->code?$building->code:$building->ShortName }}" name="code" class="block-a-edit form-control">

            <label class="bold-type">Year built</label>     <label class="block-a-label" id="year_built"> {{ $building->year_built?$building->year_built:'' }}</label>
              <input type="text" value="{{ $building->year_built?$building->year_built:'NO YEAR BUILT' }}" name="year_built" class="block-a-edit form-control date">

            <label class="bold-type">Units</label>          <label class="block-a-label" id="units">{{ $building->units?$building->units:$building->Units }}</label>
              <input type="text" value="{{ $building->units?$building->units:$building->Units }}" name="units" class="block-a-edit form-control">

            <label class="bold-type">Floors</label>         <label class="block-a-label" id="floors">{{ $building->floors?$building->floors:'NO FLOORS INFO' }}</label>
              <input type="text" value="{{ $building->floors?$building->floors:'NO FLOORS INFO' }}" name="floors" class="block-a-edit form-control">


            {{-- EXTRA DATA SERVICE LOCATION TABLE --}}
            <label class="bold-type">INT Speed/Price</label>          <label class="block-a-label" id="units">{!! $building->Speeds?$building->Speeds:'' !!}</label>
            <input type="text" value="speeds" name="units" class="block-a-edit form-control">
            <label class="bold-type">INT Setup</label>          <label class="block-a-label" id="units">{!! $building->HowToConnect?$building->HowToConnect:' ' !!}</label>
            <input type="text" value="howtoconnect" name="units" class="block-a-edit form-control">
            <label class="bold-type">TV</label>          <label class="block-a-label" id="units">{{ $building->DirectTV?$building->DirectTV:' ' }}</label>
            <input type="text" value="howtoconnect" name="units" class="block-a-edit form-control">


          </div>
        </div>

        <div class="bld block-c">
          <div class="bld-info-data">
            <label class="bold-type">Type</label>            <label class="block-a-label" id="type">{{ $building->type?$building->type:$building->ServiceType }}</label>
              <input type="text" value="{{ $building->type?$building->type:$building->ServiceType }}" name="type" class="block-a-edit form-control">

            <label class="bold-type">Legal name</label>      <label class="block-a-label" id="legal_name">{{ $building->legal_name?$building->legal_name:$building->Description }}</label>
              <input type="text" value="{{ $building->legal_name?$building->legal_name:$building->Description }}" name="legal_name" class="block-a-edit form-control">

            <label class="bold-type">Builder</label>         <label class="block-a-label" id="builder">{{ $building->builder?$building->builder:' ' }}</label>
              <input type="text" value="{{ $building->builder?$building->builder:'NO BUILDER DATA' }}" name="builder" class="block-a-edit form-control">


            {{-- EXTRA DATA SERVICE LOCATION TABLE --}}
            <label class="bold-type">Services</label>         <label class="block-a-label" id="builder">{{ $building->ServiceType?$building->ServiceType:' ' }}</label>
            <input type="text" value="{{ $building->ServiceType }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Dedicate Number</label>         <label class="block-a-label" id="builder">{{ $building->SupportNumber?$building->SupportNumber:' ' }}</label>
            <input type="text" value="{{ $building->SupportNumber }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Management</label>         <label class="block-a-label" id="builder">{{ $building->MgrCompany?$building->MgrCompany:' ' }}</label>
            <input type="text" value="{{ $building->MgrCompany }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Site Manager</label>         <label class="block-a-label" id="builder">{!! $building->MgrName?$building->MgrName:'NO DATA' . ' <br /><b>TEL: </b>' .$building->MgmtTel . ' <br /><b>Email: </b>' . $building->MgmtEmail  !!}</label>
            <input type="text" value="{{ $building->MgrName }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Front Desk</label>         <label class="block-a-label" id="builder">Front Desk</label>
            <input type="text" value="Front Desk" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Backhaul Links</label>         <label class="block-a-label" id="builder">Backhaul Links</label>
            <input type="text" value="Backhaul Links" name="builder" class="block-a-edit form-control">
            <label class="bold-type">IP</label>         <label class="block-a-label" id="builder">{!! $building->IP?$building->IP:' ' !!}</label>
            <input type="text" value="{{ $building->IP }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">DNS</label>         <label class="block-a-label" id="builder">{!! $building->DNS?$building->DNS:' ' !!}</label>
            <input type="text" value="{{ $building->DNS }}" name="builder" class="block-a-edit form-control">
            <label class="bold-type">Gateway</label>         <label class="block-a-label" id="builder">{!! $building->Gateway?$building->Gateway:' ' !!}</label>
            <input type="text" value="{{ $building->Gateway }}" name="builder" class="block-a-edit form-control">
          </div>
        </div>

        <button class="btn m-b-xs w-xs btn-success save-btn" id="save-block-a">Save</button>

      </form>


    </div>
    <!-- end Building -->

  </div>
  {{-- END COL --}}

  {{-- COL --}}
  <div class="col-lg-12">

    <div class="bg-light lter b-b wrapper-md">
      <div class="row">
        <div class="col-sm-6 col-xs-12">
          <h3 class="m-n font-thin h3 text-black">Building Information</h3>
        </div>
      </div>
    </div>

  </div>
  {{-- END COL --}}
  {{-- COL --}}

  {{--@if($exist)--}}
    {{--<div class="col-lg-12" id="tmpcss">--}}

      {{--<style>--}}
        {{--@import url(http://fonts.googleapis.com/css?family=Dosis:600,200|Great+Vibes);--}}
        {{--@import url(http://weloveiconfonts.com/api/?family=fontawesome);--}}
      {{--</style>--}}

      {{--<section>--}}
        {{--<input type="radio" id="profile" value="1" name="tractor" checked='checked' />--}}
        {{--<input type="radio" id="settings" value="2" name="tractor" />--}}
        {{--<input type="radio" id="posts" value="3" name="tractor" />--}}
        {{--<input type="radio" id="books" value="4" name="tractor" />--}}

        {{--<nav>--}}
          {{--<label for="profile" class='fontawesome-building'></label>--}}
          {{--<label for="settings" class='fontawesome-phone-sign'></label>--}}
          {{--<label for="posts" class='fontawesome-hospital'></label>--}}
          {{--<label for="books" class='fontawesome-wrench'></label>--}}
        {{--</nav>--}}

        {{--<article class='uno'>--}}
          {{--<div id="building-properties" class="building">--}}
            {{--<div>--}}
              {{--<button class="btn m-b-xs w-xs btn-info btn-edit" id="block-b">Edit</button>--}}
            {{--</div>--}}
            {{--<form action="{{ url('/bldblockb') }}" class="xeditforms">--}}
            {{--@if($properties)--}}
              {{--@foreach($properties as $index => $property)--}}
                {{--<div class="bld block-full">--}}
                  {{--<h4 class="h4-bld">Building Properties # {{ $index + 1 }}</h4>--}}
                  {{--<div class="bld-info-data">--}}

                    {{--<label class="bold-type">Dedicate Number</label>      <label class="block-b-label">{{ $property->dedicate_number }}</label>--}}
                    {{--<input type="text" value="{{ $property->dedicate_number }}" name="dedicate_number{{ $index + 1 }}" class="block-b-edit form-control">--}}

                    {{--<label class="bold-type">Wifi Info</label>            <label class="block-b-label">{{ $property->wifi_info }}</label>--}}
                    {{--<input type="text" value="{{ $property->wifi_info }}" name="wifi_info{{ $index + 1 }}" class="block-b-edit form-control">--}}

                    {{--<label class="bold-type">ip range</label>             <label class="block-b-label">{{ $property->ip_range }}</label>--}}
                    {{--<input type="text" value="{{ $property->ip_range }}" name="ip_range{{ $index + 1 }}" class="block-b-edit form-control">--}}

                    {{--<label class="bold-type">DNS</label>                  <label class="block-b-label">{{ $property->dns }}</label>--}}
                    {{--<input type="text" value="{{ $property->dns }}" name="dns{{ $index + 1 }}" class="block-b-edit form-control">--}}

                    {{--<label class="bold-type">Gateway</label>              <label class="block-b-label">{{ $property->gateway }}</label>--}}
                    {{--<input type="text" value="{{ $property->gateway }}" name="gateway{{ $index + 1 }}" class="block-b-edit form-control">--}}

                    {{--<label class="bold-type">Comments</label>             <label class="block-b-label">{{ $property->comments }}</label>--}}
                    {{--<input type="text" value="{{ $property->comments }}" name="comments{{ $index + 1 }}" class="block-b-edit form-control">--}}

                  {{--</div>--}}
                {{--</div>--}}
              {{--@endforeach--}}
              {{--@endif--}}
              {{--<button class="btn m-b-xs w-xs btn-success save-btn save-btn-info" id="save-block-b" type="submit">Save</button>--}}

            {{--</form>--}}
          {{--</div>--}}
        {{--</article>--}}

        {{--<article class='dos'>--}}
          {{--<div id="building-contact" class="building">--}}
          {{--@if($contact)--}}
            {{--@foreach($contact as $index => $contacts)--}}
              {{--<div class="bld block-full">--}}
                {{--<h4 class="h4-bld">Building Contact # {{ $index + 1 }}</h4>--}}
                {{--<div class="bld-info-data">--}}
                  {{--<label class="bold-type">First Name</label>           <label>{{ $contacts->first_name }}</label>--}}
                  {{--<label class="bold-type">Last Name</label>            <label>{{ $contacts->last_name }}</label>--}}
                  {{--<label class="bold-type">Contact info</label>         <label>{{ $contacts->contact }}</label>--}}
                  {{--<label class="bold-type">Fax</label>                  <label>{{ $contacts->fax }}</label>--}}
                  {{--<label class="bold-type">Company</label>              <label>{{ $contacts->company }}</label>--}}
                  {{--<label class="bold-type">Comments</label>             <label>{{ $contacts->comments }}</label>--}}
                {{--</div>--}}
              {{--</div>--}}
            {{--@endforeach--}}
            {{--@endif--}}
          {{--</div>--}}
        {{--</article>--}}

        {{--<article class='tres'>--}}
          {{--<div id="building-propertiesx" class="building">--}}
            {{--<div>--}}
              {{--<button class="btn m-b-xs w-xs btn-info btn-edit" id="block-c">Edit</button>--}}
            {{--</div>--}}
            {{--<form action="{{ url('/bldblockc') }}" class="xeditforms">--}}
            {{--@if($properties)--}}
              {{--@foreach($properties as $index => $property)--}}
                {{--<div class="bld block-full">--}}
                  {{--<h4 class="h4-bld">Building Properties # {{ $index + 1 }}</h4>--}}
                  {{--<div class="bld-info-data">--}}

                    {{--<label class="bold-type">Dedicate Number</label>      <label class="block-c-label">{{ $property->dedicate_number }}</label>--}}
                    {{--<input type="text" value="{{ $property->dedicate_number }}" name="dedicate_number{{ $index + 1 }}" class="block-c-edit form-control">--}}

                    {{--<label class="bold-type">Wifi Info</label>            <label class="block-c-label">{{ $property->wifi_info }}</label>--}}
                    {{--<input type="text" value="{{ $property->wifi_info }}" name="wifi_info{{ $index + 1 }}" class="block-c-edit form-control">--}}

                    {{--<label class="bold-type">ip range</label>             <label class="block-c-label">{{ $property->ip_range }}</label>--}}
                    {{--<input type="text" value="{{ $property->ip_range }}" name="ip_range{{ $index + 1 }}" class="block-c-edit form-control">--}}

                    {{--<label class="bold-type">DNS</label>                  <label class="block-c-label">{{ $property->dns }}</label>--}}
                    {{--<input type="text" value="{{ $property->dns }}" name="dns{{ $index + 1 }}" class="block-c-edit form-control">--}}

                    {{--<label class="bold-type">Gateway</label>              <label class="block-c-label">{{ $property->gateway }}</label>--}}
                    {{--<input type="text" value="{{ $property->gateway }}" name="gateway{{ $index + 1 }}" class="block-c-edit form-control">--}}

                    {{--<label class="bold-type">Comments</label>             <label class="block-c-label">{{ $property->comments }}</label>--}}
                    {{--<input type="text" value="{{ $property->comments }}" name="comments{{ $index + 1 }}" class="block-c-edit form-control">--}}

                  {{--</div>--}}
                {{--</div>--}}
              {{--@endforeach--}}
              {{--@endif--}}
              {{--<button class="btn m-b-xs w-xs btn-success save-btn save-btn-info" id="save-block-c" type="submit">Save</button>--}}

            {{--</form>--}}
          {{--</div>--}}
        {{--</article>--}}

        {{--<article class='cuatro'>--}}
          {{--<div id="building-contactx" class="building">--}}
          {{--@if($contact)--}}
            {{--@foreach($contact as $index => $contacts)--}}
              {{--<div class="bld block-full">--}}
                {{--<h4 class="h4-bld">Building Contact # {{ $index + 1 }}</h4>--}}
                {{--<div class="bld-info-data">--}}
                  {{--<label class="bold-type">First Name</label>          <label>{{ $contacts->first_name }}</label>--}}
                  {{--<label class="bold-type">Last Name</label>           <label>{{ $contacts->last_name }}</label>--}}
                  {{--<label class="bold-type">Contact info</label>                <label>{{ $contacts->contact }}</label>--}}
                  {{--<label class="bold-type">Fax</label>            <label>{{ $contacts->fax }}</label>--}}
                  {{--<label class="bold-type">Company</label>           <label>{{ $contacts->company }}</label>--}}
                  {{--<label class="bold-type">Comments</label>           <label>{{ $contacts->comments }}</label>--}}
                {{--</div>--}}
              {{--</div>--}}
            {{--@endforeach--}}
            {{--@endif--}}
          {{--</div>--}}
        {{--</article>--}}
      {{--</section>--}}

    {{--</div>--}}
  {{--@endif--}}
  {{-- END COL --}}


@stop