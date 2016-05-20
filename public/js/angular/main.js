
var app = angular.module('app', ['ngRoute', "xeditable", "ngAnimate", "ngSanitize", "cgNotify", "ui.bootstrap", "ngMaterial"]);

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
    .when("/support", {templateUrl: "/views/support.html"})
    .when("/adminusers", {templateUrl: "/angularviews/partials/home.html", controller: "adminusers"})
    .when("/customer", {templateUrl: "/views/customer.html"})
    .when("/testView", {templateUrl: "/views/test.html"})


    // else 404
    .otherwise("/404", {templateUrl: "/angularviews/partials/404.html", controller: "PageCtrl"});


}]);

app.run(function(editableOptions) {
  editableOptions.theme = 'bs3';
});









/**
 * Controls the Blog
 */
app.controller('gral', function (/* $scope, $location, $http */) {

  warpol.getScript("/js/portal/lib.js");
  warpol.getScript("/js/portal/js.js");
  warpol.getScript("/js/portal/exec.js", function (){alert('loadedd');});

});