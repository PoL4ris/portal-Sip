app.controller('tabsController', function ($scope, $http, customerService, generalService) {

  customerService.tabs = {};
  customerService.customerArray = [];


    $scope.addToTabArray = function (id) {

        if (customerService.tabs[id]) {
            return;
        }

        customerService.customerArray.push(id);
        customerService.lastRequestedId = id;
        $scope.tabsArray = customerService.customerArray;
        generalService.rightView = true;
        $scope.customerServiceData = customerService.tabs;

//         console.log(customerService);
    };

    $scope.removeFromTabArray = function (id, tabIndex) {

        customerService.customerArray.splice(tabIndex, 1);
        $scope.tabsArray = customerService.customerArray;
        delete customerService.tabs[id];

        if (customerService.customerArray.length > 0) {
            // Open next tab which now has the same index
            $scope.customerGoToTab(customerService.customerArray[tabIndex]);
        } else {
            // Open the customer search tab
            $scope.customerGoToTab('');
        }
    }

    $scope.createTab = function ($event, action) {

        $scope.callTabAction = action;
        $scope.addToTabArray(action ? customerService.lastRequestedId : this.customerData.id);

        if ($event)
            $event.stopPropagation();
    }

    $scope.customerGoToTab = function (customerId) {
        if (customerId == '') {
            // customerService.lastRequestedId = customerId;
            $('#customer-search-tab').trigger('click');
        } else {
            customerService.lastRequestedId = customerId;
            $('#customer-tab-link-id-' + customerId).trigger('click');
        }
    }

});