app.controller('menuController',                    function($scope, $http){
  $http.get('/menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
});
//Building Controllers
app.controller('buildingCtl',                       function($scope, $http) {

  if (!$scope.sbid) {
    $scope.SiteMenu = [];
    $http.get('buildings').then(function (data){
      $scope.bldData = data.data;
      $scope.bld = $scope.bldData.building;
//       $scope.offsetLimitFunction($scope.bldData.offset, $scope.bldData.limit);
    }), function (error){
      alert('Error');
    }
  }
  else {
    $http.get("buildings/" + $scope.sbid)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }
  $scope.displayBldData = function (idBld) {
    $http.get("buildings/" + idBld)
      .then(function (response) {
        $scope.bld = response.data.building;
      });

  }
  $scope.displayBldForm = function () {
    if ($scope.show == false) {
      $scope.show = true;
      $('#bld-content-form').fadeIn('slow');
      $('#add-bld-btn').fadeOut('fast');
      $('#cancel-bld-btn').fadeIn('fast');
    }
    else {
      $scope.show = false;
      $('#bld-content-form').fadeOut('slow');
      $('#add-bld-btn').fadeIn('fast');
      $('#cancel-bld-btn').fadeOut('fast');
    }
  }
  $scope.buildingsList = function (position) {
    //Math var operations.
    var offset              = parseInt($scope.bldData.offset);
    var limit               = parseInt($scope.bldData.limit);
    var a                   = parseInt(offset);
    var b                   = parseInt(limit);
    //Back Arrow empty
    if (position == 0 && offset <= 0 || ($scope.limitoffset  == 'dif' && position == 1))
      return;
    //Solve correct LIMIT OFFSET info to request
    if(position == 1)
    {
      offset = b;
      limit = b + (b - a);
    }
    else
    {
      limit = a;
      offset = a - (b - a);
    }
    //Case result is wrong
    if (offset < 0 || limit <= 0)
      return;
    //Main info to do request
    var query = {"offset": offset, "limit": limit, "position": position};


    $http.get("buildingsList", {params:query})
      .then(function (response) {
        if (response.data.length == 0)
          $scope.limitoffset  = 'dif';
        else
        {
          $scope.bldData['buildingList'] = response.data;
          $scope.limitoffset  = '';
        }
      });

    if($scope.limitoffset  == 'dif')
    {
      limit = offset;
      offset = (limit - 20);
    }

    $scope.bldData.offset = offset;
    $scope.bldData.limit = limit;

//     $scope.offsetLimitFunction(offset, limit);
  }
  $scope.buscador = function(searchType, side) {
    var query = {};

    if (side == 'left')
      query = {'querySearch' : this.searchLeft};
    else
      query = {'querySearch' : this.searchRight};

    $http.get("buildingsSearch", {params:query})
      .then(function (response) {
        if (side == 'left')
          $scope.bldSearchResultLeft = response.data;
        else
          $scope.bldSearchResultRight = response.data;
      });

    return;

  }
  $scope.clearSearch = function (){
    this.searchRight = '';
    $scope.buscador();
  }
  $scope.editFormByType = function (id) {

    tempTicketID = id;

    if ($('#' + id).attr('stand') == '1')
    {
      $('.' + id + '-label').css('display','table-cell');
      $('.' + id + '-edit').css('display','none');
      $('#save-' + id).fadeOut( "slow" );
      $('#' + id).html('Edit');
      $('#' + id).switchClass('btn-danger', 'btn-info');
      $('#' + id).attr('stand', '2');
      if(path == '/supportdash')
      {
        $('.resultadosComplex').html('');
        $('.dis-input').val('');
      }

    }
    else
    {
      $('.' + id + '-label').css('display','none');
      $('.' + id + '-edit').fadeIn( "slow" );
      $('#save-' + id).fadeIn( "slow" );
      $('#' + id).html('Cancel');
      $('#' + id).switchClass('btn-success', 'btn-danger');
      $('#' + id).attr('stand', '1');
    }

  }
  $scope.submitForm = function () {
    console.log('buildingCtl');
    var objects = $('#building-update-form').serializeArray();
    var infoData = {};
    for(var obj in objects )
    {
      if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
      {
        var tmp = objects[obj]['name'].split('id_');
        alert('Verify ' + (tmp[1]?tmp[1]:objects[obj]['name']) + ' Field');
        return;
      }
      infoData[objects[obj]['name']] = objects[obj]['value'];
    }

    $http.get("updateBuilding", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    $scope.editFormByType('block-a');
  }
  $scope.getBuildingPropertyValues = function(){
    $http.get("getBuildingProperties")
      .then(function (response) {
        $scope.propValuesList = response.data;
      });
  }
  $scope.insertBuildingProperty = function (){

    var infoData = getFormValues('new-bpv-form');
    infoData['id_buildings'] = $scope.bld.id;

    $http.get("insertBuildingProperties", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-property-cancel').scope().fadeViews('bpv-container', 'new-form-function', 0, 'enable', 'add-property', 'add-property-cancel')
    $('#new-bpv-form').trigger("reset");
  }
  $scope.insertBuildingContact = function  (){
    var infoData = getFormValues('new-bc-form');
    if (!infoData['first_name'] && !infoData['last_name'] && !infoData['contact'])
      return;

    infoData['id_buildings'] = $scope.bld.id;

    $http.get("insertBuildingContacts", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-contact-cancel').scope().fadeViews('bc-container', 'new-contact-form', 0, 'enable-contact', 'add-contact', 'add-contact-cancel')
    $('#new-bc-form').trigger("reset");

  };
})
.directive('getBuildingPropValues',                 function (){
  return function (scope){
    scope.getBuildingPropertyValues();
  }

})
.directive('buildingContactForm',                   function(){


  return {
    restrict: 'E',
    replace: true,
    link: function(scope, form){
      form.bootstrapValidator({
        container : '#messages',
        feedbackIcons : {
          valid : 'glyphicon glyphicon-ok',
          invalid : 'glyphicon glyphicon-remove',
          validating : 'glyphicon glyphicon-refresh'
        },
        fields : {
          first_name : {
            validators : {
              notEmpty : {
                message : 'The First Name is required and cannot be empty'
              }
            }
          },
          last_name : {
            validators : {
              notEmpty : {
                message : 'The Last Name is required and cannot be empty'
              }
            }
          },
          contact : {
            validators : {
              notEmpty : {
                message : 'The Contact field is required and cannot be empty'
              }
            }
          },
          fax:{
            validators : {
              stringLength : {
                max : 100,
                message : 'The Fax must be less than 100 characters long'
              }
            }
          },
          company:{
            validators : {
              stringLength : {
                max : 200,
                message : 'The Company must be less than 200 characters long'
              }
            }
          },
          comments : {
            validators : {
              stringLength : {
                max : 500,
                message : 'The Comment must be less than 500 characters long'
              }
            }
          }
        }
      });
    }
  }
})
//Network Controllers
app.controller('networkController',                 function ($scope, $http){

  $http.get("networkdash")
    .then(function (response) {
      $scope.networkData = response.data;
    });

  $scope.switchStatusLink = function (){

//
//     warpol('.SwitchStatusLink').click(function (event) {
//       event.preventDefault();
    var ipAddress = warpol(this).attr('IP');
    var location = warpol(this).attr('LOC');
//       var formDataLoadUrl = "assets/includes/network_switch_handler.php";

    //        console.error('IP = '+ipAddress);

    warpol('#switchInfoDialog').html('');
//      displayAjaxLoader('#switchInfoDialog','<center><span>Loading</span><br><img src="assets/images/ajax-loader-bar.gif" alt=""></center>');
    warpol('#switchInfoDialog').load(formDataLoadUrl, {
      'action': 'get-core-switch-info-page',
      'ipAddress': '"'+ipAddress+'"',
      'location' : location
    }, function(){
//        hideAjaxLoader('#switchInfoDialog');
    }); //, function(responseText){
    warpol('#switchInfoDialog').dialog('open');
    //        warpol('#ticketInfoDialog').css('display','block');
    return false;
//     });


  };
  $scope.addTR = function addTR(id) {
    var stance = warpol('#net-btn-' + id).attr('stance');
    var iconoA = '<i class="fa fa-plus-circle txt-green sign-network"></i>';
    var iconoB = '<i class="fa fa-minus-circle txt-red sign-network"></i>';

    if (stance == '1')
    {
      warpol('#net-btn-' + id).attr('stance', '2');
      warpol('#net-btn-' + id).html(iconoB);
      warpol(getNetworkResult(id)).insertAfter('#det-net-' + id).hide().slideDown('slow');
    }
    else
    {
      warpol('#net-btn-' + id).attr('stance', '1');
      warpol('#net-btn-' + id).html(iconoA);
      warpol('#nt-tmp-data-' + id).remove();
    }
    //getNetworkResult();

  };

});
app.controller('networkControllerTSort',            function (DTOptionsBuilder, DTColumnDefBuilder, $scope){
  var vm = this;
  vm.persons = [];
  vm.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(25).withOption('order', [0, 'desc']);
//   vm.dtColumnDefs = [
//     DTColumnDefBuilder.newColumnDef(0),
//     DTColumnDefBuilder.newColumnDef(1).withClass('WWWWWWW'),
//     DTColumnDefBuilder.newColumnDef(2).notSortable()
//   ];

  vm.persons = $scope.networkData;
});
//Customer Controllers
app.controller('customerControllerList',            function ($scope, $http){
  $http.get("getCustomerList")
    .then(function (response) {
      $scope.supportDataCustomer = response.data;
    });
});
app.controller('customerController',                function ($scope, $http, $stateParams){

  console.log('customerController');
  $scope.idCustomer = 501;

  if ($scope.stcid || $stateParams.id)
    $scope.idCustomer = $scope.stcid ? $scope.stcid : $stateParams.id;

  if (($(location).attr('href').split('http://silverip-portal.com/#/')[1]) == 'customers') {
    $scope.idCustomer = 501;
    $scope.buscadorFlag = true;
  }

  console.log($scope.idCustomer);

  $http.get("customersData", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerData = response.data;
      $scope.bld = $scope.customerData.address;
    });
  $http.get("getContactTypes", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.contactTypes = response.data;
    });
  $http.get("getTableData", {params:{'table':'reasons'}})
    .then(function (response) {
      $scope.newTicketData = response.data;
    });

  $scope.checkboxModel = true;
  $scope.checkboxModelA = true;
  $scope.animationsEnabled = false;
  $scope.currentServiceDisplay = '';

  $scope.clearSearch                = function (){
    this.searchCustomer = '';
    $scope.buscador();
  }
  $scope.buscador                   = function() {

    if(!this.searchCustomer)
    {
      $scope.customerSearchResult = false;
      return;
    }

    var query = {'querySearch' : this.searchCustomer};

    $http.get("customersSearch", {params:query})
      .then(function (response) {
          $scope.customerSearchResult = response.data;
      });

    return;

  }
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

    var infoData = getFormValues('new-ct-form');
    infoData['id_customers'] = $scope.customerData.id;

    $http.get("insertCustomerTicket", {params:infoData})
      .then(function (response) {
        if(response.data == 'OK')
          $.smallBox({
            title: "New Ticket Created!",
            content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
            color: "#739E73",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
          });
      });
  };
  $scope.validate                   = function(value, table, field) {
    var data = {};
    data[field] = value;
    data['id_customers'] = $scope.customerData.id;

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

    var infoData = getFormValues('new-cct-form');
    infoData['id_customers'] = $scope.customerData.id;

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

    var mode = $scope.customerData.servicesMode;

    //Mode updateService customerId = oldIdProduct

    if (mode == 'updateService')
    {
      $http.get("updateCustomerServices", {params:{'id' : $scope.idCustomer,'newId' : $scope.currentServiceDisplay.id, 'oldId' : $scope.customerData.serviceTmpId}})
        .then(function (response) {
          console.log("Service Added / Updated::OK");
          $scope.availableServices();
        });
        //$scope.cancel();
      $('#myModalService').modal('toggle');
    }
    else
    {
      $http.get("insertCustomerService", {params:{'idCustomer':$scope.idCustomer,'idProduct' :$scope.currentServiceDisplay.id}})
        .then(function (response) {
          console.log("Service Added / Updated::OK");
          $scope.availableServices();
        });
        //$scope.cancel();
      $('#myModalService').modal('toggle');
    }
  }
  $scope.setModeType                = function (modeType){
    $scope.customerData.servicesMode = modeType;
    $scope.customerData.serviceTmpId = this.service.id;
  }
  $scope.serviceDataDisplay         = function(option) {
    if(option)
      $scope.currentServiceDisplay = this.customerProduct.product;
    else
      $scope.currentServiceDisplay = this.selectedItem;
  };
  $scope.availableServices          = function(){
    $http.get("getCustomerServices", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.customerData.customerServices = response.data;
      });
  }

});
app.controller('customerTicketHistoryController',   function ($scope, $http){
  $http.get("getTicketHistory", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.ticketHistory = response.data;
      $scope.letterLimit = 20;
    });
  $scope.showFullComment  = function(id) {
    $('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment  = function(id) {
    $('#ticket-' + id).fadeOut('fast');
  }
});
app.controller('customerBillingHistoryController',  function ($scope, $http){

  if(!$scope.billingHistory)
    $http.get("getBillingHistory", {params:{'id':$scope.idCustomer}})
      .then(function (response) {
        $scope.billingHistory = response.data;
      });

  $scope.setInvoiceData = function (){
    $scope.customerData['prueba'] = this.billing;
  };

});
app.controller('customerNetworkController',         function ($scope, $http){

  $http.get("getCustomerNetwork", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerNetwork = response.data[0];
    });

  $scope.networkServices    = function (service) {
    networkServices(service);
  }
  function networkServices (service) {

    var routes = ['networkCheckStatus',
                  'netwokAdvancedInfo',
                  'networkAdvanceIPs',
                  'networkRecyclePort',
                  '4',
                  'networkSignUp',
                  'networkActivate'];

    $('.network-functions').addClass('disabled');

    var service = service;
    var portID = $scope.customerNetwork.port_number;
    var customerID = $scope.customerData.id;
    var dataSend = {'portid':portID, 'id':customerID};

    //AJAX request
    $.ajax(
      {type:"GET",
        url:"/" + routes[service],
        data:dataSend,
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          $.each(data,function(i, item) {
            $('#' + i).html(item);
          });
//           $('#basic-info-net').notify('OK');

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

          service = 2;
          $.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:dataSend,
              success: function(data)
              {

//                 $('#IPs').notify('IPs Array.');
                $.smallBox({
                  title: "IPs Array.",
                  content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
                  color: "#739E73",
                  iconSmall: "fa fa-thumbs-up bounce animated",
                  timeout: 6000
                });

                $('.network-functions').removeClass('disabled');

//                   $.each(data,function(i, item)
//                   {
//                     $('#' + i).html(item);
//                   });

              }
            }
          );

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
  $scope.smartModEg1        = function () {

    var service       = $('#rport').attr('type');
    var portID        = $('#rport').attr('portid');
    var serviceID     = $('#rport').attr('serviceid');
    var serviceStatus = $('#rport').attr('displaystatus');
    var routeID       = $('#rport').attr('route');



    $.SmartMessageBox({
      title: "Please Confirm Your Action!",
      content: "Once you click Yes, you need to wait the process to finish.",
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {

        if (portID)
          networkServices(service);
        else if(serviceID)
          servicesInfoUpdate(serviceID, serviceStatus, routeID);

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

});
app.controller('customerBuildingController',        function ($scope, $http){

  console.log($scope.customerData);
  console.log('this is Entramos');

  if($scope.customerData) {
    $http.get("buildings/" + $scope.customerData.address.id_buildings)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }

});











//PAYMENT METHOD INCOMPLETE.
app.controller('customerPaymentMethodsController',  function ($scope, $http){
console.log('something here con el id de  : ' + $scope.idCustomer);
// return;
// app.controller('customerPaymentMethodsController',  function ($scope, $http,$uibModal, $log){
// return;
  $http.get("getCustomerPayment", {params:{'id':$scope.stcid?$scope.stcid:$scope.idCustomer}})
    .then(function (response) {
      $scope.paymentData = response.data[0];
      console.log(response.data);
    });


  $http.get("getPaymentMethods", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.paymentMethods = response.data;
      console.log(response.data);
    });

  $scope.setDefault = function (id) {
    $http.get("updatePaymentMethods", {params:{'id':id, 'customerID':$scope.customerData.id}})
      .then(function (response) {
        $scope.paymentMethods = response.data;

        $http.get("getCustomerPayment", {params:{'id':$scope.stcid?$scope.stcid:$scope.customerData.id}})
          .then(function (response) {
            $scope.paymentData = response.data[0];
          });
      });
  };
  $scope.getPaymentMethods = function (customerId){
    $http.get("getPaymentMethods", {params:{'id':customerId}})
      .then(function (response) {
        $scope.paymentMethods = response.data;
      });
  };
  $scope.openManualRef = function (){
    $scope.openTransparentBGManual();
    $('.manual-ref').fadeIn('slow');
  };
  $scope.openManualChar = function (){
    $scope.openTransparentBGManual();
    $('.manual-char').fadeIn('slow');
  };
  $scope.openTransparentBGManual = function (){
    $('.transparent-charge').fadeIn();
  };
  $scope.refundFunct = function (){
    var cid = $scope.customerData.id;
    var amount = $('#mf-input-am').val();
    var desc = $('#mf-input-de').val();

    $http.get("refundAmount", {params:{'cid':cid, 'amount':amount, 'desc':desc}})
      .then(function (response) {
//         $scope.paymentMethods = response.data;
        console.log(response.data);
        if(response.data.RESPONSETEXT == 'RETURN ACCEPTED')
          $scope.closeTransparentBGManual();
      });
  };
  $scope.chargeFunct = function (){
    var cid = $scope.customerData.id;
    var amount = $('#mc-input-am').val();
    var desc = $('#mc-input-de').val();

    $http.get("chargeAmount", {params:{'cid':cid, 'amount':amount, 'desc':desc}})
      .then(function (response) {
//         $scope.paymentMethods = response.data;
        console.log(response.data);
        if(response.data.RESPONSETEXT == 'APPROVED')
          $scope.closeTransparentBGManual();
      });
  };
  $scope.closeTransparentBGManual = function (){
    $('.transparent-charge').fadeOut();
    $('.manual-ref').fadeOut();
    $('.manual-char').fadeOut();
    $('#mc-input-am').val('');
    $('#mc-input-de').val('');
    $('#mf-input-am').val('');
    $('#mf-input-de').val('');
  };
  $scope.open = function (){
    $scope.customerId = $scope.customerData.id;

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
console.log('this is addPaymentMethodController ');
  $scope.addNewPaymentMethod = function (){
    var objects = $('#paymentmethodform').serializeArray();

    if(!objects[0].value || !objects[1].value || !objects[2].value || !objects[3].value || !objects[4].value || !objects[5].value || !objects[6].value)
      return;

    var regexCC = /\b(?:3[47]\d{2}([\ \-]?)\d{6}\1\d|(?:(?:4\d|5[1-5]|65)\d{2}|6011)([\ \-]?)\d{4}\2\d{4}\2)\d{4}\b/g;

    console.log(objects);
    console.log(regexCC.test(objects[1].value));
    var boolResult = regexCC.test(objects[1].value);



    if (boolResult == true)
      console.log('this is true ===> ' + boolResult);
    else
      console.log('this is false ===> ' + boolResult);


      return;








    if (!regexCC.test(objects[1].value)) {
//       notify({ message: 'Verify your Account Number', templateUrl:'/views/notify.html'} );
      console.log('Verify your Account Number');
//       return;
    }
    else{
      console.log('this is false');
    }

    return;



    if(objects[6].value.length < 3 || objects[6].value.length > 4) {
//       notify({ message: 'Verify your CCV Number', templateUrl:'/views/notify.html'} );
      console.log('Verify your CCV Number');
      return;
    }

    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    infoData['id_customers'] = customerId;

    $http.get("insertPaymentMethod", {params:infoData})
      .then(function (response) {
        if(response.data == 'OK')
        {
          $uibModalInstance.dismiss('cancel');
//           notify({ message: 'Account ' + infoData['account_number'] + ' ready to use.', templateUrl:'/views/notify.html'} );
          console.log('Account ' + infoData['account_number'] + ' ready to use.');
          angular.element('#tom').scope().getPaymentMethods(customerId);
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

  $http.get("getAvailableServices", {params:{'id':$scope.idCustomer}})
    .then(function (response) {
      $scope.customerData.availableServices = response.data;
    });

  $scope.cSrvCrlFun       = function (){
    $http.get("getCustomerServices", {params:{'id':$scope.customerData.id}})
      .then(function (response) {
        $scope.customerServices = response.data;
      });
  }
  $scope.showConfirm      = function (idProduct, status) {

    $.SmartMessageBox({
      title: "Please Confirm Your Action!",
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

  console.log($scope.service.id);
  $http.get("getCustomerProduct", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProduct = response.data;
    });

  $http.get("getCustomerProductType", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProductStatus = response.data;
    });

});
//Support Controllers
app.controller('supportController',                 function ($scope, $http, DTOptionsBuilder){

  $http.get("supportTickets")
    .then(function (response) {
      $scope.supportData = response.data;
    });

  $http.get("getTicketOpenTime")
    .then(function (response) {
      $scope.ticketOpenTime = response.data;
    });

  $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(25).withOption('order', [10, 'desc']);
  $scope.letterLimit = 400;

  $scope.showFullComment = function(id) {
    $('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment = function(id) {
    $('#ticket-' + id).fadeOut('fast');
  }

  function callMidView (view) {
    $scope.globalViewON = view;
    var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
//       warpol("#mid-content-tickets").html(compiledeHTML);
    warpol("#viewMidContent").html(compiledeHTML);
  };//NO se usara mas
  function setActiveBtn (activeView) {
    $scope.activeViewFull     = 'no-style';
    $scope.activeViewBilling  = 'no-style';
    $scope.activeViewAll      = 'no-style';
  };//NO se usara mas

  $scope.displayCustomerResume = function (id){
    $scope.stcid = id;
    $scope.stcFlag = false;
    callMidView('Customer');
  };//NO se usara mas

  //Faltan botones de ALL, Billing, No Billing.
  $scope.fullTickets = function (){
    $http.get("supportTickets")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'Full';
    callMidView('Full');
    setActiveBtn('Full');
    $scope.activeViewFull     = 'Active';
  };//All ticket Not in use
  $scope.billingTickets = function (){
    $http.get("supportTicketsBilling")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'Billing';
    callMidView('Billing');
    setActiveBtn('Billing');
    $scope.activeViewBilling     = 'Active';
  };//Billing tickets Not in use
  $scope.allTickets = function (){
    $http.get("supportTicketsAll")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'All';
    callMidView('All');
    setActiveBtn('All');
    $scope.activeViewAll     = 'Active';
  };//ALL? Not in use



  //MODAL DATA
  $scope.displayTicketResume  = function (id, idCustomer){
    $scope.midTicketId = id;
    $scope.stcid = idCustomer;
    $scope.stcFlag = true;

    $scope.getTicketInfo();
    $scope.getReasons();
    $scope.getUsers();
  };
  $scope.getTicketInfo        = function () {
    $http.get("getTicketInfo", {params:{'ticketId':$scope.midTicketId}})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });

  }
  $scope.getReasons           = function () {
    $http.get("getReasonsData")
      .then(function (response) {
        $scope.dataReasons = response.data;
      });
  }
  $scope.getUsers             = function () {
    $http.get("admin")
      .then(function (response) {
        $scope.dataUsersAssigned = response.data;
      });
  }
  $scope.editFormByType       = function (id) {

    tempTicketID = id;

    if ($('#' + id).attr('stand') == '1')
    {
      $('.' + id + '-label').css('display','table-cell');
      $('.' + id + '-edit').css('display','none');
      $('#save-' + id).fadeOut( "slow" );
      $('#' + id).html('Edit');
      $('#' + id).switchClass('btn-danger', 'btn-info');
      $('#' + id).attr('stand', '2');
      if(path == '/supportdash')
      {
        $('.resultadosComplex').html('');
        $('.dis-input').val('');
      }

    }
    else
    {
      $('.' + id + '-label').css('display','none');
      $('.' + id + '-edit').fadeIn( "slow" );
      $('#save-' + id).fadeIn( "slow" );
      $('#' + id).html('Cancel');
      $('#' + id).switchClass('btn-success', 'btn-danger');
      $('#' + id).attr('stand', '1');
    }

//     if (id == 'block-a')
//     {
//       $scope.getReasons();
//       $scope.getUsers();
//     }

  }
  $scope.submitForm           = function (idForm) {

    var infoData = getFormValues(idForm);

    infoData['id'] = $scope.selectedTicket.id;

    $http.get("updateTicketDetails", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
  }
  $scope.submitFormUpdate     = function (idForm) {

    var infoData = getFormValues(idForm);
    if (infoData.comment == '')
      return;

    infoData['id'] = $scope.selectedTicket.id;

    $http.get("updateTicketHistory", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
    $('.thistory-form-2').val('');
  }
});
app.controller('supportTicketHistory',              function ($scope, $http){
  $http.get("supportTicketHistory", {params:{'id':$scope.history.id}})
    .then(function (response) {
      $scope.historyData = response.data;
    });
});
//En espera de edicion de usuario data
app.controller('supportControllerTools',            function ($scope, $http) {
  console.log('supportControllerTools');
  $scope.buscador = function(side) {
    var query = {};
    if (side == 'center')
      query = {'code': this.searchCenterCode?this.searchCenterCode:false,
        'unit': this.searchCenterUnit?this.searchCenterUnit:false};

    if (query['code'] == false && query['unit'] == false)
    {
      $scope.customerCodeUnitList = '';
      return;
    }

    $http.get("getTicketCustomerList", {params: query})
      .then(function (response) {
        $scope.customerCodeUnitList = response.data;
      });
  }
  $scope.selectCustomerUpdate = function (name, id) {
    warpol('.preview-name').val(name);
    warpol('#save-block-b').attr('idCustomerUpdate', id);
  };
  $scope.updateCustomerTicketName = function () {
    var customerID =  warpol('#save-block-b').attr('idCustomerUpdate')
    var ticketID = $scope.selectedTicket.id;

    $http.get("updateTicketCustomerName", {params:{'id':ticketID, 'id_customers':customerID}})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });

    $scope.customerCodeUnitList = '';
    if ($scope.globalViewON != 'Resume')
      $scope.cancel();
    else
      $scope.displayTicketResume(ticketID);

  }
  $scope.editFormByType = function (id) {

    tempTicketID = id;

    if (warpol('#' + id).attr('stand') == '1')
    {
      warpol('.' + id + '-label').css('display','table-cell');
      warpol('.' + id + '-edit').css('display','none');
      warpol('#save-' + id).fadeOut( "slow" );
      warpol('#' + id).html('Edit');
      warpol('#' + id).switchClass('btn-danger', 'btn-info');
      warpol('#' + id).attr('stand', '2');
      if(path == '/supportdash')
      {
        warpol('.resultadosComplex').html('');
        warpol('.dis-input').val('');
      }

    }
    else
    {
      warpol('.' + id + '-label').css('display','none');
      warpol('.' + id + '-edit').fadeIn( "slow" );
      warpol('#save-' + id).fadeIn( "slow" );
      warpol('#' + id).html('Cancel');
      warpol('#' + id).switchClass('btn-success', 'btn-danger');
      warpol('#' + id).attr('stand', '1');
    }

    if (id == 'block-a')
    {
      $scope.getReasons();
      $scope.getUsers();
    }

  }
});


//User Profile Controllers
app.controller('userProfileController',             function ($scope, $http){

  console.log('COSA LOCA');

  $scope.checkboxModel = false;

  $http.get("getProfileInfo")
    .then(function (response){
      $scope.profileData = response.data;
    });



  $scope.customerEditMode = function (){
    if ( $scope.checkboxModel == true)
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


  $scope.updatePassword = function() {
    var psw1 = this.psw1;
    var psw2 = this.psw2;

    if(psw1 == psw2)
    {
      console.log('passwords match update data');
      $http.get("updateProfileInfo", {params:{'password':psw1}})
        .then(function (response){
          if (response.data == 'OK')
          {

            $.smallBox({
              title: "Password Updated!",
              content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
              color: "#739E73",
              iconSmall: "fa fa-thumbs-up bounce animated",
              timeout: 6000
            });

            $('#uno').val('');
            $('#dos').val('');
            $scope.checkboxModel = true;
            $scope.customerEditMode();
          }

//           console.log( response.data);
        });
    }
    else
      alert('Passwords do not match.');

  };

  $scope.lengthpsw = function ()
  {
    var psw1Length = this.psw1?this.psw1.length:0;
    var psw2Length = this.psw2?this.psw2.length:0;

    if (psw1Length >= 5 && psw2Length >= 5 )
      $('#pswbton').attr('disabled', false);
    else
      $('#pswbton').attr('disabled', true);
  }




});

/* Global Tools */
app.controller('globalToolsCtl',      function ($scope, $http, $compile, $sce){

  console.log('globalToolsCtl');

  $scope.leftColumnOpenClose = function (){
    if($('#content').hasClass("ccr-small"))
    {
      $('#lcontent').removeClass("ccl-small");
      $('#content').removeClass("ccr-small");
      $('.slc-dtrue').toggleClass("display-none");
      $('.slc-dfalse').removeClass("display-none");
      $('#arrowChange').toggleClass("fa-arrow-circle-left");
      $('#arrowChange').removeClass("fa-arrow-circle-right");
    }
    else
    {
      $('#lcontent').toggleClass("ccl-small");
      $('#content').toggleClass("ccr-small");
      $('.slc-dtrue').removeClass("display-none");
      $('.slc-dfalse').toggleClass("display-none");
      $('#arrowChange').toggleClass("fa-arrow-circle-right");
      $('#arrowChange').removeClass("fa-arrow-circle-left");
    }
  };
  $scope.singleUpdateXedit   = function(id, value, field, table) {

    var data = {};
    data['id']    = id;
    data['value'] = value;
    data['field'] = field;
    data['table'] = table;
//     data['id_customers'] = $scope.customerData.id;
    $http.get("update" + table + "Table", {params:data})
      .then(function (response) {
        console.log('OK');
      });
  }
  $scope.fadeViews           = function (view1, view2, action,bt1,bt2,bt3){
  /*
    view1 = view to hide CLASS
    view2 = view to show CLASS
    action = [0 = cancel
              1 = addNew
              2 = ]
    bt1 = actionButtons ID
    bt2 = actionButtons ID
    bt3 = cancelButton  ID
   */

    if(action == 0){
      $('.' + view2).fadeOut();
      $('.' + view1).fadeIn('slow');
      $('#' + bt1).attr('disabled', false);
      $('#' + bt2).attr('disabled', false);
      $('#' + bt3).fadeOut();
    }
    if(action == 1) {
      $('.' + view1).fadeOut();
      $('.' + view2).fadeIn('slow');
      $('#' + bt1).attr('disabled', 'disabled');
      $('#' + bt2).attr('disabled', 'disabled');
      $('#' + bt3).fadeIn();
    }

  };

  });
function gToolsxEdit(value, field, id, idContainer, table){
  angular.element('#' + idContainer + '-gTools').scope().singleUpdateXedit(id, value, field, table);
}
function getFormValues(id){
  var objects = $('#' + id).serializeArray();
  var infoData = {};
  for(var obj in objects ) {
    infoData[objects[obj]['name']] = objects[obj]['value'];
  }
  return infoData;
}
/* User Authenticated Data */
app.controller('userAuthController',   function ($scope){
  $scope.userDataAuth = JSON.parse($('#auth-user').val());
})


