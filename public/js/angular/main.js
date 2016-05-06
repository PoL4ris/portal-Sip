
var app = angular.module('app', ['ngRoute']);

/**
 * Configure the Routes
 */
app.config(['$routeProvider', function ($routeProvider) {
  $routeProvider
    // Home
    .when("/buildingdash", {templateUrl: "/views/building/dashboard.html"})
    .when("/:buildings*", {templateUrl: "/views/building/buildings.html", controller:"building"})
//     .when("/", {template: "partials/home.html"})
//     .when("/", {templateUrl: "/angularviews/partials/dummy.html", controller: "PageCtrl"})
    .when("/adminusers", {templateUrl: "/angularviews/partials/home.html", controller: "adminusers"})
//     .when("/dummy", {templateUrl: "/angularviews/partials/dummy.html", controller: 'customersCtrl'})
    // Pages
    .when("/about", {templateUrl: "/angularviews/partials/about.html", controller: "PageCtrl"})
    .when("/faq", {templateUrl: "/angularviews/partials/faq.html", controller: "PageCtrl"})
    .when("/pricing", {templateUrl: "/angularviews/partials/pricing.html", controller: "PageCtrl"})
    .when("/services", {templateUrl: "/angularviews/partials/services.html", controller: "PageCtrl"})
    .when("/contact", {templateUrl: "/angularviews/partials/contact.html", controller: "PageCtrl"})
    // Blog
    .when("/blog", {templateUrl: "/angularviews/partials/blog.html", controller: "BlogCtrl"})
    .when("/blog/post", {templateUrl: "/angularviews/partials/blog_item.html", controller: "BlogCtrl"})
    // else 404
    .otherwise("/404", {templateUrl: "/angularviews/partials/404.html", controller: "PageCtrl"});


}]);


/**
 * MENU
 */
app.controller('menuController', ['$scope', '$http', function($scope, $http){
  $scope.SiteMenu = [];
  $http.get('menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
}]);



app.controller('preNextArrows', ['$scope', '$http', function($scope, $http)
{
  console.log('inside');
  $scope.buildingsList = function (position)
  {
    //Get Data from current Info
    var idDivResult    = warpol('#bldlist-result').attr('id');
    var offset         = parseInt(warpol('.' + idDivResult + '-limits-' + position).attr('offset'));
    var limit          = parseInt(warpol('.' + idDivResult + '-limits-' + position).attr('limit'));
    //Math var operations.
    var a              = parseInt(offset);
    var b              = parseInt(limit);
    //Back Arrow empty
    if (position == 0 && offset <= 0)
      return;
    //Solve correct LIMIT OFFSET info to request
    if(position == 1)
    {
      offset = b;
      limit = b + (b - a);
    }
    else
    {
      limit = a;
      offset = a - (b - a);
    }
    //Case result is wrong
    if (offset < 0 || limit <= 0)
      return;
    //Main info to do request
    var query = {"offset": offset, "limit": limit, "position": position};
    //AJAX request
    warpol.ajax(
      {
        type: "GET",
        url: "buildingsList",
        data: query,
        success: function (data) {
          if (data.length === 0 || !data.trim())
            return;
          //Result JsonParser to use data
          var resultData = jQuery.parseJSON(data);

          warpol('#' + idDivResult).html('');
          warpol.each(resultData, function (i, item) {
            warpol('#' + idDivResult).append('<p>' + item.id + item.name + '</p>');
          });
          //Rewrite LIMIT OFFSET fields for new calcRequest
          warpol('.' + idDivResult + '-limits-' + 0).attr('offset',offset);
          warpol('.' + idDivResult + '-limits-' + 0).attr('limit',limit);
          warpol('.' + idDivResult + '-limits-' + 1).attr('offset',offset);
          warpol('.' + idDivResult + '-limits-' + 1).attr('limit',limit);
          warpol('#' + idDivResult).scrollTop(0);
        }
      }
    );
  }
}]);

app.controller('bldGralList', ['$scope', '$http', function($scope, $http){
  $scope.SiteMenu = [];
  $http.get('buildings').then(function (data){
    $scope.bldData = data.data;

    warpol('#ol-left-btn').attr('offset', $scope.bldData.offset);
    warpol('#ol-left-btn').attr('limit', $scope.bldData.limit);
    warpol('#ol-right-btn').attr('offset', $scope.bldData.offset);
    warpol('#ol-right-btn').attr('limit', $scope.bldData.limit);
//     $scope.parentmethod('gral');
  }), function (error){
    alert('Error');
  }

}]);

// app.controller('testController', alert('a'));

app.controller('adminusers', function($scope, $http) {
  $http.get("adminusers")
    .then(function (response) {
//       console.log(response.data);
      $scope.users = response.data;
    });
});


/**
 * Controls the Blog
 */
app.controller('gral', function (/* $scope, $location, $http */) {

  warpol.getScript("/js/portal/lib.js");
  warpol.getScript("/js/portal/js.js");
  warpol.getScript("/js/portal/exec.js", function (){alert('loadedd');});

});


/**
 * Controls the Blog
 */
app.controller('BlogCtrl', function (/* $scope, $location, $http */) {
  console.log("Blog Controller reporting for duty.");
});

/**
 * Controls all other Pages
 */
app.controller('PageCtrl', function (/* $scope, $location, $http */) {
  console.log("Page Controller reporting for duty.");

  // Activates the Carousel
  $('.carousel').carousel({
    interval: 5000
  });

  // Activates Tooltips for Social Links
  $('.tooltip-social').tooltip({
    selector: "a[data-toggle=tooltip]"
  })
});

