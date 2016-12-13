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
        url: '/customers',
        data: {
          title: 'Customers'
        },
        views: {
          "content@app": {
            templateUrl: '/views/customers.html',
            controller: 'customerController'
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
          templateUrl: '/views/dummy.html',
          controller: 'networkController as showCase'
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