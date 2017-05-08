@extends('main.main')
@section('bodyContent')
  <div class="bg-light lter b-b wrapper-md">
    <h1 class="m-n font-thin h3">Buildings Dashboard</h1>
  </div>
  <div class="col-lg-10 bld-search-main">
    <br />

    <a href="newbuildingform">
      <button class="btn btn-primary btn-addon btn-lg">
        <i class="fa fa-plus"></i>
        Add new Building
      </button>
    </a>

    {{-- Building Search/Simple --}}
    <div class="search-content">
      <input type="text" id="id-buildings-search" class="input-search id-search" placeholder="Search Buildings..."><i class="is-lupa fa fa-search"></i>
      <div id="id-buildings-search-result" class="search-result anim"></div>
    </div>
    {{-- END Building Search/Simple --}}

  </div>
@stop