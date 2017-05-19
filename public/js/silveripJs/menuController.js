//Menu Controller
app.controller('menuController',                    function($scope, $http){
  $http.get('/menumaker').then(function (response){
    console.log(response.data);
    $scope.SiteMenu = response.data;
  }), function (error){
    alert('Error');
  }
});