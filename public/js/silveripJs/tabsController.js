app.controller('tabsController', function ($scope, $http, customerService, generalService) {

  $scope.addToTabArray      = function (id){

    if (customerService.customerArray[id])
      return;

    customerService.customerArray[id] = id;
    customerService.lastRequestedId   = id;
    $scope.tabsArray = customerService.customerArray;
    generalService.rightView          = true;
    $scope.customerServiceData        = customerService.tabs;

  };

  $scope.removeFromTabArray = function (id){

    delete customerService.customerArray[id];
    $scope.tabsArray = customerService.customerArray;
    delete customerService.tabs[id];
  }

  $scope.createTab          = function ($event, action){

    $scope.callTabAction = action;
    $scope.addToTabArray(action ? customerService.lastRequestedId : this.customerData.id);

    if($event)
      $event.stopPropagation();
  }

  $scope.customerGoToTab    = function (){
    customerService.lastRequestedId = this.customerData.id;
    $('#customer-tab-link-id-' + this.customerData.id).trigger('click');
  }

});