var app = angular.module('app', ["ngRoute"]);
// var app = angular.module('app', ["ngRoute","xeditable","ngAnimate","ngSanitize","cgNotify","ui.bootstrap","ngMaterial","ngTable","chart.js","nvd3","nvd3ChartDirectives"]);

/**
 * Configure the Routes
 */

app.config(['$routeProvider', function ($routeProvider) {
  $routeProvider
    .when("/",              {templateUrl: "/views/billing.html"})
    .when("/buildings",     {templateUrl: "/views/dummy.html"})
//     .when("/buildings",     {templateUrl: "/views/building/buildings.html"})
//     .when("/admin",         {templateUrl: "/views/admin.html", controller:"admin"})
//     .when('#/',             {templateUrl: '/angularviews/partials/page-home.html', controller: 'mainController'})
//     .when('#/about',        {templateUrl: '/angularviews/partials/page-about.html', controller: 'aboutController'})
//     .when('#/contact',      {templateUrl: '/angularviews/partials/page-contact.html', controller: 'contactController'})
//     .when("/buildingdash",  {templateUrl: "/views/building/dashboard.html"})
//     .when("/support",       {templateUrl: "/views/support.html"})
//     .when("/adminusers",    {templateUrl: "/angularviews/partials/home.html", controller: "adminusers"})
//     .when("/customer",      {templateUrl: "/views/customers.html"})
//     .when("/network",       {templateUrl: "/views/allNetwork.html"})
//     .when("/userprofile",   {templateUrl: "/views/userprofile.html", controller: 'userProfileController'})
//     .when("/calendar",      {templateUrl: "/views/v2Test.html"})
//
//     .when("/clients",       {templateUrl: "/views/404.html"})
    .otherwise("/404", {templateUrl: "/angularviews/partials/404.html", controller: "PageCtrl"});
}]);