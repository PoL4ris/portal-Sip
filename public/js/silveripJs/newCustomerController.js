app.controller('newcustomerAppController', function ($scope, $http, customerService, $state, generalService) {

    console.log('this is the newcustomerAppController');

    if (generalService.sideBarFlag) {
        $scope.sipTool(2);
        generalService.sideBarFlag = false;
    }

    $scope.buildingImageLocation = '/img/logoSmall.png';

    $http.get("getLatestManuallyCustomers")
        .then(function (response) {
            $scope.latestCustomers = response.data;
        })

    $scope.getBuildingInfomation = function (type) {

        var optionVal = null;

        if (type == 0) {
            if (this.selectedOption == '') {
                $('#img-displayed').fadeOut();
                return;
            }

            $scope.tmpDta = this.selectedOption.address;

            //RESET VALUES
            $scope.availableServices = false;
            $scope.buildingSwitches = false;
            $scope.switchAvailablePorts = false;
            $scope.selectedServiceDisplay = false;
            $scope.selectedSwitch = false;
            $scope.portData = false;
            $scope.portIndex = false;
            //RESET VALUES

            $scope.buildingImageLocation = '/img/buildings/' + this.selectedOption.img_building;

            $('#cn-filter').val('');
            optionVal = this.selectedOption.id;
        }
        else {
            if (this.filterBld == '') {
                $('#img-displayed').fadeOut();
                return;
            }

            $scope.buildingImageLocation = '/img/buildings/' + this.filterBld.img_building;
            $scope.selectedBuilding = true;
            $('#cn-filter').val(this.filterBld.code + ' | ' + this.filterBld.name);
            $('#cn-address').val(this.filterBld.address.address);
            $('#cn-city-state-zip').val(this.filterBld.address.city + ', ' + this.filterBld.address.state + ' ' + this.filterBld.address.zip);

            $scope.address = this.filterBld.address.address;
            $scope.citystatezip = this.filterBld.address.city + ', ' + this.filterBld.address.state + ' ' + this.filterBld.address.zip;
            $scope.buildingId = this.filterBld.id;

            optionVal = this.filterBld.id;
        }

        $scope.bldListResult = null;
        // $scope.address =

        $scope.buildingId = optionVal;
        // $('#cn-address-value').val(optionVal);
        // $('#cn-address-value').attr('pass', true);
        $('#img-displayed').fadeIn();

        $scope.getAvailableServices(optionVal);
        $scope.getSwitchInfo(optionVal);
        $scope.resetSelectedService();
        $scope.resetSelectedPort();

    }
    $scope.getAvailableServices = function (idBuilding) {
        $http.get("getAvailableServices", {params: {'id': idBuilding}})
            .then(function (response) {
                $scope.availableServices = response.data;
            });
    }
    $scope.getSwitchInfo = function (idBuilding) {
        $http.get("getBuildingSwitches", {params: {'id': idBuilding}})
            .then(function (response) {
                $scope.buildingSwitches = response.data;
            });
    }

    $scope.serviceSelected = function () {
        var id = this.service.id;
        $scope.selectedServiceDisplay = this.service.product;

        $('.service-list').addClass('unfocus-service');
        $('.service-list').removeClass('selected-service');
        $('.service-list i').fadeOut();
        $('.select-service-' + id).removeClass('unfocus-service');
        $('.select-service-' + id).addClass('selected-service');
        $('.select-service-' + id + ' i').fadeIn();
    }

    $scope.resetSelectedService = function () {
        $scope.selectedServiceDisplay = '';
    }

    $scope.getavailablePorts = function () {

        //RESET VALUES
        $scope.portData = false;
        $scope.portIndex = false;
        //RESET VALUES

        $scope.loadingPorts = false;
        $scope.selectedSwitch = this.switch;
        var id = this.switch.id;
        $scope.ableDisableSwitches(id);
        $http.get("getAvailableSwitchPorts", {params: {'ip': this.switch.ip_address}})
            .then(function (response) {
                $scope.switchAvailablePorts = response.data;
                $scope.loadingPorts = true;
            });
    }

    $scope.portSelected = function () {

        var initIndexId = this.index;
        $scope.portData = this.ports;
        $scope.portIndex = this.index;

        $('.init-ports').addClass('unfocus-ports');
        $('.init-ports').removeClass('selected-port');
        $('.init-ports i').fadeOut();
        $('.port-index-' + initIndexId).removeClass('unfocus-ports');
        $('.port-index-' + initIndexId).addClass('selected-port');
        $('.port-index-' + initIndexId + ' i').fadeIn();

        $('#cn-port').val(initIndexId);
    }

    $scope.resetSelectedPort = function () {

        $scope.portData = false;
        $scope.portIndex = false;
        $scope.loadingPorts = false;
        $scope.selectedSwitch = false;
        $scope.switchAvailablePorts = false;

        $('#cn-port').val('');
    }

    $scope.ableDisableSwitches = function (id) {
        $('.switches-list').addClass('unfocus-switch');
        $('.switches-list').removeClass('selected-switch');
        $('.select-switch-' + id).removeClass('unfocus-switch');
        $('.select-switch-' + id).addClass('selected-switch');
    }

    $scope.verifyNewCustomerForm = function (verifyOnly) {

        // console.log('tab = ' + $('.step-pane.active').attr('data-step'));
        var tabNumber = $('.step-pane.active').attr('data-step');

        var newCustomerFormObjects;

        // if (!formData)
        newCustomerFormObjects = getFormValues('new-customer-form');
        // else
        //     newCustomerFormObjects = formData;

        var errorMessages = {
            'customers.first_name': 'First name',
            'customers.last_name': 'Last name',
            'customers.email': 'Email (missing or invalid format)',
            'contacts.value': 'Phone number (missing or invalid format)',

            'building.id': 'Location',
            'address.unit': 'Unit number (missing or invalid format)',

            'product.id': 'Service or product',
            'switch.id': 'Switch',
            'port.id': 'Port',
        }

        var pIni = '<p class="required-fields-p">';
        var pFin = '</p>';
        var requiredContactErrorTitle = '<div class="req-div">Missing contact info:</div>';
        var requiredLocationtErrorTitle = '<div class="req-div">Missing Location info:</div>';
        var requiredServiceErrorTitle = '<div class="req-div">Missing service/network info:</div>';

        var contactErrorContainer = '';
        $scope.showContactErrors = false;
        var addressErrorContainer = '';
        $scope.showAddressErrors = false;
        var serviceErrorContainer = '';
        $scope.showServiceErrors = false;

        for (var field in newCustomerFormObjects) {
            if (newCustomerFormObjects[field])
                continue;

            // console.log('field = '+field);
            if (field.split('.')[0] == 'customers' || field.split('.')[0] == 'contacts') {
                contactErrorContainer += pIni + errorMessages[field] + pFin;
                $scope.showContactErrors = true;
            }
            if (field.split('.')[0] == 'building' || field.split('.')[0] == 'address') {
                addressErrorContainer += pIni + errorMessages[field] + pFin;
                $scope.showAddressErrors = true;
            }
            if (field.split('.')[0] == 'product' || field.split('.')[0] == 'switch' || field.split('.')[0] == 'port') {
                serviceErrorContainer += pIni + errorMessages[field] + pFin;
                $scope.showServiceErrors = true;
            }
        }

        $scope.contactErrorContainer = $scope.showContactErrors ? (requiredContactErrorTitle + contactErrorContainer) : false;
        $scope.addressErrorContainer = $scope.showAddressErrors ? (requiredLocationtErrorTitle + addressErrorContainer) : false;
        $scope.serviceErrorContainer = $scope.showServiceErrors ? (requiredServiceErrorTitle + serviceErrorContainer) : false;

        // $scope.customerObj = newCustomerFormObjects;

        if ($scope.contactErrorContainer || $scope.addressErrorContainer || $scope.serviceErrorContainer) {
            // console.log('New customer form has errors. Correct them then try again. ');
            $scope.showSpinner = false;
            // $scope.errorResponse = 'Please correct the missing or invalid fields';
            // $scope.triggerErrorMsg();
            return false;
        } else {
            // $scope.errorResponse = false;
            // console.log('New customer form is good: tabNumber = ' + tabNumber + ', ' + 'verifyOnly = ' + verifyOnly);
            if (tabNumber == 5 && !verifyOnly) {
                // console.log('Calling sendNewCustomer()');
                $scope.sendNewCustomer(newCustomerFormObjects);
            }
        }
    }
    $scope.filterBldList = function () {

        if (this.filterBldListModel && this.filterBldListModel.length > 0) {
            $http.get("getFilterBld", {params: {'query': this.filterBldListModel}})
                .then(function (response) {
                    $scope.bldListResult = response.data;
                });
        }
        else {
            $scope.selectedBuilding = false;
        }
    }
    $scope.clearAddress = function () {
        $scope.filterBldListModel = null;

        $('#cn-filter').val('');
        $('#img-displayed img').attr('src', '');
        $('.bloque-b img').attr('src', '');
        $('#cn-address').val('');
        $('#cn-city-state-zip').val('');

        $scope.address = false;
        $scope.citystatezip = false;
        $scope.buildingId = false;

        $scope.filterBldList();
    }
    $scope.filterName = function (name) {

        var result;
        var case1 = name.split('GigabitEthernet');
        var case2 = name.split('FastEthernet');

        if (case1[1])
            result = 'Gi' + case1[1];
        else if (case2[1])
            result = 'Fa' + case2[1];
        else
            result = name;

        return result;

    }


    $scope.sendNewCustomer = function (objects) {

        $scope.showSpinner = true;

        $http.get("insertNewCustomer", {params: objects})
            .then(function (response) {

                $scope.showSpinner = false;

                var newCustomerResponse = response.data;
                if (!newCustomerResponse.error) {

                    console.log(newCustomerResponse);
                    customerId = newCustomerResponse.ok[0].id;
                    $scope.errorResponse = 'Added customer id: ' + customerId;
                    $scope.latestCustomers = newCustomerResponse.ok;
                    $scope.triggerErrorMsg();
                    $scope.resetFullForm();
                    return true;
                }

                for (var errorSection in newCustomerResponse.messages) {
                    if (errorSection == 'customer') {
                        $scope.showCustomerErrorMessages(newCustomerResponse.messages.customer);
                        return true;
                    }
                    // Add more sections here
                }

                // comment these out later
                $scope.newCustomerResponse = response.data
                console.log($scope.newCustomerResponse);

            })
    }
    $scope.triggerErrorMsg = function () {
        $scope.showSpinner = false;

        $('.error-message p').css('background', 'rgba(220, 20, 60, 0.42)');
        $('.error-message p').css('border', '2px solid rgba(220, 20, 60, 0.42)');

        setTimeout(function () {
            $('.error-message p').css('background', 'white');
            $('.error-message p').css('border', '1px solid rgba(220, 20, 60, 0.2)');
            $('.error-message p').css('border-radius', '3px');
        }, 2500);
    }

    $scope.showCustomerErrorMessages = function (customerErrors) {

        var pIni = '<p class="required-fields-p">';
        var pFin = '</p>';
        var contactErrorTitle = '<div class="req-div">Contact errors:</div>';
        var contactErrorContainer = '';
        $scope.showContactErrors = true;

        for (var field in customerErrors) {
            contactErrorContainer += pIni + customerErrors[field] + pFin;
        }
        $scope.contactErrorContainer = contactErrorTitle + contactErrorContainer;
    }

    $scope.resetScopeVariables = function () {
        $scope.model
            = $scope.validaMail
            = $scope.validaTel
            = $scope.validVip
            = $scope.buildingId
            = $scope.address
            = $scope.citystatezip
            = $scope.validaUnit
            = $scope.selectedServiceDisplay
            = $scope.selectedSwitch
            = $scope.selectedBuilding
            = $scope.portIndex
            = $scope.portData
            = $scope.loadingPorts
            = $scope.selectedSwitch
            = $scope.switchAvailablePorts
            = $scope.availableServices
            = $scope.buildingSwitches
            = null;
    }
    $scope.resetFullForm = function (verifyForm) {

        $scope.showSpinner = false;

        $('#new-customer-form').trigger("reset");

        $scope.resetScopeVariables();
        $scope.clearAddress();

        $('.cn-containers input').css('border-bottom', '1px solid #ddd');
        $('.cn-containers input').css('-moz-border-bottom-colors', '#ddd');

        if (verifyForm) {
            $scope.verifyNewCustomerForm(true);
        }

        console.log(getFormValues('new-customer-form'));

        $('#inicio-nc-form').click();
    }


});