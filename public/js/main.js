angular.module('app.buildings',     ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.buildings', {
      url:  '/buildings?{id:int}',
      data: {
        title: 'Buildings'
      },
      views: {
        "content@app": {
          templateUrl: '/views/building/building.html?'+appConfig.appCacheClear,
          controller:  'buildingCtl'
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute = 'buildings';
          generalService.stateRoute  = 'buildings';

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.customershome', ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.customershome', {
      url:  '/customershome',
      data: {
        title: 'Customer Home'
      },
      views: {
        "content@app": {
          templateUrl: '/views/customersHome.html?'+appConfig.appCacheClear,
          controller:  'customersHomeController'
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute = 'customershome';
          generalService.stateRoute  = 'customershome';

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.customers',     ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.customers', {
      url:  '/customers?{id:int}',
      data: {
        title: 'Customers'
      },
      views: {
        "content@app": {
          templateUrl: '/views/customers.html?'+appConfig.appCacheClear,
          controller:  'customerController',
        },
        "silveriptool": {
          templateUrl: '/views/customer/activityLog.html?'+appConfig.appCacheClear,
          controller:  'customerController',
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute  = 'customers';
          generalService.stateRoute   = 'customers';

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.network',       ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.network', {
      url:  '/network',
      data: {
        title: 'Network'
      },
      views: {
        "content@app": {
          templateUrl: '/views/allNetwork.html?'+appConfig.appCacheClear,
          controller:  'networkController as datatables'
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.support',       ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.support', {
      url:  '/support',
      data: {
        title: 'Support'
      },
      views: {
        "content@app": {
          templateUrl: '/views/support/support.html?'+appConfig.appCacheClear,
          controller:  'supportController'
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute  = 'support';
          generalService.stateRoute   = 'support';

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
//NOT IN USE ->CALENDAR CONTROLLER
angular.module('app.calendar',      ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.calendar', {
      url:  '/calendar',
      data: {
        title: 'calendar'
      },
      views: {
        "content@app": {
//           templateUrl: '/views/billing.html'
          templateUrl: '/views/v2Test.html?'+appConfig.appCacheClear,
        }
      },
      resolve: {
        scripts: function (lazyScript) {
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.reports',       ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.reports', {
      url:  '/reports?{code:string}',
      data: {
        title: 'reports'
      },
      views: {
        "content@app": {
          templateUrl: '/views/reports.html?'+appConfig.appCacheClear,
          controller:  'reportController',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.admin',         ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.admin', {
      url:  '/admin',
      data: {
        title: 'Admin'
      },
      views: {
        "content@app": {
          templateUrl: '/views/admin/admin.html?'+appConfig.appCacheClear,
          controller:  'adminController'
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.walkthrough',   ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.walkthrough', {
      url:  '/walkthrough',
      data: {
        title: 'Walkthrough'
      },
      views: {
        "content@app": {
          templateUrl: '/views/mobile/walkthrough.html?'+appConfig.appCacheClear,
          controller:  'walkthroughController as app',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.warpol',        ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.warpol', {
      url:  '/warpol',
      data: {
        title: 'Warpol'
      },
      views: {
        "content@app": {
          templateUrl: '/views/warpol.html?'+appConfig.appCacheClear,
          controller:  'dropZoneController as app',
        }
      },
      resolve: {
        scripts: function (lazyScript) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.dummyapp',      ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.dummyapp', {
      url:  '/dummyapp',
      data: {
        title: 'dummyapp'
      },
      views: {
        "content@app": {
          templateUrl: '/views/dummyapp.html?'+appConfig.appCacheClear,
          controller: 'tabsController',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.charges',       ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.charges', {
      url:  '/charges',
      data: {
        title: 'Charges'
      },
      views: {
        "content@app": {
          templateUrl: '/views/billing/billing.html?'+appConfig.appCacheClear,
          controller: 'chargesController',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.tabs',          ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.tabs', {
      url:  '/tabs',
      data: {
        title: 'Tabs'
      },
      views: {
        "content@app": {
          templateUrl: '/views/warp.html?'+appConfig.appCacheClear,
          controller:  'tabsController',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {
          generalService.stateRoute   = 'tabs';
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.newcustomer',   ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.newcustomer', {
      url:  '/newcustomer',
      data: {
        title: 'newcustomer'
      },
      views: {
        "content@app": {
          templateUrl: '/views/newcustomer.html?'+appConfig.appCacheClear,
          controller: 'newcustomerAppController',
        }
      },
      resolve: {
        scripts: function (lazyScript, generalService) {

          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.test',          ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.test', {
      url:  '/test',
      data: {
        title: 'Test'
      },
      views: {
        "content@app": {
          templateUrl: '/views/test.html?'+appConfig.appCacheClear,
        }
      },
      resolve: {
        scripts: function (lazyScript) {
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);

        }
      }
    })
});



angular.module('app.tech-schedule', ['ui.router']).config(function ($stateProvider) {
    $stateProvider
        .state('app.tech-schedule', {
            url: '/tech-schedule',
            data: {
                title: 'tech-schedule'
            },
            views: {
                "content@app": {
                    templateUrl: '/views/support/tech-scheduler.html',
                    controller: 'techschedulercontroller'
                }
            },
            resolve: {
                scripts: function (lazyScript) {
                    return lazyScript.register([
                        '/js/smart/build/vendor.ui.js'
                    ]);
                }
            }
        })
});
angular.module('app.tech-appointments', ['ui.router']).config(function ($stateProvider) {
    $stateProvider
        .state('app.tech-appointments', {
            url: '/tech-appointments',
            data: {
                title: 'tech-appointments'
            },
            views: {
                "content@app": {
                    templateUrl: '/views/support/tech-appointments.html',
                    controller: 'tech-appointments'
                }
            },
            resolve: {
                scripts: function (lazyScript) {
                    return lazyScript.register([
                        '/js/smart/build/vendor.ui.js'
                    ]);
                }
            }
        })
});

app.factory('customerService',  function () {
  return {
    exist: true,
//    sideBarFlag: true,
    rightView: false,
    statusArrayConstant : constArray,//remove.
    tabs: {},
    customerArray : {},

  };
});
app.factory('buildingService',  function () {
  return {
    exist: true
  };
});
app.factory('supportService',   function () {
  return {
    exist: true
  };
});
app.factory('adminService',     function () {
  return {
    existeToken: $('#auth-user').attr('tmpTokenTest')
  };
});
app.factory('generalService',   function () {
  return {
    exist: true,
    statusArrayConstant : constArray,
    sideBarFlag: false,
    rightView: false,
    cacheClear: appConfig.appCacheClear,
  };
});
































//
//Stronger Service Dif Solution.
// app.factory('myFactory', function() {
//
//   var exist = true;
//   return {
//     getExist : function() {
//       return exist;
//     },
//     setExist : function(ex){
//       exist = ex;
//     }
//   }
// });