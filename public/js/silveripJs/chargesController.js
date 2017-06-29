app.controller('chargesController', function ($scope, $http, customerService, adminService, DTOptionsBuilder) {
  console.log('This is ChargesController');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }

  $scope.displayView  = 'charges';
  $scope.allMonths    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $scope.allYears     = ['2015', '2016', '2017', '2018', '2019'];
  $scope.dtOptions    = DTOptionsBuilder.newOptions().withDisplayLength(25);

  $http.get('getPendingManualCharges')
    .then(function (response) {
      $scope.chargesData = response.data;
    });

  $scope.getChargeStat        = function () {
    $http.get('getChargesStats')
      .then(function (response) {
        $scope.resultStatsData = response.data;
      });
  }
  $scope.getChargeStat();
  $scope.confirmAction        = function () {

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
  $scope.cancelAction         = function () {

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
  $scope.editAction           = function () {
    $scope.editRecordTmp = this.charge;
  }
  $scope.editAndConfirm       = function () {
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
  //Charges and invoices
  $scope.getCharges           = function () {


    $http.get('getChargesAndInvoices', {params : adminService})
      .then(function (response) {

        console.log(response.data);
        $scope.chargesAndInvoices       = response.data.charges;
        $scope.chargesAndInvoicesYear   = response.data.year;
        $scope.chargesAndInvoicesMonth  = response.data.month;

        adminService.chAndInMonth       = response.data.month
        adminService.chAndInYear        = response.data.year
      });
  }
//  $scope.getCharges();
  $scope.showProductDetail    = function () {
    $scope.showProductDetails = this.charge.product_detail;
  }
  $scope.showInvoiceDetail    = function () {
    $scope.showInvoiceDetails = this.charge;
  }
  //Global
  $scope.setView              = function (id) {
    //Views:
    //0 = Pending
    //1 = Profiles
    //2 = Apps
    //3 = Building Properties
    var views = {'0' : 'charges',
                 '1' : 'pending',
                 '2' : 'apps',
                 '3' : 'bprop',
    }
    $scope.displayView = views[id];

    if(id == 1)
    {
      adminService.chAndInMonth = null;
      adminService.chAndInYear  = null;
      $scope.getCharges();
    }


  };
  $scope.statusLabel          = function (id) {
  var statusLabel = {
                      0:'none',
                      1:'pending',
                      2:'invoiced',
                      3:'paid',
                      4:'failed',
                      5:'disabled',
                      6:'pending-Approval',
                      7:'denied',
      }

      return(statusLabel[id]);
  };
  $scope.getDataByMonth       = function () {
    adminService.chAndInMonth = this.$index + 1;
    $scope.getCharges();
  };
  $scope.getDataByYear        = function () {
    adminService.chAndInYear = this.year;
    $scope.getCharges();
  };

  $scope.somethinHere        = function () {

    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Should I Process this Charge?',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {

      if (ButtonPressed === "Yes") {
//        $scope.resetPassword();
      }
     if (ButtonPressed === "No") {

       $.smallBox({
         title: "Callback function",
         content: "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
         color: "#C46A69",
         iconSmall: "fa fa-times fa-2x fadeInRight animated",
         timeout: 4000
       });
     }

    });
  };
});