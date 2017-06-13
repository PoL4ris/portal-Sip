app.controller('chargesController', function ($scope, $http, customerService, adminService) {
  console.log('This is ChargesController');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }


  $http.post('getCharges', {params: {'token': adminService.existeToken}})
    .then(function (response) {
      $scope.chargesData = response.data;
    });


  $http.post('getChargesStats', {params: {'token': adminService.existeToken}})
    .then(function (response) {
      $scope.resultStatsData = response.data;
    });


  $scope.confirmAction = function () {
    console.log(this.charge);

//    $http.get('confirmActionWithRecord', {params: {'data': this.charge}})
//    $http.post('confirmActionWithRecord', {params: {'token': adminService.existeToken, 'data': this.charge}})
//      .then(function (response) {
//        $scope.resultStatsData = response.data;
//      });

  }
  $scope.cancelAction  = function () {
    console.log(this.charge);

//    $http.get('cancelActionWithRecord', {params: {'data': this.charge}})
//    $http.post('cancelActionWithRecord', {params: {'token': adminService.existeToken, 'data': this.charge}})
//      .then(function (response) {
//        $scope.resultStatsData = response.data;
//      });

  }
  $scope.editAction    = function () {
    $scope.editRecordTmp = this.charge;
  }
  $scope.editAndConfirm = function () {
    console.log(this.editRecordTmp);

    var objects = getFormValues('edit-action-form');
    $http.get('cancelActionWithRecord', {params: {'data': this.charge}})
//    $http.post('cancelActionWithRecord', {params: {'token': adminService.existeToken, 'data': this.charge}})
//      .then(function (response) {
//        $scope.resultStatsData = response.data;
//      });
  }


});