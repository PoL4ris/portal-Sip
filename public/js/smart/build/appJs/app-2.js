
"use strict";


angular.module('app.maps', ['ui.router',
  'uiGmapgoogle-maps'
])
//.config(function(uiGmapGoogleMapApiProvider) {
//    uiGmapGoogleMapApiProvider.configure({
//        //    key: 'your api key',
//        v: '3.20', //defaults to latest 3.X anyhow
//        libraries: 'weather,geometry,visualization'
//    });
//})


angular.module('app.maps').config(function ($stateProvider) {

  $stateProvider
    .state('app.maps', {
      url: '/maps',
      data: {
        title: 'Maps'
      },
      views: {
        "content@app": {
          controller: 'MapsDemoCtrl',
          templateUrl: 'app/maps/views/maps-demo.html'
        }
      }
    })
});
"use strict";

angular.module('app.misc', ['ui.router']);


angular.module('app.misc').config(function ($stateProvider) {

  $stateProvider
    .state('app.misc', {
      abstract: true,
      data: {
        title: 'Miscellaneous'
      }
    })

    .state('app.misc.pricingTable', {
      url: '/pricing-table',
      data: {
        title: 'Pricing Table'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/pricing-table.html'
        }
      }
    })

    .state('app.misc.invoice', {
      url: '/invoice',
      data: {
        title: 'Invoice'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/invoice.html'
        }
      }
    })

    .state('app.misc.error404', {
      url: '/404',
      data: {
        title: 'Error 404'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/error404.html'
        }
      }
    })

    .state('app.misc.error500', {
      url: '/500',
      data: {
        title: 'Error 500'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/error500.html'
        }
      }
    })

    .state('app.misc.blank', {
      url: '/blank',
      data: {
        title: 'Blank'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/blank.html'
        }
      }
    })

    .state('app.misc.test', {
      url: '/test',
      data: {
        title: 'Test'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/billing.html'
        }
      }
    })

    .state('app.misc.emailTemplate', {
      url: '/email-template',
      data: {
        title: 'Email Template'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/email-template.html'
        }
      }
    })

    .state('app.misc.search', {
      url: '/search',
      data: {
        title: 'Search'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/search.html'
        }
      }
    })

    .state('app.misc.ckeditor', {
      url: '/ckeditor',
      data: {
        title: 'CK Editor'
      },
      views: {
        "content@app": {
          templateUrl: 'app/misc/views/ckeditor.html'
        }
      },
      resolve:{
        scripts: function(lazyScript){
          return lazyScript.register('smartadmin-plugin/legacy/ckeditor/ckeditor.js');
        }
      }
    })
});
"use strict";


angular.module('app.smartAdmin', ['ui.router']);


angular.module('app.smartAdmin').config(function ($stateProvider) {

  $stateProvider
    .state('app.smartAdmin', {
      abstract: true,
      data: {
        title: 'SmartAdmin Intel'
      }
    })

    .state('app.smartAdmin.appLayout', {
      url: '/app-layout',
      data: {
        title: 'App Layout'
      },
      views: {
        "content@app": {
          templateUrl: 'app/smart-admin/views/app-layout.html'
        }
      }
    })

    .state('app.smartAdmin.diffVer', {
      url: '/different-versions',
      data: {
        title: 'Different Versions'
      },
      views: {
        "content@app": {
          templateUrl: 'app/smart-admin/views/different-versions.html'
        }
      }
    })

    .state('app.smartAdmin.appLayouts', {
      url: '/app-layouts',
      data: {
        title: 'App Layouts'
      },
      views: {
        "content@app": {
          templateUrl: 'app/smart-admin/views/app-layouts.html'
        }
      }
    })

    .state('app.smartAdmin.prebuiltSkins', {
      url: '/prebuilt-skins',
      data: {
        title: 'Prebuilt Skins'
      },
      views: {
        "content@app": {
          templateUrl: 'app/smart-admin/views/prebuilt-skins.html'
        }
      }
    })
});
"use strict";

angular.module('app.tables', [ 'ui.router', 'datatables', 'datatables.bootstrap']);

angular.module('app.tables').config(function ($stateProvider) {
//   console.log('app.tables');
//   console.log($stateProvider);
  return;
  $stateProvider
    .state('app.tables', {
      abstract: true,
      data: {
        title: 'Tables'
      }
    })

    .state('app.tables.normal', {
      url: '/tables/normal',
      data: {
        title: 'Normal Tables'
      },
      views: {
        "content@app": {
          templateUrl: "app/tables/views/normal.html"

        }
      }
    })

    .state('app.tables.datatables', {
      url: '/tables/datatables',
      data: {
        title: 'Data Tables'
      },
      views: {
        "content@app": {
          controller: 'DatatablesCtrl as datatables',
          templateUrl: "app/tables/views/datatables.html"
        }
      }
    })

    .state('app.tables.jqgrid', {
      url: '/tables/jqgrid',
      data: {
        title: 'Jquery Grid'
      },
      views: {
        "content@app": {
          controller: 'JqGridCtrl',
          templateUrl: "app/tables/views/jqgrid.html"
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/smartadmin-plugin/legacy/jqgrid/js/minified/jquery.jqGrid.min.js',
            '/js/smart/smartadmin-plugin/legacy/jqgrid/js/i18n/grid.locale-en.js'
          ])

        }
      }
    })
});
'use strict'

angular.module('app.ui', ['ui.router']);

angular.module('app.ui').config(function($stateProvider){

  $stateProvider
    .state('app.ui', {
      abstract: true,
      data: {
        title: 'UI Elements'
      }
    })
    .state('app.ui.general', {
      url: '/ui/general',
      data: {
        title: 'General Elements'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/general-elements.html',
          controller: 'GeneralElementsCtrl'
        }
      }
    })
    .state('app.ui.buttons', {
      url: '/ui/buttons',
      data: {
        title: 'Buttons'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/buttons.html',
          controller: 'GeneralElementsCtrl'
        }
      }
    })
    .state('app.ui.iconsFa', {
      url: '/ui/icons-font-awesome',
      data: {
        title: 'Font Awesome'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/icons-fa.html'
        }
      }
    })
    .state('app.ui.iconsGlyph', {
      url: '/ui/icons-glyph',
      data: {
        title: 'Glyph Icons'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/icons-glyph.html'
        }
      }
    })
    .state('app.ui.iconsFlags', {
      url: '/ui/icons-flags',
      data: {
        title: 'Flags'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/icons-flags.html'
        }
      }
    })
    .state('app.ui.grid', {
      url: '/ui/grid',
      data: {
        title: 'Grid'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/grid.html'
        }
      }
    })
    .state('app.ui.treeView', {
      url: '/ui/tree-view',
      data: {
        title: 'Tree View'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/tree-view.html',
          controller: 'TreeviewCtrl'
        }
      }
    })
    .state('app.ui.nestableLists', {
      url: '/ui/nestable-lists',
      data: {
        title: 'Nestable Lists'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/nestable-lists.html'
        }
      },
      resolve: {
        srcipts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ])

        }
      }
    })
    .state('app.ui.jqueryUi', {
      url: '/ui/jquery-ui',
      data: {
        title: 'JQuery UI'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/jquery-ui.html',
          controller: 'JquiCtrl'
        }
      },
      resolve: {
        srcipts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ])

        }
      }
    })
    .state('app.ui.typography', {
      url: '/ui/typography',
      data: {
        title: 'JQuery UI'
      },
      views: {
        "content@app": {
          templateUrl: 'app/ui/views/typography.html'
        }
      }
    })
});
"use strict";

angular.module('app.widgets', ['ui.router'])


  .config(function ($stateProvider) {

    $stateProvider
      .state('app.widgets', {
        url: '/widgets',
        data: {
          title: 'Widgets'
        },
        views: {
          "content@app": {
            templateUrl: 'app/widgets/views/widgets-demo.html'

          }
        }

      })

  });

(function(){
  "use strict";

  angular.module('SmartAdmin', [
    "SmartAdmin.Forms",
    "SmartAdmin.Layout",
    "SmartAdmin.UI",
  ]);
})();
"use strict";


angular.module('app.chat', ['ngSanitize'])
  .run(function ($templateCache) {

    $templateCache.put("template/popover/popover.html",
      "<div class=\"popover {{placement}}\" ng-class=\"{ in: isOpen(), fade: animation() }\">\n" +
      "  <div class=\"arrow\"></div>\n" +
      "\n" +
      "  <div class=\"popover-inner\">\n" +
      "      <h3 class=\"popover-title\" ng-bind-html=\"title | unsafe\" ng-show=\"title\"></h3>\n" +
      "      <div class=\"popover-content\"ng-bind-html=\"content | unsafe\"></div>\n" +
      "  </div>\n" +
      "</div>\n" +
      "");

  }).filter('unsafe', ['$sce', function ($sce) {
  return function (val) {
    return $sce.trustAsHtml(val);
  };
}]);
(function(){
  "use strict";

  angular.module('SmartAdmin.Forms', []);
})();
(function(){
  "use strict";

  angular.module('SmartAdmin.Layout', []);
})();
(function(){
  "use strict";

  angular.module('SmartAdmin.UI', []);
})();
'use strict';

angular.module('app.dashboard').controller('DashboardCtrl', function ($scope, $interval, customerService) {
// angular.module('app.dashboard').controller('DashboardCtrl', function ($scope, $interval, CalendarEvent) {
  // Live Feeds Widget Data And Display Controls
  // Live Stats Tab

  if(customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }


  function getFakeItem(index, prevValue){
    var limitUp = Math.min(100, prevValue + 5),
      limitDown = Math.abs(prevValue - 5);
    return [
      index,
      _.random(limitDown, limitUp, true)
    ]
  }

  function getFakeData() {
    return _(_.range(199)).reduce(function (out, number) {

      out.push(getFakeItem(number+1, _.last(out)[1]));
      return out;
    }, [
      [0, 50] // starting point
    ])
  }

  $scope.autoUpdate = false;

  var updateInterval;
  $scope.$watch('autoUpdate', function(autoUpdate){

    if(autoUpdate){
      updateInterval = $interval(function(){
        var stats = _.rest($scope.liveStats[0]).map(function(elem, i){
          elem[0] = i;
          return elem;
        });
        stats.push([199, _.last(stats)[1]]);
        $scope.liveStats = [stats];
      }, 1500)
    } else {
      $interval.cancel(updateInterval);
    }
  });


  $scope.liveStats = [getFakeData()];



  $scope.liveStatsOptions = {
    yaxis: {
      min: 0,
      max: 100
    },
    xaxis: {
      min: 0,
      max: 100
    },
    colors: ['rgb(87, 136, 156)'],
    series: {
      lines: {
        lineWidth: 1,
        fill: true,
        fillColor: {
          colors: [
            {
              opacity: 0.4
            },
            {
              opacity: 0
            }
          ]
        },
        steps: false

      }
    }
  };


  // Stats Display With Flot Chart

  var twitter = [
    [1, 27],
    [2, 34],
    [3, 51],
    [4, 48],
    [5, 55],
    [6, 65],
    [7, 61],
    [8, 70],
    [9, 65],
    [10, 75],
    [11, 57],
    [12, 59],
    [13, 62]
  ];
  var facebook = [
    [1, 25],
    [2, 31],
    [3, 45],
    [4, 37],
    [5, 38],
    [6, 40],
    [7, 47],
    [8, 55],
    [9, 43],
    [10, 50],
    [11, 47],
    [12, 39],
    [13, 47]
  ];
  $scope.statsData = [
    {
      label: "Twitter",
      data: twitter,
      lines: {
        show: true,
        lineWidth: 1,
        fill: true,
        fillColor: {
          colors: [
            {
              opacity: 0.1
            },
            {
              opacity: 0.13
            }
          ]
        }
      },
      points: {
        show: true
      }
    },
    {
      label: "Facebook",
      data: facebook,
      lines: {
        show: true,
        lineWidth: 1,
        fill: true,
        fillColor: {
          colors: [
            {
              opacity: 0.1
            },
            {
              opacity: 0.13
            }
          ]
        }
      },
      points: {
        show: true
      }
    }
  ];

  $scope.statsDisplayOptions = {
    grid: {
      hoverable: true
    },
    colors: ["#568A89", "#3276B1"],
    tooltip: true,
    tooltipOpts: {
      //content : "Value <b>$x</b> Value <span>$y</span>",
      defaultTheme: false
    },
    xaxis: {
      ticks: [
        [1, "JAN"],
        [2, "FEB"],
        [3, "MAR"],
        [4, "APR"],
        [5, "MAY"],
        [6, "JUN"],
        [7, "JUL"],
        [8, "AUG"],
        [9, "SEP"],
        [10, "OCT"],
        [11, "NOV"],
        [12, "DEC"],
        [13, "JAN+1"]
      ]
    },
    yaxes: {

    }
  };


  /* Live stats TAB 3: Revenew  */

  var trgt = [[1354586000000, 153], [1364587000000, 658], [1374588000000, 198], [1384589000000, 663], [1394590000000, 801], [1404591000000, 1080], [1414592000000, 353], [1424593000000, 749], [1434594000000, 523], [1444595000000, 258], [1454596000000, 688], [1464597000000, 364]],
    prft = [[1354586000000, 53], [1364587000000, 65], [1374588000000, 98], [1384589000000, 83], [1394590000000, 980], [1404591000000, 808], [1414592000000, 720], [1424593000000, 674], [1434594000000, 23], [1444595000000, 79], [1454596000000, 88], [1464597000000, 36]],
    sgnups = [[1354586000000, 647], [1364587000000, 435], [1374588000000, 784], [1384589000000, 346], [1394590000000, 487], [1404591000000, 463], [1414592000000, 479], [1424593000000, 236], [1434594000000, 843], [1444595000000, 657], [1454596000000, 241], [1464597000000, 341]];

  var targets = {
    label : "Target Profit",
    data : trgt,
    bars : {
      show : true,
      align : "center",
      barWidth : 30 * 30 * 60 * 1000 * 80
    }
  };
  $scope.targetsShow = true;

  $scope.$watch('targetsShow', function(toggle){
    reveniewElementToggle(targets, toggle);
  });


  var actuals = {
    label : "Actual Profit",
    data : prft,
    color : '#3276B1',
    lines : {
      show : true,
      lineWidth : 3
    },
    points : {
      show : true
    }
  };

  $scope.actualsShow = true;

  $scope.$watch('actualsShow', function(toggle){
    reveniewElementToggle(actuals, toggle);
  });

  var signups = {
    label : "Actual Signups",
    data : sgnups,
    color : '#71843F',
    lines : {
      show : true,
      lineWidth : 1
    },
    points : {
      show : true
    }
  };
  $scope.signupsShow = true;

  $scope.$watch('signupsShow', function(toggle){
    reveniewElementToggle(signups, toggle);
  });

  $scope.revenewData = [targets, actuals, signups];

  function reveniewElementToggle(element, toggle){
    if(toggle){
      if($scope.revenewData.indexOf(element) == -1)
        $scope.revenewData.push(element)
    } else {
      $scope.revenewData = _.without($scope.revenewData, element);
    }
  }

  $scope.revenewDisplayOptions = {
    grid : {
      hoverable : true
    },
    tooltip : true,
    tooltipOpts : {
      //content: '%x - %y',
      //dateFormat: '%b %y',
      defaultTheme : false
    },
    xaxis : {
      mode : "time"
    },
    yaxes : {
      tickFormatter : function(val, axis) {
        return "$" + val;
      },
      max : 1200
    }

  };

  // bird eye widget data
  $scope.countriesVisitsData = {
    "US": 4977,
    "AU": 4873,
    "IN": 3671,
    "BR": 2476,
    "TR": 1476,
    "CN": 146,
    "CA": 134,
    "BD": 100
  };

  $scope.events = [];

  // Queriing our events from CalendarEvent resource...
  // Scope update will automatically update the calendar
//   CalendarEvent.query().$promise.then(function (events) {
//     $scope.events = events;
//   });


});
'use strict'

angular.module('app.forms').value('formsCommon', {
  countries: [
    {key: "244", value: "Aaland Islands"},
    {key: "1", value: "Afghanistan"},
    {key: "2", value: "Albania"},
    {key: "3", value: "Algeria"},
    {key: "4", value: "American Samoa"},
    {key: "5", value: "Andorra"},
    {key: "6", value: "Angola"},
    {key: "7", value: "Anguilla"},
    {key: "8", value: "Antarctica"},
    {key: "9", value: "Antigua and Barbuda"},
    {key: "10", value: "Argentina"},
    {key: "11", value: "Armenia"},
    {key: "12", value: "Aruba"},
    {key: "13", value: "Australia"},
    {key: "14", value: "Austria"},
    {key: "15", value: "Azerbaijan"},
    {key: "16", value: "Bahamas"},
    {key: "17", value: "Bahrain"},
    {key: "18", value: "Bangladesh"},
    {key: "19", value: "Barbados"},
    {key: "20", value: "Belarus"},
    {key: "21", value: "Belgium"},
    {key: "22", value: "Belize"},
    {key: "23", value: "Benin"},
    {key: "24", value: "Bermuda"},
    {key: "25", value: "Bhutan"},
    {key: "26", value: "Bolivia"},
    {key: "245", value: "Bonaire, Sint Eustatius and Saba"},
    {key: "27", value: "Bosnia and Herzegovina"},
    {key: "28", value: "Botswana"},
    {key: "29", value: "Bouvet Island"},
    {key: "30", value: "Brazil"},
    {key: "31", value: "British Indian Ocean Territory"},
    {key: "32", value: "Brunei Darussalam"},
    {key: "33", value: "Bulgaria"},
    {key: "34", value: "Burkina Faso"},
    {key: "35", value: "Burundi"},
    {key: "36", value: "Cambodia"},
    {key: "37", value: "Cameroon"},
    {key: "38", value: "Canada"},
    {key: "251", value: "Canary Islands"},
    {key: "39", value: "Cape Verde"},
    {key: "40", value: "Cayman Islands"},
    {key: "41", value: "Central African Republic"},
    {key: "42", value: "Chad"},
    {key: "43", value: "Chile"},
    {key: "44", value: "China"},
    {key: "45", value: "Christmas Island"},
    {key: "46", value: "Cocos (Keeling) Islands"},
    {key: "47", value: "Colombia"},
    {key: "48", value: "Comoros"},
    {key: "49", value: "Congo"},
    {key: "50", value: "Cook Islands"},
    {key: "51", value: "Costa Rica"},
    {key: "52", value: "Cote D'Ivoire"},
    {key: "53", value: "Croatia"},
    {key: "54", value: "Cuba"},
    {key: "246", value: "Curacao"},
    {key: "55", value: "Cyprus"},
    {key: "56", value: "Czech Republic"},
    {key: "237", value: "Democratic Republic of Congo"},
    {key: "57", value: "Denmark"},
    {key: "58", value: "Djibouti"},
    {key: "59", value: "Dominica"},
    {key: "60", value: "Dominican Republic"},
    {key: "61", value: "East Timor"},
    {key: "62", value: "Ecuador"},
    {key: "63", value: "Egypt"},
    {key: "64", value: "El Salvador"},
    {key: "65", value: "Equatorial Guinea"},
    {key: "66", value: "Eritrea"},
    {key: "67", value: "Estonia"},
    {key: "68", value: "Ethiopia"},
    {key: "69", value: "Falkland Islands (Malvinas)"},
    {key: "70", value: "Faroe Islands"},
    {key: "71", value: "Fiji"},
    {key: "72", value: "Finland"},
    {key: "74", value: "France, skypolitan"},
    {key: "75", value: "French Guiana"},
    {key: "76", value: "French Polynesia"},
    {key: "77", value: "French Southern Territories"},
    {key: "126", value: "FYROM"},
    {key: "78", value: "Gabon"},
    {key: "79", value: "Gambia"},
    {key: "80", value: "Georgia"},
    {key: "81", value: "Germany"},
    {key: "82", value: "Ghana"},
    {key: "83", value: "Gibraltar"},
    {key: "84", value: "Greece"},
    {key: "85", value: "Greenland"},
    {key: "86", value: "Grenada"},
    {key: "87", value: "Guadeloupe"},
    {key: "88", value: "Guam"},
    {key: "89", value: "Guatemala"},
    {key: "241", value: "Guernsey"},
    {key: "90", value: "Guinea"},
    {key: "91", value: "Guinea-Bissau"},
    {key: "92", value: "Guyana"},
    {key: "93", value: "Haiti"},
    {key: "94", value: "Heard and Mc Donald Islands"},
    {key: "95", value: "Honduras"},
    {key: "96", value: "Hong Kong"},
    {key: "97", value: "Hungary"},
    {key: "98", value: "Iceland"},
    {key: "99", value: "India"},
    {key: "100", value: "Indonesia"},
    {key: "101", value: "Iran (Islamic Republic of)"},
    {key: "102", value: "Iraq"},
    {key: "103", value: "Ireland"},
    {key: "104", value: "Israel"},
    {key: "105", value: "Italy"},
    {key: "106", value: "Jamaica"},
    {key: "107", value: "Japan"},
    {key: "240", value: "Jersey"},
    {key: "108", value: "Jordan"},
    {key: "109", value: "Kazakhstan"},
    {key: "110", value: "Kenya"},
    {key: "111", value: "Kiribati"},
    {key: "113", value: "Korea, Republic of"},
    {key: "114", value: "Kuwait"},
    {key: "115", value: "Kyrgyzstan"},
    {key: "116", value: "Lao People's Democratic Republic"},
    {key: "117", value: "Latvia"},
    {key: "118", value: "Lebanon"},
    {key: "119", value: "Lesotho"},
    {key: "120", value: "Liberia"},
    {key: "121", value: "Libyan Arab Jamahiriya"},
    {key: "122", value: "Liechtenstein"},
    {key: "123", value: "Lithuania"},
    {key: "124", value: "Luxembourg"},
    {key: "125", value: "Macau"},
    {key: "127", value: "Madagascar"},
    {key: "128", value: "Malawi"},
    {key: "129", value: "Malaysia"},
    {key: "130", value: "Maldives"},
    {key: "131", value: "Mali"},
    {key: "132", value: "Malta"},
    {key: "133", value: "Marshall Islands"},
    {key: "134", value: "Martinique"},
    {key: "135", value: "Mauritania"},
    {key: "136", value: "Mauritius"},
    {key: "137", value: "Mayotte"},
    {key: "138", value: "Mexico"},
    {key: "139", value: "Micronesia, Federated States of"},
    {key: "140", value: "Moldova, Republic of"},
    {key: "141", value: "Monaco"},
    {key: "142", value: "Mongolia"},
    {key: "242", value: "Montenegro"},
    {key: "143", value: "Montserrat"},
    {key: "144", value: "Morocco"},
    {key: "145", value: "Mozambique"},
    {key: "146", value: "Myanmar"},
    {key: "147", value: "Namibia"},
    {key: "148", value: "Nauru"},
    {key: "149", value: "Nepal"},
    {key: "150", value: "Netherlands"},
    {key: "151", value: "Netherlands Antilles"},
    {key: "152", value: "New Caledonia"},
    {key: "153", value: "New Zealand"},
    {key: "154", value: "Nicaragua"},
    {key: "155", value: "Niger"},
    {key: "156", value: "Nigeria"},
    {key: "157", value: "Niue"},
    {key: "158", value: "Norfolk Island"},
    {key: "112", value: "North Korea"},
    {key: "159", value: "Northern Mariana Islands"},
    {key: "160", value: "Norway"},
    {key: "161", value: "Oman"},
    {key: "162", value: "Pakistan"},
    {key: "163", value: "Palau"},
    {key: "247", value: "Palestinian Territory, Occupied"},
    {key: "164", value: "Panama"},
    {key: "165", value: "Papua New Guinea"},
    {key: "166", value: "Paraguay"},
    {key: "167", value: "Peru"},
    {key: "168", value: "Philippines"},
    {key: "169", value: "Pitcairn"},
    {key: "170", value: "Poland"},
    {key: "171", value: "Portugal"},
    {key: "172", value: "Puerto Rico"},
    {key: "173", value: "Qatar"},
    {key: "174", value: "Reunion"},
    {key: "175", value: "Romania"},
    {key: "176", value: "Russian Federation"},
    {key: "177", value: "Rwanda"},
    {key: "178", value: "Saint Kitts and Nevis"},
    {key: "179", value: "Saint Lucia"},
    {key: "180", value: "Saint Vincent and the Grenadines"},
    {key: "181", value: "Samoa"},
    {key: "182", value: "San Marino"},
    {key: "183", value: "Sao Tome and Principe"},
    {key: "184", value: "Saudi Arabia"},
    {key: "185", value: "Senegal"},
    {key: "243", value: "Serbia"},
    {key: "186", value: "Seychelles"},
    {key: "187", value: "Sierra Leone"},
    {key: "188", value: "Singapore"},
    {key: "189", value: "Slovak Republic"},
    {key: "190", value: "Slovenia"},
    {key: "191", value: "Solomon Islands"},
    {key: "192", value: "Somalia"},
    {key: "193", value: "South Africa"},
    {key: "194", value: "South Georgia &amp; South Sandwich Islands"},
    {key: "248", value: "South Sudan"},
    {key: "195", value: "Spain"},
    {key: "196", value: "Sri Lanka"},
    {key: "249", value: "St. Barthelemy"},
    {key: "197", value: "St. Helena"},
    {key: "250", value: "St. Martin (French part)"},
    {key: "198", value: "St. Pierre and Miquelon"},
    {key: "199", value: "Sudan"},
    {key: "200", value: "Suriname"},
    {key: "201", value: "Svalbard and Jan Mayen Islands"},
    {key: "202", value: "Swaziland"},
    {key: "203", value: "Sweden"},
    {key: "204", value: "Switzerland"},
    {key: "205", value: "Syrian Arab Republic"},
    {key: "206", value: "Taiwan"},
    {key: "207", value: "Tajikistan"},
    {key: "208", value: "Tanzania, United Republic of"},
    {key: "209", value: "Thailand"},
    {key: "210", value: "Togo"},
    {key: "211", value: "Tokelau"},
    {key: "212", value: "Tonga"},
    {key: "213", value: "Trinidad and Tobago"},
    {key: "214", value: "Tunisia"},
    {key: "215", value: "Turkey"},
    {key: "216", value: "Turkmenistan"},
    {key: "217", value: "Turks and Caicos Islands"},
    {key: "218", value: "Tuvalu"},
    {key: "219", value: "Uganda"},
    {key: "220", value: "Ukraine"},
    {key: "221", value: "United Arab Emirates"},
    {key: "222", value: "United Kingdom"},
    {key: "223", value: "United States"},
    {key: "224", value: "United States Minor Outlying Islands"},
    {key: "225", value: "Uruguay"},
    {key: "226", value: "Uzbekistan"},
    {key: "227", value: "Vanuatu"},
    {key: "228", value: "Vatican City State (Holy See)"},
    {key: "229", value: "Venezuela"},
    {key: "230", value: "Viet Nam"},
    {key: "231", value: "Virgin Islands (British)"},
    {key: "232", value: "Virgin Islands (U.S.)"},
    {key: "233", value: "Wallis and Futuna Islands"},
    {key: "234", value: "Western Sahara"},
    {key: "235", value: "Yemen"},
    {key: "238", value: "Zambia"},
    {key: "239", value: "Zimbabwe"}
  ],
  validateOptions: {
    errorElement: 'em',
    errorClass: 'invalid',
    highlight: function(element, errorClass, validClass) {
      $(element).addClass(errorClass).removeClass(validClass);
      $(element).parent().addClass('state-error').removeClass('state-success');

    },
    unhighlight: function(element, errorClass, validClass) {
      $(element).removeClass(errorClass).addClass(validClass);
      $(element).parent().removeClass('state-error').addClass('state-success');
    },
    errorPlacement : function(error, element) {
      error.insertAfter(element.parent());
    }
  }
});