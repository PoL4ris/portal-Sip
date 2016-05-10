<!doctype html>
<html ng-app>
<head>
  <meta charset="utf-8">
  <title>Top Animation</title>
  <script>document.write('<base href="' + document.location + '" />');</script>
  <style>
    .animate-enter,
    .animate-leave
    {
      -webkit-transition: 400ms cubic-bezier(0.250, 0.250, 0.750, 0.750) all;
      -moz-transition: 400ms cubic-bezier(0.250, 0.250, 0.750, 0.750) all;
      -ms-transition: 400ms cubic-bezier(0.250, 0.250, 0.750, 0.750) all;
      -o-transition: 400ms cubic-bezier(0.250, 0.250, 0.750, 0.750) all;
      transition: 400ms cubic-bezier(0.250, 0.250, 0.750, 0.750) all;
      position: relative;
      display: block;
      overflow: hidden;
      text-overflow: clip;
      white-space:nowrap;
    }

    .animate-leave.animate-leave-active,
    .animate-enter {
      opacity: 0;
      width: 0px;
      height: 0px;
    }

    .animate-enter.animate-enter-active,
    .animate-leave {
      opacity: 1;
      width: 150px;
      height: 30px;
    }
  </style>
  <link href="//netdna.bootstrapcdn.com/twitter-bootstrap/2.3.1/css/bootstrap-combined.min.css" rel="stylesheet">
  <script src="http://code.angularjs.org/1.1.5/angular.js"></script>
</head>
<body ng-init="names=['Igor Minar', 'Brad Green', 'Dave Geddes', 'Naomi Black', 'Greg Weber', 'Dean Sofer', 'Wes Alvaro', 'John Scott', 'Daniel Nadasi'];">
<div class="well" style="margin-top: 30px; width: 200px; overflow: hidden;">
  <form class="form-search">
    <div class="input-append">
      <input type="text" ng-model="search" class="search-query" style="width: 80px">
      <button type="submit" class="btn">Search</button>
    </div>
    <ul class="nav nav-pills nav-stacked">
      <li ng-animate="'animate'" ng-repeat="name in names | filter:search">
        <a href="#"> </a>
      </li>
    </ul>
  </form>
</div>
</body>
</html>
