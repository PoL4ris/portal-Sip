app.controller('reportController', function ($scope, $http, customerService, $stateParams, generalService) {



  var canvas  = document.getElementById('revenueChart');
  var context = canvas.getContext('2d');

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  $http.get("getBuildingsList", {params: {'query': 'reports'}})
    .then(function (response) {
      $scope.bldListResult = response.data;
    });

  $scope.executeChartReport   = function () {

    if(!$stateParams.code)
      return;

    $scope.stateExist = true;
    $http.get("getDisplayRetailRevenue", {params: {'code': $stateParams.code }})
      .then(function (response) {
        $scope.data         = response.data.data;
        $scope.latestMonth  = response.data.latestMonth;
        $scope.months       = response.data.months;
        $scope.shortname    = response.data.shortname;
        $scope.route        = response.data.route;
        $scope.data_points  = response.data.data_points;
        $scope.data_points  = response.data.data_points;
        $scope.renderAll(response.data.building);
      });
  };
  $scope.renderAll            = function (bld) {

    var date      = $scope.latestMonth;
    var shortname = $scope.shortname;
    var root_url  = $scope.route;

    $http.get("getDisplayLocationStats", {params: {'id_buildings': bld.id, 'code': bld.code}})
      .then(function (response) {
        $scope.yaExisteTres = true;
        $scope.products     = response.data.products;
      });

    $http.get('getDisplayRetailRevenueDetails', {params: {"shortname": shortname, "date": date}})
      .then(function (response) {
        $scope.yaExiste   = true;
        $scope.month      = response.data.month;
        $scope.year       = response.data.year;
        $scope.detailsArr = $scope.parJson(response.data.retail_stat['revenue_data']);
        $scope.runUnitsDetail();
      });

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

    var o = Math.round, r = Math.random, s = 255;

    var barData = {
      labels:   $scope.months,
      datasets: [
        {
          label: "My First dataset",
          fillColor: "rgba(40,159,247,0.5)",
          strokeColor: 'rgba(50,118,177,0)',
          highlightFill: 'rgba(255,124,68,0.75)',
          highlightStroke: "rgba(40,159,247,0)",
          data: $scope.data_points
        }
      ]
    };
    $scope.barData = barData;

    //RENDER
    myNewChart_1 = new Chart(context).Bar(barData, barOptions);
    // END BAR CHART

  };
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
        $scope.detailsArrDos = $scope.parJson(response.data.retail_stat['revenue_data']);
      });
  };
  $scope.filterBldList        = function () {
    $http.get("getFilterBld", {params: {'query': this.filterBldListModel, 'report' : true}})
      .then(function (response) {
        $scope.bldListResult = response.data;
      });
  }

  $scope.executeChartReport();

  $("#revenueChart").click(function (evt) {

    var activeBars    = myNewChart_1.getBarsAtEvent(evt);
    if(activeBars.length <= 0 )
      return;

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
  });
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

});