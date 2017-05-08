@include('main.snippet.header')
<!-- content -->
<div id="content" class="app-content" role="main">
  <div class="app-content-body ">
    <div class="hbox hbox-auto-xs hbox-auto-sm" ng-init="
    app.settings.asideFolded = false;
    app.settings.asideDock = false;
    ">
      @yield('bodyContent')
      @include('main.snippet.rightcol')
    </div>
  </div>
</div>


