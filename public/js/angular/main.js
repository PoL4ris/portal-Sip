
var app = angular.module('app', ['ngRoute', "xeditable", "ngAnimate", "ngSanitize"]);

















/**
 * Configure the Routes
 */
app.config(['$routeProvider', function ($routeProvider) {
  $routeProvider
    // Home
    .when("/", {templateUrl: "/views/admin.html", controller:"admin"})
    .when("/admin", {templateUrl: "/views/admin.html", controller:"admin"})




    .when('#/', {templateUrl: '/angularviews/partials/page-home.html', controller: 'mainController'})
    .when('#/about', {templateUrl: '/angularviews/partials/page-about.html', controller: 'aboutController'})
    .when('#/contact', {templateUrl: '/angularviews/partials/page-contact.html', controller: 'contactController'})



    .when("/buildingdash", {templateUrl: "/views/building/dashboard.html"})
    .when("/buildings", {templateUrl: "/views/building/buildings.html"})
    .when("/adminusers", {templateUrl: "/angularviews/partials/home.html", controller: "adminusers"})








    // Blog
    .when("/blog", {templateUrl: "/angularviews/partials/blog.html", controller: "BlogCtrl"})
    .when("/blog/post", {templateUrl: "/angularviews/partials/blog_item.html", controller: "BlogCtrl"})
    // else 404
    .otherwise("/404", {templateUrl: "/angularviews/partials/404.html", controller: "PageCtrl"});


}]);



app.run(function(editableOptions) {
  editableOptions.theme = 'bs3'; // bootstrap3 theme. Can be also 'bs2', 'default'
});


/* MENU */
app.controller('menuController', ['$scope', '$http', function($scope, $http){
  $scope.SiteMenu = [];
  $http.get('menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
}]);


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

