angular.module('app.buildings', ['ui.router'])
.config(function ($stateProvider) {
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