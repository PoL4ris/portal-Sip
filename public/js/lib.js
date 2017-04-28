



app.controller('warpolController',                  function($scope, $http){


  $scope.warpolString = 'warpolController --> This is not the Santa Muerte';


  //SEARCH
  $scope.cDashboardSearch                = function (){
    if(!this.genericSearch || this.genericSearch == ''){
      $scope.cDashboardSearchResult = null;
      return;
    }

    var query = {'querySearch' : this.genericSearch};

    $http.get("customersSearch", {params:query})
      .then(function (response) {
        $scope.cDashboardSearchResult = response.data;
      });

    return;
  };




    $http.get("supportTest")
      .then(function (response) {
        $scope.war = response.data;
      });



/*
--->dashboard numeros anim

 var options = {
 useEasing : true,
 useGrouping : true,
 separator : ',',
 decimal : '.',
 prefix : '',
 suffix : ''
 };


  $http.get("dummyRouteController")
    .then(function (response) {
      console.log(response.data);
      var data = response.data;
      new CountUp('testCountdos', 0, data.open_tickets, 0, 3).start();

    });
* */


});


