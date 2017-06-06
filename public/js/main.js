angular.module('app.buildings',     ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.buildings', {
      url:  '/buildings?{id:int}',
      data: {
        title: 'Buildings'
      },
      views: {
        "content@app": {
          templateUrl: '/views/building/building.html',
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
          templateUrl: '/views/customersHome.html',
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
          templateUrl: '/views/customers.html',
          controller:  'customerController',
        },
        "silveriptool": {
          templateUrl: '/views/silverip-tool.html',
          controller:  'customerController',
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute = 'customers';
          generalService.stateRoute  = 'customers';
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
          templateUrl: '/views/allNetwork.html',
          controller:  'networkController as datatables'
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
angular.module('app.support',       ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.support', {
      url:  '/support',
      data: {
        title: 'Support'
      },
      views: {
        "content@app": {
          templateUrl: '/views/support/support.html',
          controller:  'supportController'
        }
      },
      resolve: {
        scripts: function (lazyScript, customerService, generalService) {
          customerService.stateRoute = 'support';
          generalService.stateRoute  = 'support';
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.calendar',      ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.calendar', {
      url:  '/calendar',
      data: {
        title: 'calendar'
      },
      views: {
        "content@app": {
//           templateUrl: '/views/test.html'
          templateUrl: '/views/v2Test.html'
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
          templateUrl: '/views/reports.html',
          controller:  'reportController',
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
angular.module('app.admin',         ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.admin', {
      url:  '/admin',
      data: {
        title: 'Admin'
      },
      views: {
        "content@app": {
          templateUrl: '/views/admin/admin.html',
          controller:  'adminController'
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
angular.module('app.warpol',        ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.warpol', {
      url:  '/warpol',
      data: {
        title: 'Warpol'
      },
      views: {
        "content@app": {
          templateUrl: '/views/warpol.html',
//          controller: function($scope){
//          },
          controller:  'warpolController',
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
          templateUrl: '/views/dummyapp.html',
//                    controller: 'warpolController',
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
    sideBarFlag: true,
    rightView: false
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
    exist: true
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