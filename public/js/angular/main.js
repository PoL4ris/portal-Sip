
var app = angular.module('app', ['ngRoute']);

/**
 * Configure the Routes
 */
app.config(['$routeProvider', function ($routeProvider) {
  $routeProvider
    // Home
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

app.controller('adminusers', function($scope, $http) {
  $http.get("adminusers")
    .then(function (response) {
      console.log(response.data);
      $scope.users = response.data;
    });
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

