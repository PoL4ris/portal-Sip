// Global Tools //
app.controller('globalToolsCtl',                    function ($scope, $http, $compile, $sce, $stateParams, customerService, supportService, buildingService, generalService, $timeout){

  $scope.customerData   = {};
  $scope.globalScopeVar = true;
  $scope.sipToolLeft    = false;
  $scope.sipToolRight   = true;
  $scope.focusIndex     = 0;
  $scope.statusArrayConstant = generalService.statusArrayConstant;
  $scope.cacheClear = generalService.cacheClear;

  //Get Constants from constantConfig
  $http.get("getConstantData").then(function (response) {
    generalService.constPHP = response.data;
  });


  //console.log($.browser.mobile);
  $scope.mobDevice = $.browser.mobile;
  $scope.mobDevice = true;

//  console.log($scope.statusArrayConstant);
//  console.log(localStorage);
//  console.log($templateCache.info);
//  console.log($state);
//  console.log(appConfig);
//  console.log(generalService);
//  console.log(customerService);


  $scope.factoryReset = function () {
      $.SmartMessageBox({
          title: "<i class='fa fa-refresh' style='color:green'></i> Clear Local Storage",
          content: "Would you like to RESET all your saved widgets and clear LocalStorage?1",
          buttons: '[No][Yes]'
      }, function (ButtonPressed) {
          if (ButtonPressed == "Yes" && localStorage) {
              localStorage.clear();
              location.reload()
          }
      });
  };

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
  $scope.singleUpdateXedit    = function (id, value, field, table, model, index, routeFunction = 'getCustomer') {

//    console.log('singleUpdateXedit -> globalTools function to send xedit update');
//    console.log('Function singleUpdateXedit---> with routeFunction ---' + routeFunction);
//    console.log(routeFunction);
//    console.log(customerService.customer.id);

    var mainId;
    if(generalService.stateRoute == 'buildings')
      mainId = buildingService.building.id;
    else
      mainId = id;

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

          if(routeFunction){
            $scope.resolveRouteFunction(routeFunction, id);
          }

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
        console.log(response.data.length);
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
        console.log('this is enter');

//        if ($("#admin-id-search-input").is(":focus")) {
//          $('.admin-id-search-input').trigger('click');
//        }

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

      console.log('this is up');
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

//  console.log(routeFunction);
//  console.log(id);

    switch(routeFunction)
    {
      case 'getCustomer':
      {
        console.log('todo beim');
        console.log(customerService);
        console.log(customerService.tabs[$scope.idCustomer]);

//        $timeout(function() {
          //console.log(data.invoice_id[0]);

//          $scope.getCustomerDataRefresh(id);
//          $('#inv-list-' + data.invoice_id[0]).trigger('click');
//        }, 1);

//          $http.get("customersData", {params: {'id': id}})
//            .then(function (response) {
//              customerService.tabs[$scope.idCustomer].info  = response.data;
//            });

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
  $scope.xEditVisual          = function (valor, id, loc){
    if(loc)
      customerService.tabs[id].xEditContactInfo = valor;
    else
      customerService.tabs[id].xEditMainInfo = valor;
    return;
  }
  $scope.convertDate          = function (valor){
    return new Date(valor);
  }
  $scope.copyClipboard        = function (id){
    document.querySelector("#"+id).select();
    // Copy to the clipboard
    document.execCommand('copy');
  }
  $scope.safeHtml             = function (valor){
    return $sce.trustAsHtml(valor);
  }

  $scope.validateField        = function (type, $event){
//    console.log('validate field | ' + type + ' | ' + this.validaMail);
//    console.log($event);
//    return;

    switch (type)
    {
      case 'unit':
        //MODEL = validaUnit
        if(!this.validaUnit)
        {
          $scope.validateColors($event, false);
          return;
        }

        var regex = /([~!@#$%^&*()+=`'/?{}|<>,.;:])/g;
        var reviewRegex = regex.test(this.validaUnit);

        if(!reviewRegex && this.validaUnit.length >= 2)
        {
          $scope.validateColors($event, true);
        }
        else
        {
          $scope.validateColors($event, false);
        }



      break;
      case 'email':
        //MODEL = validaMail

        if(!this.validaMail)
        {
          $scope.validateColors($event, false);
          return;
        }

        var regex = /^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/g;
        var reviewRegex = regex.test(this.validaMail);

        if(reviewRegex)
        {
          $scope.validateColors($event, true);
        }
        else
        {
          $scope.validateColors($event, false);
        }

      break;
      case 'name':
        //MODEL = model['NAME'] as modelName ATTR


        var modelName = $('#'+$event.target.id).attr('modelname');

        if(!this.model || !this.model[modelName])
        {
          $scope.validateColors($event, false);
          return;
        }

        if(this.model[modelName].length >= 2)
          $scope.validateColors($event, true);
        else
          $scope.validateColors($event, false);


      break;
      case 'tel':
        //MODEL = validaTel

        var regex = /\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/g;
        var reviewRegex = regex.test(this.validaTel);


        if(reviewRegex)
        {
          $scope.validateColors($event, true);
        }
        else
          $scope.validateColors($event, false);

//        console.log(reviewRegex);




      break;
    }
  }
  $scope.validateColors       = function (event, val){
    if(val){
      $('#' + event.target.id).css('border-bottom', '1px solid #00c853');
      $('#' + event.target.id).css('-moz-border-bottom-colors', '#00c853');
      $('#' + event.target.id).attr('pass', true);
    }
    else{
      $('#' + event.target.id).css('border-bottom', '1px solid crimson');
      $('#' + event.target.id).css('-moz-border-bottom-colors', 'crimson');
      $('#' + event.target.id).attr('pass', false);
    }
  }

  $scope.idSearch             = function(){
//    console.log('this is idSearch');
//    console.log(this.adminSearch);
//    return;
//ROUTE         return Customer::find($request->id);

    if(!this.adminSearch)
    {
      console.log('emptyString');
    }
    else {
      $http.get("getCustomerById", {params:{'id':this.adminSearch}})
        .then(function (response) {

          $scope.idCustomerResult = response.data;

          if($scope.idCustomerResult)
          {
            $('#admin-id-search-input').val('');
            window.location = '#/customers?id='+response.data.id
          }
          else
          {
            $('#admin-id-search-input').val('Customer not in the DB').css('color', 'crimson');
            setTimeout( function(){
              $('#admin-id-search-input').val('').css('color', 'inherit');
            }  , 1000 );
          }

        });

    }


  }

})
.directive('enterAction', function () {
  return function (scope, element, attrs) {

    element.bind("keydown keypress", function (event) {


      if(event.which === 13) {
        scope.$apply(function (){
          console.log(this.target);
          scope.$eval(attrs.enterAction);
        });

        event.preventDefault();
      }
    });
  };
});
function gToolsxEdit(value, field, id, idContainer, table, model, index = null){
  console.log(' ||::|| ID==> '    +  id +
              ' ||::|| Value==> ' +  value +
              ' ||::|| Field==> ' +  field +
              ' ||::|| Table==> ' +  table +
              ' ||::|| idContainer==> '+  idContainer +
              ' ||::|| Model==> ' +  model +
              ' ||::|| Index==> ' +  index);


  angular.element('#' + idContainer + '-gTools-' + (index ? index : id)).scope().singleUpdateXedit(id, value, field, table, model, index);
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



