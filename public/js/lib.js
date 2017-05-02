



app.controller('warpolController',                  function($scope, $http){

//   var CountUp = function(target, startVal, endVal, decimals, duration, options){}

  $http.get("dummyRouteController")
    .then(function (response) {
      var data = response.data;
      new CountUp('t1', 0, data['commercial'], 0, 3).start();
      new CountUp('t2', 0, data['retail'], 0, 3).start();
      new CountUp('t3', 0, data['tickets'], 0, 3).start();
    });

});


