app.controller('chargesController', function ($scope, $http, customerService, adminService) {
  console.log('This is ChargesController');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }


  $http.post('getPendingManualCharges', {params: {'token': adminService.existeToken}})
    .then(function (response) {
      $scope.chargesData = response.data;
    });


  $http.post('getChargesStats', {params: {'token': adminService.existeToken}})
    .then(function (response) {
      $scope.resultStatsData = response.data;
    });


  $scope.confirmAction = function () {
    console.log(this.charge);

    $http.post('approveManualCharge', {params: {'data': this.charge.id}})
      .then(function (response) {
        console.log(response.data);
      });

  }
  $scope.cancelAction  = function () {
    console.log(this.charge);

    $http.post('denyManualCharge', {params: {'data': this.charge.id}})
      .then(function (response) {
        console.log(response.data);
      });

  }
  $scope.editAction    = function () {
    $scope.editRecordTmp = this.charge;
  }
  $scope.editAndConfirm = function () {
    console.log(this.editRecordTmp);

    var objects = getFormValues('edit-action-form');
    objects['id'] = $scope.idCustomer;
    $http.post('updateManualCharge', {params: objects})
      .then(function (response) {
        console.log(response.data);
      });
  }


});