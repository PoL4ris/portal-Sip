angular.module('app.buildings', ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.buildings', {
      url: '/buildings',
      data: {
        title: 'Buildings'
      },
      views: {
        "content@app": {
          templateUrl: '/views/building/building.html',
          controller: 'buildingCtl'
        },
        "silveriptool": {
          templateUrl: '/views/test.html',
          controller: 'buildingSideController'
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
            templateUrl: '/views/test.html',
            controller: 'customerController',

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
        scripts: function(lazyScript){
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
angular.module('app.admin'  , ['ui.router']).config(function ($stateProvider) {
  $stateProvider
    .state('app.admin', {
      url: '/admin',
      data: {
        title: 'Admin'
      },
      views: {
        "content@app": {
//           templateUrl: '/views/dummy.html',
        },
        'silveriptool':{
          templateUrl: '/views/test.html',
          controller: 'sidebarController',
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


