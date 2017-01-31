
'use strict';

angular.module('app.appViews').controller('ProjectsDemoCtrl', function ($scope, projects) {

  $scope.projects = projects.data;

  $scope.tableOptions =  {
    "data": projects.data.data,
//            "bDestroy": true,
    "iDisplayLength": 15,
    "columns": [
      {
        "class":          'details-control',
        "orderable":      false,
        "data":           null,
        "defaultContent": ''
      },
      { "data": "name" },
      { "data": "est" },
      { "data": "contacts" },
      { "data": "status" },
      { "data": "target-actual" },
      { "data": "starts" },
      { "data": "ends" },
      { "data": "tracker" }
    ],
    "order": [[1, 'asc']]
  }
});
angular.module("app").run(["$templateCache", function($templateCache) {
  return;
  $templateCache.put("app/dashboard/live-feeds.tpl.html","<div jarvis-widget id=\"live-feeds-widget\" data-widget-togglebutton=\"false\" data-widget-editbutton=\"false\"\r\n     data-widget-fullscreenbutton=\"false\" data-widget-colorbutton=\"false\" data-widget-deletebutton=\"false\">\r\n<!-- widget options:\r\nusage: <div class=\"jarviswidget\" id=\"wid-id-0\" data-widget-editbutton=\"false\">\r\n\r\ndata-widget-colorbutton=\"false\"\r\ndata-widget-editbutton=\"false\"\r\ndata-widget-togglebutton=\"false\"\r\ndata-widget-deletebutton=\"false\"\r\ndata-widget-fullscreenbutton=\"false\"\r\ndata-widget-custombutton=\"false\"\r\ndata-widget-collapsed=\"true\"\r\ndata-widget-sortable=\"false\"\r\n\r\n-->\r\n<header>\r\n    <span class=\"widget-icon\"> <i class=\"glyphicon glyphicon-stats txt-color-darken\"></i> </span>\r\n\r\n    <h2>Live Feeds </h2>\r\n\r\n    <ul class=\"nav nav-tabs pull-right in\" id=\"myTab\">\r\n        <li class=\"active\">\r\n            <a data-toggle=\"tab\" href=\"#s1\"><i class=\"fa fa-clock-o\"></i> <span class=\"hidden-mobile hidden-tablet\">Live Stats</span></a>\r\n        </li>\r\n\r\n        <li>\r\n            <a data-toggle=\"tab\" href=\"#s2\"><i class=\"fa fa-facebook\"></i> <span class=\"hidden-mobile hidden-tablet\">Social Network</span></a>\r\n        </li>\r\n\r\n        <li>\r\n            <a data-toggle=\"tab\" href=\"#s3\"><i class=\"fa fa-dollar\"></i> <span class=\"hidden-mobile hidden-tablet\">Revenue</span></a>\r\n        </li>\r\n    </ul>\r\n\r\n</header>\r\n\r\n<!-- widget div-->\r\n<div class=\"no-padding\">\r\n\r\n    <div class=\"widget-body\">\r\n        <!-- content -->\r\n        <div id=\"myTabContent\" class=\"tab-content\">\r\n            <div class=\"tab-pane fade active in padding-10 no-padding-bottom\" id=\"s1\">\r\n                <div class=\"row no-space\">\r\n                    <div class=\"col-xs-12 col-sm-12 col-md-8 col-lg-8\">\r\n														<span class=\"demo-liveupdate-1\"> <span\r\n                                                                class=\"onoffswitch-title\">Live switch</span> <span\r\n                                                                class=\"onoffswitch\">\r\n																<input type=\"checkbox\" name=\"start_interval\" ng-model=\"autoUpdate\"\r\n                                                                       class=\"onoffswitch-checkbox\" id=\"start_interval\">\r\n																<label class=\"onoffswitch-label\" for=\"start_interval\">\r\n                                                                    <span class=\"onoffswitch-inner\"\r\n                                                                          data-swchon-text=\"ON\"\r\n                                                                          data-swchoff-text=\"OFF\"></span>\r\n                                                                    <span class=\"onoffswitch-switch\"></span>\r\n                                                                </label> </span> </span>\r\n\r\n                        <div id=\"updating-chart\" class=\"chart-large txt-color-blue\" flot-basic flot-data=\"liveStats\" flot-options=\"liveStatsOptions\"></div>\r\n\r\n                    </div>\r\n                    <div class=\"col-xs-12 col-sm-12 col-md-4 col-lg-4 show-stats\">\r\n\r\n                        <div class=\"row\">\r\n                            <div class=\"col-xs-6 col-sm-6 col-md-12 col-lg-12\"><span class=\"text\"> My Tasks <span\r\n                                    class=\"pull-right\">130/200</span> </span>\r\n\r\n                                <div class=\"progress\">\r\n                                    <div class=\"progress-bar bg-color-blueDark\" style=\"width: 65%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                            <div class=\"col-xs-6 col-sm-6 col-md-12 col-lg-12\"><span class=\"text\"> Transfered <span\r\n                                    class=\"pull-right\">440 GB</span> </span>\r\n\r\n                                <div class=\"progress\">\r\n                                    <div class=\"progress-bar bg-color-blue\" style=\"width: 34%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                            <div class=\"col-xs-6 col-sm-6 col-md-12 col-lg-12\"><span class=\"text\"> Bugs Squashed<span\r\n                                    class=\"pull-right\">77%</span> </span>\r\n\r\n                                <div class=\"progress\">\r\n                                    <div class=\"progress-bar bg-color-blue\" style=\"width: 77%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                            <div class=\"col-xs-6 col-sm-6 col-md-12 col-lg-12\"><span class=\"text\"> User Testing <span\r\n                                    class=\"pull-right\">7 Days</span> </span>\r\n\r\n                                <div class=\"progress\">\r\n                                    <div class=\"progress-bar bg-color-greenLight\" style=\"width: 84%;\"></div>\r\n                                </div>\r\n                            </div>\r\n\r\n                            <span class=\"show-stat-buttons\"> <span class=\"col-xs-12 col-sm-6 col-md-6 col-lg-6\"> <a\r\n                                    href-void class=\"btn btn-default btn-block hidden-xs\">Generate PDF</a> </span> <span\r\n                                    class=\"col-xs-12 col-sm-6 col-md-6 col-lg-6\"> <a href-void\r\n                                                                                     class=\"btn btn-default btn-block hidden-xs\">Report\r\n                                a bug</a> </span> </span>\r\n\r\n                        </div>\r\n\r\n                    </div>\r\n                </div>\r\n\r\n                <div class=\"show-stat-microcharts\" data-sparkline-container data-easy-pie-chart-container>\r\n                    <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">\r\n\r\n                        <div class=\"easy-pie-chart txt-color-orangeDark\" data-percent=\"33\" data-pie-size=\"50\">\r\n                            <span class=\"percent percent-sign\">35</span>\r\n                        </div>\r\n                        <span class=\"easy-pie-title\"> Server Load <i class=\"fa fa-caret-up icon-color-bad\"></i> </span>\r\n                        <ul class=\"smaller-stat hidden-sm pull-right\">\r\n                            <li>\r\n                                <span class=\"label bg-color-greenLight\"><i class=\"fa fa-caret-up\"></i> 97%</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"label bg-color-blueLight\"><i class=\"fa fa-caret-down\"></i> 44%</span>\r\n                            </li>\r\n                        </ul>\r\n                        <div class=\"sparkline txt-color-greenLight hidden-sm hidden-md pull-right\"\r\n                             data-sparkline-type=\"line\" data-sparkline-height=\"33px\" data-sparkline-width=\"70px\"\r\n                             data-fill-color=\"transparent\">\r\n                            130, 187, 250, 257, 200, 210, 300, 270, 363, 247, 270, 363, 247\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">\r\n                        <div class=\"easy-pie-chart txt-color-greenLight\" data-percent=\"78.9\" data-pie-size=\"50\">\r\n                            <span class=\"percent percent-sign\">78.9 </span>\r\n                        </div>\r\n                        <span class=\"easy-pie-title\"> Disk Space <i class=\"fa fa-caret-down icon-color-good\"></i></span>\r\n                        <ul class=\"smaller-stat hidden-sm pull-right\">\r\n                            <li>\r\n                                <span class=\"label bg-color-blueDark\"><i class=\"fa fa-caret-up\"></i> 76%</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"label bg-color-blue\"><i class=\"fa fa-caret-down\"></i> 3%</span>\r\n                            </li>\r\n                        </ul>\r\n                        <div class=\"sparkline txt-color-blue hidden-sm hidden-md pull-right\" data-sparkline-type=\"line\"\r\n                             data-sparkline-height=\"33px\" data-sparkline-width=\"70px\" data-fill-color=\"transparent\">\r\n                            257, 200, 210, 300, 270, 363, 130, 187, 250, 247, 270, 363, 247\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">\r\n                        <div class=\"easy-pie-chart txt-color-blue\" data-percent=\"23\" data-pie-size=\"50\">\r\n                            <span class=\"percent percent-sign\">23 </span>\r\n                        </div>\r\n                        <span class=\"easy-pie-title\"> Transfered <i class=\"fa fa-caret-up icon-color-good\"></i></span>\r\n                        <ul class=\"smaller-stat hidden-sm pull-right\">\r\n                            <li>\r\n                                <span class=\"label bg-color-darken\">10GB</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"label bg-color-blueDark\"><i class=\"fa fa-caret-up\"></i> 10%</span>\r\n                            </li>\r\n                        </ul>\r\n                        <div class=\"sparkline txt-color-darken hidden-sm hidden-md pull-right\"\r\n                             data-sparkline-type=\"line\" data-sparkline-height=\"33px\" data-sparkline-width=\"70px\"\r\n                             data-fill-color=\"transparent\">\r\n                            200, 210, 363, 247, 300, 270, 130, 187, 250, 257, 363, 247, 270\r\n                        </div>\r\n                    </div>\r\n                    <div class=\"col-xs-12 col-sm-3 col-md-3 col-lg-3\">\r\n                        <div class=\"easy-pie-chart txt-color-darken\" data-percent=\"36\" data-pie-size=\"50\">\r\n                            <span class=\"percent degree-sign\">36 <i class=\"fa fa-caret-up\"></i></span>\r\n                        </div>\r\n                        <span class=\"easy-pie-title\"> Temperature <i\r\n                                class=\"fa fa-caret-down icon-color-good\"></i></span>\r\n                        <ul class=\"smaller-stat hidden-sm pull-right\">\r\n                            <li>\r\n                                <span class=\"label bg-color-red\"><i class=\"fa fa-caret-up\"></i> 124</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"label bg-color-blue\"><i class=\"fa fa-caret-down\"></i> 40 F</span>\r\n                            </li>\r\n                        </ul>\r\n                        <div class=\"sparkline txt-color-red hidden-sm hidden-md pull-right\" data-sparkline-type=\"line\"\r\n                             data-sparkline-height=\"33px\" data-sparkline-width=\"70px\" data-fill-color=\"transparent\">\r\n                            2700, 3631, 2471, 2700, 3631, 2471, 1300, 1877, 2500, 2577, 2000, 2100, 3000\r\n                        </div>\r\n                    </div>\r\n                </div>\r\n\r\n            </div>\r\n            <!-- end s1 tab pane -->\r\n\r\n            <div class=\"tab-pane fade\" id=\"s2\">\r\n                <div class=\"widget-body-toolbar bg-color-white\">\r\n\r\n                    <form class=\"form-inline\" role=\"form\">\r\n\r\n                        <div class=\"form-group\">\r\n                            <label class=\"sr-only\" for=\"s123\">Show From</label>\r\n                            <input type=\"email\" class=\"form-control input-sm\" id=\"s123\" placeholder=\"Show From\">\r\n                        </div>\r\n                        <div class=\"form-group\">\r\n                            <input type=\"email\" class=\"form-control input-sm\" id=\"s124\" placeholder=\"To\">\r\n                        </div>\r\n\r\n                        <div class=\"btn-group hidden-phone pull-right\">\r\n                            <a class=\"btn dropdown-toggle btn-xs btn-default\" data-toggle=\"dropdown\"><i\r\n                                    class=\"fa fa-cog\"></i> More <span class=\"caret\"> </span> </a>\r\n                            <ul class=\"dropdown-menu pull-right\">\r\n                                <li>\r\n                                    <a href-void><i class=\"fa fa-file-text-alt\"></i> Export to PDF</a>\r\n                                </li>\r\n                                <li>\r\n                                    <a href-void><i class=\"fa fa-question-sign\"></i> Help</a>\r\n                                </li>\r\n                            </ul>\r\n                        </div>\r\n\r\n                    </form>\r\n\r\n                </div>\r\n                <div class=\"padding-10\">\r\n                    <div id=\"statsChart\" class=\"chart-large has-legend-unique\" flot-basic flot-data=\"statsData\" flot-options=\"statsDisplayOptions\"></div>\r\n                </div>\r\n\r\n            </div>\r\n            <!-- end s2 tab pane -->\r\n\r\n            <div class=\"tab-pane fade\" id=\"s3\">\r\n\r\n                <div class=\"widget-body-toolbar bg-color-white smart-form\" id=\"rev-toggles\">\r\n\r\n                    <div class=\"inline-group\">\r\n\r\n                        <label for=\"gra-0\" class=\"checkbox\">\r\n                            <input type=\"checkbox\" id=\"gra-0\" ng-model=\"targetsShow\">\r\n                            <i></i> Target </label>\r\n                        <label for=\"gra-1\" class=\"checkbox\">\r\n                            <input type=\"checkbox\" id=\"gra-1\" ng-model=\"actualsShow\">\r\n                            <i></i> Actual </label>\r\n                        <label for=\"gra-2\" class=\"checkbox\">\r\n                            <input type=\"checkbox\" id=\"gra-2\" ng-model=\"signupsShow\">\r\n                            <i></i> Signups </label>\r\n                    </div>\r\n\r\n                    <div class=\"btn-group hidden-phone pull-right\">\r\n                        <a class=\"btn dropdown-toggle btn-xs btn-default\" data-toggle=\"dropdown\"><i\r\n                                class=\"fa fa-cog\"></i> More <span class=\"caret\"> </span> </a>\r\n                        <ul class=\"dropdown-menu pull-right\">\r\n                            <li>\r\n                                <a href-void><i class=\"fa fa-file-text-alt\"></i> Export to PDF</a>\r\n                            </li>\r\n                            <li>\r\n                                <a href-void><i class=\"fa fa-question-sign\"></i> Help</a>\r\n                            </li>\r\n                        </ul>\r\n                    </div>\r\n\r\n                </div>\r\n\r\n                <div class=\"padding-10\">\r\n                    <div id=\"flotcontainer\" class=\"chart-large has-legend-unique\" flot-basic flot-data=\"revenewData\" flot-options=\"revenewDisplayOptions\" ></div>\r\n                </div>\r\n            </div>\r\n            <!-- end s3 tab pane -->\r\n        </div>\r\n\r\n        <!-- end content -->\r\n    </div>\r\n\r\n</div>\r\n<!-- end widget div -->\r\n</div>\r\n");
  $templateCache.put("app/layout/layout.tpl.html","<!-- HEADER -->\r\n<div data-smart-include=\"app/layout/partials/header.tpl.html\" class=\"placeholder-header\"></div>\r\n<!-- END HEADER -->\r\n\r\n\r\n<!-- Left panel : Navigation area -->\r\n<!-- Note: This width of the aside area can be adjusted through LESS variables -->\r\n<div data-smart-include=\"app/layout/partials/navigation.tpl.html\" class=\"placeholder-left-panel\"></div>\r\n\r\n<!-- END NAVIGATION -->\r\n\r\n<!-- MAIN PANEL -->\r\n<div id=\"main\" role=\"main\">\r\n    <demo-states></demo-states>\r\n\r\n    <!-- RIBBON -->\r\n    <div id=\"ribbon\">\r\n\r\n				<span class=\"ribbon-button-alignment\">\r\n					<span id=\"refresh\" class=\"btn btn-ribbon\" reset-widgets\r\n                          tooltip-placement=\"bottom\"\r\n                          smart-tooltip-html=\"<i class=\'text-warning fa fa-warning\'></i> Warning! This will reset all your widget settings.\">\r\n						<i class=\"fa fa-refresh\"></i>\r\n					</span>\r\n				</span>\r\n\r\n        <!-- breadcrumb -->\r\n        <state-breadcrumbs></state-breadcrumbs>\r\n        <!-- end breadcrumb -->\r\n\r\n\r\n    </div>\r\n    <!-- END RIBBON -->\r\n\r\n\r\n    <div data-smart-router-animation-wrap=\"content content@app\" data-wrap-for=\"#content\">\r\n        <div data-ui-view=\"content\" data-autoscroll=\"false\"></div>\r\n    </div>\r\n\r\n</div>\r\n<!-- END MAIN PANEL -->\r\n\r\n<!-- PAGE FOOTER -->\r\n<div data-smart-include=\"app/layout/partials/footer.tpl.html\"></div>\r\n\r\n<div data-smart-include=\"app/layout/shortcut/shortcut.tpl.html\"></div>\r\n\r\n<!-- END PAGE FOOTER -->\r\n\r\n\r\n");
  $templateCache.put("app/auth/directives/login-info.tpl.html","<div class=\"login-info ng-cloak\">\r\n    <span> <!-- User image size is adjusted inside CSS, it should stay as it -->\r\n        <a  href=\"\" toggle-shortcut>\r\n            <img ng-src=\"{{user.picture}}\" alt=\"me\" class=\"online\">\r\n                <span>{{user.username}}\r\n                </span>\r\n            <i class=\"fa fa-angle-down\"></i>\r\n        </a>\r\n     </span>\r\n</div>");
  $templateCache.put("app/calendar/directives/full-calendar.tpl.html","<div jarvis-widget data-widget-color=\"blueDark\">\r\n    <header>\r\n        <span class=\"widget-icon\"> <i class=\"fa fa-calendar\"></i> </span>\r\n\r\n        <h2> My Events </h2>\r\n\r\n        <div class=\"widget-toolbar\">\r\n            <!-- add: non-hidden - to disable auto hide -->\r\n            <div class=\"btn-group dropdown\" dropdown >\r\n                <button class=\"btn dropdown-toggle btn-xs btn-default\" data-toggle=\"dropdown\">\r\n                    Showing <i class=\"fa fa-caret-down\"></i>\r\n                </button>\r\n                <ul class=\"dropdown-menu js-status-update pull-right\">\r\n                    <li>\r\n                        <a ng-click=\"changeView(\'month\')\">Month</a>\r\n                    </li>\r\n                    <li>\r\n                        <a ng-click=\"changeView(\'agendaWeek\')\">Agenda</a>\r\n                    </li>\r\n                    <li>\r\n                        <a ng-click=\"changeView(\'agendaDay\')\">Today</a>\r\n                    </li>\r\n                </ul>\r\n            </div>\r\n        </div>\r\n    </header>\r\n\r\n    <!-- widget div-->\r\n    <div>\r\n        <div class=\"widget-body no-padding\">\r\n            <!-- content goes here -->\r\n            <div class=\"widget-body-toolbar\">\r\n\r\n                <div id=\"calendar-buttons\">\r\n\r\n                    <div class=\"btn-group\">\r\n                        <a ng-click=\"prev()\" class=\"btn btn-default btn-xs\"><i\r\n                                class=\"fa fa-chevron-left\"></i></a>\r\n                        <a ng-click=\"next()\" class=\"btn btn-default btn-xs\"><i\r\n                                class=\"fa fa-chevron-right\"></i></a>\r\n                    </div>\r\n                </div>\r\n            </div>\r\n            <div id=\"calendar\"></div>\r\n\r\n            <!-- end content -->\r\n        </div>\r\n\r\n    </div>\r\n    <!-- end widget div -->\r\n</div>\r\n");
  $templateCache.put("app/calendar/views/calendar.tpl.html","<!-- MAIN CONTENT -->\r\n<div id=\"content\">\r\n\r\n    <div class=\"row\">\r\n        <big-breadcrumbs items=\"[\'Home\', \'Calendar\']\" class=\"col-xs-12 col-sm-7 col-md-7 col-lg-4\"></big-breadcrumbs>\r\n        <div smart-include=\"app/layout/partials/sub-header.tpl.html\"></div>\r\n    </div>\r\n    <!-- widget grid -->\r\n    <section id=\"widget-grid\" widget-grid>\r\n        <!-- row -->\r\n        <div class=\"row\" ng-controller=\"CalendarCtrl\" >\r\n\r\n\r\n            <div class=\"col-sm-12 col-md-12 col-lg-3\">\r\n                <!-- new widget -->\r\n                <div class=\"jarviswidget jarviswidget-color-blueDark\">\r\n                    <header>\r\n                        <h2> Add Events </h2>\r\n                    </header>\r\n\r\n                    <!-- widget div-->\r\n                    <div>\r\n\r\n                        <div class=\"widget-body\">\r\n                            <!-- content goes here -->\r\n\r\n                            <form id=\"add-event-form\">\r\n                                <fieldset>\r\n\r\n                                    <div class=\"form-group\">\r\n                                        <label>Select Event Icon</label>\r\n                                        <div class=\"btn-group btn-group-sm btn-group-justified\" data-toggle=\"buttons\" > <!--  -->\r\n                                            <label class=\"btn btn-default active\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-1\" value=\"fa-info\" radio-toggle ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-info text-muted\"></i> </label>\r\n                                            <label class=\"btn btn-default\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-2\" value=\"fa-warning\" radio-toggle  ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-warning text-muted\"></i> </label>\r\n                                            <label class=\"btn btn-default\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-3\" value=\"fa-check\" radio-toggle  ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-check text-muted\"></i> </label>\r\n                                            <label class=\"btn btn-default\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-4\" value=\"fa-user\" radio-toggle  ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-user text-muted\"></i> </label>\r\n                                            <label class=\"btn btn-default\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-5\" value=\"fa-lock\" radio-toggle  ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-lock text-muted\"></i> </label>\r\n                                            <label class=\"btn btn-default\">\r\n                                                <input type=\"radio\" name=\"iconselect\" id=\"icon-6\" value=\"fa-clock-o\" radio-toggle  ng-model=\"newEvent.icon\">\r\n                                                <i class=\"fa fa-clock-o text-muted\"></i> </label>\r\n                                        </div>\r\n                                    </div>\r\n\r\n                                    <div class=\"form-group\">\r\n                                        <label>Event Title</label>\r\n                                        <input ng-model=\"newEvent.title\" class=\"form-control\"  id=\"title\" name=\"title\" maxlength=\"40\" type=\"text\" placeholder=\"Event Title\">\r\n                                    </div>\r\n                                    <div class=\"form-group\">\r\n                                        <label>Event Description</label>\r\n                                        <textarea  ng-model=\"newEvent.description\" class=\"form-control\" placeholder=\"Please be brief\" rows=\"3\" maxlength=\"40\" id=\"description\"></textarea>\r\n                                        <p class=\"note\">Maxlength is set to 40 characters</p>\r\n                                    </div>\r\n\r\n                                    <div class=\"form-group\">\r\n                                        <label>Select Event Color</label>\r\n                                        <div class=\"btn-group btn-group-justified btn-select-tick\" data-toggle=\"buttons\" >\r\n                                            <label class=\"btn bg-color-darken active\">\r\n                                                <input   ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option1\" value=\"bg-color-darken txt-color-white\" >\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                            <label class=\"btn bg-color-blue\">\r\n                                                <input  ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option2\" value=\"bg-color-blue txt-color-white\">\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                            <label class=\"btn bg-color-orange\">\r\n                                                <input  ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option3\" value=\"bg-color-orange txt-color-white\">\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                            <label class=\"btn bg-color-greenLight\">\r\n                                                <input  ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option4\" value=\"bg-color-greenLight txt-color-white\">\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                            <label class=\"btn bg-color-blueLight\">\r\n                                                <input  ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option5\" value=\"bg-color-blueLight txt-color-white\">\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                            <label class=\"btn bg-color-red\">\r\n                                                <input  ng-model=\"newEvent.className\" radio-toggle   type=\"radio\" name=\"priority\" id=\"option6\" value=\"bg-color-red txt-color-white\">\r\n                                                <i class=\"fa fa-check txt-color-white\"></i> </label>\r\n                                        </div>\r\n                                    </div>\r\n\r\n                                </fieldset>\r\n                                <div class=\"form-actions\">\r\n                                    <div class=\"row\">\r\n                                        <div class=\"col-md-12\">\r\n                                            <button class=\"btn btn-default\" type=\"button\" id=\"add-event\" ng-click=\"addEvent()\" >\r\n                                                Add Event\r\n                                            </button>\r\n                                        </div>\r\n                                    </div>\r\n                                </div>\r\n                            </form>\r\n\r\n                            <!-- end content -->\r\n                        </div>\r\n\r\n                    </div>\r\n                    <!-- end widget div -->\r\n                </div>\r\n                <!-- end widget -->\r\n\r\n                <div class=\"well well-sm\" id=\"event-container\">\r\n                    <form>\r\n                        <legend>\r\n                            Draggable Events\r\n                        </legend>\r\n                        <ul id=\'external-events\' class=\"list-unstyled\">\r\n\r\n                            <li ng-repeat=\"event in eventsExternal\" dragable-event>\r\n                                <span class=\"{{event.className}}\" \r\n                                    data-description=\"{{event.description}}\"\r\n                                    data-icon=\"{{event.icon}}\"\r\n                                >\r\n                                {{event.title}}</span>\r\n                            </li>\r\n                            \r\n                        </ul>\r\n\r\n                        <!-- <ul id=\'external-events\' class=\"list-unstyled\">\r\n                            <li>\r\n                                <span class=\"bg-color-darken txt-color-white\" data-description=\"Currently busy\" data-icon=\"fa-time\">Office Meeting</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"bg-color-blue txt-color-white\" data-description=\"No Description\" data-icon=\"fa-pie\">Lunch Break</span>\r\n                            </li>\r\n                            <li>\r\n                                <span class=\"bg-color-red txt-color-white\" data-description=\"Urgent Tasks\" data-icon=\"fa-alert\">URGENT</span>\r\n                            </li>\r\n                        </ul> -->\r\n\r\n                        <div class=\"checkbox\">\r\n                            <label>\r\n                                <input type=\"checkbox\" id=\"drop-remove\" class=\"checkbox style-0\" checked=\"checked\">\r\n                                <span>remove after drop</span> </label>\r\n\r\n                        </div>\r\n                    </form>\r\n\r\n                </div>\r\n            </div>\r\n\r\n\r\n            <article class=\"col-sm-12 col-md-12 col-lg-9\">\r\n                <full-calendar id=\"main-calendar-widget\" data-events=\"events\"></full-calendar>\r\n            </article>\r\n        </div>\r\n    </section>\r\n</div>");
  $templateCache.put("app/dashboard/projects/recent-projects.tpl.html","<div class=\"project-context hidden-xs dropdown\" dropdown>\r\n\r\n    <span class=\"label\">{{getWord(\'Projects\')}}:</span>\r\n    <span class=\"project-selector dropdown-toggle\" data-toggle=\"dropdown\">{{getWord(\'Recent projects\')}} <i ng-if=\"projects.length\"\r\n            class=\"fa fa-angle-down\"></i></span>\r\n\r\n    <ul class=\"dropdown-menu\" ng-if=\"projects.length\">\r\n        <li ng-repeat=\"project in projects\">\r\n            <a href=\"{{project.href}}\">{{project.title}}</a>\r\n        </li>\r\n        <li class=\"divider\"></li>\r\n        <li>\r\n            <a ng-click=\"clearProjects()\"><i class=\"fa fa-power-off\"></i> Clear</a>\r\n        </li>\r\n    </ul>\r\n\r\n</div>");
  $templateCache.put("app/dashboard/todo/todo-widget.tpl.html","<div id=\"todo-widget\" jarvis-widget data-widget-editbutton=\"false\" data-widget-color=\"blue\"\r\n     ng-controller=\"TodoCtrl\">\r\n    <header>\r\n        <span class=\"widget-icon\"> <i class=\"fa fa-check txt-color-white\"></i> </span>\r\n\r\n        <h2> ToDo\'s </h2>\r\n\r\n        <div class=\"widget-toolbar\">\r\n            <!-- add: non-hidden - to disable auto hide -->\r\n            <button class=\"btn btn-xs btn-default\" ng-class=\"{active: newTodo}\" ng-click=\"toggleAdd()\"><i ng-class=\"{ \'fa fa-plus\': !newTodo, \'fa fa-times\': newTodo}\"></i> Add</button>\r\n\r\n        </div>\r\n    </header>\r\n    <!-- widget div-->\r\n    <div>\r\n        <div class=\"widget-body no-padding smart-form\">\r\n            <!-- content goes here -->\r\n            <div ng-show=\"newTodo\">\r\n                <h5 class=\"todo-group-title\"><i class=\"fa fa-plus-circle\"></i> New Todo</h5>\r\n\r\n                <form name=\"newTodoForm\" class=\"smart-form\">\r\n                    <fieldset>\r\n                        <section>\r\n                            <label class=\"input\">\r\n                                <input type=\"text\" required class=\"input-lg\" ng-model=\"newTodo.title\"\r\n                                       placeholder=\"What needs to be done?\">\r\n                            </label>\r\n                        </section>\r\n                        <section>\r\n                            <div class=\"col-xs-6\">\r\n                                <label class=\"select\">\r\n                                    <select class=\"input-sm\" ng-model=\"newTodo.state\"\r\n                                            ng-options=\"state as state for state in states\"></select> <i></i> </label>\r\n                            </div>\r\n                        </section>\r\n                    </fieldset>\r\n                    <footer>\r\n                        <button ng-disabled=\"newTodoForm.$invalid\" type=\"button\" class=\"btn btn-primary\"\r\n                                ng-click=\"createTodo()\">\r\n                            Add\r\n                        </button>\r\n                        <button type=\"button\" class=\"btn btn-default\" ng-click=\"toggleAdd()\">\r\n                            Cancel\r\n                        </button>\r\n                    </footer>\r\n                </form>\r\n            </div>\r\n\r\n            <todo-list state=\"Critical\"  title=\"Critical Tasks\" icon=\"warning\" todos=\"todos\"></todo-list>\r\n\r\n            <todo-list state=\"Important\" title=\"Important Tasks\" icon=\"exclamation\" todos=\"todos\"></todo-list>\r\n\r\n            <todo-list state=\"Completed\" title=\"Completed Tasks\" icon=\"check\" todos=\"todos\"></todo-list>\r\n\r\n            <!-- end content -->\r\n        </div>\r\n\r\n    </div>\r\n    <!-- end widget div -->\r\n</div>");
  $templateCache.put("app/layout/language/language-selector.tpl.html","<ul class=\"header-dropdown-list hidden-xs ng-cloak\" ng-controller=\"LanguagesCtrl\">\r\n    <li class=\"dropdown\" dropdown>\r\n        <a class=\"dropdown-toggle\"  data-toggle=\"dropdown\" href> <img src=\"styles/img/blank.gif\" class=\"flag flag-{{currentLanguage.key}}\" alt=\"{{currentLanguage.alt}}\"> <span> {{currentLanguage.title}} </span>\r\n            <i class=\"fa fa-angle-down\"></i> </a>\r\n        <ul class=\"dropdown-menu pull-right\">\r\n            <li ng-class=\"{active: language==currentLanguage}\" ng-repeat=\"language in languages\">\r\n                <a ng-click=\"selectLanguage(language)\" ><img src=\"styles/img/blank.gif\" class=\"flag flag-{{language.key}}\"\r\n                                                   alt=\"{{language.alt}}\"> {{language.title}}</a>\r\n            </li>\r\n        </ul>\r\n    </li>\r\n</ul>");
  $templateCache.put("app/layout/partials/footers.tpl.html","<div class=\"page-footer\">\r\n    <div class=\"row\">\r\n        <div class=\"col-xs-12 col-sm-6\">\r\n            <span class=\"txt-color-white\">SmartAdmin WebApp © 2016</span>\r\n        </div>\r\n\r\n        <div class=\"col-xs-6 col-sm-6 text-right hidden-xs\">\r\n            <div class=\"txt-color-white inline-block\">\r\n                <i class=\"txt-color-blueLight hidden-mobile\">Last account activity <i class=\"fa fa-clock-o\"></i>\r\n                    <strong>52 mins ago &nbsp;</strong> </i>\r\n\r\n                <div class=\"btn-group dropup\">\r\n                    <button class=\"btn btn-xs dropdown-toggle bg-color-blue txt-color-white\" data-toggle=\"dropdown\">\r\n                        <i class=\"fa fa-link\"></i> <span class=\"caret\"></span>\r\n                    </button>\r\n                    <ul class=\"dropdown-menu pull-right text-left\">\r\n                        <li>\r\n                            <div class=\"padding-5\">\r\n                                <p class=\"txt-color-darken font-sm no-margin\">Download Progress</p>\r\n\r\n                                <div class=\"progress progress-micro no-margin\">\r\n                                    <div class=\"progress-bar progress-bar-success\" style=\"width: 50%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                        </li>\r\n                        <li class=\"divider\"></li>\r\n                        <li>\r\n                            <div class=\"padding-5\">\r\n                                <p class=\"txt-color-darken font-sm no-margin\">Server Load</p>\r\n\r\n                                <div class=\"progress progress-micro no-margin\">\r\n                                    <div class=\"progress-bar progress-bar-success\" style=\"width: 20%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                        </li>\r\n                        <li class=\"divider\"></li>\r\n                        <li>\r\n                            <div class=\"padding-5\">\r\n                                <p class=\"txt-color-darken font-sm no-margin\">Memory Load <span class=\"text-danger\">*critical*</span>\r\n                                </p>\r\n\r\n                                <div class=\"progress progress-micro no-margin\">\r\n                                    <div class=\"progress-bar progress-bar-danger\" style=\"width: 70%;\"></div>\r\n                                </div>\r\n                            </div>\r\n                        </li>\r\n                        <li class=\"divider\"></li>\r\n                        <li>\r\n                            <div class=\"padding-5\">\r\n                                <button class=\"btn btn-block btn-default\">refresh</button>\r\n                            </div>\r\n                        </li>\r\n                    </ul>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</div>");
  $templateCache.put("app/layout/partials/header.tpl.html","<header id=\"header\">\r\n<div id=\"logo-group\">\r\n\r\n    <!-- PLACE YOUR LOGO HERE -->\r\n    <span id=\"logo\"> <img src=\"/css/smart/styles/img/logo.png\" alt=\"SmartAdmin\"> </span>\r\n    <!-- END LOGO PLACEHOLDER -->\r\n\r\n    <!-- Note: The activity badge color changes when clicked and resets the number to 0\r\n    Suggestion: You may want to set a flag when this happens to tick off all checked messages / notifications -->\r\n    <span id=\"activity\" class=\"activity-dropdown\" activities-dropdown-toggle> \r\n        <i class=\"fa fa-user\"></i> \r\n        <b class=\"badge bg-color-red\">21</b> \r\n    </span>\r\n    <div smart-include=\"app/dashboard/activities/activities.html\"></div>\r\n</div>\r\n\r\n\r\n<recent-projects></recent-projects>\r\n\r\n\r\n\r\n<!-- pulled right: nav area -->\r\n<div class=\"pull-right\">\r\n\r\n    <!-- collapse menu button -->\r\n    <div id=\"hide-menu\" class=\"btn-header pull-right\">\r\n        <span> <a toggle-menu title=\"Collapse Menu\"><i\r\n                class=\"fa fa-reorder\"></i></a> </span>\r\n    </div>\r\n    <!-- end collapse menu -->\r\n\r\n    <!-- #MOBILE -->\r\n    <!-- Top menu profile link : this shows only when top menu is active -->\r\n    <ul id=\"mobile-profile-img\" class=\"header-dropdown-list hidden-xs padding-5\">\r\n        <li class=\"\">\r\n            <a href=\"#\" class=\"dropdown-toggle no-margin userdropdown\" data-toggle=\"dropdown\">\r\n                <img src=\"styles/img/avatars/sunny.png\" alt=\"John Doe\" class=\"online\"/>\r\n            </a>\r\n            <ul class=\"dropdown-menu pull-right\">\r\n                <li>\r\n                    <a href-void class=\"padding-10 padding-top-0 padding-bottom-0\"><i\r\n                            class=\"fa fa-cog\"></i> Setting</a>\r\n                </li>\r\n                <li class=\"divider\"></li>\r\n                <li>\r\n                    <a ui-sref=\"app.appViews.profileDemo\" class=\"padding-10 padding-top-0 padding-bottom-0\"> <i class=\"fa fa-user\"></i>\r\n                        <u>P</u>rofile</a>\r\n                </li>\r\n                <li class=\"divider\"></li>\r\n                <li>\r\n                    <a href-void class=\"padding-10 padding-top-0 padding-bottom-0\"\r\n                       data-action=\"toggleShortcut\"><i class=\"fa fa-arrow-down\"></i> <u>S</u>hortcut</a>\r\n                </li>\r\n                <li class=\"divider\"></li>\r\n                <li>\r\n                    <a href-void class=\"padding-10 padding-top-0 padding-bottom-0\"\r\n                       data-action=\"launchFullscreen\"><i class=\"fa fa-arrows-alt\"></i> Full <u>S</u>creen</a>\r\n                </li>\r\n                <li class=\"divider\"></li>\r\n                <li>\r\n                    <a href=\"#/login\" class=\"padding-10 padding-top-5 padding-bottom-5\" data-action=\"userLogout\"><i\r\n                            class=\"fa fa-sign-out fa-lg\"></i> <strong><u>L</u>ogout</strong></a>\r\n                </li>\r\n            </ul>\r\n        </li>\r\n    </ul>\r\n\r\n    <!-- logout button -->\r\n    <div id=\"logout\" class=\"btn-header transparent pull-right\">\r\n        <span> <a ui-sref=\"login\" title=\"Sign Out\" data-action=\"userLogout\"\r\n                  data-logout-msg=\"You can improve your security further after logging out by closing this opened browser\"><i\r\n                class=\"fa fa-sign-out\"></i></a> </span>\r\n    </div>\r\n    <!-- end logout button -->\r\n\r\n    <!-- search mobile button (this is hidden till mobile view port) -->\r\n    <div id=\"search-mobile\" class=\"btn-header transparent pull-right\" data-search-mobile>\r\n        <span> <a href=\"#\" title=\"Search\"><i class=\"fa fa-search\"></i></a> </span>\r\n    </div>\r\n    <!-- end search mobile button -->\r\n\r\n    <!-- input: search field -->\r\n    <form action=\"#/search\" class=\"header-search pull-right\">\r\n        <input id=\"search-fld\" type=\"text\" name=\"param\" placeholder=\"Find reports and more\" data-autocomplete=\'[\r\n					\"ActionScript\",\r\n					\"AppleScript\",\r\n					\"Asp\",\r\n					\"BASIC\",\r\n					\"C\",\r\n					\"C++\",\r\n					\"Clojure\",\r\n					\"COBOL\",\r\n					\"ColdFusion\",\r\n					\"Erlang\",\r\n					\"Fortran\",\r\n					\"Groovy\",\r\n					\"Haskell\",\r\n					\"Java\",\r\n					\"JavaScript\",\r\n					\"Lisp\",\r\n					\"Perl\",\r\n					\"PHP\",\r\n					\"Python\",\r\n					\"Ruby\",\r\n					\"Scala\",\r\n					\"Scheme\"]\'>\r\n        <button type=\"submit\">\r\n            <i class=\"fa fa-search\"></i>\r\n        </button>\r\n        <a href=\"$\" id=\"cancel-search-js\" title=\"Cancel Search\"><i class=\"fa fa-times\"></i></a>\r\n    </form>\r\n    <!-- end input: search field -->\r\n\r\n    <!-- fullscreen button -->\r\n    <div id=\"fullscreen\" class=\"btn-header transparent pull-right\">\r\n        <span> <a full-screen title=\"Full Screen\"><i\r\n                class=\"fa fa-arrows-alt\"></i></a> </span>\r\n    </div>\r\n    <!-- end fullscreen button -->\r\n\r\n    <!-- #Voice Command: Start Speech -->\r\n    <div id=\"speech-btn\" class=\"btn-header transparent pull-right hidden-sm hidden-xs\">\r\n        <div>\r\n            <a title=\"Voice Command\" id=\"voice-command-btn\" speech-recognition><i class=\"fa fa-microphone\"></i></a>\r\n\r\n            <div class=\"popover bottom\">\r\n                <div class=\"arrow\"></div>\r\n                <div class=\"popover-content\">\r\n                    <h4 class=\"vc-title\">Voice command activated <br>\r\n                        <small>Please speak clearly into the mic</small>\r\n                    </h4>\r\n                    <h4 class=\"vc-title-error text-center\">\r\n                        <i class=\"fa fa-microphone-slash\"></i> Voice command failed\r\n                        <br>\r\n                        <small class=\"txt-color-red\">Must <strong>\"Allow\"</strong> Microphone</small>\r\n                        <br>\r\n                        <small class=\"txt-color-red\">Must have <strong>Internet Connection</strong></small>\r\n                    </h4>\r\n                    <a href-void class=\"btn btn-success\" id=\"speech-help-btn\">See Commands</a>\r\n                    <a href-void class=\"btn bg-color-purple txt-color-white\"\r\n                       onclick=\"$(\'#speech-btn .popover\').fadeOut(50);\">Close Popup</a>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </div>\r\n    <!-- end voice command -->\r\n\r\n\r\n\r\n    <!-- multiple lang dropdown : find all flags in the flags page -->\r\n    <language-selector></language-selector>\r\n    <!-- end multiple lang -->\r\n\r\n</div>\r\n<!-- end pulled right: nav area -->\r\n\r\n</header>");
  $templateCache.put("app/layout/partials/navigation.tpl.html","<aside id=\"left-panel\">\r\n\r\n    <!-- User info -->\r\n    <div login-info></div>\r\n    <!-- end user info -->\r\n\r\n    <nav>\r\n        <!-- NOTE: Notice the gaps after each icon usage <i></i>..\r\n        Please note that these links work a bit different than\r\n        traditional href=\"\" links. See documentation for details.\r\n        -->\r\n\r\n        <ul data-smart-menu>\r\n            <li data-menu-collapse>\r\n                <a href=\"#\" title=\"Dashboard\"><i class=\"fa fa-lg fa-fw fa-home\"></i> <span\r\n                        class=\"menu-item-parent\">{{getWord(\'Dashboard\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.dashboard\">{{getWord(\'Analytics Dashboard\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.dashboard-social\">{{getWord(\'Social Wall\')}}</a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse class=\"top-menu-invisible\">\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-cube txt-color-blue\"></i> <span class=\"menu-item-parent\">{{getWord(\'SmartAdmin Intel\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.smartAdmin.appLayouts\"><i class=\"fa fa-gear\"></i>\r\n                            {{getWord(\'App Layouts\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.smartAdmin.prebuiltSkins\"><i class=\"fa fa-picture-o\"></i>\r\n                            {{getWord(\'Prebuilt Skins\')}}</a>\r\n                    </li>\r\n                    <!--<li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.smartAdmin.appLayout\"><i class=\"fa fa-cube\"></i> {{getWord(\'App Settings\')}}</a>\r\n                    </li>-->\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-ui-sref-active=\"active\">\r\n                <a data-ui-sref=\"app.inbox.folder\" title=\"Outlook\">\r\n                    <i class=\"fa fa-lg fa-fw fa-inbox\"></i> <span class=\"menu-item-parent\">{{getWord(\'Outlook\')}}</span><span\r\n                        unread-messages-count class=\"badge pull-right inbox-badge\"></span></a>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-bar-chart-o\"></i> <span class=\"menu-item-parent\">{{getWord(\'Graphs\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.flot\">{{getWord(\'Flot Chart\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.morris\">{{getWord(\'Morris Charts\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.sparkline\">{{getWord(\'Sparkline\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.easyPieCharts\">{{getWord(\'Easy Pie Charts\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.dygraphs\">{{getWord(\'Dygraphs\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.chartjs\">Chart.js</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.graphs.highchartTables\">Highchart Tables <span\r\n                                class=\"badge pull-right inbox-badge bg-color-yellow\">new</span></a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-table\"></i> <span\r\n                        class=\"menu-item-parent\">{{getWord(\'Tables\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.tables.normal\">{{getWord(\'Normal Tables\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.tables.datatables\">{{getWord(\'Data Tables\')}} <span\r\n                                class=\"badge inbox-badge bg-color-greenLight\">v1.10</span></a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.tables.jqgrid\">{{getWord(\'Jquery Grid\')}}</a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-pencil-square-o\"></i> <span class=\"menu-item-parent\">{{getWord(\'Forms\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.elements\">{{getWord(\'Smart Form Elements\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.layouts\">{{getWord(\'Smart Form Layouts\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.validation\">{{getWord(\'Smart Form Validation\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.bootstrapForms\">{{getWord(\'Bootstrap Form Elements\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.bootstrapValidation\">{{getWord(\'Bootstrap Form Validation\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.plugins\">{{getWord(\'Form Plugins\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.wizards\">{{getWord(\'Wizards\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.editors\">{{getWord(\'Bootstrap Editors\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.dropzone\">{{getWord(\'Dropzone\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.form.imageEditor\">{{getWord(\'Image Cropping\')}} <span\r\n                                class=\"badge pull-right inbox-badge bg-color-yellow\">new</span></a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-desktop\"></i> <span class=\"menu-item-parent\">{{getWord(\'UI Elements\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.general\">{{getWord(\'General Elements\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.buttons\">{{getWord(\'Buttons\')}}</a>\r\n                    </li>\r\n                    <li data-menu-collapse>\r\n                        <a href=\"#\">{{getWord(\'Icons\')}}</a>\r\n                        <ul>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.ui.iconsFa\"><i class=\"fa fa-plane\"></i> {{getWord(\'Font Awesome\')}}</a>\r\n                            </li>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.ui.iconsGlyph\"><i class=\"glyphicon glyphicon-plane\"></i>\r\n                                    {{getWord(\'Glyph Icons\')}}</a>\r\n                            </li>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.ui.iconsFlags\"><i class=\"fa fa-flag\"></i> {{getWord(\'Flags\')}}</a>\r\n                            </li>\r\n                        </ul>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.grid\">{{getWord(\'Grid\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.treeView\">{{getWord(\'Tree View\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.nestableLists\">{{getWord(\'Nestable Lists\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.jqueryUi\">{{getWord(\'JQuery UI\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.ui.typography\">{{getWord(\'Typography\')}}</a>\r\n                    </li>\r\n                    <li data-menu-collapse>\r\n                        <a href=\"#\">{{getWord(\'Six Level Menu\')}}</a>\r\n                        <ul>\r\n                            <li data-menu-collapse>\r\n                                <a href=\"#\"><i class=\"fa fa-fw fa-folder-open\"></i> {{getWord(\'Item #2\')}}</a>\r\n                                <ul>\r\n                                    <li data-menu-collapse>\r\n                                        <a href=\"#\"><i class=\"fa fa-fw fa-folder-open\"></i> {{getWord(\'Sub #2.1\')}} </a>\r\n                                        <ul>\r\n                                            <li>\r\n                                                <a href=\"#\"><i class=\"fa fa-fw fa-file-text\"></i> {{getWord(\'Item\r\n                                                    #2.1.1\')}}</a>\r\n                                            </li>\r\n                                            <li data-menu-collapse>\r\n                                                <a href=\"#\"><i class=\"fa fa-fw fa-plus\"></i>{{getWord(\'Expand\')}}</a>\r\n                                                <ul>\r\n                                                    <li>\r\n                                                        <a href=\"#\"><i class=\"fa fa-fw fa-file-text\"></i>\r\n                                                            {{getWord(\'File\')}}</a>\r\n                                                    </li>\r\n                                                    <li>\r\n                                                        <a href=\"#\"><i class=\"fa fa-fw fa-trash-o\"></i>\r\n                                                            {{getWord(\'Delete\')}}</a></li>\r\n                                                </ul>\r\n                                            </li>\r\n                                        </ul>\r\n                                    </li>\r\n                                </ul>\r\n                            </li>\r\n                            <li data-menu-collapse>\r\n                                <a href=\"#\"><i class=\"fa fa-fw fa-folder-open\"></i> {{getWord(\'Item #3\')}}</a>\r\n\r\n                                <ul>\r\n                                    <li data-menu-collapse>\r\n                                        <a href=\"#\"><i class=\"fa fa-fw fa-folder-open\"></i> {{getWord(\'3ed Level\')}}\r\n                                        </a>\r\n                                        <ul>\r\n                                            <li>\r\n                                                <a href=\"#\"><i class=\"fa fa-fw fa-file-text\"></i>\r\n                                                    {{getWord(\'File\')}}</a>\r\n                                            </li>\r\n                                            <li>\r\n                                                <a href=\"#\"><i class=\"fa fa-fw fa-file-text\"></i>\r\n                                                    {{getWord(\'File\')}}</a>\r\n                                            </li>\r\n                                        </ul>\r\n                                    </li>\r\n                                </ul>\r\n\r\n                            </li>\r\n                        </ul>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n\r\n            <li data-ui-sref-active=\"active\">\r\n                <a data-ui-sref=\"app.widgets\" title=\"Widgets\"><i class=\"fa fa-lg fa-fw fa-list-alt\"></i><span\r\n                        class=\"menu-item-parent\">{{getWord(\'Widgets\')}}</span></a>\r\n            </li>\r\n\r\n\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\">\r\n                    <i class=\"fa fa-lg fa-fw fa-cloud\"><em>3</em></i> <span class=\"menu-item-parent\">{{getWord(\'Cool Features\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.calendar\" title=\"Calendar\"><i\r\n                                class=\"fa fa-lg fa-fw fa-calendar\"></i> <span\r\n                                class=\"menu-item-parent\">{{getWord(\'Calendar\')}}</span></a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.maps\"><i class=\"fa fa-lg fa-fw fa-map-marker\"></i> <span class=\"menu-item-parent\">{{getWord(\'GMap Skins\')}}</span><span\r\n                                class=\"badge bg-color-greenLight pull-right inbox-badge\">9</span></a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\">\r\n                    <i class=\"fa fa-lg fa-fw fa-puzzle-piece\"></i> <span class=\"menu-item-parent\">{{getWord(\'App Views\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.appViews.projects\"><i class=\"fa fa-file-text-o\"></i>\r\n                            {{getWord(\'Projects\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.appViews.blogDemo\"><i class=\"fa fa-paragraph\"></i> {{getWord(\'Blog\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.appViews.galleryDemo\"><i class=\"fa fa-picture-o\"></i>\r\n                            {{getWord(\'Gallery\')}}</a>\r\n                    </li>\r\n\r\n                    <li data-menu-collapse>\r\n                        <a href=\"#\"><i class=\"fa fa-comments\"></i> {{getWord(\'Forum Layout\')}}</a>\r\n                        <ul>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.appViews.forumDemo\"><i class=\"fa fa-picture-o\"></i>\r\n                                    {{getWord(\'General View\')}}</a>\r\n                            </li>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.appViews.forumTopicDemo\"><i class=\"fa fa-picture-o\"></i>\r\n                                    {{getWord(\'Topic View\')}}</a>\r\n                            </li>\r\n                            <li data-ui-sref-active=\"active\">\r\n                                <a data-ui-sref=\"app.appViews.forumPostDemo\"><i class=\"fa fa-picture-o\"></i>\r\n                                    {{getWord(\'Post View\')}}</a>\r\n                            </li>\r\n                        </ul>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.appViews.profileDemo\"><i class=\"fa fa-group\"></i>\r\n                            {{getWord(\'Profile\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.appViews.timelineDemo\"><i class=\"fa fa-clock-o\"></i>\r\n                            {{getWord(\'Timeline\')}}</a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\">\r\n                    <i class=\"fa fa-lg fa-fw fa-shopping-cart\"></i> <span class=\"menu-item-parent\">{{getWord(\'E-Commerce\')}}</span></a>\r\n                <ul>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.eCommerce.orders\" title=\"Orders\"> {{getWord(\'Orders\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.eCommerce.products\" title=\"Products View\"> {{getWord(\'Products View\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.eCommerce.detail\" title=\"Products Detail\"> {{getWord(\'Products Detail\')}}</a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse>\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-windows\"></i> <span class=\"menu-item-parent\">{{getWord(\'Miscellaneous\')}}</span></a>\r\n                <ul>\r\n                    <li>\r\n                        <a href=\"http://bootstraphunter.com/smartadmin-landing/\" target=\"_blank\">{{getWord(\'Landing\r\n                            Page\')}} <i class=\"fa fa-external-link\"></i></a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.pricingTable\">{{getWord(\'Pricing Tables\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.invoice\">{{getWord(\'Invoice\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"login\">{{getWord(\'Login\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"register\">{{getWord(\'Register\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"lock\">{{getWord(\'Locked Screen\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.error404\">{{getWord(\'Error 404\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.error500\">{{getWord(\'Error 500\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.blank\">{{getWord(\'Blank Page\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.emailTemplate\">{{getWord(\'Email Template\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.search\">{{getWord(\'Search Page\')}}</a>\r\n                    </li>\r\n                    <li data-ui-sref-active=\"active\">\r\n                        <a data-ui-sref=\"app.misc.ckeditor\">{{getWord(\'CK Editor\')}}</a>\r\n                    </li>\r\n                </ul>\r\n            </li>\r\n\r\n            <li data-menu-collapse class=\"chat-users top-menu-invisible\">\r\n                <a href=\"#\"><i class=\"fa fa-lg fa-fw fa-comment-o\"><em class=\"bg-color-pink flash animated\">!</em></i>\r\n                    <span class=\"menu-item-parent\">{{getWord(\'Smart Chat API\')}} <sup>{{getWord(\'beta\')}}</sup></span></a>\r\n                <div aside-chat-widget></div>\r\n            </li>\r\n        </ul>\r\n\r\n        <!-- NOTE: This allows you to pull menu items from server -->\r\n        <!-- <ul data-smart-menu-items=\"/api/menu-items.json\"></ul> -->\r\n    </nav>\r\n\r\n  <span class=\"minifyme\" data-action=\"minifyMenu\" minify-menu>\r\n    <i class=\"fa fa-arrow-circle-left hit\"></i>\r\n  </span>\r\n\r\n</aside>");
  $templateCache.put("app/layout/partials/sub-header.tpl.html","<div class=\"col-xs-12 col-sm-5 col-md-5 col-lg-8\" data-sparkline-container>\r\n    <ul id=\"sparks\" class=\"\">\r\n        <li class=\"sparks-info\">\r\n            <h5> My Income <span class=\"txt-color-blue\">$47,171</span></h5>\r\n            <div class=\"sparkline txt-color-blue hidden-mobile hidden-md hidden-sm\">\r\n                1300, 1877, 2500, 2577, 2000, 2100, 3000, 2700, 3631, 2471, 2700, 3631, 2471\r\n            </div>\r\n        </li>\r\n        <li class=\"sparks-info\">\r\n            <h5> Site Traffic <span class=\"txt-color-purple\"><i class=\"fa fa-arrow-circle-up\"></i>&nbsp;45%</span></h5>\r\n            <div class=\"sparkline txt-color-purple hidden-mobile hidden-md hidden-sm\">\r\n                110,150,300,130,400,240,220,310,220,300, 270, 210\r\n            </div>\r\n        </li>\r\n        <li class=\"sparks-info\">\r\n            <h5> Site Orders <span class=\"txt-color-greenDark\"><i class=\"fa fa-shopping-cart\"></i>&nbsp;2447</span></h5>\r\n            <div class=\"sparkline txt-color-greenDark hidden-mobile hidden-md hidden-sm\">\r\n                110,150,300,130,400,240,220,310,220,300, 270, 210\r\n            </div>\r\n        </li>\r\n    </ul>\r\n</div>\r\n			");
  $templateCache.put("app/layout/partials/voice-commands.tpl.html","<!-- TRIGGER BUTTON:\r\n<a href=\"/my-ajax-page.html\" data-toggle=\"modal\" data-target=\"#remoteModal\" class=\"btn btn-default\">Open Modal</a>  -->\r\n\r\n<!-- MODAL PLACE HOLDER\r\n<div class=\"modal fade\" id=\"remoteModal\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"remoteModalLabel\" aria-hidden=\"true\">\r\n<div class=\"modal-dialog\">\r\n<div class=\"modal-content\"></div>\r\n</div>\r\n</div>   -->\r\n<!--////////////////////////////////////-->\r\n\r\n<!--<div class=\"modal-header\">\r\n<button type=\"button\" class=\"close\" data-dismiss=\"modal\" aria-hidden=\"true\">\r\n&times;\r\n</button>\r\n<h4 class=\"modal-title\" id=\"myModalLabel\">Command List</h4>\r\n</div>-->\r\n<div class=\"modal-body\">\r\n\r\n	<h1><i class=\"fa fa-microphone text-muted\"></i>&nbsp;&nbsp; SmartAdmin Voice Command</h1>\r\n	<hr class=\"simple\">\r\n	<h5>Instruction</h5>\r\n\r\n	Click <span class=\"text-success\">\"Allow\"</span> to access your microphone and activate Voice Command.\r\n	You will notice a <span class=\"text-primary\"><strong>BLUE</strong> Flash</span> on the microphone icon indicating activation.\r\n	The icon will appear <span class=\"text-danger\"><strong>RED</strong></span> <span class=\"label label-danger\"><i class=\"fa fa-microphone fa-lg\"></i></span> if you <span class=\"text-danger\">\"Deny\"</span> access or don\'t have any microphone installed.\r\n	<br>\r\n	<br>\r\n	As a security precaution, your browser will disconnect the microphone every 60 to 120 seconds (sooner if not being used). In which case Voice Command will prompt you again to <span class=\"text-success\">\"Allow\"</span> or <span class=\"text-danger\">\"Deny\"</span> access to your microphone.\r\n	<br>\r\n	<br>\r\n	If you host your page over <strong>http<span class=\"text-success\">s</span></strong> (secure socket layer) protocol you can wave this security measure and have an unintrupted Voice Command.\r\n	<br>\r\n	<br>\r\n	<h5>Commands</h5>\r\n	<ul>\r\n		<li>\r\n			<strong>\'show\' </strong> then say the <strong>*page*</strong> you want to go to. For example <strong>\"show inbox\"</strong> or <strong>\"show calendar\"</strong>\r\n		</li>\r\n		<li>\r\n			<strong>\'mute\' </strong> - mutes all sound effects for the theme.\r\n		</li>\r\n		<li>\r\n			<strong>\'sound on\'</strong> - unmutes all sound effects for the theme.\r\n		</li>\r\n		<li>\r\n			<span class=\"text-danger\"><strong>\'stop\'</strong></span> - deactivates voice command.\r\n		</li>\r\n		<li>\r\n			<span class=\"text-primary\"><strong>\'help\'</strong></span> - brings up the command list\r\n		</li>\r\n		<li>\r\n			<span class=\"text-danger\"><strong>\'got it\'</strong></span> - closes help modal\r\n		</li>\r\n		<li>\r\n			<strong>\'hide navigation\'</strong> - toggle navigation collapse\r\n		</li>\r\n		<li>\r\n			<strong>\'show navigation\'</strong> - toggle navigation to open (can be used again to close)\r\n		</li>\r\n		<li>\r\n			<strong>\'scroll up\'</strong> - scrolls to the top of the page\r\n		</li>\r\n		<li>\r\n			<strong>\'scroll down\'</strong> - scrollts to the bottom of the page\r\n		</li>\r\n		<li>\r\n			<strong>\'go back\' </strong> - goes back in history (history -1 click)\r\n		</li>\r\n		<li>\r\n			<strong>\'logout\'</strong> - logs you out\r\n		</li>\r\n	</ul>\r\n	<br>\r\n	<h5>Adding your own commands</h5>\r\n	Voice Command supports up to 80 languages. Adding your own commands is extreamly easy. All commands are stored inside <strong>app.config.js</strong> file under the <code>var commands = {...}</code>. \r\n\r\n	<hr class=\"simple\">\r\n	<div class=\"text-right\">\r\n		<button type=\"button\" class=\"btn btn-success btn-lg\" data-dismiss=\"modal\">\r\n			Got it!\r\n		</button>\r\n	</div>\r\n\r\n</div>\r\n<!--<div class=\"modal-footer\">\r\n<button type=\"button\" class=\"btn btn-primary\" data-dismiss=\"modal\">Got it!</button>\r\n</div> -->");
  $templateCache.put("app/layout/shortcut/shortcut.tpl.html","<div id=\"shortcut\">\r\n	<ul>\r\n		<li>\r\n			<a href=\"#/inbox/\" class=\"jarvismetro-tile big-cubes bg-color-blue\"> <span class=\"iconbox\"> <i class=\"fa fa-envelope fa-4x\"></i> <span>Mail <span class=\"label pull-right bg-color-darken\">14</span></span> </span> </a>\r\n		</li>\r\n		<li>\r\n			<a href=\"#/calendar\" class=\"jarvismetro-tile big-cubes bg-color-orangeDark\"> <span class=\"iconbox\"> <i class=\"fa fa-calendar fa-4x\"></i> <span>Calendar</span> </span> </a>\r\n		</li>\r\n		<li>\r\n			<a href=\"#/maps\" class=\"jarvismetro-tile big-cubes bg-color-purple\"> <span class=\"iconbox\"> <i class=\"fa fa-map-marker fa-4x\"></i> <span>Maps</span> </span> </a>\r\n		</li>\r\n		<li>\r\n			<a href=\"#/invoice\" class=\"jarvismetro-tile big-cubes bg-color-blueDark\"> <span class=\"iconbox\"> <i class=\"fa fa-book fa-4x\"></i> <span>Invoice <span class=\"label pull-right bg-color-darken\">99</span></span> </span> </a>\r\n		</li>\r\n		<li>\r\n			<a href=\"#/gallery\" class=\"jarvismetro-tile big-cubes bg-color-greenLight\"> <span class=\"iconbox\"> <i class=\"fa fa-picture-o fa-4x\"></i> <span>Gallery </span> </span> </a>\r\n		</li>\r\n		<li>\r\n			<a href=\"#/profile\" class=\"jarvismetro-tile big-cubes selected bg-color-pinkDark\"> <span class=\"iconbox\"> <i class=\"fa fa-user fa-4x\"></i> <span>My Profile </span> </span> </a>\r\n		</li>\r\n	</ul>\r\n</div>");
  $templateCache.put("app/dashboard/chat/directives/aside-chat-widget.tpl.html","<ul>\r\n    <li>\r\n        <div class=\"display-users\">\r\n            <input class=\"form-control chat-user-filter\" placeholder=\"Filter\" type=\"text\">\r\n            <dl>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr\"\r\n                       data-chat-id=\"cha1\"\r\n                       data-chat-fname=\"Sadi\"\r\n                       data-chat-lname=\"Orlaf\"\r\n                       data-chat-status=\"busy\"\r\n                       data-chat-alertmsg=\"Sadi Orlaf is in a meeting. Please do not disturb!\"\r\n                       data-chat-alertshow=\"true\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/5.png\' alt=\'Sadi Orlaf\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Sadi Orlaf</h3>\r\n												<p>Marketing Executive</p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Sadi Orlaf\r\n                    </a>\r\n                </dt>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr\"\r\n                       data-chat-id=\"cha2\"\r\n                       data-chat-fname=\"Jessica\"\r\n                       data-chat-lname=\"Dolof\"\r\n                       data-chat-status=\"online\"\r\n                       data-chat-alertmsg=\"\"\r\n                       data-chat-alertshow=\"false\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/1.png\' alt=\'Jessica Dolof\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Jessica Dolof</h3>\r\n												<p>Sales Administrator</p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Jessica Dolof\r\n                    </a>\r\n                </dt>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr\"\r\n                       data-chat-id=\"cha3\"\r\n                       data-chat-fname=\"Zekarburg\"\r\n                       data-chat-lname=\"Almandalie\"\r\n                       data-chat-status=\"online\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/3.png\' alt=\'Zekarburg Almandalie\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Zekarburg Almandalie</h3>\r\n												<p>Sales Admin</p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Zekarburg Almandalie\r\n                    </a>\r\n                </dt>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr\"\r\n                       data-chat-id=\"cha4\"\r\n                       data-chat-fname=\"Barley\"\r\n                       data-chat-lname=\"Krazurkth\"\r\n                       data-chat-status=\"away\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/4.png\' alt=\'Barley Krazurkth\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Barley Krazurkth</h3>\r\n												<p>Sales Director</p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Barley Krazurkth\r\n                    </a>\r\n                </dt>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr offline\"\r\n                       data-chat-id=\"cha5\"\r\n                       data-chat-fname=\"Farhana\"\r\n                       data-chat-lname=\"Amrin\"\r\n                       data-chat-status=\"incognito\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/female.png\' alt=\'Farhana Amrin\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Farhana Amrin</h3>\r\n												<p>Support Admin <small><i class=\'fa fa-music\'></i> Playing Beethoven Classics</small></p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Farhana Amrin (offline)\r\n                    </a>\r\n                </dt>\r\n                <dt>\r\n                    <a href=\"#\" class=\"usr offline\"\r\n                       data-chat-id=\"cha6\"\r\n                       data-chat-fname=\"Lezley\"\r\n                       data-chat-lname=\"Jacob\"\r\n                       data-chat-status=\"incognito\"\r\n                       popover-trigger=\"hover\"\r\n                       popover-placement=\"right\"\r\n                       smart-popover-html=\"\r\n										<div class=\'usr-card\'>\r\n											<img src=\'styles/img/avatars/male.png\' alt=\'Lezley Jacob\'>\r\n											<div class=\'usr-card-content\'>\r\n												<h3>Lezley Jacob</h3>\r\n												<p>Sales Director</p>\r\n											</div>\r\n										</div>\r\n									\">\r\n                        <i></i>Lezley Jacob (offline)\r\n                    </a>\r\n                </dt>\r\n            </dl>\r\n\r\n\r\n            <!--<a href=\"chat.html\" class=\"btn btn-xs btn-default btn-block sa-chat-learnmore-btn\">About the API</a>-->\r\n        </div>\r\n    </li>\r\n</ul>");
  $templateCache.put("app/dashboard/chat/directives/chat-users.tpl.html","<div id=\"chat-container\" ng-class=\"{open: open}\">\r\n    <span class=\"chat-list-open-close\" ng-click=\"openToggle()\"><i class=\"fa fa-user\"></i><b>!</b></span>\r\n\r\n    <div class=\"chat-list-body custom-scroll\">\r\n        <ul id=\"chat-users\">\r\n            <li ng-repeat=\"chatUser in chatUsers | filter: chatUserFilter\">\r\n                <a ng-click=\"messageTo(chatUser)\"><img ng-src=\"{{chatUser.picture}}\">{{chatUser.username}} <span\r\n                        class=\"badge badge-inverse\">{{chatUser.username.length}}</span><span class=\"state\"><i\r\n                        class=\"fa fa-circle txt-color-green pull-right\"></i></span></a>\r\n            </li>\r\n        </ul>\r\n    </div>\r\n    <div class=\"chat-list-footer\">\r\n        <div class=\"control-group\">\r\n            <form class=\"smart-form\">\r\n                <section>\r\n                    <label class=\"input\" >\r\n                        <input type=\"text\" ng-model=\"chatUserFilter\" id=\"filter-chat-list\" placeholder=\"Filter\">\r\n                    </label>\r\n                </section>\r\n            </form>\r\n        </div>\r\n    </div>\r\n</div>");
  $templateCache.put("app/dashboard/chat/directives/chat-widget.tpl.html","<div id=\"chat-widget\" jarvis-widget data-widget-color=\"blueDark\" data-widget-editbutton=\"false\"\r\n     data-widget-fullscreenbutton=\"false\">\r\n\r\n\r\n    <header>\r\n        <span class=\"widget-icon\"> <i class=\"fa fa-comments txt-color-white\"></i> </span>\r\n\r\n        <h2> SmartMessage </h2>\r\n\r\n        <div class=\"widget-toolbar\">\r\n            <!-- add: non-hidden - to disable auto hide -->\r\n\r\n            <div class=\"btn-group\" data-dropdown>\r\n                <button class=\"btn dropdown-toggle btn-xs btn-success\" data-toggle=\"dropdown\">\r\n                    Status <i class=\"fa fa-caret-down\"></i>\r\n                </button>\r\n                <ul class=\"dropdown-menu pull-right js-status-update\">\r\n                    <li>\r\n                        <a href-void><i class=\"fa fa-circle txt-color-green\"></i> Online</a>\r\n                    </li>\r\n                    <li>\r\n                        <a href-void><i class=\"fa fa-circle txt-color-red\"></i> Busy</a>\r\n                    </li>\r\n                    <li>\r\n                        <a href-void><i class=\"fa fa-circle txt-color-orange\"></i> Away</a>\r\n                    </li>\r\n                    <li class=\"divider\"></li>\r\n                    <li>\r\n                        <a href-void><i class=\"fa fa-power-off\"></i> Log Off</a>\r\n                    </li>\r\n                </ul>\r\n            </div>\r\n        </div>\r\n    </header>\r\n\r\n    <!-- widget div-->\r\n    <div>\r\n        <div class=\"widget-body widget-hide-overflow no-padding\">\r\n            <!-- content goes here -->\r\n\r\n            <chat-users></chat-users>\r\n\r\n            <!-- CHAT BODY -->\r\n            <div id=\"chat-body\" class=\"chat-body custom-scroll\">\r\n                <ul>\r\n                    <li class=\"message\" ng-repeat=\"message in chatMessages\">\r\n                        <img class=\"message-picture online\" ng-src=\"{{message.user.picture}}\">\r\n\r\n                        <div class=\"message-text\">\r\n                            <time>\r\n                                {{message.date | date }}\r\n                            </time>\r\n                            <a ng-click=\"messageTo(message.user)\" class=\"username\">{{message.user.username}}</a>\r\n                            <div ng-bind-html=\"message.body\"></div>\r\n\r\n                        </div>\r\n                    </li>\r\n                </ul>\r\n            </div>\r\n\r\n            <!-- CHAT FOOTER -->\r\n            <div class=\"chat-footer\">\r\n\r\n                <!-- CHAT TEXTAREA -->\r\n                <div class=\"textarea-div\">\r\n\r\n                    <div class=\"typearea\">\r\n                        <textarea placeholder=\"Write a reply...\" id=\"textarea-expand\"\r\n                                  class=\"custom-scroll\" ng-model=\"newMessage\"></textarea>\r\n                    </div>\r\n\r\n                </div>\r\n\r\n                <!-- CHAT REPLY/SEND -->\r\n											<span class=\"textarea-controls\">\r\n												<button class=\"btn btn-sm btn-primary pull-right\" ng-click=\"sendMessage()\">\r\n                                                    Reply\r\n                                                </button> <span class=\"pull-right smart-form\"\r\n                                                                style=\"margin-top: 3px; margin-right: 10px;\"> <label\r\n                                                    class=\"checkbox pull-right\">\r\n                                                <input type=\"checkbox\" name=\"subscription\" id=\"subscription\">\r\n                                                <i></i>Press <strong> ENTER </strong> to send </label> </span> <a\r\n                                                    href-void class=\"pull-left\"><i\r\n                                                    class=\"fa fa-camera fa-fw fa-lg\"></i></a> </span>\r\n\r\n            </div>\r\n\r\n            <!-- end content -->\r\n        </div>\r\n\r\n    </div>\r\n    <!-- end widget div -->\r\n</div>");
  $templateCache.put("app/dashboard/todo/directives/todo-list.tpl.html","<div>\r\n    <h5 class=\"todo-group-title\"><i class=\"fa fa-{{icon}}\"></i> {{title}} (\r\n        <small class=\"num-of-tasks\">{{scopeItems.length}}</small>\r\n        )\r\n    </h5>\r\n    <ul class=\"todo\">\r\n        <li ng-class=\"{complete: todo.completedAt}\" ng-repeat=\"todo in todos | orderBy: todo._id | filter: filter  track by todo._id\" >\r\n    	<span class=\"handle\"> <label class=\"checkbox\">\r\n            <input type=\"checkbox\" ng-click=\"todo.toggle()\" ng-checked=\"todo.completedAt\"\r\n                   name=\"checkbox-inline\">\r\n            <i></i> </label> </span>\r\n\r\n            <p>\r\n                <strong>Ticket #{{$index + 1}}</strong> - {{todo.title}}\r\n                <span class=\"text-muted\" ng-if=\"todo.description\">{{todo.description}}</span>\r\n                <span class=\"date\">{{todo.createdAt | date}} &dash; <a ng-click=\"deleteTodo(todo)\" class=\"text-muted\"><i\r\n                        class=\"fa fa-trash\"></i></a></span>\r\n\r\n            </p>\r\n        </li>\r\n    </ul>\r\n</div>");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-attribute-form.tpl.html","<form id=\"attributeForm\" class=\"form-horizontal\"\r\n      data-bv-message=\"This value is not valid\"\r\n      data-bv-feedbackicons-valid=\"glyphicon glyphicon-ok\"\r\n      data-bv-feedbackicons-invalid=\"glyphicon glyphicon-remove\"\r\n      data-bv-feedbackicons-validating=\"glyphicon glyphicon-refresh\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Set validator options via HTML attributes\r\n        </legend>\r\n\r\n        <div class=\"alert alert-warning\">\r\n            <code>&lt; input\r\n                data-bv-validatorname\r\n                data-bv-validatorname-validatoroption=\"...\" / &gt;</code>\r\n\r\n            <br>\r\n            <br>\r\n            More validator options can be found here:\r\n            <a href=\"http://bootstrapvalidator.com/validators/\" target=\"_blank\">http://bootstrapvalidator.com/validators/</a>\r\n        </div>\r\n\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Full name</label>\r\n            <div class=\"col-lg-4\">\r\n                <input type=\"text\" class=\"form-control\" name=\"firstName\" placeholder=\"First name\"\r\n                       data-bv-notempty=\"true\"\r\n                       data-bv-notempty-message=\"The first name is required and cannot be empty\" />\r\n            </div>\r\n            <div class=\"col-lg-4\">\r\n                <input type=\"text\" class=\"form-control\" name=\"lastName\" placeholder=\"Last name\"\r\n                       data-bv-notempty=\"true\"\r\n                       data-bv-notempty-message=\"The last name is required and cannot be empty\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Username</label>\r\n            <div class=\"col-lg-5\">\r\n                <input type=\"text\" class=\"form-control\" name=\"username\"\r\n                       data-bv-message=\"The username is not valid\"\r\n\r\n                       data-bv-notempty=\"true\"\r\n                       data-bv-notempty-message=\"The username is required and cannot be empty\"\r\n\r\n                       data-bv-regexp=\"true\"\r\n                       data-bv-regexp-regexp=\"^[a-zA-Z0-9_\\.]+$\"\r\n                       data-bv-regexp-message=\"The username can only consist of alphabetical, number, dot and underscore\"\r\n\r\n                       data-bv-stringlength=\"true\"\r\n                       data-bv-stringlength-min=\"6\"\r\n                       data-bv-stringlength-max=\"30\"\r\n                       data-bv-stringlength-message=\"The username must be more than 6 and less than 30 characters long\"\r\n\r\n                       data-bv-different=\"true\"\r\n                       data-bv-different-field=\"password\"\r\n                       data-bv-different-message=\"The username and password cannot be the same as each other\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Email address</label>\r\n            <div class=\"col-lg-5\">\r\n                <input class=\"form-control\" name=\"email\" type=\"email\"\r\n                       data-bv-emailaddress=\"true\"\r\n                       data-bv-emailaddress-message=\"The input is not a valid email address\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Password</label>\r\n            <div class=\"col-lg-5\">\r\n                <input type=\"password\" class=\"form-control\" name=\"password\"\r\n                       data-bv-notempty=\"true\"\r\n                       data-bv-notempty-message=\"The password is required and cannot be empty\"\r\n\r\n                       data-bv-identical=\"true\"\r\n                       data-bv-identical-field=\"confirmPassword\"\r\n                       data-bv-identical-message=\"The password and its confirm are not the same\"\r\n\r\n                       data-bv-different=\"true\"\r\n                       data-bv-different-field=\"username\"\r\n                       data-bv-different-message=\"The password cannot be the same as username\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Retype password</label>\r\n            <div class=\"col-lg-5\">\r\n                <input type=\"password\" class=\"form-control\" name=\"confirmPassword\"\r\n                       data-bv-notempty=\"true\"\r\n                       data-bv-notempty-message=\"The confirm password is required and cannot be empty\"\r\n\r\n                       data-bv-identical=\"true\"\r\n                       data-bv-identical-field=\"password\"\r\n                       data-bv-identical-message=\"The password and its confirm are not the same\"\r\n\r\n                       data-bv-different=\"true\"\r\n                       data-bv-different-field=\"username\"\r\n                       data-bv-different-message=\"The password cannot be the same as username\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Languages</label>\r\n            <div class=\"col-lg-5\">\r\n                <div class=\"checkbox\">\r\n                    <label>\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"english\"\r\n                               data-bv-message=\"Please specify at least one language you can speak\"\r\n                               data-bv-notempty=\"true\" />\r\n                        English </label>\r\n                </div>\r\n                <div class=\"checkbox\">\r\n                    <label>\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"french\" />\r\n                        French </label>\r\n                </div>\r\n                <div class=\"checkbox\">\r\n                    <label>\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"german\" />\r\n                        German </label>\r\n                </div>\r\n                <div class=\"checkbox\">\r\n                    <label>\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"russian\" />\r\n                        Russian </label>\r\n                </div>\r\n                <div class=\"checkbox\">\r\n                    <label>\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"other\" />\r\n                        Other </label>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n</form>\r\n     ");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-button-group-form.tpl.html","<form id=\"buttonGroupForm\" method=\"post\" class=\"form-horizontal\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Default Form Elements\r\n        </legend>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Gender</label>\r\n            <div class=\"col-lg-9\">\r\n                <div class=\"btn-group\" data-toggle=\"buttons\">\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"radio\" name=\"gender\" value=\"male\" />\r\n                        Male </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"radio\" name=\"gender\" value=\"female\" />\r\n                        Female </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"radio\" name=\"gender\" value=\"other\" />\r\n                        Other </label>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Languages</label>\r\n            <div class=\"col-lg-9\">\r\n                <div class=\"btn-group\" data-toggle=\"buttons\">\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"english\" />\r\n                        English </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"german\" />\r\n                        German </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"french\" />\r\n                        French </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"russian\" />\r\n                        Russian </label>\r\n                    <label class=\"btn btn-default\">\r\n                        <input type=\"checkbox\" name=\"languages[]\" value=\"italian\">\r\n                        Italian </label>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n</form>\r\n");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-contact-form.tpl.html","<form id=\"contactForm\" method=\"post\" class=\"form-horizontal\">\r\n\r\n    <fieldset>\r\n        <legend>Showing messages in custom area</legend>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-md-3 control-label\">Full name</label>\r\n            <div class=\"col-md-6\">\r\n                <input type=\"text\" class=\"form-control\" name=\"fullName\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-md-3 control-label\">Email</label>\r\n            <div class=\"col-md-6\">\r\n                <input type=\"text\" class=\"form-control\" name=\"email\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-md-3 control-label\">Title</label>\r\n            <div class=\"col-md-6\">\r\n                <input type=\"text\" class=\"form-control\" name=\"title\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-md-3 control-label\">Content</label>\r\n            <div class=\"col-md-6\">\r\n                <textarea class=\"form-control\" name=\"content\" rows=\"5\"></textarea>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <!-- #messages is where the messages are placed inside -->\r\n        <div class=\"form-group\">\r\n            <div class=\"col-md-9 col-md-offset-3\">\r\n                <div id=\"messages\"></div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n</form>\r\n");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-movie-form.tpl.html","\r\n<form id=\"movieForm\" method=\"post\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Default Form Elements\r\n        </legend>\r\n        <div class=\"form-group\">\r\n            <div class=\"row\">\r\n                <div class=\"col-md-8\">\r\n                    <label class=\"control-label\">Movie title</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"title\" />\r\n                </div>\r\n\r\n                <div class=\"col-md-4 selectContainer\">\r\n                    <label class=\"control-label\">Genre</label>\r\n                    <select class=\"form-control\" name=\"genre\">\r\n                        <option value=\"\">Choose a genre</option>\r\n                        <option value=\"action\">Action</option>\r\n                        <option value=\"comedy\">Comedy</option>\r\n                        <option value=\"horror\">Horror</option>\r\n                        <option value=\"romance\">Romance</option>\r\n                    </select>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <div class=\"row\">\r\n                <div class=\"col-sm-12 col-md-4\">\r\n                    <label class=\"control-label\">Director</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"director\" />\r\n                </div>\r\n\r\n                <div class=\"col-sm-12 col-md-4\">\r\n                    <label class=\"control-label\">Writer</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"writer\" />\r\n                </div>\r\n\r\n                <div class=\"col-sm-12 col-md-4\">\r\n                    <label class=\"control-label\">Producer</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"producer\" />\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <div class=\"row\">\r\n                <div class=\"col-sm-12 col-md-6\">\r\n                    <label class=\"control-label\">Website</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"website\" />\r\n                </div>\r\n\r\n                <div class=\"col-sm-12 col-md-6\">\r\n                    <label class=\"control-label\">Youtube trailer</label>\r\n                    <input type=\"text\" class=\"form-control\" name=\"trailer\" />\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"control-label\">Review</label>\r\n            <textarea class=\"form-control\" name=\"review\" rows=\"8\"></textarea>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n\r\n            <div class=\"row\">\r\n                <div class=\"col-sm-12 col-md-12\">\r\n                    <label class=\"control-label\">Rating</label>\r\n                </div>\r\n\r\n                <div class=\"col-sm-12 col-md-10\">\r\n\r\n                    <label class=\"radio radio-inline no-margin\">\r\n                        <input type=\"radio\" name=\"rating\" value=\"terrible\" class=\"radiobox style-2\" />\r\n                        <span>Terrible</span> </label>\r\n\r\n                    <label class=\"radio radio-inline\">\r\n                        <input type=\"radio\" name=\"rating\" value=\"watchable\" class=\"radiobox style-2\" />\r\n                        <span>Watchable</span> </label>\r\n                    <label class=\"radio radio-inline\">\r\n                        <input type=\"radio\" name=\"rating\" value=\"best\" class=\"radiobox style-2\" />\r\n                        <span>Best ever</span> </label>\r\n\r\n                </div>\r\n\r\n            </div>\r\n\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n\r\n</form>\r\n\r\n ");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-product-form.tpl.html","<form id=\"productForm\" class=\"form-horizontal\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Default Form Elements\r\n        </legend>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-xs-2 col-lg-3 control-label\">Price</label>\r\n            <div class=\"col-xs-9 col-lg-6 inputGroupContainer\">\r\n                <div class=\"input-group\">\r\n                    <input type=\"text\" class=\"form-control\" name=\"price\" />\r\n                    <span class=\"input-group-addon\">$</span>\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-xs-2 col-lg-3 control-label\">Amount</label>\r\n            <div class=\"col-xs-9 col-lg-6 inputGroupContainer\">\r\n                <div class=\"input-group\">\r\n                    <span class=\"input-group-addon\">&#8364;</span>\r\n                    <input type=\"text\" class=\"form-control\" name=\"amount\" />\r\n                </div>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-xs-2 col-lg-3 control-label\">Color</label>\r\n            <div class=\"col-xs-9 col-lg-6 selectContainer\">\r\n                <select class=\"form-control\" name=\"color\">\r\n                    <option value=\"\">Choose a color</option>\r\n                    <option value=\"blue\">Blue</option>\r\n                    <option value=\"green\">Green</option>\r\n                    <option value=\"red\">Red</option>\r\n                    <option value=\"yellow\">Yellow</option>\r\n                    <option value=\"white\">White</option>\r\n                </select>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-xs-2 col-lg-3 control-label\">Size</label>\r\n            <div class=\"col-xs-9 col-lg-6 selectContainer\">\r\n                <select class=\"form-control\" name=\"size\">\r\n                    <option value=\"\">Choose a size</option>\r\n                    <option value=\"S\">S</option>\r\n                    <option value=\"M\">M</option>\r\n                    <option value=\"L\">L</option>\r\n                    <option value=\"XL\">XL</option>\r\n                </select>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</form>\r\n\r\n");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-profile-form.tpl.html","<form id=\"profileForm\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Default Form Elements\r\n        </legend>\r\n        <div class=\"form-group\">\r\n            <label>Email address</label>\r\n            <input type=\"text\" class=\"form-control\" name=\"email\" />\r\n        </div>\r\n    </fieldset>\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label>Password</label>\r\n            <input type=\"password\" class=\"form-control\" name=\"password\" />\r\n        </div>\r\n    </fieldset>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</form>\r\n");
  $templateCache.put("app/_common/forms/directives/bootstrap-validation/bootstrap-toggling-form.tpl.html","<form id=\"togglingForm\" method=\"post\" class=\"form-horizontal\">\r\n\r\n    <fieldset>\r\n        <legend>\r\n            Default Form Elements\r\n        </legend>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Full name <sup>*</sup></label>\r\n            <div class=\"col-lg-4\">\r\n                <input type=\"text\" class=\"form-control\" name=\"firstName\" placeholder=\"First name\" />\r\n            </div>\r\n            <div class=\"col-lg-4\">\r\n                <input type=\"text\" class=\"form-control\" name=\"lastName\" placeholder=\"Last name\" />\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Company <sup>*</sup></label>\r\n            <div class=\"col-lg-5\">\r\n                <input type=\"text\" class=\"form-control\" name=\"company\"\r\n                       required data-bv-notempty-message=\"The company name is required\" />\r\n            </div>\r\n            <div class=\"col-lg-2\">\r\n                <button type=\"button\" class=\"btn btn-info btn-sm\" data-toggle=\"#jobInfo\">\r\n                    Add more info\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n\r\n    <!-- These fields will not be validated as long as they are not visible -->\r\n    <div id=\"jobInfo\" style=\"display: none;\">\r\n        <fieldset>\r\n            <div class=\"form-group\">\r\n                <label class=\"col-lg-3 control-label\">Job title <sup>*</sup></label>\r\n                <div class=\"col-lg-5\">\r\n                    <input type=\"text\" class=\"form-control\" name=\"job\" />\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n\r\n        <fieldset>\r\n            <div class=\"form-group\">\r\n                <label class=\"col-lg-3 control-label\">Department <sup>*</sup></label>\r\n                <div class=\"col-lg-5\">\r\n                    <input type=\"text\" class=\"form-control\" name=\"department\" />\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n    </div>\r\n\r\n    <fieldset>\r\n        <div class=\"form-group\">\r\n            <label class=\"col-lg-3 control-label\">Mobile phone <sup>*</sup></label>\r\n            <div class=\"col-lg-5\">\r\n                <input type=\"text\" class=\"form-control\" name=\"mobilePhone\" />\r\n            </div>\r\n            <div class=\"col-lg-2\">\r\n                <button type=\"button\" class=\"btn btn-info btn-sm\" data-toggle=\"#phoneInfo\">\r\n                    Add more phone numbers\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </fieldset>\r\n    <!-- These fields will not be validated as long as they are not visible -->\r\n    <div id=\"phoneInfo\" style=\"display: none;\">\r\n\r\n        <fieldset>\r\n            <div class=\"form-group\">\r\n                <label class=\"col-lg-3 control-label\">Home phone</label>\r\n                <div class=\"col-lg-5\">\r\n                    <input type=\"text\" class=\"form-control\" name=\"homePhone\" />\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n        <fieldset>\r\n            <div class=\"form-group\">\r\n                <label class=\"col-lg-3 control-label\">Office phone</label>\r\n                <div class=\"col-lg-5\">\r\n                    <input type=\"text\" class=\"form-control\" name=\"officePhone\" />\r\n                </div>\r\n            </div>\r\n        </fieldset>\r\n    </div>\r\n\r\n    <div class=\"form-actions\">\r\n        <div class=\"row\">\r\n            <div class=\"col-md-12\">\r\n                <button class=\"btn btn-default\" type=\"submit\">\r\n                    <i class=\"fa fa-eye\"></i>\r\n                    Validate\r\n                </button>\r\n            </div>\r\n        </div>\r\n    </div>\r\n</form>");
  $templateCache.put("app/_common/layout/directives/demo/demo-states.tpl.html","<div class=\"demo\"><span id=\"demo-setting\"><i class=\"fa fa-cog txt-color-blueDark\"></i></span>\r\n\r\n    <form>\r\n        <legend class=\"no-padding margin-bottom-10\">Layout Options</legend>\r\n        <section>\r\n            <label><input type=\"checkbox\" ng-model=\"fixedHeader\"\r\n                          class=\"checkbox style-0\"><span>Fixed Header</span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"fixedNavigation\"\r\n                          class=\"checkbox style-0\"><span>Fixed Navigation</span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"fixedRibbon\"\r\n                          class=\"checkbox style-0\"><span>Fixed Ribbon</span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"fixedPageFooter\"\r\n                          class=\"checkbox style-0\"><span>Fixed Footer</span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"insideContainer\"\r\n                          class=\"checkbox style-0\"><span>Inside <b>.container</b></span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"rtl\"\r\n                          class=\"checkbox style-0\"><span>RTL</span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"menuOnTop\"\r\n                          class=\"checkbox style-0\"><span>Menu on <b>top</b></span></label>\r\n            <label><input type=\"checkbox\"\r\n                          ng-model=\"colorblindFriendly\"\r\n                          class=\"checkbox style-0\"><span>For Colorblind <div\r\n                    class=\"font-xs text-right\">(experimental)\r\n            </div></span>\r\n            </label><span id=\"smart-bgimages\"></span></section>\r\n        <section><h6 class=\"margin-top-10 semi-bold margin-bottom-5\">Clear Localstorage</h6><a\r\n                ng-click=\"factoryReset()\" class=\"btn btn-xs btn-block btn-primary\" id=\"reset-smart-widget\"><i\r\n                class=\"fa fa-refresh\"></i> Factory Reset</a></section>\r\n\r\n        <h6 class=\"margin-top-10 semi-bold margin-bottom-5\">SmartAdmin Skins</h6>\r\n\r\n\r\n        <section id=\"smart-styles\">\r\n            <a ng-repeat=\"skin in skins\" ng-click=\"setSkin(skin)\" class=\"{{skin.class}}\" style=\"{{skin.style}}\"><i ng-if=\"skin.name == $parent.smartSkin\" class=\"fa fa-check fa-fw\"></i> {{skin.label}} <sup ng-if=\"skin.beta\">beta</sup></a>\r\n        </section>\r\n    </form>\r\n</div>");}]);
"use strict";

angular.module('app.auth').directive('loginInfo', function(User){
  return {
    restrict: 'A',
    templateUrl: 'app/auth/directives/login-info.tpl.html',
    link: function(scope, element){
      User.initialized.then(function(){
        scope.user = User
      });
    }
  }
})

"use strict";

angular.module('app.auth').controller('LoginCtrl', function ($scope, $state, GooglePlus, User, ezfb) {

  $scope.$on('event:google-plus-signin-success', function (event, authResult) {
    if (authResult.status.method == 'PROMPT') {
      GooglePlus.getUser().then(function (user) {
        User.username = user.name;
        User.picture = user.picture;
        $state.go('app.dashboard');
      });
    }
  });

  $scope.$on('event:facebook-signin-success', function (event, authResult) {
    ezfb.api('/me', function (res) {
      User.username = res.name;
      User.picture = 'https://graph.facebook.com/' + res.id + '/picture';
      $state.go('app.dashboard');
    });
  });
})



'use strict';

angular.module('app.auth').factory('User', function ($http, $q, APP_CONFIG) {
  var dfd = $q.defer();

  var UserModel = {
    initialized: dfd.promise,
    username: undefined,
    picture: undefined
  };
  $http.get(APP_CONFIG.apiRootUrl + '/user.json').then(function(response){
    UserModel.username = response.data.username;
    UserModel.picture= response.data.picture;
    dfd.resolve(UserModel)
  });

  return UserModel;
});

'use strict';
//
// angular.module('app.calendar').controller('CalendarCtrl', function ($scope, $log, CalendarEvent) {
//
//
//   // Events scope
//   $scope.events = [];
//
//   // Unassigned events scope
//   $scope.eventsExternal = [
//     {
//       title: "Office Meeting",
//       description: "Currently busy",
//       className: "bg-color-darken txt-color-white",
//       icon: "fa-time"
//     },
//     {
//       title: "Lunch Break",
//       description: "No Description",
//       className: "bg-color-blue txt-color-white",
//       icon: "fa-pie"
//     },
//     {
//       title: "URGENT",
//       description: "urgent tasks",
//       className: "bg-color-red txt-color-white",
//       icon: "fa-alert"
//     }
//   ];
//
//
//   // Queriing our events from CalendarEvent resource...
//   // Scope update will automatically update the calendar
//   CalendarEvent.query().$promise.then(function (events) {
//     $scope.events = events;
//   });
//
//
//   $scope.newEvent = {};
//
//   $scope.addEvent = function() {
//
//     $log.log("Adding new event:", $scope.newEvent);
//
//     var newEventDefaults = {
//       title: "Untitled Event",
//       description: "no description",
//       className: "bg-color-darken txt-color-white",
//       icon: "fa-info"
//     };
//
//
//     $scope.newEvent = angular.extend(newEventDefaults, $scope.newEvent);
//
//     $scope.eventsExternal.unshift($scope.newEvent);
//
//     $scope.newEvent = {};
//
//     // $log.log("New events now:", $scope.eventsExternal);
//
//   };
//
//
// });

"use strict";

// angular.module('app.calendar').directive('dragableEvent', function ($log) {
//   return {
//     restrict: 'A',
//     link: function (scope, element) {
//
//       // $log.log(element.scope());
//
//       var eventObject = element.scope().event;
//
//       element.data('eventObject', eventObject);
//
//
//       element.draggable({
//         zIndex: 999,
//         revert: true, // will cause the event to go back to its
//         revertDuration: 0 //  original position after the drag
//       });
//
//
//     }
//   }
// })
// "use strict";
//
// angular.module('app.calendar').directive('fullCalendar', function (CalendarEvent, $log, $timeout) {
//   return {
//     restrict: 'E',
//     replace: true,
//     templateUrl: 'app/calendar/directives/full-calendar.tpl.html',
//     scope: {
//       events: "=events"
//     },
//     link: function (scope, element) {
//
//
//       var $calendar = $("#calendar");
//
//       var calendar = null;
//
//
//       function initCalendar() {
//
//         // $log.log(events);
//
//
//         calendar = $calendar.fullCalendar({
//           lang: 'en',
//           editable: true,
//           draggable: true,
//           selectable: false,
//           selectHelper: true,
//           unselectAuto: false,
//           disableResizing: false,
//           droppable: true,
//
//           header: {
//             left: 'title', //,today
//             center: 'prev, next, today',
//             right: 'month, agendaWeek, agendaDay' //month, agendaDay,
//           },
//
//           drop: function (date, allDay) { // this function is called when something is dropped
//
//             // retrieve the dropped element's stored Event Object
//             var originalEventObject = $(this).data('eventObject');
//
//             // we need to copy it, so that multiple events don't have a reference to the same object
//             var copiedEventObject = $.extend({}, originalEventObject);
//
//             // assign it the date that was reported
//             copiedEventObject.start = date;
//             copiedEventObject.allDay = allDay;
//
//             // $log.log(scope);
//
//             // render the event on the calendar
//             // the last `true` argument determines if the event "sticks" (http://arshaw.com/fullcalendar/docs/event_rendering/renderEvent/)
//             $('#calendar').fullCalendar('renderEvent', copiedEventObject, true);
//
//             // is the "remove after drop" checkbox checked?
//             if ($('#drop-remove').is(':checked')) {
//
//               // if so, remove the element from the "Draggable Events" list
//               // $(this).remove();
//               // $log.log($(this).scope());
//               var index = $(this).scope().$index;
//
//               $("#external-events").scope().eventsExternal.splice(index, 1);
//               $(this).remove();
//
//             }
//
//           },
//
//           select: function (start, end, allDay) {
//             var title = prompt('Event Title:');
//             if (title) {
//               calendar.fullCalendar('renderEvent', {
//                   title: title,
//                   start: start,
//                   end: end,
//                   allDay: allDay
//                 }, true // make the event "stick"
//               );
//             }
//             calendar.fullCalendar('unselect');
//           },
//
//           // events: scope.events,
//
//           events: function(start, end, timezone, callback) {
//
//             callback(scope.events);
//
//           },
//
//           eventRender: function (event, element, icon) {
//             if (!event.description == "") {
//               element.find('.fc-event-title').append("<br/><span class='ultra-light'>" + event.description + "</span>");
//             }
//             if (!event.icon == "") {
//               element.find('.fc-event-title').append("<i class='air air-top-right fa " + event.icon + " '></i>");
//             }
//           }
//         });
//
//         $('.fc-header-right, .fc-header-center', $calendar).hide();
//       }
//
//
//       initCalendar();
//
//
//       // Now events will be refetched every time events scope is updated in controller!!!
//       scope.$watch("events", function(newValue, oldValue) {
//
//         $calendar.fullCalendar( 'refetchEvents' );
//
//       }, true);
//
//
//       scope.next = function () {
//         $('.fc-button-next', $calendar).click();
//       };
//       scope.prev = function () {
//         $('.fc-button-prev', $calendar).click();
//       };
//       scope.today = function () {
//         $('.fc-button-today', $calendar).click();
//       };
//       scope.changeView = function (period) {
//         $calendar.fullCalendar('changeView', period);
//       };
//     }
//   }
// });
//
// "use strict";
//
// angular.module('app.calendar').factory('CalendarEvent', function($resource, APP_CONFIG){
//   return $resource( APP_CONFIG.apiRootUrl + '/events.json', {_id:'@id'})
// });
"use strict";

angular.module('app').controller("ActivitiesCtrl", function ActivitiesCtrl($scope, $log, activityService){

  $scope.activeTab = 'default';
  $scope.currentActivityItems = [];

  // Getting different type of activites
  activityService.get(function(data){

    $scope.activities = data.activities;

  });


  $scope.isActive = function(tab){
    return $scope.activeTab === tab;
  };

  $scope.setTab = function(activityType){
    $scope.activeTab = activityType;

    activityService.getbytype(activityType, function(data) {

      $scope.currentActivityItems = data.data;

    });

  };

});
"use strict";

angular.module('app').directive('activitiesDropdownToggle', function($log) {

  var link = function($scope,$element, attrs){
    var ajax_dropdown = null;

    $element.on('click',function(){
      var badge = $(this).find('.badge');

      if (badge.hasClass('bg-color-red')) {

        badge.removeClass('bg-color-red').text(0);

      }

      ajax_dropdown = $(this).next('.ajax-dropdown');

      if (!ajax_dropdown.is(':visible')) {

        ajax_dropdown.fadeIn(150);

        $(this).addClass('active');

      }
      else {

        ajax_dropdown.fadeOut(150);

        $(this).removeClass('active');

      }

    })

    $(document).mouseup(function(e) {
      if (ajax_dropdown && !ajax_dropdown.is(e.target) && ajax_dropdown.has(e.target).length === 0) {
        ajax_dropdown.fadeOut(150);
        $element.removeClass('active');
      }
    });
  }

  return{
    restrict:'EA',
    link:link
  }
});
"use strict";

angular.module('app').factory('activityService', function($http, $log, APP_CONFIG) {

  function getActivities(callback){

    $http.get(APP_CONFIG.apiRootUrl + '/activities/activity.json').success(function(data){

      callback(data);

    }).error(function(){

      $log.log('Error');
      callback([]);

    });

  }

  function getActivitiesByType(type, callback){

    $http.get(APP_CONFIG.apiRootUrl + '/activities/activity-' + type + '.json').success(function(data){

      callback(data);

    }).error(function(){

      $log.log('Error');
      callback([]);

    });

  }

  return{
    get:function(callback){
      getActivities(callback);
    },
    getbytype:function(type,callback){
      getActivitiesByType(type, callback);
    }
  }
});
"use strict";

angular.module('app').factory('Project', function($http, APP_CONFIG){
  return {
    list: $http.get(APP_CONFIG.apiRootUrl + '/projects.json')
  }
});
"use strict";

angular.module('app').directive('recentProjects', function(Project){
  return {
    restrict: "EA",
    replace: true,
    templateUrl: "app/dashboard/projects/recent-projects.tpl.html",
    scope: true,
    link: function(scope, element){

      Project.list.then(function(response){
        scope.projects = response.data;
      });
      scope.clearProjects = function(){
        scope.projects = [];
      }
    }
  }
});
"use strict";

angular.module('app').controller('TodoCtrl', function ($scope, $timeout, Todo) {
  $scope.newTodo = undefined;

  $scope.states = ['Critical', 'Important', 'Completed'];

  $scope.todos = Todo.getList().$object;

  // $scope.$watch('todos', function(){ }, true)

  $scope.toggleAdd = function () {
    if (!$scope.newTodo) {
      $scope.newTodo = {
        state: 'Important'
      };
    } else {
      $scope.newTodo = undefined;
    }
  };

  $scope.createTodo = function () {
    $scope.todos.push(
      Todo.normalize($scope.newTodo)
    );
    $scope.newTodo = undefined;

  };

  $scope.deleteTodo = function (todo) {
    todo.remove().then(function () {
      $scope.todos.splice($scope.todos.indexOf(todo), 1);
    });

  };

});
'use strict';

angular.module('app.eCommerce').controller('OrdersDemoCtrl', function ($scope, orders) {

  $scope.orders = orders.data;

  $scope.tableOptions =  {
    "data": orders.data.data,
//            "bDestroy": true,
    "iDisplayLength": 15,
    columns: [
      {data: "orderId"},
      {data: "customerId"},
      {data: "purchase"},
      {data: "delivery"},
      {data: "basePrice"},
      {data: "postalZip"},
      {data: "status"}
    ],
    "order": [[1, 'asc']]
  }
});

"use strict";

angular.module('app.forms').controller('FormLayoutsCtrl', function($scope, $modal, $log){

  $scope.openModal = function () {
    var modalInstance = $modal.open({
      templateUrl: 'app/forms/views/form-layout-modal.html',
      controller: 'ModalDemoCtrl'
    });

    modalInstance.result.then(function () {
      $log.info('Modal closed at: ' + new Date());

    }, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });


  };

  $scope.registration = {};

  $scope.$watch('registration.date', function(changed){
    console.log('registration model changed', $scope.registration)
  })


});

"use strict";

angular.module('app.forms').controller('FormPluginsCtrl', function($scope, $log){
  $scope.editableOptions =  {
    mode: 'popup',
    disabled: true
  };

  $scope.toggleInline = function() {
    if($scope.editableOptions.mode == 'popup') {
      $scope.editableOptions.mode = 'inline';
    }
    else {
      $scope.editableOptions.mode = 'popup'
    }
  };

  $scope.toggleDisabled = function() {
    $scope.editableOptions.disabled = !$scope.editableOptions.disabled;
  };


  $scope.datepickerOptions = {
    changeMonth: true,
    changeYear: true
  }
});
"use strict";


angular.module('app.forms').controller('FormWizardCtrl', function($scope){

  $scope.wizard1CompleteCallback = function(wizardData){
    console.log('wizard1CompleteCallback', wizardData);
    $.smallBox({
      title: "Congratulations! Smart wizard finished",
      content: "<i class='fa fa-clock-o'></i> <i>1 seconds ago...</i>",
      color: "#5F895F",
      iconSmall: "fa fa-check bounce animated",
      timeout: 4000
    });
  };

  $scope.wizard2CompleteCallback = function(wizardData){
    console.log('wizard2CompleteCallback', wizardData);
    $.smallBox({
      title: "Congratulations! Smart fuekux wizard finished",
      content: "<i class='fa fa-clock-o'></i> <i>1 seconds ago...</i>",
      color: "#5F895F",
      iconSmall: "fa fa-check bounce animated",
      timeout: 4000
    });

  };

});
"use strict";

angular.module('app.forms').controller('FormXeditableCtrl', function($scope, $log){
//   console.log('Si esta en uso');
  $scope.username = 'superuser';
  $scope.firstname = null;
  $scope.sex = 'not selected';
  $scope.group = "Admin";
  $scope.vacation = "25.02.2013";
  $scope.combodate = "15/05/1984";
  $scope.event = null;
  $scope.comments = 'awesome user!';
  $scope.state2 = 'California';
  $scope.fruits = 'peach<br/>apple';


  $scope.fruits_data = [
    {value: 'banana', text: 'banana'},
    {value: 'peach', text: 'peach'},
    {value: 'apple', text: 'apple'},
    {value: 'watermelon', text: 'watermelon'},
    {value: 'orange', text: 'orange'}]
  ;


  $scope.genders =  [
    {value: 'not selected', text: 'not selected'},
    {value: 'Male', text: 'Male'},
    {value: 'Female', text: 'Female'}
  ];

  $scope.groups =  [
    {value: 'Guest', text: 'Guest'},
    {value: 'Service', text: 'Service'},
    {value: 'Customer', text: 'Customer'},
    {value: 'Operator', text: 'Operator'},
    {value: 'Support', text: 'Support'},
    {value: 'Admin', text: 'Admin'}
  ];

});
"use strict";


angular.module('app.forms').controller('ImageEditorCtrl', function ($scope) {

  // api tab
  $scope.apiDemoSelection = [100, 100, 400, 300];

  $scope.apiDemoOptions = {
    allowSelect: true,
    allowResize: true,
    allowMove: true,
    animate: false
  };

  $scope.apiRandomSelection = function () {
    $scope.apiDemoOptions.animate = false;
    $scope.apiDemoSelection = [
      Math.round(Math.random() * 600),
      Math.round(Math.random() * 400),
      Math.round(Math.random() * 600),
      Math.round(Math.random() * 400)
    ]
  };

  $scope.apiRandomAnimation = function () {
    $scope.apiDemoOptions.animate = true;
    $scope.apiDemoSelection = [
      Math.round(Math.random() * 600),
      Math.round(Math.random() * 400),
      Math.round(Math.random() * 600),
      Math.round(Math.random() * 400)
    ]
  };

  $scope.apiReleaseSelection = function () {
    $scope.apiDemoOptions.animate = true;
    $scope.apiDemoSelection = 'release';
  };


  $scope.apiToggleDisable = function () {
    $scope.apiDemoOptions.disabled = !$scope.apiDemoOptions.disabled;
  };

  $scope.apiToggleDestroy = function () {
    $scope.apiDemoOptions.destroyed = !$scope.apiDemoOptions.destroyed;
  };

  $scope.apiDemoShowAspect = false;
  $scope.apiDemoToggleAspect = function () {
    $scope.apiDemoShowAspect = !$scope.apiDemoShowAspect;
    if ($scope.apiDemoShowAspect)
      $scope.apiDemoOptions.aspectRatio = 4 / 3;
    else
      $scope.apiDemoOptions.aspectRatio = 0;
  };

  $scope.apiDemoShowSizeRestrict = false;
  $scope.apiDemoToggleSizeRestrict = function () {
    $scope.apiDemoShowSizeRestrict = !$scope.apiDemoShowSizeRestrict;
    if ($scope.apiDemoShowSizeRestrict) {
      $scope.apiDemoOptions.minSizeWidth = 80;
      $scope.apiDemoOptions.minSizeHeight = 80;
      $scope.apiDemoOptions.maxSizeWidth = 350;
      $scope.apiDemoOptions.maxSizeHeight = 350;
    } else {
      $scope.apiDemoOptions.minSizeWidth = 0;
      $scope.apiDemoOptions.minSizeHeight = 0;
      $scope.apiDemoOptions.maxSizeWidth = 0;
      $scope.apiDemoOptions.maxSizeHeight = 0;
    }

  };


  $scope.setApiDemoImage = function (image) {
    $scope.apiDemoImage = image;
    $scope.apiDemoOptions.src = image.src;
    $scope.apiDemoOptions.bgOpacity = image.bgOpacity;
    $scope.apiDemoOptions.outerImage = image.outerImage;
    $scope.apiRandomAnimation();
  };

  $scope.apiDemoImages = [
    {
      name: 'Lego',
      src: 'styles/img/superbox/superbox-full-24.jpg',
      bgOpacity: .6
    },
    {
      name: 'Breakdance',
      src: 'styles/img/superbox/superbox-full-7.jpg',
      bgOpacity: .6
    },
    {
      name: 'Dragon Fly',
      src: 'styles/img/superbox/superbox-full-20.jpg',
      bgOpacity: 1,
      outerImage: 'styles/img/superbox/superbox-full-20-bw.jpg'
    }
  ];

  $scope.apiDemoImage = $scope.apiDemoImages[1];

  // animations tab
  $scope.animationsDemoOptions = {
    bgOpacity: undefined,
    bgColor: undefined,
    bgFade: true,
    shade: false,
    animate: true
  };
  $scope.animationsDemoSelection = undefined;
  $scope.selections = {
    1: [217, 122, 382, 284],
    2: [20, 20, 580, 380],
    3: [24, 24, 176, 376],
    4: [347, 165, 550, 355],
    5: [136, 55, 472, 183],
    Release: 'release'
  };

  $scope.opacities = {
    Low: .2,
    Mid: .5,
    High: .8,
    Full: 1
  };

  $scope.colors = {
    R: '#900',
    B: '#4BB6F0',
    Y: '#F0B207',
    G: '#46B81C',
    W: 'white',
    K: 'black'
  };


  // styling tab

  $scope.styles = [
    {
      name: 'jcrop-light',
      bgFade: true,
      animate: true,
      selection: [130, 65, 130 + 350, 65 + 285],
      bgColor: 'white',
      bgOpacity: 0.5
    },
    {
      name: 'jcrop-dark',
      bgFade: true,
      animate: true,
      selection: [130, 65, 130 + 350, 65 + 285],
      bgColor: 'black',
      bgOpacity: 0.4
    },
    {
      name: 'jcrop-normal',
      bgFade: true,
      animate: true,
      selection: [130, 65, 130 + 350, 65 + 285],
      bgColor: 'black',
      bgOpacity: 0.6
    }
  ];

  $scope.demoStyle = $scope.styles[0]
});
'use strict'

angular.module('app.forms').controller('ModalDemoCtrl', function($scope, $modalInstance){
  $scope.closeModal = function(){
    $modalInstance.dismiss('cancel');
  }
});
"use strict";

angular.module('app.graphs').controller('FlotCtrl', function ($scope) {


  $scope.salesChartData = [
    [1196463600000, 0],
    [1196550000000, 0],
    [1196636400000, 0],
    [1196722800000, 77],
    [1196809200000, 3636],
    [1196895600000, 3575],
    [1196982000000, 2736],
    [1197068400000, 1086],
    [1197154800000, 676],
    [1197241200000, 1205],
    [1197327600000, 906],
    [1197414000000, 710],
    [1197500400000, 639],
    [1197586800000, 540],
    [1197673200000, 435],
    [1197759600000, 301],
    [1197846000000, 575],
    [1197932400000, 481],
    [1198018800000, 591],
    [1198105200000, 608],
    [1198191600000, 459],
    [1198278000000, 234],
    [1198364400000, 1352],
    [1198450800000, 686],
    [1198537200000, 279],
    [1198623600000, 449],
    [1198710000000, 468],
    [1198796400000, 392],
    [1198882800000, 282],
    [1198969200000, 208],
    [1199055600000, 229],
    [1199142000000, 177],
    [1199228400000, 374],
    [1199314800000, 436],
    [1199401200000, 404],
    [1199487600000, 253],
    [1199574000000, 218],
    [1199660400000, 476],
    [1199746800000, 462],
    [1199833200000, 500],
    [1199919600000, 700],
    [1200006000000, 750],
    [1200092400000, 600],
    [1200178800000, 500],
    [1200265200000, 900],
    [1200351600000, 930],
    [1200438000000, 1200],
    [1200524400000, 980],
    [1200610800000, 950],
    [1200697200000, 900],
    [1200783600000, 1000],
    [1200870000000, 1050],
    [1200956400000, 1150],
    [1201042800000, 1100],
    [1201129200000, 1200],
    [1201215600000, 1300],
    [1201302000000, 1700],
    [1201388400000, 1450],
    [1201474800000, 1500],
    [1201561200000, 546],
    [1201647600000, 614],
    [1201734000000, 954],
    [1201820400000, 1700],
    [1201906800000, 1800],
    [1201993200000, 1900],
    [1202079600000, 2000],
    [1202166000000, 2100],
    [1202252400000, 2200],
    [1202338800000, 2300],
    [1202425200000, 2400],
    [1202511600000, 2550],
    [1202598000000, 2600],
    [1202684400000, 2500],
    [1202770800000, 2700],
    [1202857200000, 2750],
    [1202943600000, 2800],
    [1203030000000, 3245],
    [1203116400000, 3345],
    [1203202800000, 3000],
    [1203289200000, 3200],
    [1203375600000, 3300],
    [1203462000000, 3400],
    [1203548400000, 3600],
    [1203634800000, 3700],
    [1203721200000, 3800],
    [1203807600000, 4000],
    [1203894000000, 4500]
  ]
    .map(function (item) {
      return [
        item[0] + 60 * 60 * 1000,
        item[1]
      ]
    });

  $scope.barChartData = _.range(3).map(function (barNum) {
    return {
      data: _.range(12).map(function (i) {
        return [i, parseInt(Math.random() * 30)]
      }),
      bars: {
        show: true,
        barWidth: 0.2,
        order: barNum + 1
      }
    }
  });

  $scope.horizontalBarChartData = _.range(3).map(function (barNum) {
    return {
      data: _.range(4).map(function (i) {
        return [i, parseInt(Math.random() * 30)]
      }),
      bars: {
        horizontal: true,
        show: true,
        barWidth: 0.2,
        order: barNum + 1
      }
    }
  });

  $scope.sinChartData = [
    {
      data: _.range(16).map(function (i) {
        return [i, Math.sin(i)];
      }),
      label: "sin(x)"
    },
    {
      data: _.range(16).map(function (i) {
        return [i, Math.cos(i)];
      }),
      label: "cos(x)"
    }
  ];


  // fill chart

  var males = {
    '15%' : [[2, 88.0], [3, 93.3], [4, 102.0], [5, 108.5], [6, 115.7], [7, 115.6], [8, 124.6], [9, 130.3], [10, 134.3], [11, 141.4], [12, 146.5], [13, 151.7], [14, 159.9], [15, 165.4], [16, 167.8], [17, 168.7], [18, 169.5], [19, 168.0]],
    '90%' : [[2, 96.8], [3, 105.2], [4, 113.9], [5, 120.8], [6, 127.0], [7, 133.1], [8, 139.1], [9, 143.9], [10, 151.3], [11, 161.1], [12, 164.8], [13, 173.5], [14, 179.0], [15, 182.0], [16, 186.9], [17, 185.2], [18, 186.3], [19, 186.6]],
    '25%' : [[2, 89.2], [3, 94.9], [4, 104.4], [5, 111.4], [6, 117.5], [7, 120.2], [8, 127.1], [9, 132.9], [10, 136.8], [11, 144.4], [12, 149.5], [13, 154.1], [14, 163.1], [15, 169.2], [16, 170.4], [17, 171.2], [18, 172.4], [19, 170.8]],
    '10%' : [[2, 86.9], [3, 92.6], [4, 99.9], [5, 107.0], [6, 114.0], [7, 113.5], [8, 123.6], [9, 129.2], [10, 133.0], [11, 140.6], [12, 145.2], [13, 149.7], [14, 158.4], [15, 163.5], [16, 166.9], [17, 167.5], [18, 167.1], [19, 165.3]],
    'mean' : [[2, 91.9], [3, 98.5], [4, 107.1], [5, 114.4], [6, 120.6], [7, 124.7], [8, 131.1], [9, 136.8], [10, 142.3], [11, 150.0], [12, 154.7], [13, 161.9], [14, 168.7], [15, 173.6], [16, 175.9], [17, 176.6], [18, 176.8], [19, 176.7]],
    '75%' : [[2, 94.5], [3, 102.1], [4, 110.8], [5, 117.9], [6, 124.0], [7, 129.3], [8, 134.6], [9, 141.4], [10, 147.0], [11, 156.1], [12, 160.3], [13, 168.3], [14, 174.7], [15, 178.0], [16, 180.2], [17, 181.7], [18, 181.3], [19, 182.5]],
    '85%' : [[2, 96.2], [3, 103.8], [4, 111.8], [5, 119.6], [6, 125.6], [7, 131.5], [8, 138.0], [9, 143.3], [10, 149.3], [11, 159.8], [12, 162.5], [13, 171.3], [14, 177.5], [15, 180.2], [16, 183.8], [17, 183.4], [18, 183.5], [19, 185.5]],
    '50%' : [[2, 91.9], [3, 98.2], [4, 106.8], [5, 114.6], [6, 120.8], [7, 125.2], [8, 130.3], [9, 137.1], [10, 141.5], [11, 149.4], [12, 153.9], [13, 162.2], [14, 169.0], [15, 174.8], [16, 176.0], [17, 176.8], [18, 176.4], [19, 177.4]]
  };

  var females = {
    '15%' : [[2, 84.8], [3, 93.7], [4, 100.6], [5, 105.8], [6, 113.3], [7, 119.3], [8, 124.3], [9, 131.4], [10, 136.9], [11, 143.8], [12, 149.4], [13, 151.2], [14, 152.3], [15, 155.9], [16, 154.7], [17, 157.0], [18, 156.1], [19, 155.4]],
    '90%' : [[2, 95.6], [3, 104.1], [4, 111.9], [5, 119.6], [6, 127.6], [7, 133.1], [8, 138.7], [9, 147.1], [10, 152.8], [11, 161.3], [12, 166.6], [13, 167.9], [14, 169.3], [15, 170.1], [16, 172.4], [17, 169.2], [18, 171.1], [19, 172.4]],
    '25%' : [[2, 87.2], [3, 95.9], [4, 101.9], [5, 107.4], [6, 114.8], [7, 121.4], [8, 126.8], [9, 133.4], [10, 138.6], [11, 146.2], [12, 152.0], [13, 153.8], [14, 155.7], [15, 158.4], [16, 157.0], [17, 158.5], [18, 158.4], [19, 158.1]],
    '10%' : [[2, 84.0], [3, 91.9], [4, 99.2], [5, 105.2], [6, 112.7], [7, 118.0], [8, 123.3], [9, 130.2], [10, 135.0], [11, 141.1], [12, 148.3], [13, 150.0], [14, 150.7], [15, 154.3], [16, 153.6], [17, 155.6], [18, 154.7], [19, 153.1]],
    'mean' : [[2, 90.2], [3, 98.3], [4, 105.2], [5, 112.2], [6, 119.0], [7, 125.8], [8, 131.3], [9, 138.6], [10, 144.2], [11, 151.3], [12, 156.7], [13, 158.6], [14, 160.5], [15, 162.1], [16, 162.9], [17, 162.2], [18, 163.0], [19, 163.1]],
    '75%' : [[2, 93.2], [3, 101.5], [4, 107.9], [5, 116.6], [6, 122.8], [7, 129.3], [8, 135.2], [9, 143.7], [10, 148.7], [11, 156.9], [12, 160.8], [13, 163.0], [14, 165.0], [15, 165.8], [16, 168.7], [17, 166.2], [18, 167.6], [19, 168.0]],
    '85%' : [[2, 94.5], [3, 102.8], [4, 110.4], [5, 119.0], [6, 125.7], [7, 131.5], [8, 137.9], [9, 146.0], [10, 151.3], [11, 159.9], [12, 164.0], [13, 166.5], [14, 167.5], [15, 168.5], [16, 171.5], [17, 168.0], [18, 169.8], [19, 170.3]],
    '50%' : [[2, 90.2], [3, 98.1], [4, 105.2], [5, 111.7], [6, 118.2], [7, 125.6], [8, 130.5], [9, 138.3], [10, 143.7], [11, 151.4], [12, 156.7], [13, 157.7], [14, 161.0], [15, 162.0], [16, 162.8], [17, 162.2], [18, 162.8], [19, 163.3]]
  };

  $scope.fillChartData = [{
    label : 'female mean',
    data : females['mean'],
    lines : {
      show : true
    },
    color : "rgb(255,50,50)"
  }, {
    id : 'f15%',
    data : females['15%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : false
    },
    color : "rgb(255,50,50)"
  }, {
    id : 'f25%',
    data : females['25%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.2
    },
    color : "rgb(255,50,50)",
    fillBetween : 'f15%'
  }, {
    id : 'f50%',
    data : females['50%'],
    lines : {
      show : true,
      lineWidth : 0.5,
      fill : 0.4,
      shadowSize : 0
    },
    color : "rgb(255,50,50)",
    fillBetween : 'f25%'
  }, {
    id : 'f75%',
    data : females['75%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.4
    },
    color : "rgb(255,50,50)",
    fillBetween : 'f50%'
  }, {
    id : 'f85%',
    data : females['85%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.2
    },
    color : "rgb(255,50,50)",
    fillBetween : 'f75%'
  }, {
    label : 'male mean',
    data : males['mean'],
    lines : {
      show : true
    },
    color : "rgb(50,50,255)"
  }, {
    id : 'm15%',
    data : males['15%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : false
    },
    color : "rgb(50,50,255)"
  }, {
    id : 'm25%',
    data : males['25%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.2
    },
    color : "rgb(50,50,255)",
    fillBetween : 'm15%'
  }, {
    id : 'm50%',
    data : males['50%'],
    lines : {
      show : true,
      lineWidth : 0.5,
      fill : 0.4,
      shadowSize : 0
    },
    color : "rgb(50,50,255)",
    fillBetween : 'm25%'
  }, {
    id : 'm75%',
    data : males['75%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.4
    },
    color : "rgb(50,50,255)",
    fillBetween : 'm50%'
  }, {
    id : 'm85%',
    data : males['85%'],
    lines : {
      show : true,
      lineWidth : 0,
      fill : 0.2
    },
    color : "rgb(50,50,255)",
    fillBetween : 'm75%'
  }];



  //
  $scope.pieChartData = _.range(Math.floor(Math.random() * 10) + 1).map(function(i){
    return {
      label : "Series" + (i + 1),
      data : Math.floor(Math.random() * 100) + 1
    }
  });

  var pageviews = [[1, 75], [3, 87], [4, 93], [5, 127], [6, 116], [7, 137], [8, 135], [9, 130], [10, 167], [11, 169], [12, 179], [13, 185], [14, 176], [15, 180], [16, 174], [17, 193], [18, 186], [19, 177], [20, 153], [21, 149], [22, 130], [23, 100], [24, 50]];
  var visitors = [[1, 65], [3, 50], [4, 73], [5, 100], [6, 95], [7, 103], [8, 111], [9, 97], [10, 125], [11, 100], [12, 95], [13, 141], [14, 126], [15, 131], [16, 146], [17, 158], [18, 160], [19, 151], [20, 125], [21, 110], [22, 100], [23, 85], [24, 37]];

  $scope.siteStatsData = [{
    data : pageviews,
    label : "Your pageviews"
  }, {
    data : visitors,
    label : "Site visitors"
  }];
});