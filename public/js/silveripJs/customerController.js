//Customer Controllers
app.controller('customerControllerList',            function ($scope, $http){
  $http.get("getCustomerList")
    .then(function (response) {
      $scope.supportDataCustomer = response.data;
    });
});
app.controller('customerController',                function ($scope, $http, $stateParams, customerService, DTOptionsBuilder){

//   $scope.globalServiceLocationSide = null;
  if(!customerService.rightView) {
    customerService.rightView = true;
    //     console.log('right');
  }
  else {
    //console.log('left');
    //SideBar verify if need to be there.
    if(!customerService.sideBarFlag) {
      $scope.sipTool(2);
      customerService.sideBarFlag = true;
    }

    customerService.leftView = true;
    $scope.customerFlag      = true;
    $scope.idCustomer        = Math.floor((Math.random() * (11656 - 11155 + 1) ) + 11155);

    if ($scope.stcid || $stateParams.id)
      $scope.idCustomer = $scope.stcid ? $scope.stcid : $stateParams.id;

//         if (($(location).attr('href').split('http://silverip-portal.com/#/')[1]) == 'customers') {
//             $scope.idCustomer = 501;
//             $scope.buscadorFlag = true;
//         }

    //SET INPUT VALUE
    $('#customerIdScope').val($scope.idCustomer);

    $http.get("customersData", {params:{'id' : $scope.idCustomer}})
      .then(function (response) {
        customerService.customer = response.data;
        $scope.customerData.customer = customerService.customer;
        $scope.bld = $scope.customerData.customer.address;
      });
    $http.get("getContactTypes", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.contactTypes = response.data;
      });
    $http.get("getTableData", {params:{'table':'reasons'}})
      .then(function (response) {
        $scope.newTicketData = response.data;
      });
    $http.get("getStatus")//VERIFY HOW TO CREATE INDEX 0 ON THE SELECT OPTION X-EDIT
      .then(function (response) {
        $scope.customerTypes = response.data;
        for(var obj in response.data ) {
          $scope.customerTypes[obj]['value'] = response.data[obj].id;
          $scope.customerTypes[obj]['text']  = response.data[obj].name;
        }
        $scope.customerData.statusList = $scope.customerTypes;
      });

    $scope.checkboxModel         = true;
    $scope.checkboxModelA        = true;
    $scope.animationsEnabled     = false;
    $scope.currentServiceDisplay = '';
  }

  //Reloads Data
  $scope.getCustomerStatus          = function (id){
    $http.get("getCustomerStatus", {params:{'id':id}})
      .then(function (response) {
        $scope.customerData.customer.status = response.data;
      });
  };
  //----------TEMP DISABLED
//     $scope.clearSearch                = function (){
//         this.searchCustomer = '';
//         $scope.buscador();
//     }
//     $scope.buscador                   = function () {
//         if(!this.searchCustomer)
//         {
//             $scope.customerSearchResult = false;
//             return;
//         }
//
//         var query = {'querySearch' : this.searchCustomer};
//
//         $http.get("customersSearch", {params:query})
//             .then(function (response) {
//             $scope.customerSearchResult = response.data;
//         });
//
//         return;
//
//     }
  $scope.getAddressItems            = function (){
    $http.get("getAddress")
      .then(function (response) {
        $scope.addressData = response.data;
      });
  };
  $scope.getCustomerContactData     = function (){
    $http.get("getCustomerContactData", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.customerContactsData = response.data.contacts;
      });
  }
  $scope.getCustomerContactData();
  $scope.submitForm                 = function (table) {
    console.log('este' + table);
    return;
    var objects = $('#'+table+'-insert-form').serializeArray();
    var infoData = {};


    for(var obj in objects )
    {
      if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
      {
        var tmp = objects[obj]['name'].split('id_');
        console.log(tmp);
        alert('Verify ' + (tmp[1]?tmp[1]:objects[obj]['name']) + ' Field');
        return;
      }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }
    infoData['id_customers'] = $scope.customerData.id;

    //     validator.startValidations;

    $http.get("insertCustomerTicket", {params:infoData})
      .then(function (response) {
        //         cancelForm();
        if(response.data == 'OK')
        {
          document.getElementById(table+"-insert-form").reset();
          notify({ message: 'New Ticket Created!', templateUrl:'/views/notify.html'} );

        }
      });

    //     callAdminView(infoData['table']);
    //     notify({ message: 'Data inserted!', templateUrl:'/views/notify.html'} );

  };
  $scope.submitNewTicketForm        = function (){
    $('#create-customer-ticket').attr('disabled', true);


    var infoData = getFormValues('new-ct-form');

    if(infoData.id_reasons == '' || infoData.status == '' || infoData.comment == ''){
      $('#create-customer-ticket').removeAttr('disabled');
      return;
    }

    $scope.loadingGif = true;

    infoData['id_customers'] = $scope.idCustomer;

    $http.get("insertCustomerTicket", {params:infoData})
      .then(function (response) {
        if(response.data == 'OK'){
          $('#create-customer-ticket').removeAttr('disabled');
          $scope.loadingGif = false;
          $.smallBox({
            title: "New Ticket Created!",
            content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
            color: "#739E73",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });
          $('#new-ct-form').trigger("reset");
        }
      });
  };
  $scope.validate                   = function (value, table, field) {
    var data = {};
    data[field] = value;
    data['id_customers'] = $scope.idCustomer;

    $http.get("update" + table + "Table", {params:data})
      .then(function (response) {
        console.log('OK');
      });
  }
  $scope.customerEditMode           = function (){
    if ( $scope.checkboxModel == false)
    {
      $('.editable-text').fadeIn('slow');
      $('.no-editable-text').css('display', 'none');
    }
    else
    {
      $('.no-editable-text').fadeIn('slow');
      $('.editable-text').css('display', 'none');
    }
  };
  $scope.customerEditMode();
  $scope.contactEditMode            = function (){
    if ( $scope.checkboxModelA == false)
    {
      console.log($scope.checkboxModelA);
      $('.c-no-editable-text').fadeIn('slow');
      $('.c-editable-text').css('display', 'none');
      $scope.checkboxModelA = true;
    }
    else
    {
      console.log($scope.checkboxModelA);
      $('.c-editable-text').fadeIn('slow');
      $('.c-no-editable-text').css('display', 'none');
      $scope.checkboxModelA = false;
    }
  };
  $scope.updateContactInfo          = function (value, id){
    var data = {};
    data['id']    = id;
    data['value'] = value;
    $http.get("updateContactInfo", {params:data})
      .then(function (response) {
        console.log(response.data);
      });
  };
  $scope.open                       = function (id, type){

    $scope.customerId = id;
    $scope.type = type;

    var modalInstance = $uibModal.open(
      {
        animation: $scope.animationsEnabled,
        templateUrl: 'myContactInfoAdd.html',
        controller: 'addContInfoController',
        size: 'md',
        resolve: {
          customerId: function () {
            return $scope.customerId;
          },
          mode: function (){
            return $scope
          }
        }
      });

    modalInstance.result.then(function () {}, function () {
      //       if (type == 'services' || type == 'updateService')
      //         $scope.cSrvCrlFun();

      $log.info('Modal dismissed at: ' + new Date());
    });
  };
  $scope.insertCustomerContact      = function (){
    alert('disabled action.');

    var infoData = getFormValues('new-cct-form');
    infoData['id_customers'] = $scope.idCustomer;

    console.log(infoData);
    return;

    $http.get("insertBuildingProperties", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-property-cancel').scope().fadeViews('bpv-container', 'new-form-function', 0, 'enable', 'add-property', 'add-property-cancel')
    $('#new-bpv-form').trigger("reset");
  }
  //Product.html
  $scope.addNewService              = function () {

    if(!$scope.currentServiceDisplay)
    {
      $('.add-prod-select-color').css('border-color', 'red');
      return;
    }
    $('.add-prod-select-color').css('border-color', 'inherit');

    var mode = $scope.customerData.servicesMode;

    if (mode == 'updateService')
    {
      $http.get("updateCustomerServices", {params:{'id' : $scope.idCustomer,'newId' : $scope.currentServiceDisplay.id, 'oldId' : $scope.customerData.serviceTmpId}})
        .then(function (response) {
          console.log("Service Added / Updated::OK");
          $scope.availableServices();
        });
      $('#myModalService').modal('toggle');
    }
    else
    {
      $http.get("insertCustomerService", {params : {'idCustomer' : $scope.idCustomer,'idProduct' : $scope.currentServiceDisplay.id}})
        .then(function (response) {
          console.log("Service Added / Updated::OK");
          $scope.availableServices();
        });
      $('#myModalService').modal('toggle');
    }
  };
  $scope.setModeType                = function (modeType){
    $scope.customerData.servicesMode = modeType;
    $scope.customerData.serviceTmpId = this.service ? this.service.id : false;
    $scope.currentServiceDisplay = null;
    $scope.showingCurrent = null;

    $scope.getBldProducts();
  };
  $scope.getBldProducts             = function (){

    $http.get("getAvailableServices", {params:{'id':$scope.customerData.customer.address.id_buildings}})
      .then(function (response) {
        $scope.customerData.availableServices = response.data;
      });

  };
  $scope.serviceDataDisplay         = function (option) {
    if(option)
    {
      $scope.serviceFlag = true;
      $scope.currentServiceDisplay = this.customerProduct.product;
      $scope.showingCurrent = this.service.product;
    }
    else
    {
      $scope.serviceFlag = false;
      $scope.currentServiceDisplay = this.selectedItem.product;
    }
  };
  //disable becaus its gone the button that triggers.
  $scope.setInvoiceTab              = function (){
    $('#invoice-payment-tab-link').trigger('click');
  };


  $scope.availableServices          = function (){
    $http.get("getCustomerServices", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.customerData.customerServices = response.data;
      });

    $scope.resolveRouteFunction(null, $scope.idCustomer);
  };
  //addresses
  $scope.clearAddressModal          = function (){
    $('#mb-ca-edit').fadeOut();
    $('#mb-ca-table').fadeIn();
  }
  $scope.editAddressModal           = function (action){
    //  action = 1 EDIT
    //  action = 0 CANCEL
    if( action == 1)
    {
      console.log('aqui estamos ' + this.$index);
      $('#mb-ca-table').fadeOut();
      $('#mb-ca-edit').fadeIn();
      $scope.indexOfToShow = this.$index;
    }
    else
    {
      console.log('aqui estamos ' + action);
      $('#mb-ca-edit').fadeOut();
      $('#mb-ca-table').fadeIn();
    }
  };
  //ResetPassword
  $scope.showConfirmPassword        = function (idProduct, status) {

    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Should I reset this customer’s password?',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {
        $scope.resetPassword();
      }
      //       if (ButtonPressed === "No") {
      //
      //         $.smallBox({
      //           title: "Callback function",
      //           content: "<i class='fa fa-clock-o'></i> <i>You pressed No...</i>",
      //           color: "#C46A69",
      //           iconSmall: "fa fa-times fa-2x fadeInRight animated",
      //           timeout: 4000
      //         });
      //       }

    });
  };
  $scope.resetPassword              = function (){

    $http.get("resetCustomerPassword", {params:{'id':$scope.idCustomer}})
      .then(function (response){

        if (response.data['response'] == 'OK')
        {
          $.smallBox({
            title: "Password updated to " + response.data['password'],
            content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
            color: "#739E73",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });
        }

      });
  };
  //Network
  $scope.getCoreData = function (ip){
    $scope.pLoad     = true;
    $scope.pRecord   = ip;
    $http.get("getSwitchStats", {params:{'ip':ip}})
      .then(function (response) {
        $scope.pLoad    = false;
        $scope.pStatus  = response.data[0]
        $scope.pInfo    = response.data[1]
      });
  }
  $scope.pStInOptions = DTOptionsBuilder.newOptions().withDisplayLength(50);
});
app.controller('customerTicketHistoryController',   function ($scope, $http){
  $scope.getTicketHistory = function () {
    $http.get("getTicketHistory", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.ticketHistory = response.data;
        $scope.letterLimit = 20;
      });
  }
  $scope.showFullComment  = function(id) {
    $('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment  = function(id) {
    $('#ticket-' + id).fadeOut('fast');
  }

  $scope.getTicketHistory();
});
app.controller('customerInvoiceHistoryController',  function ($scope, $http){
  //   console.log($scope.customerData);

  if(!$scope.invoiceData)
    $http.get("getInvoiceHistory", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.invoiceData = response.data;
      });

  $scope.setInvoiceData = function (){
    $scope.modalInvoice         = this.invoice;
    $scope.modalInvoice.details = $scope.parJson($scope.modalInvoice.details);
  };

});
app.controller('customerNetworkController',         function ($scope, $http){

  // console.log('esto es  : customerNetworkController con el id de -- > ' + $scope.idCustomer);

  $http.get("getCustomerNetwork", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerNetwork = response.data[0];
//      console.log($scope.customerNetwork);



      if(response.data.length > 0){
        networkServices(0, true);
      }
    });

  $scope.networkServices    = function (service) {
    networkServices(service);
  }


  function networkServices (service, flagService) {

    var routes = ['networkCheckStatus',
      'netwokAdvancedInfo',
      'networkAdvanceIPs',
      'networkRecyclePort',
      '4',
      'networkSignUp',
      'networkActivate'];

    $('.network-functions').addClass('disabled');

    var service = service;
    var portID = $scope.customerNetwork.id;
    var customerID = $scope.idCustomer;
    var dataSend = {'portid':portID, 'id':customerID};

//         console.log(service);
//         console.log(portID);
//         console.log(customerID);
//         console.log(dataSend);


    //AJAX request
    $.ajax(
      {type:"GET",
        url:"/" + routes[service],
        data:dataSend,
        success: function(data) {

          $scope.customerData.networkServices = data;

          if (data == 'ERROR')
            alert(data);

          $.each(data,function(i, item) {
            $('#' + i).html(item);
          });

          service = 1;
          $.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:dataSend,
              success: function(data)
              {
                $.each(data,function(i, item)
                {
                  $('#' + i).html(item);
                });
              }
            }
          );

          //                 service = 2;
          //                 $.ajax(
          //                     {type:"GET",
          //                      url:"/" + routes[service],
          //                      data:dataSend,
          //                      success: function(data)
          //                      {
          //                          //                 $('#IPs').notify('IPs Array.');
          //
          ////                          if(!flagService)
          ////                          {
          ////                              $.smallBox({
          ////                                  title: "IPs Array.",
          ////                                  content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
          ////                                  color: "#739E73",
          ////                                  iconSmall: "fa fa-thumbs-up bounce animated",
          ////                                  timeout: 6000
          ////                              });
          ////                          }
          //
          $('.network-functions').removeClass('disabled');
          //                          //                   $.each(data,function(i, item)
          //                          //                   {
          //                          //                     $('#' + i).html(item);
          //                          //                   });
          //                      }
          //                     }
          //                 );
          $('.network-functions').removeClass('disabled');
        }
      }
    );

    if (service == 5) {
      $('.access-type-net').removeClass('btn-danger ');
      $('.access-type-net').addClass('btn-info');
      $('.access-type-net').html('Activate');
      $('.access-type-net').attr('type','6');
      $('#acces-network-id').html('signup');
    }
    else if ( service == 6 ) {
      $('.access-type-net').removeClass('btn-info')
      $('.access-type-net').addClass('btn-danger')
      $('.access-type-net').html('Send to Signup');
      $('.access-type-net').attr('type','5');
      $('#acces-network-id').html('yes');
    }

  };

  //PENDING
  function servicesInfoUpdate (serviceID, serviceStatus, routeID) {
    var routes = ['updateCustomerServiceInfo'];

    //   $('.network-functions').addClass('disabled');

    //AJAX request
    $.ajax(
      {type:"GET",
        url:"/" + routes[routeID],
        data:{'serviceid':serviceID, 'status':serviceStatus},
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          if (serviceStatus == 'active')
          {
            $('#serviceno-' + serviceID).addClass('disabled ital');
            $('#serviceinfo-status-' + serviceID).html('disabled');
            $('#xservice-btn-id-' + serviceID).attr('displaystatus','disabled');
            $('#xservice-btn-id-' + serviceID).addClass('btn-success fa-check');
            $('#xservice-btn-id-' + serviceID).removeClass('btn-dark');
            $('#xservice-btn-id-' + serviceID).removeClass('fa-times');
          }
          else
          {
            $('#serviceno-' + serviceID).removeClass('disabled ital');
            $('#serviceinfo-status-' + serviceID).html('active');
            $('#xservice-btn-id-' + serviceID).attr('displaystatus','active');
            $('#xservice-btn-id-' + serviceID).addClass('btn-dark fa-times');
            $('#xservice-btn-id-' + serviceID).removeClass('btn-success');
            $('#xservice-btn-id-' + serviceID).removeClass('fa-check');
          }

        }
      }
    );
  };
  $scope.smartModEg1        = function (event) {
    //     console.log($(this).attr('type'));
    var elementId = event.target.id;
    var service       = $('#'+elementId).attr('type');
    var portID        = $('#'+elementId).attr('portid');
    var serviceID     = $('#'+elementId).attr('serviceid');
    var serviceStatus = $('#'+elementId).attr('displaystatus');
    var routeID       = $('#'+elementId).attr('route');

    if(service == 3)
      var txtMsg = 'Should I recycle this customer’s port?';
    if(service == 5)
      var txtMsg = 'Are you sure you want to send this customer to the signup page?';
    if(service == 6)
      var txtMsg = 'Are you sure you want to active this customer?';

    console.log('Inside smartModEg1(): ');
    console.log('service='+service);
    console.log('portID='+portID);
    console.log('serviceID='+serviceID);
    console.log('serviceStatus='+serviceStatus);

    $.SmartMessageBox({
      title: "Please Confirm",
      content: txtMsg,
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {
        if(portID)
          networkServices(service); // Recycle port, send to signup, activate, etc
        else if(serviceID)
          servicesInfoUpdate(serviceID, serviceStatus, routeID); // ??

      }
    });
  };

});
app.controller('customerBuildingController',        function ($scope, $http){

  //   console.log($scope.customerData);
  console.log('this is Entramos');

  if($scope.customerData) {
    $http.get("buildings/" + $scope.customerData.address.id_buildings)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }

});
app.controller('customerPaymentMethodsController',  function ($scope, $http){
  // console.log('something here con el id de  : ' + $scope.idCustomer);
  // return;
  // app.controller('customerPaymentMethodsController',  function ($scope, $http,$uibModal, $log){
  // return;

  $http.get("getDefaultPaymentMethod", {params:{'id':$scope.stcid ? $scope.stcid : $scope.idCustomer}})
    .then(function (response) {
      $scope.paymentData = response.data[0];
      $scope.pproperties = response.data[1];
      $scope.customerData.defaultPayment = $scope.paymentData;
      $scope.customerData.defaultPproperties = $scope.pproperties;
    });
  $http.get("getAllPaymentMethods", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.paymentMethods = response.data;
    });

  $scope.setAsDefaultPaymentMethod = function (id) {

    $http.get("setDefaultPaymentMethod", {params:{'id' : id, 'customerID' : $scope.idCustomer}})
      .then(function (response) {
        $scope.defaultCssColor = true;
        setTimeout(function(){$scope.defaultCssColor = false;}, 500);

        $scope.paymentMethods = response.data;

        $http.get("getDefaultPaymentMethod", {params:{'id' : $scope.idCustomer}})
          .then(function (response) {

            $scope.paymentData = response.data[0];
            $scope.pproperties = response.data[1];
          });
      });
  };
  $scope.getPaymentMethods  = function (customerId){
    $http.get("getAllPaymentMethods", {params:{'id':customerId}})
      .then(function (response) {
        $scope.paymentMethods = response.data;
      });
  };



  //Temporal version to work with
  $scope.refundFunct        = function (){

    $scope.errorMsgPaymentMethods = null;

    var regex = /[^\w]/g

    var objects = getFormValues('manual-refund-form');
    objects['cid'] = $scope.idCustomer;


    if(!objects.cid || !objects.amount || !objects.desc)
      return;

    if(regex.test(objects.amount)){
      $scope.errorMsgPaymentMethods = 'Verify the amount.';
      return;
    }

    processing(1);

    $http.get("manualRefund", {params:objects})
      .then(function (response)
      {
        console.log(response.data);
        processing(0);
      });


  }



  $scope.refundFunctXXX        = function (){
    $scope.errorMsgPaymentMethods = null;

    var regex = /[^\w]/g

    var objects = getFormValues('manual-refund-form');
    objects['cid'] = $scope.idCustomer;


    if(!objects.cid || !objects.amount || !objects.desc)
      return;

    if(regex.test(objects.amount)){
      $scope.errorMsgPaymentMethods = 'Verify the amount.';
      return;
    }

    processing(1);

    $http.get("refundAmount", {params:objects})
      .then(function (response)
      {
        //$scope.paymentMethods = response.data;
        console.log(response.data);

        if(response.data.RESPONSETEXT == 'RETURN ACCEPTED')
        {
          $.smallBox({
            title: "Return Accepted!",
            content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
            color: "transparent",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });
        }
        else{
          processing(0);
          $scope.errorMsgPaymentMethods = response.data.ERRMSG;
          return;
        }

        processing(0);
        $('#paymentManualRefound').modal('toggle');
        $('#manual-refund-form').trigger("reset");
      });
  };//working functin to refound.





  $scope.chargeFunct        = function (){
    $scope.errorMsgPaymentMethods = null;

    var regex = /[^\w]/g

    var objects = getFormValues('manual-charge-form');
    objects['cid'] = $scope.idCustomer;

    if(!objects.cid || !objects.amount || !objects.desc)
      return;

    if(regex.test(objects.amount)){
      $scope.errorMsgPaymentMethods = 'Verify the amount.';
      return;
    }

    processing(1);

    $http.get("manualCharge", {params:objects})
      .then(function (response)
      {
        //$scope.paymentMethods = response.data;
        console.log(response.data);
        processing(0);

      });
  }
  $scope.chargeFunctXXX        = function (){
    $scope.errorMsgPaymentMethods = null;

    var regex = /[^\w]/g

    var objects = getFormValues('manual-charge-form');
    objects['cid'] = $scope.idCustomer;

    if(!objects.cid || !objects.amount || !objects.desc)
      return;

    if(regex.test(objects.amount)){
      $scope.errorMsgPaymentMethods = 'Verify the amount.';
      return;
    }

    processing(1);

    $http.get("chargeAmount", {params:objects})
      .then(function (response)
      {
        //$scope.paymentMethods = response.data;
        console.log(response.data);
        if(response.data.RESPONSETEXT == 'APPROVED')
        {
          $.smallBox({
            title: "Transaction Approved!",
            content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
            color: "transparent",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });
          //$scope.closeTransparentBGManual();
        }
        else{
          processing(0);
          $scope.errorMsgPaymentMethods = response.data.ERRMSG;
          return;
        }

        processing(0);
        $('#paymentManualCharge').modal('toggle');
        $('#manual-charge-form').trigger("reset");
      });
  };
  $scope.prepareFields = function(){
    $('#manual-refund-form').trigger("reset");
    $('#manual-charge-form').trigger("reset");
    $scope.errorMsgPaymentMethods = null;
  };
  $scope.editPaymentMethod  = function (flag){

    if(flag){
      $scope.editPaymentFlag = false;
      $scope.editPaymentValues = null;
    }
    else{
      $scope.editPaymentFlag = true;
      $scope.editPaymentValues = this.payment;
    }

    $('#paymentMethodModal').trigger("reset");
  };
  function processing(status){
    if(status == 1)
      $scope.something = true;
    else
      $scope.something = false;
  }
  //OLD THING
  $scope.open = function (){
    $scope.customerId = $scope.idCustomer;

    var modalInstance = $uibModal.open(
      {
        animation: $scope.animationsEnabled,
        templateUrl: 'addPaymentMethod.html',
        controller: 'addPaymentMethodController',
        size: 'md',
        resolve: {
          customerId: function () {
            return $scope.customerId;
          }
        }
      });

    modalInstance.result.then(function () {}, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });

  };

});
app.controller('addPaymentMethodController',        function ($scope, $http){
  // app.controller('addPaymentMethodController',        function ($scope, $http, customerId, notify, $uibModalInstance){
  /*
   * value[0] = Name on Card
   * value[1] = Account Number
   * value[2] = Billing Phone
   * value[3] = Exp Month
   * value[4] = Exp Year
   * value[5] = Card Type
   * value[6] = CCV
   * */
  // console.log('this is addPaymentMethodController ');
  $scope.addNewPaymentMethod = function (){

    var objects = getFormValues('paymentmethodform');

    for(var obj in objects ) {
      if(!objects[obj])
        return;
    }

    var regexCC = /(\d{4}[-. ]?){4}|\d{4}[-. ]?\d{6}[-. ]?\d{5}/g;

    var boolResult = regexCC.test(objects['account_number']);

    //CONSOLE MSG
    if (boolResult == true)
      console.log('this is true ===> ' + boolResult);
    else
      console.log('this is false ===> ' + boolResult);

    if (!boolResult) {
      //Mensaje alerta numero de cuenta malo
      console.log('Verify your Account Number');
      return;
    }

    if(objects['CCV'].length < 3 || objects['CCV'].length > 4) {
      //Mensaje alerta ccv malo
      console.log('Verify your CCV Number');
      return;
    }

    if($scope.editPaymentFlag)
      objects['id'] = $scope.editPaymentValues.id;

    objects['id_customers'] = $scope.idCustomer;

    $http.get("insertPaymentMethod", {params : objects})
      .then(function (response) {
        if(response.data == 'OK')
        {
          $('#paymentMethodModal').modal('toggle');

          $.smallBox({
            title: $scope.editPaymentFlag ? "Card Updated." : "New Card added to this customer.",
            content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
            color: "#739E73",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });

          $scope.getPaymentMethods($scope.idCustomer);
          $('#paymentMethodModal').trigger("reset");
        }
        else
        {
          //           notify({ message: 'ERROR: Verify your information.', templateUrl:'/views/notify.html'} );
          console.log('ERROR: Verify your information.');
        }
      });
  }


});
app.controller('customerServicesController',        function ($scope, $http){

  $http.get("getCustomerServices", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerData.customerServices = response.data;
    });

  $scope.cSrvCrlFun       = function (){
    $http.get("getCustomerServices", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.customerServices = response.data;
      });
  }
  $scope.showConfirm      = function (idProduct, status) {

    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Would you like to ' + (status == 'disable' ? 'cancel' : 'activate') + ' this service?',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {

        if (status == 'disable')
          $scope.disableService(idProduct);
        else
          $scope.activeService(idProduct);

      }
      if (ButtonPressed === "No") {

        console.log('dijo que no');

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
  $scope.disableService   = function (id){
    $http.get("disableCustomerServices", {params:{'id':$scope.idCustomer, 'idService':id}})
      .then(function (response) {
        $scope.customerServices = response.data;
        $scope.availableServices();
      });
  }
  $scope.activeService    = function (id){
    $http.get("activeCustomerServices", {params:{'id':$scope.idCustomer, 'idService':id}})
      .then(function (response) {
        $scope.customerServices = response.data;
        $scope.availableServices();
      });
  }

});
app.controller('serviceProductController',          function ($scope, $http){

  //   console.log($scope.service.id);
  $http.get("getCustomerProduct", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProduct = response.data;
    });

  $http.get("getCustomerProductType", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProductStatus = response.data;
    });

});
app.controller('customerNotesController',           function ($scope, $http){

  $http.get("getCustomerNotes", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerNotes = response.data;
    });

  $scope.newNote = function(){
    var content = $('#textarea-note-customer').val();
    if(content)
    {
      $http.get("insertCustomerNote", {params:{'id':$scope.idCustomer, 'note':content}})
        .then(function (response) {
          $scope.customerNotes = response.data;
          $('#c-n-f').trigger("reset");
        });
    }
    else
      return;
  }
});
app.controller('customerBillingHistoryController',  function ($scope, $http, $uibModal, $log, DTOptionsBuilder){

  $http.get("getBillingHistory", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.billingHistory = response.data;
    });

  $scope.open = function (){
    $scope.customerId = $scope.idCustomer;

    var modalInstance = $uibModal.open(
      {
        animation: $scope.animationsEnabled,
        templateUrl: 'seeInvoiceById.html',
        controller: 'invoiceController',
        size: 'md',
        resolve: {
          customerId: function () {
            return $scope.customerId;
          }
        }
      });

    modalInstance.result.then(function () {}, function () {
      $log.info('Modal dismissed at: ' + new Date());
    });

  };
  $scope.dtOptions = DTOptionsBuilder.newOptions().withOption('order', [0, 'desc']);
});
app.controller('customersHomeController',           function ($scope, $http, customerService){

  if(customerService.stateRoute == 'customershome'){

    if(customerService.sideBarFlag) {
      $scope.sipTool(2);
      customerService.sideBarFlag = false;
    }
  }


  //SEARCH CUSTOMER HOME
  $scope.cDashboardSearch                = function (){
    if(!this.genericSearch || this.genericSearch == ''){
      $scope.cDashboardSearchResult = null;
      return;
    }
    var query = {'querySearch' : this.genericSearch};

    $http.get("customersSearch", {params:query})
      .then(function (response) {
        $scope.cDashboardSearchResult = response.data;
      });

    return;
  };

  $scope.customerGoTo = function (){
    document.location.href = '#/customers?id=' + this.customerData.id;
  }
})
