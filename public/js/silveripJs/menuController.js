//Menu Controller
app.controller('menuController',                    function($scope, $http){
  $http.get('/menumaker').then(function (response){
    $scope.SiteMenu = response.data;
  }), function (error){
    alert('Error');
  }
});