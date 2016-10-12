<!DOCTYPE html>
<html lang="en-us">
<head>
  <meta charset="utf-8">
  <title> SilverIP </title>
  <meta name="description" content="">
  <meta name="author" content="">

  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">

  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/bootstrap.min.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/font-awesome.min.css">

  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/smartadmin-production.min.css">
  <link rel="stylesheet" type="text/css" media="screen" href="/css/smart/smartadmin-skins.min.css">
  {{--<link rel="stylesheet" type="text/css" media="screen" href="/css/smart/your_style.css">--}}
  <link rel="stylesheet"    type="text/css"     href="/css/style.css"/>

  <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
  <link rel="icon" href="/favicon.ico" type="image/x-icon">

  <link rel="stylesheet" href="http://fonts.googleapis.com/css?family=Open+Sans:400italic,700italic,300,400,700">

  <!-- #APP SCREEN / ICONS -->
  <!-- Specifying a Webpage Icon for Web Clip
     Ref: https://developer.apple.com/library/ios/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html -->
  <link rel="apple-touch-icon" href="/img/smart/splash/sptouch-icon-iphone.png">
  <link rel="apple-touch-icon" sizes="76x76" href="/img/smart/splash/touch-icon-ipad.png">
  <link rel="apple-touch-icon" sizes="120x120" href="/img/smart/splash/touch-icon-iphone-retina.png">
  <link rel="apple-touch-icon" sizes="152x152" href="/img/smart/splash/touch-icon-ipad-retina.png">

  <!-- iOS web-app metas : hides Safari UI Components and Changes Status Bar Appearance -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">

  <!-- Startup image for web apps -->
  <link rel="apple-touch-startup-image" href="/img/smart/splash/ipad-landscape.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:landscape)">
  <link rel="apple-touch-startup-image" href="/img/smart/splash/ipad-portrait.png" media="screen and (min-device-width: 481px) and (max-device-width: 1024px) and (orientation:portrait)">
  <link rel="apple-touch-startup-image" href="/img/smart/splash/iphone.png" media="screen and (max-device-width: 320px)">

</head>

<!--

TABLE OF CONTENTS.

Use search to find needed section.

===================================================================

|  01. #CSS Links                |  all CSS links and file paths  |
|  02. #FAVICONS                 |  Favicon links and file paths  |
|  03. #GOOGLE FONT              |  Google font link              |
|  04. #APP SCREEN / ICONS       |  app icons, screen backdrops   |
|  05. #BODY                     |  body tag                      |
|  06. #HEADER                   |  header tag                    |
|  07. #PROJECTS                 |  project lists                 |
|  08. #TOGGLE LAYOUT BUTTONS    |  layout buttons and actions    |
|  09. #MOBILE                   |  mobile view dropdown          |
|  10. #SEARCH                   |  search field                  |
|  11. #NAVIGATION               |  left panel & navigation       |
|  12. #MAIN PANEL               |  main panel                    |
|  13. #MAIN CONTENT             |  content holder                |
|  14. #PAGE FOOTER              |  page footer                   |
|  15. #SHORTCUT AREA            |  dropdown shortcuts area       |
|  16. #PLUGINS                  |  all scripts and plugins       |

===================================================================

-->

<!-- #BODY -->
<!-- Possible Classes

  * 'smart-style-{SKIN#}'
  * 'smart-rtl'         - Switch theme mode to RTL
  * 'menu-on-top'       - Switch to top navigation (no DOM change required)
  * 'no-menu'			  - Hides the menu completely
  * 'hidden-menu'       - Hides the main menu but still accessable by hovering over left edge
  * 'fixed-header'      - Fixes the header
  * 'fixed-navigation'  - Fixes the main menu
  * 'fixed-ribbon'      - Fixes breadcrumb
  * 'fixed-page-footer' - Fixes footer
  * 'container'         - boxed layout mode (non-responsive: will not work with fixed-navigation & fixed-ribbon)
-->
<body class="" ng-app="app">

<!-- #HEADER -->
<header id="header">
  <div id="logo-group">

    <!-- PLACE YOUR LOGO HERE -->
    <span id="logo"> <img src="/img/silverip-logo-magnus.png" alt="SmartAdmin"> </span>
    <!-- END LOGO PLACEHOLDER -->

    <!-- Note: The activity badge color changes when clicked and resets the number to 0
       Suggestion: You may want to set a flag when this happens to tick off all checked messages / notifications -->
    <span id="activity" class="activity-dropdown"> <i class="fa fa-user"></i> <b class="badge"> 21 </b> </span>

    <!-- AJAX-DROPDOWN : control this dropdown height, look and feel from the LESS variable file -->
    <div class="ajax-dropdown">

      <!-- the ID links are fetched via AJAX to the ajax container "ajax-notifications" -->
      <div class="btn-group btn-group-justified" data-toggle="buttons">
        <label class="btn btn-default">
          <input type="radio" name="activity" id="ajax/notify/mail.html">
          Msgs (14) </label>
        <label class="btn btn-default">
          <input type="radio" name="activity" id="ajax/notify/notifications.html">
          notify (3) </label>
        <label class="btn btn-default">
          <input type="radio" name="activity" id="ajax/notify/tasks.html">
          Tasks (4) </label>
      </div>

      <!-- notification content -->
      <div class="ajax-notifications custom-scroll">

        <div class="alert alert-transparent">
          <h4>Click a button to show messages here</h4>
          This blank page message helps protect your privacy, or you can show the first message here automatically.
        </div>

        <i class="fa fa-lock fa-4x fa-border"></i>

      </div>
      <!-- end notification content -->

      <!-- footer: refresh area -->
      <span> Last updated on: 12/12/2013 9:43AM
						<button type="button" data-loading-text="<i class='fa fa-refresh fa-spin'></i> Loading..." class="btn btn-xs btn-default pull-right">
							<i class="fa fa-refresh"></i>
						</button> </span>
      <!-- end footer -->

    </div>
    <!-- END AJAX-DROPDOWN -->
  </div>

  <!-- #PROJECTS: projects dropdown -->
  <div class="project-context hidden-xs">

    <span class="label">Projects:</span>
    <span class="project-selector dropdown-toggle" data-toggle="dropdown">Recent projects <i class="fa fa-angle-down"></i></span>

    <!-- Suggestion: populate this list with fetch and push technique -->
    <ul class="dropdown-menu">
      <li>
        <a href="javascript:void(0);">Online e-merchant management system - attaching integration with the iOS</a>
      </li>
      <li>
        <a href="javascript:void(0);">Notes on pipeline upgradee</a>
      </li>
      <li>
        <a href="javascript:void(0);">Assesment Report for merchant account</a>
      </li>
      <li class="divider"></li>
      <li>
        <a href="javascript:void(0);"><i class="fa fa-power-off"></i> Clear</a>
      </li>
    </ul>
    <!-- end dropdown-menu-->

  </div>
  <!-- end projects dropdown -->

  <!-- #TOGGLE LAYOUT BUTTONS -->
  <!-- pulled right: nav area -->
  <div class="pull-right">

    <!-- collapse menu button -->
    <div id="hide-menu" class="btn-header pull-right">
      <span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
    </div>
    <!-- end collapse menu -->

    <!-- #MOBILE -->
    <!-- Top menu profile link : this shows only when top menu is active -->
    <ul id="mobile-profile-img" class="header-dropdown-list hidden-xs padding-5">
      <li class="">
        <a href="#" class="dropdown-toggle no-margin userdropdown" data-toggle="dropdown">
          <img src="/img/smart/avatars/sunny.png" alt="John Doe" class="online" />
        </a>
        <ul class="dropdown-menu pull-right">
          <li>
            <a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0"><i class="fa fa-cog"></i> Setting</a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="#ajax/profile.html" class="padding-10 padding-top-0 padding-bottom-0"> <i class="fa fa-user"></i> <u>P</u>rofile</a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0" data-action="toggleShortcut"><i class="fa fa-arrow-down"></i> <u>S</u>hortcut</a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0" data-action="launchFullscreen"><i class="fa fa-arrows-alt"></i> Full <u>S</u>creen</a>
          </li>
          <li class="divider"></li>
          <li>
            <a href="login.html" class="padding-10 padding-top-5 padding-bottom-5" data-action="userLogout"><i class="fa fa-sign-out fa-lg"></i> <strong><u>L</u>ogout</strong></a>
          </li>
        </ul>
      </li>
    </ul>

    <!-- logout button -->
    <div id="logout" class="btn-header transparent pull-right">
      <span> <a href="login.html" title="Sign Out" data-action="userLogout" data-logout-msg="You can improve your security further after logging out by closing this opened browser"><i class="fa fa-sign-out"></i></a> </span>
    </div>
    <!-- end logout button -->

    <!-- search mobile button (this is hidden till mobile view port) -->
    <div id="search-mobile" class="btn-header transparent pull-right">
      <span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
    </div>
    <!-- end search mobile button -->

    <!-- #SEARCH -->
    <!-- input: search field -->
    <form action="#ajax/search.html" class="header-search pull-right">
      <input id="search-fld" type="text" name="param" placeholder="Find reports and more">
      <button type="submit">
        <i class="fa fa-search"></i>
      </button>
      <a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
    </form>
    <!-- end input: search field -->

    <!-- fullscreen button -->
    <div id="fullscreen" class="btn-header transparent pull-right">
      <span> <a href="javascript:void(0);" data-action="launchFullscreen" title="Full Screen"><i class="fa fa-arrows-alt"></i></a> </span>
    </div>
    <!-- end fullscreen button -->

  </div>
  <!-- end pulled right: nav area -->

</header>
<!-- END HEADER -->

<!-- #NAVIGATION -->
<!-- Left panel : Navigation area -->
<!-- Note: This width of the aside area can be adjusted through LESS/SASS variables -->
<aside id="left-panel">

  <!-- User info -->
  <div class="login-info">
    <span> <!-- User image size is adjusted inside CSS, it should stay as is -->

      <a href="javascript:void(0);" id="show-shortcut" data-action="toggleShortcut">
        <img src="/img/smart/avatars/sunny.png" alt="me" class="online" />
        <span>
          john.doe
        </span>
        <i class="fa fa-angle-down"></i>
      </a>

    </span>
  </div>
  <!-- end user info -->

  <!-- NAVIGATION : This navigation is also responsive

  To make this navigation dynamic please make sure to link the node
  (the reference to the nav > ul) after page load. Or the navigation
  will not initialize.
  -->
  <nav ng-include='"/angularviews/templates/nav.html"'></nav>

  <span class="minifyme" data-action="minifyMenu"> <i class="fa fa-arrow-circle-left hit"></i> </span>

</aside>
<!-- END NAVIGATION -->

<!-- #MAIN PANEL -->
<div id="main" role="main" ng-view >



  <!-- #MAIN CONTENT -->





  <!-- END #MAIN CONTENT -->

</div>
<!-- END #MAIN PANEL -->

<!-- #PAGE FOOTER -->
<div class="page-footer">
  <div class="row">
    <div class="col-xs-12 col-sm-6">
      <span class="txt-color-white">2016 © SilverIP <span class="hidden-xs"> Magnus </span> v.3.0</span>
    </div>
  </div>
  <!-- end row -->
</div>
<!-- END FOOTER -->

<!-- #SHORTCUT AREA : With large tiles (activated via clicking user name tag)
   Note: These tiles are completely responsive, you can add as many as you like -->
<div id="shortcut">
  <ul>
    <li>
      <a href="index.html" class="jarvismetro-tile big-cubes bg-color-blue"> <span class="iconbox"> <i class="fa fa-envelope fa-4x"></i> <span>Mail <span class="label pull-right bg-color-darken">14</span></span> </span> </a>
    </li>
  </ul>
</div>
<!-- END SHORTCUT AREA -->

<!--================================================== -->


<script src="/js/jquery/jquery.min.js"></script>
<script src="/js/jquery/jquery-ui.min.js"></script>

<!-- IMPORTANT: APP CONFIG -->
<script src="/js/smart/app.config.seed.js"></script>

<!-- BOOTSTRAP JS -->
<script src="/js/smart/bootstrap/bootstrap.min.js"></script>
<!-- MAIN APP JS FILE -->
<script src="/js/smart/app.seed.js"></script>

{{--Vendor: Angular, followed by our custom Javascripts --}}
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-route.min.js"></script>
<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.5.5/angular-animate.js"></script>

{{--Our Website Javascripts --}}
<script src="/js/angular/main.js"></script>
<script src="/js/portal/lib.js"></script>

</body>

</html>