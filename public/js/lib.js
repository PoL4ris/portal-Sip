app.controller('menuController',          function($scope, $http){
    $http.get('/menumaker').then(function (data){
        $scope.SiteMenu = data.data;
    }), function (error){
        alert('Error');
    }
});


//Building Controllers
app.controller('buildingSideController', function ($scope, $http){

    $http.get("getBuildingsListTMP")
        .then(function (response) {
        $scope.buildingResultSide = response.data;
    });

});
app.controller('buildingCtl',             function($scope, $http, customerService) {

    if(!customerService.sideBarFlag) {
        $scope.sipTool(2);
        customerService.sideBarFlag = true;
    }



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
    .directive('getBuildingPropValues',       function (){
    return function (scope){
        scope.getBuildingPropertyValues();
    }

})
    .directive('buildingContactForm',         function(){


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
app.controller('networkController',       function ($scope, $http, customerService){


    if(customerService.sideBarFlag) {
        $scope.sipTool(2);
        customerService.sideBarFlag = false;
    }

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


    };//????



    $scope.addTR = function addTR(id) {

        var stance   = $('#net-btn-' + id);
        var mas      = 'details-control';
        var menos    = 'dc-show-menos';
        var idString = 'nt-tmp-data-' + id;

        if (stance.attr('stance') == '1')
        {
            console.log('Stance = 2');
            stance.attr('stance', 2);
            stance.removeClass(mas);
            stance.addClass(menos);
            $(' <tr id="' + idString + '"><td colspan="11">info</td></tr>').insertAfter('#det-net-' + id).hide().slideDown('slow');
        }
        else
        {
            console.log('Stance = 1');
            stance.attr('stance', 1);
            stance.removeClass(menos);
            stance.addClass(mas);
            $('#nt-tmp-data-' + id).remove();
        }
    };

    $scope.cleanHrefField        = function (valor){
        var spaceClean = valor.split(' ')[0];
        var httpClean = spaceClean.match('https*');

        return httpClean ? httpClean['input'] : 'http://' + spaceClean;
    }
    $scope.cleanNetField         = function (valor){
        var httpClean = valor.match('https*');
        return httpClean ? httpClean['input'].split('https://')[1] : valor;
    }

});
app.controller('networkControllerTSort',  function (DTOptionsBuilder, DTColumnDefBuilder, $scope ){

    var vm = this;
    vm.persons = [];
    vm.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(25).withOption('order', [1, 'asc']);
    //   vm.dtColumnDefs = [
    //     DTColumnDefBuilder.newColumnDef(0),
    //     DTColumnDefBuilder.newColumnDef(1).withClass('WWWWWWW'),
    //     DTColumnDefBuilder.newColumnDef(2).notSortable()
    //   ];

    vm.persons = $scope.networkData;
});


//Customer Controllers
app.controller('customerControllerList',  function ($scope, $http){
    $http.get("getCustomerList")
        .then(function (response) {
        $scope.supportDataCustomer = response.data;
    });
});


app.controller('customerController',      function ($scope, $http, $stateParams, customerService){

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

        if (($(location).attr('href').split('http://silverip-portal.com/#/')[1]) == 'customers') {
            $scope.idCustomer = 501;
            $scope.buscadorFlag = true;
        }

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
    //----------
    $scope.clearSearch                = function (){
        this.searchCustomer = '';
        $scope.buscador();
    }
    $scope.buscador                   = function () {

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

        if(infoData.id_reasons == '' || infoData.status == '' || infoData.comment == '')
            return;

        infoData['id_customers'] = $scope.customerData.id;

        $http.get("insertCustomerTicket", {params:infoData})
            .then(function (response) {
            if(response.data == 'OK'){
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
        alert('disabled action.');

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

        if(!$scope.currentServiceDisplay)
        {
            $('.add-prod-select-color').css('border-color', 'red');
            return;
        }
        $('.add-prod-select-color').css('border-color', 'inherit');

        var mode = $scope.customerData.servicesMode;

        //Mode updateService customerId = oldIdProduct

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
    };



    $scope.serviceDataDisplay         = function (option) {
        if(option)
        {
            $scope.serviceFlag = true;
            $scope.currentServiceDisplay = this.customerProduct.product;
            $scope.showingCurrent = this.service;
        }
        else
        {
            $scope.serviceFlag = false;
            $scope.currentServiceDisplay = this.selectedItem;
        }
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
    $scope.showConfirmPassword      = function (idProduct, status) {

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


        if(response.data.length > 0){
            console.log(response.data);
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

        console.log(service);
        console.log(portID);
        console.log(customerID);
        console.log(dataSend);


        //AJAX request
        $.ajax(
            {type:"GET",
             url:"/" + routes[service],
             data:dataSend,
             success: function(data)
             {

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
    $scope.smartModEg1        = function () {
        //     console.log($(this).attr('type'));

        var service       = $('#rport').attr('type');
        var portID        = $('#rport').attr('portid');
        var serviceID     = $('#rport').attr('serviceid');
        var serviceStatus = $('#rport').attr('displaystatus');
        var routeID       = $('#rport').attr('route');



        if(service == 5 || service == 6)
            var txtMsg = 'Are you sure you want to send this customer to the signup page?';
        if (serviceID)
            var txtMsg = 'Should I recycle this customer’s port?';


        $.SmartMessageBox({
            title: "Please Confirm",
            content: txtMsg,
            buttons: '[No][Yes]'
        }, function (ButtonPressed) {
            if (ButtonPressed === "Yes") {

                if (portID)
                    networkServices(service);//SEND TO SIGNUP
                else if(serviceID)
                    servicesInfoUpdate(serviceID, serviceStatus, routeID);//Recycle port

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

    $http.get("getCustomerPayment", {params:{'id':$scope.stcid ? $scope.stcid : $scope.idCustomer}})
        .then(function (response) {
        $scope.paymentData = response.data[0];
        $scope.customerData.defaultPayment = $scope.paymentData;
    });
    $http.get("getPaymentMethods", {params:{'id':$scope.idCustomer}})
        .then(function (response) {
        $scope.paymentMethods = response.data;
    });

    $scope.setDefault         = function (id) {
        $http.get("updatePaymentMethods", {params:{'id' : id, 'customerID' : $scope.idCustomer}})
            .then(function (response) {
            $scope.paymentMethods = response.data;

            $http.get("getCustomerPayment", {params:{'id' : $scope.idCustomer}})
                .then(function (response) {
                $scope.paymentData = response.data[0];
            });
        });
    };
    $scope.getPaymentMethods  = function (customerId){
        $http.get("getPaymentMethods", {params:{'id':customerId}})
            .then(function (response) {
            $scope.paymentMethods = response.data;
        });
    };
    $scope.refundFunct        = function (){
        var cid = $scope.customerData.id;
        var amount = $('#mf-input-am').val();
        var desc = $('#mf-input-de').val();

        if(!cid || !amount || !desc)
            return;

        $http.get("refundAmount", {params:{'cid':cid, 'amount':amount, 'desc':desc}})
            .then(function (response) {
            //         $scope.paymentMethods = response.data;
            console.log(response.data);
            if(response.data.RESPONSETEXT == 'RETURN ACCEPTED')
                $scope.closeTransparentBGManual();
        });
    };
    $scope.chargeFunct        = function (){
        var cid = $scope.customerData.id;
        var amount = $('#mc-input-am').val();
        var desc = $('#mc-input-de').val();

        if(!cid || !amount || !desc)
            return;

        $http.get("chargeAmount", {params:{'cid':cid, 'amount':amount, 'desc':desc}})
            .then(function (response) {
            //         $scope.paymentMethods = response.data;
            //         console.log(response.data);
            if(response.data.RESPONSETEXT == 'APPROVED')
                $scope.closeTransparentBGManual();
        });
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
    //OLD THING
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




app.controller('customerNotesController', function($scope, $http){

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

//Support Controllers
app.controller('supportController', function ($scope, $http, DTOptionsBuilder, customerService){

    $scope.getAllOpenTickets      = function (){
        $http.get("getAllOpenTickets")
            .then(function (response) {
            $scope.supportData = response.data;
        });
    };
    $scope.getNoneBillingTickets  = function (){
        $http.get("getNoneBillingTickets")
            .then(function (response) {
            $scope.supportData = response.data;
        });
    };
    $scope.getBillingTickets      = function (){
        $http.get("getBillingTickets")
            .then(function (response) {
            $scope.supportData = response.data;
        });
    };
    $scope.getMyTickets           = function (){
        $http.get("getMyTickets")
            .then(function (response) {
            $scope.supportData = response.data;
        });
    };

    if(customerService.stateRoute == 'support'){

        if(customerService.sideBarFlag) {
            $scope.sipTool(2);
            customerService.sideBarFlag = false;
        }

        $scope.getAllOpenTickets();

        $http.get("getTicketOpenTime")
            .then(function (response) {
            $scope.ticketOpenTime = response.data;
        });
    }

    $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(50).withOption('order', [9, 'asc']);
    $scope.letterLimit = 40;

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
    };//NO se usará mas
    //MODAL DATA
    $scope.displayTicketResume  = function (id, idCustomer){
        $scope.midTicketId = id;
        $scope.stcid       = idCustomer;
        $scope.stcFlag     = true;

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
app.controller('supportTicketHistory',    function ($scope, $http){
    $http.get("supportTicketHistory", {params:{'id':$scope.history.id}})
        .then(function (response) {
        $scope.historyData = response.data;
    });
});


//En espera de edicion de usuario data
app.controller('supportControllerTools',  function ($scope, $http) {
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
            warpol('#' + id).switchClass('btn-danger', 'btn-default');
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
app.controller('userProfileController',   function ($scope, $http){

    $scope.checkboxModel = false;

    $http.get("getProfileInfo")
        .then(function (response){
        $scope.profileData = response.data;
    });

    $scope.customerEditMode   = function (){
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
    $scope.updatePassword     = function() {
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
    $scope.lengthpsw          = function () {
        var psw1Length = this.psw1?this.psw1.length:0;
        var psw2Length = this.psw2?this.psw2.length:0;

        if (psw1Length >= 5 && psw2Length >= 5 )
            $('#pswbton').attr('disabled', false);
        else
            $('#pswbton').attr('disabled', true);
    }

});


// Global Tools //
app.controller('globalToolsCtl',      function ($scope, $http, $compile, $sce, $stateParams, customerService){

    $scope.customerData   = {};
    $scope.globalScopeVar = true;
    $scope.sipToolLeft    = false;
    $scope.sipToolRight   = true;

    $scope.leftColumnOpenClose  = function (){
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
    $scope.singleUpdateXedit    = function(id, value, field, table, routeFunction) {

        console.log('Function singleUpdateXedit---> with routeFunction ---' + routeFunction);

        var data = {};
        data['id']        = customerService.customer.id;
        data['value']     = value;
        data['field']     = field;
        data['table']     = table;
        data['id_table']  = id;

        $http.get("update" + table + "Table", {params:data})
            .then(function (response) {
            if(response.data == 'OK')
            {
                if(routeFunction)
                    $scope.resolveRouteFunction(routeFunction, customerService.customer.id);

                return 'OK';
            }
            else
                alert('ERROR');

        });
    }
    $scope.fadeViews            = function (view1, view2, action,bt1,bt2,bt3){
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
    $scope.sipTool              = function (type, id){
        /*
    * Type
    * 0 = left
    * 1 = right
    * 2 = show/hide
    * */
        if(type == 0) {
            var tmpTxt = 'right';
            if(!$scope.sipToolLeft)
            {
                $scope.sipToolLeft = true;
                $scope.sipToolRight = false;
                $scope.resolveLocationClass(id, tmpTxt);
            }
        }

        if(type == 1) {
            var tmpTxt = 'left';
            if (!$scope.sipToolRight)
            {
                $scope.sipToolLeft = false;
                $scope.sipToolRight = true;
                $scope.resolveLocationClass(id, tmpTxt);
            }
        }

        if(type == 2) {
            var claseA = $('#main').hasClass('silverip-right-location');
            var claseB = $('#main').hasClass('silverip-left-location');

            if(claseA || claseB)
            {
                if($scope.sipToolLeft)
                    $('#silverip-side').addClass('silverip-left-hide');
                else
                    $('#silverip-side').addClass('silverip-right-hide');

                $('#main').removeClass('silverip-left-location');
                $('#silverip-side').removeClass('silverip-left');
                $('#main').removeClass('silverip-right-location');
                $('#silverip-side').removeClass('silverip-right');
            }
            else
            {
                $('#silverip-side').removeClass('silverip-left-hide');
                $('#silverip-side').removeClass('silverip-right-hide');

                if($scope.sipToolLeft)
                {
                    $('#silverip-side').addClass('silverip-left');
                    $('#main').addClass('silverip-left-location');
                }
                else
                {
                    $('#silverip-side').addClass('silverip-right');
                    $('#main').addClass('silverip-right-location');
                }

            }
        }

    }
    $scope.resolveLocationClass = function (id, txt) {
        $('#main').removeClass('silverip-' + txt + '-location');
        $('#silverip-side').removeClass('silverip-' + txt);

        $('#silverip-side').addClass(id);
        $('#main').addClass(id + '-location');
    }
    $scope.buscador             = function () {

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
    $scope.clearSearch          = function (){
        this.searchCustomer = '';
        $scope.buscador();
    }
    $scope.alertDummy           = function (){
        $.smallBox({
            title: "Password Updated!",
            content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
            color: "transparent",
            iconSmall: "fa fa-thumbs-up bounce animated",
            timeout: 6000
        });
    }
    $scope.resolveRouteFunction = function (routeFunction, id){

        switch(routeFunction)
                {
            case 'getCustomerData':
                {
                    angular.element('#customers-gTools').scope().getCustomerStatus(id);
                }
        };

        $http.get("getCustomerLog", {params:{'type':'customer', 'id_type':id}})
            .then(function (response) {
            customerService.customer.log = response.data;
        });

    };
    $scope.parJson              = function (json) {
        return JSON.parse(json);
    }
    $scope.xEditVisual          = function (valor, id){

        switch (id)
                {
            case 'c-c-i-e':
                if(valor)
                    $('#' + id).html('<i class="fa fa-pencil"></i> Edit customer').removeClass('btn-danger').addClass('btn-primary');
                else
                    $('#' + id).html('<i class="fa fa-plus plus-cross"></i> Cancel ').removeClass('btn-primary').addClass('btn-danger');
                break;
            case 'customer-contact':
                if(valor)
                    $('#' + id).html('<i class="fa fa-pencil"></i> Edit customer').removeClass('btn-danger').addClass('btn-primary');
                else
                    $('#' + id).html('<i class="fa fa-plus plus-cross"></i> Cancel ').removeClass('btn-primary').addClass('btn-danger');
                break;
        }
    }
    $scope.convertDate          = function(valor){
        return new Date(valor);
    }
    $scope.warpol = function (warp){
        console.log('Esto entro en Warpol y mando :');
        console.log(warp);
    }



});
function gToolsxEdit(value, field, id, idContainer, table, model){
    //   console.log(id + ' <--|--id|:: '+  value+ ' <--|--value|:: ' +  field + ' <--|--field|:: ' +  table + ' <--|--table|:: '+  idContainer + ' <--|--idContainer|');
    angular.element('#' + idContainer + '-gTools').scope().singleUpdateXedit(id, value, field, table, model);
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













































app.controller('dummuyController', function ($scope, $http){
    console.log('this is oossoomm');
    $scope.dummyControllerData = 'this is the end';

    $http.get("dummyRouteController")
        .then(function (response){
        console.log(response.data);
    });
});