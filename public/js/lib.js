



app.controller('warpolController',                  function($scope, $http){


  $scope.warpolString = 'warpolController --> This is not the Santa Muerte';


  $scope.sendCustomerMail = function(){

    $http.get("sendCustomerMail")
      .then(function (response) {
        $scope.sendCMail = response.data;
      });
  }



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


