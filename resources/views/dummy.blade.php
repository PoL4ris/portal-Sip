<!DOCTYPE html>
<html class="no-js">

<head>

  <meta charset="utf-8" />
  <title>SilverIp</title>
  <meta name="description" content="Silver IP" />
  <meta name="viewport"    content="width=device-width, initial-scale=1, maximum-scale=1" />

  <link rel="stylesheet"    type="text/css"     href="/css/angular/angular-material.css">
  <link rel="shortcut icon" type="image/x-icon" href="/favicon.ico">
  {{--DEFAULT STYLES --}}
  <link rel="stylesheet"    type="text/css"     href="/css/animate.min.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/font-awesome.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/simple-line-icons.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/bootstrap.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/font.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/app.css"/>

  {{--<link rel="stylesheet" href="/css/jquery.fancybox.css" type="text/css" />--}}

  {{-- PLUGINS --}}
  <link rel="stylesheet"    type="text/css"     href="/css/plugins/smartadmin-production-plugins.min.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/plugins/data_table.css"/>
  {{--ANGULAR PLUGINS--}}
  <link rel="stylesheet"    type="text/css"     href="/css/angular/xeditable.css">
  <link rel="stylesheet"    type="text/css"     href="/css/angular/angular-notify.css">
  <link rel="stylesheet"    type="text/css"     href="/css/angular/ng-table.min.css">
  {{--Charts--}}
  <link rel="stylesheet"    type="text/css"     href="/css/angular/angular-chart.css" >
  <link rel="stylesheet"    type="text/css"     href="/css/angular/nv.d3.css">
  {{-- Our Website CSS Styles --}}
  <link rel="stylesheet"    type="text/css"     href="/css/angular/main.css">
  {{-- NEW STYLES --}}
  <link rel="stylesheet"    type="text/css"     href="/css/portal/style.css"/>
  <link rel="stylesheet"    type="text/css"     href="/css/portal/building.css"/>

</head>

<body ng-app="app">

  <div class="app app-header-fixed app-aside-folded">
    {{--Our Website Content Goes Here --}}
    <div ng-include='"/angularviews/templates/nav.html"' class="app-aside hidden-xs bg-dark"></div>
    <div ng-include='"/angularviews/templates/header.html"' class="app-header app-header navbar navbar-fixed-top"></div>
    <div ng-view class="app-content" ng-controller="directiveController"></div>
    <div ng-include='"/angularviews/templates/footer.html"' class="app-footer"></div>
  </div>

  {{--Vendor: Javascripts --}}
  <script src="/js/jquery/jquery.min.js"></script>
  <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>

  {{--Vendor: Angular, followed by our custom Javascripts --}}
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-route.min.js"></script>
  <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.js"></script>


  <script src="/js/angular/xeditable.js"></script>
  <script src="/js/angular/angular-sanitize.js"></script>
  <script src="/js/angular/angular-notify.js"></script>
  <script src="/js/angular/ui-bootstrap.js"></script>
  <script src="/js/angular/angular-aria.js"></script>
  <script src="/js/angular/angular-material.js"></script>
  <script src="/js/angular/ng-table.min.js"></script>
  {{--Charts--}}
  <script src="/js/angular/Chart.js"></script>
  <script src="/js/angular/angular-chart.js"></script>
  <script src="/js/angular/d3.js"></script>
  <script src="/js/angular/nv.d3.js"></script>
  <script src="/js/angular/angular-nvd3.js"></script>
  <script src="//cdnjs.cloudflare.com/ajax/libs/nvd3/1.1.15-beta/nv.d3.js"></script>
  <script src="//cdn.jsdelivr.net/angularjs.nvd3-directives/v0.0.7/angularjs-nvd3-directives.js"></script>

  {{--Our Website Javascripts --}}
  <script src="/js/angular/main.js"></script>

  {{-- PLUGINS --}}
  <script src="/js/js_jsDate.js"></script>
  <script src="/js/portal/notify.js"></script>


  <script src="/js/portal/lib.js"></script>
  <script src="/js/portal/js.js"></script>
  <script src="/js/portal/exec.js"></script>

</body>
</html>