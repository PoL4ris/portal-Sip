app.controller('warpolController', function ($scope, $http, customerService) {

  console.log('wapolcontroller');

  var canvas  = document.getElementById('revenueChart');
  var context = canvas.getContext('2d');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }

  $http.get("getBuildingsList", {params: {'query': null}})
    .then(function (response) {
      $scope.bldListResult = response.data;
    });

  $scope.executeChartReport   = function (codeId) {
    $http.get("getDisplayRetailRevenue", {params: {'code': codeId}})
      .then(function (response) {
        $scope.data         = response.data.data;
        $scope.latestMonth  = response.data.latestMonth;
        $scope.months       = response.data.months;
        $scope.shortname    = response.data.shortname;
        $scope.route        = response.data.route;
        $scope.data_points  = response.data.data_points;
        $scope.renderAll();
      });
  };

  $scope.renderAll            = function () {

    var date      = $scope.latestMonth;
    var shortname = $scope.shortname;
    var root_url  = $scope.route;

    // BAR CHART
    var barOptions = {
      scaleBeginAtZero:     true,
      scaleShowGridLines:   true,
      scaleGridLineColor:   "rgba(0,0,0,.05)",
      scaleGridLineWidth:   1,
      barShowStroke:        true,
      barStrokeWidth:       1,
      barValueSpacing:      5,
      barDatasetSpacing:    1,
      responsive:           true,
      maintainAspectRatio:  false,
      scaleLabel: function (label) {
        return '$' + label.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }
    }
    $scope.barOpts = barOptions;

    var barData = {
      labels: $scope.months,
      datasets: [
        {
          label: "My First dataset",
          fillColor: "rgba(220,220,220,0.5)",
          strokeColor: "rgba(220,220,220,0.8)",
          highlightFill: "rgba(220,220,220,0.75)",
          highlightStroke: "rgba(220,220,220,1)",
          data: $scope.data_points
        }
      ]
    };
    $scope.barData = barData;

    //RENDER
    myNewChart_1 = new Chart(context).Bar(barData, barOptions);
    // END BAR CHART

  };

  $("#revenueChart").click(function (evt) {
    var activeBars    = myNewChart_1.getBarsAtEvent(evt);
    var date          = activeBars[0].label;
    var shortname     = $scope.shortname;
    var ajax_url      = 'getDisplayRetailRevenueDetails';
    var containerName = '#monthly-summary';

    $http.get(ajax_url, {params: {"shortname": shortname, "date": date}})
      .then(function (response) {
        $scope.yaExiste   = true;
        $scope.month      = response.data.month;
        $scope.year       = response.data.year;
        $scope.detailsArr = $scope.parJson(response.data.retail_stat['revenue_data']);
        $scope.runUnitsDetail();
      });
//        loadURLGet(ajax_url, $(containerName), {"shortname": shortname, "date": date}, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');

  });

  $scope.runUnitsDetail       = function () {
    var shortname     = $scope.shortname;
    var date          = $scope.month + '-' + $scope.year;
    var ajax_url      = 'getDisplayRetailRevenueUnitDetails';
    var containerName = '#unit-details-box';

    $http.get(ajax_url, {params: {"shortname": shortname, "date": date}})
      .then(function (response) {
        $scope.existeDos  = true;
        $scope.monthDos   = response.data.month;
        $scope.yearDos    = response.data.year;
        $scope.detailsArr = $scope.parJson(response.data.retail_stat['revenue_data']);
      });
//    loadURLGet(ajax_url, $(containerName), { "shortname": shortname , "date": date }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');
  };


  // Apply the filter
  $("#voip-notice-table thead th input[type=text]").on('keyup change', function () {
    sptOtable
      .column($(this).parent().index() + ':visible')
      .search(this.value)
      .draw();

  });

  $('#voip-notice-table tbody').on('click', 'tr', function () {
    var salesid = $(this).attr('salesid');
    var ajax_url = "sales/property"; // put this in php file
    var containerName = '#content';
    loadURLGet(ajax_url, $(containerName), {"salesid": salesid}); //, containerName, "Loading Page");


  });


//  var ajax_url2 = root_url + "reports/display-location-stats"; // put this in php file
//  var containerName2 = '#location-details';
//  loadURLGet(ajax_url2, $(containerName2), { "shortname": shortname }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');
//
//  var ajax_url = root_url + "reports/display-retail-revenue-details"; // put this in php file
//  var containerName = '#monthly-summary';
//  loadURLGet(ajax_url, $(containerName), { "shortname": shortname , "date": date }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');
//


//-------------------------------------------------------------------------------------
//   var CountUp = function(target, startVal, endVal, decimals, duration, options){}

//  $http.get("dummyRouteController")
//    .then(function (response) {
//      var data = response.data;
//      new CountUp('t1', 0, data['commercial'], 0, 3).start();
//      new CountUp('t2', 0, data['retail'], 0, 3).start();
//      new CountUp('t3', 0, data['tickets'], 0, 3).start();
//      new CountUp('t4', 0, data['avgHour'], 0, 3).start();
//      new CountUp('t5', 0, data['avgDay'], 0, 3).start();
//    });

});