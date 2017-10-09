app.controller('libController', function ($scope, $http) {

//  console.log('dashboardController');

    $http.get("getMainDashboard")
        .then(function (response) {
            $scope.dash1 = true;
            var data = response.data;
            new CountUp('t1', 0, data['commercial'], 0, 3).start();
            new CountUp('t2', 0, data['retail'], 0, 3).start();
            new CountUp('t3', 0, data['tickets'], 0, 3).start();
            new CountUp('t4', 0, data['avgHour'], 0, 3).start();
            new CountUp('t5', 0, data['avgDay'], 0, 3).start();
        });

    var today = new Date();
    var dateRequest = ((today.getMonth() + 1) + '/' + today.getDate() + '/' + today.getFullYear());

    $http.get("getCalendarDashboard", {params: {'date': dateRequest}})
        .then(function (response) {
            $scope.calendarData = true;
            var data = response.data;
            new CountUp('t6', 0, data['total_events'], 0, 3).start();
            new CountUp('t7', 0, data['complete'], 0, 3).start();
            new CountUp('t8', 0, data['pending'], 0, 3).start();
            new CountUp('t9', 0, data['onsite'], 0, 3).start();
        });


});



//Tabs
app.controller('dummyAppController', function ($scope, $http, customerService, generalService) {

    $scope.addToTabArray = function (id) {

        if (customerService.customerArray[id])
            return;

        customerService.customerArray[id] = id;
        customerService.lastRequestedId = id;
        $scope.tabsArray = customerService.customerArray;
        generalService.rightView = true;
        $scope.customerServiceData = customerService.tabs;

    };

    $scope.removeFromTabArray = function (id) {

        delete customerService.customerArray[id];
        $scope.tabsArray = customerService.customerArray;
        delete customerService.tabs[id];
    }


    $scope.createTab = function ($event) {
        $scope.addToTabArray(this.customerData.id);
        $event.stopPropagation();
    }


});




































