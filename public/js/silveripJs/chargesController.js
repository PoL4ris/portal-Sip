app.controller('chargesController', function ($scope, $http, customerService, adminService) {
  console.log('This is ChargesController');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }


  $http.get('getPendingManualCharges')
    .then(function (response) {
      $scope.chargesData = response.data;
    });




  $scope.getChargeStat = function () {
    $http.get('getChargesStats')
      .then(function (response) {
        $scope.resultStatsData = response.data;
      });
  }
  $scope.getChargeStat();
  $scope.confirmAction = function () {

    $http.get('approveManualCharge', {params: {'id': this.charge.id}})
      .then(function (response) {
        $scope.chargesData = response.data;
        $scope.getChargeStat();
        $.smallBox({
          title: "Action Confirmed!",
          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
          color: "transparent",
          timeout: 6000
        });
      });

  }
  $scope.cancelAction  = function () {

    $http.get('denyManualCharge', {params: {'id': this.charge.id}})
      .then(function (response) {
        $scope.chargesData = response.data;
        $scope.getChargeStat();
        $.smallBox({
          title: "Action Denied!",
          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
          color: "transparent",
          timeout: 6000
        });
      });

  }
  $scope.editAction     = function () {
    $scope.editRecordTmp = this.charge;
  }
  $scope.editAndConfirm = function () {
    $scope.loadingtap = true;

    var objects = getFormValues('edit-action-form');
    objects['id'] = $scope.editRecordTmp.id;

    $http.get('updateManualCharge', {params: objects})
      .then(function (response) {
        if(response.data.response == 'OK')
        {
          $scope.chargesData = response.data.updated_data;

          $('#editActionModal').modal('toggle');
          $('#edit-action-form').trigger("reset");

          $scope.editRecordTmp = false;
          $scope.loadingtap = false;

          $scope.getChargeStat();
          $.smallBox({
            title: "Edit Confirmed!",
            content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
            color: "transparent",
            timeout: 6000
          });
        }
      });
  }


});