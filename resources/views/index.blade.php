<!DOCTYPE html>
<html lang="en-us" class="no-js">
<head>

  <meta charset="utf-8">
  <title> SilverIP Magnus </title>
  <meta name="description" content="">
  <meta name="author" content="">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <!-- #CSS Links -->
  <!-- Basic Styles -->
  {{--<link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/bootstrap.min.css">--}}
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/bootstrap.css">
  {{--<link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/font-awesome.min.css">--}}
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">

  <!-- SmartAdmin Styles : Caution! DO NOT change the order -->
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/smartadmin-production-plugins.min.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/smartadmin-production.min.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/smartadmin-skins.min.css">

  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/smartadmin-angular.css">

  <!-- SmartAdmin RTL Support (Not using RTL? Disable the CSS below to save bandwidth) -->
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/smartadmin-rtl.min.css">

  <!-- Demo purpose only: goes with demo.js, you can delete this css when designing your own WebApp -->
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/demo.min.css">

  <!-- Data Tables CSS -->
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/styles/css/datTables.min.css">

  <!-- SILVERIP CSS -->
  <link rel="stylesheet" type="text/css" media="screen" href="/css/skin.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/style.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/buildings.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/calendar.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/mobile.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/responsive.css">

  <!-- #FAVICONS -->
  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">

  <!-- #GOOGLE FONT -->
  <!-- 	<link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700"> -->

  <!-- #APP SCREEN / ICONS -->
  <!-- Specifying a Webpage Icon for Web Clip
  Ref: https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html -->
  <link rel="apple-touch-icon" href="styles/img/splash/sptouch-icon-iphone.png">
  <link rel="apple-touch-icon" href="styles/img/splash/touch-icon-ipad.png"          sizes="76x76">
  <link rel="apple-touch-icon" href="styles/img/splash/touch-icon-iphone-retina.png" sizes="120x120">
  <link rel="apple-touch-icon" href="styles/img/splash/touch-icon-ipad-retina.png"   sizes="152x152">

  <!-- iOS web-app metas : hides Safari UI Components and Changes Status Bar Appearance -->
  <meta name="apple-mobile-web-app-capable"           content="yes">
  <meta name="apple-mobile-web-app-status-bar-style"  content="black">

  <!-- Startup image for web apps -->
  <link rel="apple-touch-startup-image" href="styles/img/splash/ipad-landscape.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)">
  <link rel="apple-touch-startup-image" href="styles/img/splash/ipad-portrait.png"  media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)">
  <link rel="apple-touch-startup-image" href="styles/img/splash/iphone.png"         media="screen and (max-device-width: 320px)">

</head>

<body data-smart-device-detect
      data-smart-fast-click
      data-smart-layout
      data-smart-page-title="SilverIP Magnus"
      ng-controller="globalToolsCtl"
      class="fixed-navigation fixed-header smart-style-7">
      {{--class="fixed-header smart-style-7">--}}

<!-- ui-view container -->
<input type="hidden" value="{{Auth::user()}}" id="auth-user" tmpTokenTest="{{ csrf_token() }}"/>

<div data-ui-view="root"  data-autoscroll="false"></div>


<!-- Use for production after building the project with grunt -->
<script src="/js/smart/build/vendor.js"></script>
{{--<script src="/js/smart/build/app.js"></script>--}}
<script src="/js/smart/build/appJs/app-1.js"></script>
<script src="/js/smart/build/appJs/app-2.js"></script>
<script src="/js/smart/build/appJs/app-3.js"></script>
<script src="/js/smart/build/appJs/app-4.js"></script>
<script src="/js/smart/build/appJs/app-5.js"></script>
<script src="/js/smart/build/appJs/app-6.js"></script>
<script src="/js/smart/build/appJs/app-7.js"></script>
<script src="/js/smart/build/appJs/app-8.js"></script>
<script src="/js/smart/build/appJs/app-9.js"></script>
<script src="/js/smart/build/appJs/app-10.js"></script>
<script src="/js/smart/build/appJs/app-11.js"></script>
<script src="/js/angular/angular-relative-date.js"></script>

<script src="/js/countUp.js"></script>
<script src="/js/chart.min.js"></script>

<script src="/js/main.js"></script>
<script src="/js/silveripJs/menuController.js"></script>
<script src="/js/silveripJs/buildingController.js"></script>
<script src="/js/silveripJs/networkController.js"></script>
<script src="/js/silveripJs/customerController.js"></script>
<script src="/js/silveripJs/supportController.js"></script>
<script src="/js/silveripJs/profileController.js"></script>
<script src="/js/silveripJs/adminController.js"></script>
<script src="/js/silveripJs/techScheduler.js"></script>
<script src="/js/silveripJs/reportController.js"></script>
<script src="/js/silveripJs/chargesController.js"></script>
<script src="/js/silveripJs/newCustomerController.js"></script>
<script src="/js/silveripJs/walkthroughController.js"></script>
<script src="/js/silveripJs/tabsController.js"></script>
<script src="/js/lib.js"></script>
<script src="/js/silveripJs/globalTools.js"></script>
<script src="/js/calendar.js"></script>
<script src="/js/sidebar.js"></script>
<script src="/js/silveripJs/icons.js"></script>
<script src="/js/silveripJs/const.js"></script>
<script src="/js/silveripJs/mobileDevice.js"></script>
<script src="/js/cluster.js"></script>


</body>

</html>
