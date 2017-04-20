
//Support Controllers
app.controller('supportController',                 function ($scope, $http, DTOptionsBuilder, customerService, supportService){

  $scope.getAllOpenTickets              = function (event){
    supportService.refreshRoute = 'getAllOpenTickets';
    setActiveBtn(event.target);
    setLoading(1);
    $http.get("getAllOpenTickets")
      .then(function (response) {
        $scope.supportData = response.data;
        setLoading(0);
      });
  };
  $scope.getNoneBillingTickets          = function (event){
    supportService.refreshRoute = 'getNoneBillingTickets';
    if(event){
      setActiveBtn(event.target);
      setLoading(1);
    }
    $http.get("getNoneBillingTickets")
      .then(function (response) {
        $scope.supportData = response.data;
        setLoading(0);
      });
  };
  $scope.getBillingTickets              = function (event){
    supportService.refreshRoute = 'getBillingTickets';
    setActiveBtn(event.target);
    setLoading(1);
    $http.get("getBillingTickets")
      .then(function (response) {
        $scope.supportData = response.data;
        setLoading(0);
      });
  };
  $scope.getMyTickets                   = function (event){
    supportService.refreshRoute = 'getMyTickets';
    setActiveBtn(event.target);
    setLoading(1);
    $http.get("getMyTickets")
      .then(function (response) {
        $scope.supportData = response.data;
        setLoading(0);
      });
  };
  if(customerService.stateRoute == 'support'){

    if(customerService.sideBarFlag) {
      $scope.sipTool(2);
      customerService.sideBarFlag = false;
    }

    $scope.getNoneBillingTickets();

    $http.get("getTicketOpenTime")
      .then(function (response) {
        $scope.ticketOpenTime = response.data;
      });
  }
  $scope.letterLimit = 40;
  $scope.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(50).withOption('order', [8, 'desc']);
  $scope.showFullComment                = function(id) {
    $('#ticket-' + id).fadeIn('slow');
  };
  $scope.hideFullComment                = function(id) {
    $('#ticket-' + id).fadeOut('fast');
  };
  $scope.displayCustomerResume          = function (id){
    $scope.stcid = id;
    $scope.stcFlag = false;
    callMidView('Customer');
  };//NO se usará mas
  //MODAL DATA
  $scope.displayTicketResume            = function (id, idCustomer){
    supportService.searchFlag = true;
    $scope.selectedCustomerTicket = null;
    $scope.midTicketId = id;
    $scope.stcid       = idCustomer;
    $scope.stcFlag     = true;

    $scope.getTicketInfo();
    $scope.getReasons();
    $scope.getUsers();
  };
  $scope.getTicketInfo                  = function () {
    $http.get("getTicketInfo", {params:{'ticketId':$scope.midTicketId}})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });

  };
  $scope.getReasons                     = function () {
    $http.get("getReasonsData")
      .then(function (response) {
        $scope.dataReasons = response.data;
      });
  };
  $scope.getUsers                       = function () {
    $http.get("admin")
      .then(function (response) {
        $scope.dataUsersAssigned = response.data;
      });
  };
  $scope.editFormByType                 = function (id) {

    tempTicketID = id;

    if ($('#' + id).attr('stand') == '1') {
      $('.' + id + '-label').css('display','table-cell');
      $('.' + id + '-edit').css('display','none');
      $('#save-' + id).fadeOut( "slow" );
      $('#' + id).html('Edit');
      $('#' + id).switchClass('btn-danger', 'btn-info');
      $('#' + id).attr('stand', '2');
//           if(path == '/supportdash')
//           {
//               $('.resultadosComplex').html('');
//               $('.dis-input').val('');
//           }

      if (id == 'block-b')
        $('#block-b-search').fadeOut();

    }
    else {
      $('.' + id + '-label').css('display','none');
      $('.' + id + '-edit').fadeIn( "slow" );
      $('#save-' + id).fadeIn( "slow" );
      $('#' + id).html('Cancel');
      $('#' + id).switchClass('btn-success', 'btn-danger');
      $('#' + id).attr('stand', '1');

      if (id == 'block-b')
        $('#block-b-search').fadeIn();

    }
//
//       if (id == 'block-b')
//         $('#block-b-search').fadeIn();

  };
  $scope.submitForm                     = function (idForm) {

    var infoData = getFormValues(idForm);

    infoData['id'] = $scope.selectedTicket.id;

    $http.get("updateTicketDetails", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
  };
  $scope.submitFormUpdate               = function (idForm) {

    var infoData = getFormValues(idForm);
    if (infoData.comment == '')
      return;

    infoData['id'] = $scope.selectedTicket.id;

    $http.get("updateTicketHistory", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
    $('.thistory-form-2').val('');
  };
  $scope.refreshSupportContent          = function (){
    $http.get(supportService.refreshRoute)
      .then(function (response) {
        $scope.supportData = response.data;
      });
  };
  //MODAL
  $scope.buscadorTicketModal            = function (){

    if(!this.genericSearch)
    {
      $scope.genericSearchResult = false;
      $scope.focusIndex = 0;
      return;
    }
    var query = {'querySearch' : this.genericSearch};

    $http.get("customersSearch", {params:query})
      .then(function (response) {
        $scope.genericSearchResult  = response.data;
      });

  };
  $scope.clearSearchTicketModal         = function (){

    this.genericSearch   = null;
    $scope.genericSearch = null;
    $scope.focusIndex = 0;
    $scope.buscadorTicketModal();
  };
  $scope.setCustomerTicket              = function (){

    $scope.selectedCustomerTicket = this.resultSearch;
    $scope.clearSearchTicketModal();

  };
  $scope.updateCustomerOnTicket         = function (){
    var objects = getFormValues('customer-update-ticket-form');

    $http.get("updateCustomerOnTicket", {params : objects})
      .then(function (response) {
        $scope.selectedTicket = response.data;

        $http.get(supportService.refreshRoute)
          .then(function (response) {
            $scope.supportData = response.data;
          });

      });

  };
  //SEARCH
  $scope.getTicketSearch                = function (){

    if(!this.genericSearch || this.genericSearch == ''){
      $scope.genericTicketSearchResult = null;
      return;
    }

    var query = {'querySearch' : this.genericSearch};

    $http.get("getTicketsSearch", {params:query})
      .then(function (response) {
        $scope.genericTicketSearchResult = response.data;
      });

    return;
  };
  $scope.cleanTicketSearch              = function(){
    $('#support-ticket-search-form').trigger('reset');
    $scope.genericTicketSearchResult = null;
  };
  function setLoading (status) {
    if(status == 1)
      $('.loading-gif-support').css('display', 'inline-block');
    else
      $('.loading-gif-support').css('display', 'none');
  };
  function setActiveBtn (element) {
    $('.support-status').removeClass('support-active');
    $(element).addClass('support-active');
  };
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