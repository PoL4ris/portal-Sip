angular.module('app.buildings', ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.buildings', {
      url: '/buildings?{id:int}',
      data: {
        title: 'Buildings'
      },
      views: {
        "content@app": {
          templateUrl: '/views/building/building.html',
          controller: 'buildingCtl'
        }
      },
      resolve: {
        scripts: function(lazyScript, customerService){
          customerService.stateRoute = 'buildings';
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.customers', ['ui.router']).config(function ($stateProvider) {
    $stateProvider
      .state('app.customers', {
        url: '/customers?{id:int}',
        data: {
          title: 'Customers'
        },
        views: {
          "content@app": {
            templateUrl: '/views/customers.html',
            controller: 'customerController',
          },
          "silveriptool": {
            templateUrl: '/views/silverip-tool.html',
            controller: 'customerController',
          }
        },
        resolve: {
          scripts: function(lazyScript, customerService){
            customerService.stateRoute = 'customers';
            return lazyScript.register([
              '/js/smart/build/vendor.ui.js'
            ]);
          }
        }
      })
  });
angular.module('app.network'  , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.network', {
      url: '/network',
      data: {
        title: 'Network'
      },
      views: {
        "content@app": {
          templateUrl: '/views/allNetwork.html',
          controller: 'networkController as datatables'
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.support'  , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.support', {
      url: '/support',
      data: {
        title: 'Support'
      },
      views: {
        "content@app": {
          templateUrl: '/views/support/support.html',
          controller : 'supportController'
        }
      },
      resolve: {
        scripts: function(lazyScript, customerService){
          customerService.stateRoute = 'support';
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.calendar' , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.calendar', {
      url: '/calendar',
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
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});
angular.module('app.admin'    , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.admin', {
      url: '/admin',
      data: {
        title: 'Admin'
      },
      views: {
        "content@app": {
          templateUrl: '/views/admin/admin.html',
          controller : 'adminController'
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});


angular.module('app.warpol'    , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.warpol', {
      url: '/warpol',
      data: {
        title: 'Warpol'
      },
      views: {
        "content@app": {
          templateUrl: '/views/warpol.html',
          controller : 'warpolController'
        }
      },
      resolve: {
        scripts: function(lazyScript){
          return lazyScript.register([
            '/js/smart/build/vendor.ui.js'
          ]);
        }
      }
    })
});



app.factory('customerService', function() {
  return {
    exist : true,
    sideBarFlag : true,
    rightView : false
  };
});
app.factory('buildingService', function() {
  return {
    exist : true
  };
});
app.factory('supportService', function() {
  return {
    exist : true
  };
});

app.factory('adminService', function() {
  return {
    existeToken : $('#auth-user').attr('tmpTokenTest')
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