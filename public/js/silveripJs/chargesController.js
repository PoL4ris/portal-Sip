app.controller('chargesController', function ($scope, $http, customerService, adminService, DTOptionsBuilder, generalService) {
  console.log('This is ChargesController');

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  $scope.displayView  = 'charges';
  $scope.allMonths    = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  $scope.allYears     = ['2015', '2016', '2017', '2018', '2019'];
  $scope.dtOptions    = DTOptionsBuilder.newOptions()
                                        .withDisplayLength(15)
                                        .withOption('paging',     false)
                                        .withOption('info',       false)
                                        .withOption('order',      [11, 'desc'])
                                        .withOption('searching',  false);
//  $scope.dtOptions    = DTOptionsBuilder.newOptions().withDisplayLength(25);
  $scope.statusLabelArr =  [
    {id : 1, label:'Pending'},
    {id : 2, label:'Invoiced'},
    {id : 3, label:'Paid'},
    {id : 4, label:'Failed'},
    {id : 5, label:'Disabled'},
    {id : 6, label:'Pending-Approval'},
    {id : 7, label:'Denied'},
    {id : 8, label:'Cancelled'}];

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
    var thisId = this.charge.id;
    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Confirm authorize action.',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {

      if (ButtonPressed === "Yes") {
        $http.get('approveManualCharge', {params: {'IDs': {0:thisId}}})
          .then(function (response) {
//            console.log(response.data);
//            $scope.chargesData = response.data;
            $scope.chargesData = response.data['pending-charges'];
            $scope.getChargeStat();
            $.smallBox({
              title: "Action Confirmed!",
              content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
              color: "transparent",
              timeout: 6000
            });
          });
      }
    });
  }
  $scope.cancelAction         = function () {

    var thisId = this.charge.id;
    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Confirm authorize action.',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {

      if (ButtonPressed === "Yes") {


        $http.get('denyManualCharge', {params: {'IDs': {0:thisId}}})
          .then(function (response) {
            $scope.chargesData = response.data['pending-charges'];
            $scope.getChargeStat();
            $.smallBox({
              title: "Charge Denied!",
              content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
              color: "transparent",
              timeout: 6000
            });
          });
      }
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
  //Process more than one request.
  $scope.getFormChecks        = function () {

    $scope.loadingTransaction = true;

    var objects = $('#pending-charges-form').serializeArray();
    objects.shift();

    if(objects.length === 0){
      alert('Verify this action.');
      return;
    }


    var infoData = [];
    for(var obj in objects ) {
      infoData.push(objects[obj]['name']);
    }

    $('#proccessingModal').modal('toggle');


    $http.get('approveManualCharge', {params: {'IDs': JSON.stringify(infoData)}})
      .then(function (response) {

        $scope.loadingTransaction = false;

        console.log(response.data);
        $scope.transactionResponse = response.data.results;
        $scope.chargesData = response.data['pending-charges'];
        processCheckFunct();


//        $scope.getChargeStat();
//        $.smallBox({
//          title: "Action Confirmed!",
//          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
//          color: "transparent",
//          timeout: 6000
//        });

      });

  }
  $scope.getFormChecksDeny    = function () {

    $scope.loadingTransaction = true;

    var objects = $('#pending-charges-form').serializeArray();
    objects.shift();

    if(objects.length === 0){
      alert('Verify this action.');
      return;
    }

    var infoData = [];
    for(var obj in objects ) {
      infoData.push(objects[obj]['name']);
    }

    $('#proccessingModal').modal('toggle');


    $http.get('denyManualCharge', {params: {'IDs': JSON.stringify(infoData)}})
      .then(function (response) {

        $scope.loadingTransaction = false;

        console.log(response.data);
        $scope.transactionResponse = response.data.results;
        $scope.chargesData = response.data['pending-charges'];
        processCheckFunct();



//        console.log(response.data['pending-charges']);
//        $scope.getChargeStat();
//        $.smallBox({
//          title: "Action Confirmed!",
//          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
//          color: "transparent",
//          timeout: 6000
//        });

      });

  }
  //ALL Actions
  $scope.approveAll           = function () {
    var confAction = confirm('Are you sure you want to Approve all this pending charges?');
    if (confAction == true) {

      $scope.loadingTransaction = true;

      var objects = $scope.chargesData
      var infoData = [];

      for (var obj in objects) {
        infoData.push(objects[obj]['id'].toString());
      }

      $('#proccessingModal').modal('toggle');


      $http.get('approveManualCharge', {params: {'IDs': JSON.stringify(infoData)}})
        .then(function (response) {

          $scope.loadingTransaction = false;

          console.log(response.data);
          $scope.transactionResponse = response.data.results;
          $scope.chargesData = response.data['pending-charges'];

//        $scope.getChargeStat();
//        $.smallBox({
//          title: "Action Confirmed!",
//          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
//          color: "transparent",
//          timeout: 6000
//        });

        });
    }
  }
  $scope.denyAll              = function () {
    var confAction = confirm('Are you sure you want to Deny all this pending charges?');
    if (confAction == true) {

      $scope.loadingTransaction = true;

      var objects = $scope.chargesData
      var infoData = [];

      for (var obj in objects) {
        infoData.push(objects[obj]['id'].toString());
      }

      $('#proccessingModal').modal('toggle');


      $http.get('denyManualCharge', {params: {'IDs': JSON.stringify(infoData)}})
        .then(function (response) {

          $scope.loadingTransaction = false;

          console.log(response.data);
          $scope.transactionResponse = response.data.results;
          $scope.chargesData = response.data['pending-charges'];

//        $scope.getChargeStat();
//        $.smallBox({
//          title: "Action Confirmed!",
//          content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
//          color: "transparent",
//          timeout: 6000
//        });

        });
    }
  }
  //Check Input verify.
  $scope.checkAllCharges      = function () {
    $('.check-input').prop('checked', $('#check-uncheck').is(':checked'));
    $scope.processCheck();
  }
  $scope.processCheck         = function () {
    processCheckFunct();
  }
  //Charges
  $scope.getCharges           = function (page = '?') {

    switch(page){
      case 'status':
        page = '?&';
        adminService.status = this.billingFilterStatus.id ? this.billingFilterStatus.id : '';
        break;
      case 'amount':
        page = '?&';
        adminService.amount = this.billingFilterAmount ? this.billingFilterAmount : '';
        break;
      case 'code':
        page = '?&';
        adminService.code = this.billingFilterCode ? this.billingFilterCode : '';
        break;
      case 'unit':
        page = '?&';
        adminService.unit = this.billingFilterUnit ? this.billingFilterUnit : '';
        break;
      case 'empty':
        page = '?';
        adminService.unit = adminService.amount = adminService.code = adminService.status = '';
        $('#charges-filter-form-container').trigger('reset');
        break;
    }

    $http.get('getChargesAndInvoices' + page, {params : adminService})
      .then(function (response) {
        $scope.chargesAndInvoices         =  response.data.charges.data;
        $scope.chargesAndInvoicesYear     =  response.data.year;
        $scope.chargesAndInvoicesMonth    =  response.data.month;
        $scope.fullResponse               =  response.data;
        adminService.chargesMonth         =  response.data.month;
        adminService.chargesYear          =  response.data.year;
      });
  }
  $scope.showProductDetail    = function () {
    $scope.showProductDetails = this.charge.product_detail;
  }
  $scope.showInvoiceDetail    = function () {
    $scope.showInvoiceDetails = this.charge;
  }

  //Invoices
  $scope.getInvoices           = function (page = '?') {

    switch(page){
      case 'status':
        page = '?&';
        adminService.status = this.billingFilterStatus.id ? this.billingFilterStatus.id : '';
        break;
      case 'amount':
        page = '?&';
        adminService.amount = this.billingFilterAmount ? this.billingFilterAmount : '';
        break;
      case 'code':
        page = '?&';
        adminService.code = this.billingFilterCode ? this.billingFilterCode : '';
        break;
      case 'unit':
        page = '?&';
        adminService.unit = this.billingFilterUnit ? this.billingFilterUnit : '';
        break;
      case 'empty':
        page = '?';
        adminService.unit = adminService.amount = adminService.code = adminService.status = '';
        $('#invoices-filter-form-container').trigger('reset');
        break;
    }

    $http.get('getInvoices' + page, {params : adminService})
      .then(function (response) {
        $scope.invoicesView         =  response.data.invoices.data;
        $scope.invoicesViewYear     =  response.data.year;
        $scope.invoicesViewMonth    =  response.data.month;
        $scope.fullInvoiceResponse               =  response.data;
        adminService.invoiceMonth         =  response.data.month;
        adminService.invoiceYear          =  response.data.year;
      });
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
                 '4' : 'invoices',
    }
    $scope.displayView = views[id];

    if(id == 1)
    {
      adminService.chargesMonth = null;
      adminService.chargesYear  = null;
      $scope.getCharges();
    }
    if(id == 4)
    {
      adminService.invoicesMonth = null;
      adminService.invoicesYear  = null;
      $scope.getInvoices();
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
    adminService.chargesMonth = this.$index + 1;
    $scope.getCharges();
  };
  $scope.getIDataByMonth       = function () {
    adminService.invoiceMonth = this.$index + 1;
    $scope.getInvoices();
  };
  $scope.getDataByYear        = function () {
    adminService.chargesYear = this.year;
    $scope.getCharges();
  };
  $scope.getIDataByYear        = function () {
    adminService.invoiceYear = this.year;
    $scope.getInvoices();
  };


  //Confirm Action Example
  $scope.somethinHere         = function () {

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


function processCheckFunct(){
  var objects = $('#pending-charges-form').serializeArray();
  objects.shift();

  if(objects.length > 0){
    $('.process-checks').attr('disabled', false);
    $('.process-all').attr('disabled', true);
  }
  else{
    $('.process-checks').attr('disabled', true);
    $('.process-all').attr('disabled', false);
  }
}