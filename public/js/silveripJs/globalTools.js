// Global Tools //
app.controller('globalToolsCtl',                    function ($scope, $http, $compile, $sce, $stateParams, customerService, supportService, buildingService, generalService){

  $scope.customerData   = {};
  $scope.globalScopeVar = true;
  $scope.sipToolLeft    = false;
  $scope.sipToolRight   = true;
  $scope.focusIndex     = 0;




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

    console.log('singleUpdateXedit -> globalTools function to send xedit update');
    console.log('Function singleUpdateXedit---> with routeFunction ---' + routeFunction);

    var mainId;
    if(generalService.stateRoute == 'buildings')
      mainId = buildingService.building.id;
    else
      mainId = customerService.customer.id;

    var data = {};
    data['id']        = mainId;
    data['value']     = value;
    data['field']     = field;
    data['table']     = table;
    data['id_table']  = id;

    $http.get("update" + table + "Table", {params:data})
      .then(function (response) {
        if(response.data == 'OK')
        {
          if(generalService.stateRoute == 'buildings')
            return 'OK';

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
    if($scope.keyboardBtn){
      $scope.keyboardBtn = !$scope.keyboardBtn;
      return;
    }
//       var string = $('.stringSearch').val();

    if(!this.searchCustomer)
//       if(!this.searchCustomer || string.length == 0)
    {
      $scope.customerSearchResult = false;
      $scope.focusIndex = 0;
      return;
    }

    var query = {'querySearch' : this.searchCustomer};

//       console.log(query);

    $http.get("customersSearch", {params:query})
      .then(function (response) {
//             if(supportService.searchFlag){
//                 $scope.genericSearchResult  = response.data;
//             }
//             else
        $scope.customerSearchResult = response.data;
      });

    return;

  };
  $scope.clearSearch          = function (){

    if($scope.keyboardBtn)
      return;

    this.searchCustomer   = null;
    $scope.searchCustomer = null;
    $scope.focusIndex = 0;
    $scope.buscador();
  }

  //  KEYBOARD ACTIONS
  $(document).keydown(function(e) {

    switch(e.which) {
      case 13: // ENTER
        if($scope.keyboardBtn)
          return;

        if($scope.customerSearchResult)
        {
          $scope.keyboardBtn = true;
          $('.focus-index-focus').children().trigger( "click" );
          $scope.searchCustomer = null;
          $scope.focusIndex = 0;
          $('#customer-global-search-id').trigger("reset");
          $scope.customerSearchResult = false;

        }
        e.stopPropagation();
        break;
      case 38: // ARROW UP
        if($scope.customerSearchResult)
        {
          $scope.keyboardBtn = true;
          $scope.focusIndex--;
        }
        e.stopPropagation();
        break;
      case 40: // ARROW DOWN
        if($scope.customerSearchResult)
        {
          $scope.keyboardBtn = true;
          $scope.focusIndex++;
        }
        e.stopPropagation();
        break;
      case 27: // ESCAPE
        if($scope.keyboardBtn)
          return;

        $scope.keyboardBtn = true;

        if($scope.customerSearchResult) {
          $scope.searchCustomer = null;
          $scope.focusIndex = 0;
          $('#customer-global-search-id').trigger("reset");
          $scope.customerSearchResult = false;
          $scope.clearSearch();
          e.stopPropagation();
        }
        break;
      default: return; // exit this handler for other keys
    }
    e.stopPropagation();
    e.preventDefault(); // prevent the default actions
  });




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
     console.log(id + ' <--|--id|:: '+  value+ ' <--|--value|:: ' +  field + ' <--|--field|:: ' +  table + ' <--|--table|:: '+  idContainer + ' <--|--idContainer|');
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
app.controller('userAuthController',                function ($scope){
  $scope.userDataAuth = JSON.parse($('#auth-user').val());
})