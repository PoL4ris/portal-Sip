<!DOCTYPE html>
<html class="no-js">

<head>

  <meta charset="utf-8" />
  <title>SilverIp</title>
  <meta name="description" content="Silver IP" />
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

   {{--DEFAULT STYLES --}}
  <link rel="stylesheet" href="/css/animate.min.css" type="text/css" />

  <link rel="stylesheet" href="/css/font-awesome.css" type="text/css" />
  <link rel="stylesheet" href="/css/simple-line-icons.css" type="text/css" />

  <link rel="stylesheet" href="/css/bootstrap.css" type="text/css" />


  <link rel="stylesheet" href="/css/font.css" type="text/css" />
  <link rel="stylesheet" href="/css/app.css" type="text/css" />
  <link rel="stylesheet" href="/css/jquery.fancybox.css" type="text/css" />

  {{-- PLUGINS --}}
  <link rel="stylesheet" href="/css/plugins/smartadmin-production-plugins.min.css" type="text/css" />
  <link rel="stylesheet" href="/css/plugins/data_table.css" type="text/css" />

  <link href="/css/angular/xeditable.css" rel="stylesheet">



  {{-- Our Website CSS Styles --}}
  <link rel="stylesheet" href="/css/angular/main.css">

  {{-- NEW STYLES --}}
  <link rel="stylesheet" href="/css/portal/style.css" type="text/css" />
  <link rel="stylesheet" href="/css/portal/building.css" type="text/css" />

</head>


<body ng-app="app">

<div class="app app-header-fixed app-aside-folded">

   {{--Our Website Content Goes Here --}}
  <div ng-include='"/angularviews/templates/nav.html"' class="app-aside hidden-xs bg-dark"></div>
  <div ng-include='"/angularviews/templates/header.html"' class="app-header app-header navbar navbar-fixed-top"></div>
  <div ng-view class="app-content"></div>
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


 {{--Our Website Javascripts --}}
<script src="/js/angular/main.js"></script>


<script src="/js/ui-load.js"></script>
<script src="/js/ui-jp.config.js"></script>
<script src="/js/ui-jp.js"></script>
<script src="/js/ui-nav.js"></script>
<script src="/js/ui-toggle.js"></script>
<script src="/js/ui-client.js"></script>

{{-- PLUGINS --}}
<script src="/js/js_jsDate.js"></script>
<script src="/js/portal/jquery.fancybox.js"></script>
<script src="/js/portal/notify.js"></script>



<script src="/js/portal/lib.js"></script>
<script src="/js/portal/js.js"></script>
<script src="/js/portal/exec.js"></script>


</body>


</html>