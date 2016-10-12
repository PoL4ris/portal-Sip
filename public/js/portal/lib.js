/* MENU */
app.controller('menuController', function($scope, $http){
  $http.get('/menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
});




app.controller('userController',                    function($scope, $http){
  $http.get("/getUserData")
    .then(function (response) {
      $scope.usr = response.data;
    });
});
app.controller('adminusers',                        function($scope, $http) {
  $http.get("adminusers")
    .then(function (response) {
      $scope.users = response.data;
    });
});
app.controller('admin',                             function($scope, $http, $compile, $sce, notify){
  $http.get("/admin")
    .then(function (response) {
      $scope.userData = response.data;
    });

  $scope.callAdminView = function (view) {
    callAdminView(view);
  };
  $scope.addNewForm = function (table)
  {
    $http.get("getAdminForm", {params:{'table':table}})
      .then(function (response) {

        var compiledeFormHTML = $compile(response.data)($scope);
//       console.log(compiledeFormHTML[0]);
        $scope.insertForm = $sce.trustAsHtml(response.data);

      });
  };
  $scope.cancelForm = function ()
  {
    cancelForm();
  };
  $scope.submitForm = function ($scope)
  {
    console.log('userinsert');
    var objects = warpol('#admin-insert-form').serializeArray();
    var infoData = {};
    console.log(objects);

    for(var obj in objects )
    {
    //FRONT VALIDA
      if(objects[obj]['value'] == '' && objects[obj]['name'] == 'avatar')
        continue;

      if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
      {
        alert('Verify ' + objects[obj]['name'] + ' Field');
        return;
      }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }
//     validator.startValidations;

    $http.get("insertAdminForm", {params:infoData})
      .then(function (response) {
        cancelForm();
      });

    callAdminView(infoData['table']);
    notify({ message: 'Data inserted!', templateUrl:'/views/notify.html'} );

  }
  function cancelForm()
  {
    $scope.insertForm = null;
  };
  function callAdminView (view)
  {
    var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
    warpol("#viewContents").html(compiledeHTML);
    $scope.insertForm = '';
  }
});
app.controller('adminViewStatus',                   function($scope, $http){
  $http.get("adminStatus")
    .then(function (response) {
      $scope.adminStatus = response.data;
    });
});
app.controller('adminViewElements',                 function($scope, $http){
  $http.get("adminElements")
    .then(function (response) {
      $scope.adminElements = response.data;
    });
});
app.controller('adminViewApps',                     function($scope, $http){
  $http.get("adminApps")
    .then(function (response) {
      $scope.adminApps = response.data;
    });
});
app.controller('adminViewProfiles',                 function($scope, $http){
  $http.get("adminProfiles")
    .then(function (response) {
      $scope.adminProfiles = response.data;
    });
});
app.controller('adminViewTypes',                    function($scope, $http){
  $http.get("adminTypes")
    .then(function (response) {
      $scope.adminTypes = response.data;
    });
});
app.controller('adminViewCustomers',                function($scope, $http){
  $http.get("adminCustomers")
    .then(function (response) {
      $scope.adminCustomers = response.data;
    });
});
app.controller('adminViewAddress',                  function($scope, $http){
  $http.get("adminAddress")
    .then(function (response) {
      $scope.adminAddress = response.data;
    });
});
app.controller('adminViewContacts',                 function($scope, $http){
  $http.get("adminContacts")
    .then(function (response) {
      $scope.adminContacts = response.data;
    });
});
app.controller('adminViewPayments',                 function($scope, $http){
  $http.get("adminPayments")
    .then(function (response) {
      $scope.adminPayments = response.data;
    });
});
app.controller('adminViewNotes',                    function($scope, $http){
  $http.get("adminNotes")
    .then(function (response) {
      $scope.adminNotes = response.data;
    });
});
app.controller('adminViewAccessApps',               function($scope, $http){
  $http.get("adminAccessApps")
    .then(function (response) {
      $scope.adminAccessApps = response.data;
    });
});
app.controller('adminViewAccessAppElements',        function($scope, $http){
  $http.get("adminAccessAppElements")
    .then(function (response) {
      $scope.adminAccessAppElements = response.data;
    });
});
app.controller('adminViewSignup',                   function($scope, $http){
console.log('entro');
//   $http.get("adminViewSignup")
//     .then(function (response) {
//       $scope.adminAccessAppElements = response.data;
//     });
});
app.controller('buildingCtl', ['$scope','$route','$http', function($scope, $route, $http) {
  if (!$scope.sbid)
  {
    $scope.SiteMenu = [];
    $http.get('buildings').then(function (data){
      $scope.bldData = data.data;
      $scope.offsetLimitFunction($scope.bldData.offset, $scope.bldData.limit);
    }), function (error){
      alert('Error');
    }
  }
  else
  {
    $http.get("buildings/" + $scope.sbid)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }

  $scope.displayBldData = function (idBld) {
    $http.get("buildings/" + idBld)
      .then(function (response) {
        $scope.bld = response.data;
      });

  }
  $scope.displayBldForm = function () {
    if ($scope.show == false)
    {
      $scope.show = true;
      warpol('#bld-content-form').fadeIn('slow');
      warpol('#add-bld-btn').fadeOut('fast');
      warpol('#cancel-bld-btn').fadeIn('fast');
    }
    else
    {
      $scope.show = false;
      warpol('#bld-content-form').fadeOut('slow');
      warpol('#add-bld-btn').fadeIn('fast');
      warpol('#cancel-bld-btn').fadeOut('fast');
    }
  }
  $scope.offsetLimitFunction = function (offset, limit) {
    warpol('#ol-left-btn').attr('offset', offset);
    warpol('#ol-left-btn').attr('limit', limit);
    warpol('#ol-right-btn').attr('offset', offset);
    warpol('#ol-right-btn').attr('limit', limit);
  }
  $scope.buildingsList = function (position) {
    //Math var operations.
    var offset              = parseInt($scope.bldData.offset);
    var limit              = parseInt($scope.bldData.limit);
    var a              = parseInt(offset);
    var b              = parseInt(limit);
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

    $scope.offsetLimitFunction(offset, limit);
  }
  $scope.buscador = function(searchType, side) {
    var query = {};

    if (side == 'left')
      query = {'querySearch' : this.searchLeft};
    else
      query = {'querySearch' : this.searchRight};

    $http.get("buildingsSearch", {params:query})
      .then(function (response) {
//         console.log(response.data);
        if (side == 'left')
          $scope.bldSearchResultLeft = response.data;
        else
          $scope.bldSearchResultRight = response.data;
      });

return;


//END SEARCH TOOL


    //id de quien solicita
    idDivResult = idDivResult?idDivResult:warpol(this).attr('id');



    if (!idDivResult)
      return;

    //Clean search fields
    warpol('#' + idDivResult + '-result').html('');
    warpol('.resultadosComplex').html('');

    //checamos si es simple o complex
    if(document.getElementById('complexSearch'))
      searchType = 'Complex';

    if (searchType == 'Simple')
    {
      warpol('.ntas-tmp').css('display', 'none');
      query = {"querySearch" : warpol(this).val()};
    }
    else // if searchType == 'COMPLEX'
    {
      index = warpol(this).attr('index');
      complexType[0] = 'complex';
      complexType[index] = warpol(this).val();
      query = {"querySearch" : complexType };
    }

    path = window.location.pathname;

    //Split ID for Dynamic UrlRequest
    urlTmp = idDivResult.split('id-');
    urlTmp = urlTmp[1].split('-search');

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/"+ urlTmp[0] + "Search",
        data:query,
        success: function(data)
        {
          //Validate info existing or die
          if (data == 'null')
          {
            warpol('.ntas-tmp').fadeIn("slow");
            return;
          }

          //Result JsonParser tu use data
          var resultData = jQuery.parseJSON(data);
          warpol('#' + idDivResult + '-result').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol('.resultadosComplex').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol.each(resultData,function(i, item)
          {
            //Rewrite results
            if (urlTmp[0] == 'customers')
              var nombre = '<label>' + item.Firstname + ' ' + item.Lastname + ' </label><label> <b> CODE: </b> ' + item.ShortName + ' </label><label> <b> UNIT: </b> ' + item.Unit + ' </label><label> <b> Address: </b> </label><label>' + item.Address  + '</label>';
            else
              var nombre = item.Name;

            if (path == '/supportdash')
            {
              warpol('.resultadosComplex').append('<p id="name-CID-' + item.CID + '" onclick="refeshDisabledInput(' + item.CID + ');">' + item.Firstname+ ' ' + item.Lastname + '</p>');
            }
            else
            {
              warpol('#' + idDivResult + '-result').append('<p><a href="/'+ urlTmp[0] +'/'+ item.LocID +'"> ' + nombre + '</a></p>');
              warpol('.resultadosComplex').append('<p><a href="/'+ urlTmp[0] +'/'+ item.CID +'"> ' + nombre + '</a></p>');
            }
          });
        }
      }
    );


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

  }
  $scope.submitForm = function () {
    console.log('buildingCtl');
    var objects = warpol('#building-update-form').serializeArray();
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



}]);
app.controller('customerSearch', function ($scope, $http){
  console.log('we in customerSearch');
  $scope.buscador = function (){
    console.log('fue keypress ' + this.search);

    $http.get("getCustomersPreview", {params:{'string':this.search}})
      .then(function (response) {
        console.log(response.data);
      });
  }
});
app.controller('newbuildingform', ['$scope', '$http', function($scope, $http)
{
  $http.get("newbuildingform")
    .then(function (response) {
      $scope.newbldform = response.data;
      warpol('#bld-form-html').html($scope.newbldform);
    });
}]);
app.controller('getCustomerDataTicket',             function ($scope, $http){
  $http.get("getCustomerDataTicket", {params:{'id':$scope.results.id_customers}})
    .then(function (response) {
      $scope.ticketCustomerData = response.data;
    });
});
app.controller('supportControllerTools',            function ($scope, $http) {
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
app.controller('supportController',                 function ($scope, $http, notify, $compile, $sce, $filter, ngTableParams){
//   notify({ message: 'Support Controller Active', templateUrl:'/views/notify.html'} );
  $http.get("supportTickets")
    .then(function (response) {
      $scope.supportData = response.data;
//       console.log($scope.supportData);
      SorterTickets();
    });
  $http.get("getTicketOpenTime")
    .then(function (response) {
      $scope.ticketOpenTime = response.data;
//       console.log($scope.ticketOpenTime);
    });

  function callMidView (view) {
    $scope.globalViewON = view;
      var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
//       warpol("#mid-content-tickets").html(compiledeHTML);
      warpol("#viewMidContent").html(compiledeHTML);
    };
  function setActiveBtn (activeView) {
    $scope.activeViewFull     = 'no-style';
    $scope.activeViewBilling  = 'no-style';
    $scope.activeViewAll      = 'no-style';
  };

  function SorterTickets()
  {
    $scope.dataSort = $scope.supportData;
    $scope.usersTable = new ngTableParams({
      page: 1,
      count: 100
    }, {
      total: $scope.dataSort.length,
      getData: function ($defer, params) {
        $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
        $defer.resolve($scope.dataResult);
      }
    });

  }

  $scope.fullTickets = function (){
    $http.get("supportTickets")
      .then(function (response) {
        $scope.supportData = response.data;
      });
      $scope.viewTicketsDirective = 'Full';
    callMidView('Full');
    setActiveBtn('Full');
    $scope.activeViewFull     = 'Active';
  };
  $scope.billingTickets = function (){
    $http.get("supportTicketsBilling")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'Billing';
    callMidView('Billing');
    setActiveBtn('Billing');
    $scope.activeViewBilling     = 'Active';
  };
  $scope.allTickets = function (){
    $http.get("supportTicketsAll")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'All';
    callMidView('All');
    setActiveBtn('All');
    $scope.activeViewAll     = 'Active';
  };

  $scope.displayTicketResume = function (id, idCustomer){
    $scope.midTicketId = id;
    $scope.stcid = idCustomer;
    $scope.stcFlag = true;
    callMidView('Resume');
  };
  $scope.displayCustomerResume = function (id){
    $scope.stcid = id;
    $scope.stcFlag = false;
    callMidView('Customer');
  };

});
app.controller('singleTicketInfo',                  function ($scope, $http){

  $http.get("getTicketInfo", {params:{'ticketId':$scope.midTicketId}})
    .then(function (response) {
      $scope.selectedTicket = response.data;
    });
})
app.controller('customerControllerList',            function ($scope, $http){
  $http.get("getCustomerList")
    .then(function (response) {
      $scope.supportDataCustomer = response.data;
    });
});
app.controller('customerController',                function ($scope, $http, $routeParams, notify, $uibModal, $log){
  var idCustomer = $routeParams.id;

  if ($scope.stcid)
    idCustomer = $scope.stcid;


    if ((warpol(location).attr('href').split('http://silverip-portal.com/#/')[1]) == 'customer')
      idCustomer = 13579;

  $http.get("customersData", {params:{'id':idCustomer}})
    .then(function (response) {
      $scope.customerData = response.data;
    });

  $http.get("getContactTypes", {params:{'id':idCustomer}})
    .then(function (response) {
    $scope.contactTypes = response.data;
    });

  $scope.getAddressItems = function (){
    $http.get("getAddress")
      .then(function (response) {
        $scope.addressData = response.data;
      });
  };

  $scope.getCustomerContactData = function (){
    $http.get("getCustomerContactData", {params:{'id':idCustomer}})
      .then(function (response) {
        $scope.customerContactsData = response.data.contacts;
      });
  }
  $scope.getCustomerContactData();


  $scope.checkboxModel = true;
  $scope.checkboxModelA = true;
  $scope.animationsEnabled = false;


  $scope.submitForm = function (table) {
  console.log('este' + table);
    var objects = warpol('#'+table+'-insert-form').serializeArray();
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

  }

  $scope.validate = function(value, table, field) {
    var data = {};
    data[field] = value;
    data['id_customers'] = $scope.customerData.id;

    $http.get("update" + table + "Table", {params:data})
      .then(function (response) {
        console.log('OK');
      });
  }

  $scope.customerEditMode = function (){
    if ( $scope.checkboxModel == false)
    {
       warpol('.editable-text').fadeIn('slow');
       warpol('.no-editable-text').css('display', 'none');
    }
    else
    {
      warpol('.no-editable-text').fadeIn('slow');
      warpol('.editable-text').css('display', 'none');
    }
  };

  $scope.customerEditMode();

  $scope.contactEditMode = function (){
    if ( $scope.checkboxModelA == false)
    {
      console.log($scope.checkboxModelA);
      warpol('.c-no-editable-text').fadeIn('slow');
      warpol('.c-editable-text').css('display', 'none');
      $scope.checkboxModelA = true;
    }
    else
    {
      console.log($scope.checkboxModelA);
      warpol('.c-editable-text').fadeIn('slow');
      warpol('.c-no-editable-text').css('display', 'none');
      $scope.checkboxModelA = false;
    }
  };

  $scope.updateContactInfo = function (value, id){
    var data = {};
    data['id']    = id;
    data['value'] = value;
    $http.get("updateContactInfo", {params:data})
      .then(function (response) {
        console.log(response.data);
      });
  };

  $scope.open = function (id, type){

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



  }



});
app.controller('addContInfoController',             function ($scope, $http, customerId, $uibModalInstance, mode){
  $http.get("getContactTypes")
    .then(function (response) {
      $scope.contactTypeOptions = response.data;
    });

  $scope.serviceDataDisplay = function() {
    $scope.currentServiceDisplay = $scope.selectedItem;
  }

  $scope.addNewContactInfo = function (){
    if(!$scope.contactInfoVal || !$scope.currentServiceDisplay)
      return;

    $http.get("insertContactInfo", {params:{'customerId':customerId,
                                            'typeId':$scope.currentServiceDisplay.id,
                                            'contactInfoVal':$scope.contactInfoVal}
                                    })
      .then(function (response) {
         $scope.customerContactsData = response.data;
      });
      angular.element('#c-cont-call').scope().getCustomerContactData();
    $scope.cancel();
  }

  $scope.cancel = function () {
    $uibModalInstance.dismiss('cancel');
  };
});
app.controller('supportTicketHistory',              function ($scope, $http){
  $http.get("supportTicketHistory", {params:{'id':$scope.history.id}})
    .then(function (response) {
      $scope.historyData = response.data;
    });
});
app.controller('userProfileController',             function ($scope, $http, notify){
  console.log('COSA LOCA');
  $scope.checkboxModel = true;

  $http.get("getProfileInfo")
    .then(function (response){
      $scope.profileData = response.data;
    });



  $scope.customerEditMode = function (){
    if ( $scope.checkboxModel == false)
    {
      warpol('.editable-text').fadeIn('slow');
      warpol('.no-editable-text').css('display', 'none');
    }
    else
    {
      warpol('.no-editable-text').fadeIn('slow');
      warpol('.editable-text').css('display', 'none');
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
            notify({message: 'Password updated', templateUrl:'/views/notify.html', classes:'alert-success'} );
            warpol('#uno').val('');
            warpol('#dos').val('');
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
      warpol('#pswbton').attr('disabled', false);
    else
      warpol('#pswbton').attr('disabled', true);
  }




});
app.controller('ModalInstanceCtrl',                 function ($scope, $http, $uibModalInstance, ticketId){
  $http.get("getTicketInfo", {params:{'ticketId':ticketId}})
    .then(function (response) {
      $scope.selectedTicket = response.data;
    });

  $scope.ok = function () {
    $uibModalInstance.close($scope.selected.item);
  };

  $scope.cancel = function () {
    $uibModalInstance.dismiss('cancel');
    console.log('modalInstanceCrl');
  };


});//sin usar
app.controller('ModalController',                   function ($scope, $uibModal, $log) {

  $scope.animationsEnabled = false;

  $scope.open = function (id, type) {
    $scope.customerId = id;
    $scope.type = type;
    if (type == 'updateService')
      $scope.labelAddUpdate = true;
    else
      $scope.labelAddUpdate = false;


    var modalInstance = $uibModal.open(
    {
      animation: $scope.animationsEnabled,
      templateUrl: 'myModalContent.html',
      controller: 'usrServiceController',
      size: 'lg',
      resolve: {
        customerId: function () {
          return $scope.customerId;
        },
        mode: function (){
          return $scope.type
        }
      }
    });

    modalInstance.result.then(function () {}, function () {
      if (type == 'services' || type == 'updateService')
        $scope.cSrvCrlFun();

      $log.info('Modal dismissed at: ' + new Date());
    });
  };

  $scope.toggleAnimation = function () {
    $scope.animationsEnabled = !$scope.animationsEnabled;
  };

});
app.controller('usrServiceController',              function ($scope, $http, $uibModalInstance, customerId, mode){
  $http.get("getAvailableServices", {params:{'id':customerId}})
    .then(function (response) {
      $scope.availableServices = response.data;
    });

  $scope.serviceDataDisplay = function() {
    $scope.currentServiceDisplay = $scope.selectedItem;
  }

  $scope.addNewService = function () {
    //Mode updateService customerId = oldIdProduct
    if (mode == 'updateService')
    {
      $http.get("updateCustomerServices", {params:{'id':customerId,'newId' :$scope.currentServiceDisplay.id}})
        .then(function (response) {
          console.log("Service Added / Updated::OK");
        });
      $scope.cancel();
    }
    else
    {
      $http.get("insertCustomerService", {params:{'idCustomer':customerId,'idProduct' :$scope.currentServiceDisplay.id}})
        .then(function (response) {
           console.log("Service Added / Updated::OK");
            $scope.cancel();
        });
    }
  }

  $scope.cancel = function () {
    console.log('cancel coso');
    $uibModalInstance.dismiss('cancel');
  };

});
app.controller('customerTicketHistoryController',   function ($scope, $http){
  $http.get("getTicketHistory", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.ticketHistory = response.data;
      $scope.letterLimit = 20;
    });
  $scope.showFullComment = function(id)
  {
    warpol('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment = function(id)
  {
    warpol('#ticket-' + id).fadeOut('fast');
  }
});
// app.controller('customerTicketHistoryData',         function ($scope, $http){
//   $http.get("getTicketHistoryNotes", {params:{'id':$scope.ticket.id_ticket_notes}})
//     .then(function (response) {
//       $scope.ticketNotes = response.data;
//       $scope.letterLimit = 20;
//     });
//   $http.get("getTicketHistoryReason", {params:{'id':$scope.ticket.id_reasons}})
//     .then(function (response) {
//       $scope.ticketReason = response.data;
//     });
// });
app.controller('customerBillingHistoryController',  function ($scope, $http, $uibModal, $log){

  $http.get("getBillingHistory", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.billingHistory = response.data;
    });

  $scope.open = function (){
    $scope.customerId = $scope.customerData.id;

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
});
app.controller('invoiceController', function ($scope, $http, customerId, notify, $uibModalInstance){
  console.log('invoiceController');
});
app.controller('customerPaymentMethodsController',  function ($scope, $http,$uibModal, $log){

  $http.get("getCustomerPayment", {params:{'id':$scope.stcid?$scope.stcid:$scope.customerData.id}})
    .then(function (response) {
      $scope.paymentData = response.data[0];
    });

  $http.get("getPaymentMethods", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.paymentMethods = response.data;
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
    warpol('.manual-ref').fadeIn('slow');
  };
  $scope.openManualChar = function (){
    $scope.openTransparentBGManual();
    warpol('.manual-char').fadeIn('slow');
  };
  $scope.openTransparentBGManual = function (){
    warpol('.transparent-charge').fadeIn();
  };
  $scope.refundFunct = function (){
    var cid = $scope.customerData.id;
    var amount = warpol('#mf-input-am').val();
    var desc = warpol('#mf-input-de').val();

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
    var amount = warpol('#mc-input-am').val();
    var desc = warpol('#mc-input-de').val();

    $http.get("chargeAmount", {params:{'cid':cid, 'amount':amount, 'desc':desc}})
      .then(function (response) {
//         $scope.paymentMethods = response.data;
        console.log(response.data);
        if(response.data.RESPONSETEXT == 'APPROVED')
          $scope.closeTransparentBGManual();
      });
  };
  $scope.closeTransparentBGManual = function (){
    warpol('.transparent-charge').fadeOut();
    warpol('.manual-ref').fadeOut();
    warpol('.manual-char').fadeOut();
    warpol('#mc-input-am').val('');
    warpol('#mc-input-de').val('');
    warpol('#mf-input-am').val('');
    warpol('#mf-input-de').val('');
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
app.controller('addPaymentMethodController',        function ($scope, $http, customerId, notify, $uibModalInstance){

  $scope.addNewPaymentMethod = function (){
    var objects = warpol('#paymentmethodform').serializeArray();

    if(!objects[0].value || !objects[1].value || !objects[2].value || !objects[3].value || !objects[4].value || !objects[5].value || !objects[6].value)
      return;

    var regexCC = /\b(?:3[47]\d{2}([\ \-]?)\d{6}\1\d|(?:(?:4\d|5[1-5]|65)\d{2}|6011)([\ \-]?)\d{4}\2\d{4}\2)\d{4}\b/g;

    if (!regexCC.test(objects[1].value)) {
      notify({ message: 'Verify your Account Number', templateUrl:'/views/notify.html'} );
      return;
    }
    if(objects[6].value.length < 3 || objects[6].value.length > 4) {
      notify({ message: 'Verify your CCV Number', templateUrl:'/views/notify.html'} );
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
          notify({ message: 'Account ' + infoData['account_number'] + ' ready to use.', templateUrl:'/views/notify.html'} );
          angular.element('#tom').scope().getPaymentMethods(customerId);
        }
        else
          notify({ message: 'ERROR: Verify your information.', templateUrl:'/views/notify.html'} );
      });
  }
});
app.controller('customerServicesController',        function ($scope, $http, $mdDialog){

  $http.get("getCustomerServices", {params:{'id':$scope.customerData.id}})
    .then(function (response) {

      $scope.customerServices = response.data;

    });

  $scope.cSrvCrlFun = function (){

    $http.get("getCustomerServices", {params:{'id':$scope.customerData.id}})
      .then(function (response) {

        $scope.customerServices = response.data;

      });
  }


  $scope.showConfirm = function(ev, id, tipo) {
    // Appending dialog to document.body to cover sidenav in docs app
    var confirm = $mdDialog.confirm()
      .title('Would you like to '+(tipo=='disable'?'cancel':'activate')+' this service?')
      .textContent('Confirm this action.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .ok('Yes!')
      .cancel('Cancel');

    $mdDialog.show(confirm).then(function() {
      //OK
      $scope.status = 'You decided to confirm';
      if (tipo == 'disable')
        $scope.disableService(id);
      else
        $scope.activeService(id);
    }, function() {
      //Cancel
      $scope.status = 'You decided to cancel';
    });
  };


  $scope.disableService = function (id){
    $http.get("disableCustomerServices", {params:{'id':$scope.customerData.id, 'idService':id}})
      .then(function (response) {

        $scope.customerServices = response.data;

      });
  }
  $scope.activeService = function (id){
    $http.get("activeCustomerServices", {params:{'id':$scope.customerData.id, 'idService':id}})
      .then(function (response) {

        $scope.customerServices = response.data;

      });
  }


});
app.controller('serviceProductController',          function ($scope, $http){

  $http.get("getCustomerProduct", {params:{'id':$scope.service.id}})
    .then(function (response) {

      $scope.customerProduct = response.data;

    });

  $http.get("getCustomerProductType", {params:{'id':$scope.service.id}})
    .then(function (response) {

      $scope.customerProductStatus = response.data;

    });

});
app.controller('customerNetworkController',         function ($scope, $http, $mdDialog, $mdMedia){
  $scope.status = '  ';
  $scope.customFullscreen = $mdMedia('xs') || $mdMedia('sm');

  $http.get("getCustomerNetwork", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.customerNetwork = response.data[0];
      console.log($scope.customerNetwork);
    });

  $scope.networkServices = function (service)
  {
    networkServices(service);
  }
  function networkServices (service)
  {
    var routes = ['networkCheckStatus',
                  'netwokAdvancedInfo',
                  'networkAdvanceIPs',
                  'networkRecyclePort',
                  '4',
                  'networkSignUp',
                  'networkActivate'];

    warpol('.network-functions').addClass('disabled');

    var service = service;
    var portID = $scope.customerNetwork.port_number;
    var customerID = $scope.customerData.id;
    var dataSend = {'portid':portID, 'id':customerID};

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/" + routes[service],
        data:dataSend,
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          warpol.each(data,function(i, item)
          {
            warpol('#' + i).html(item);
          });
          warpol('#basic-info-net').notify('OK');

          service = 1;
          warpol.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:dataSend,
              success: function(data)
              {
                warpol.each(data,function(i, item)
                {
                  warpol('#' + i).html(item);
                });
              }
            }
          );

          service = 2;
          warpol.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:dataSend,
              success: function(data)
              {

                warpol('#IPs').notify('IPs Array.');
                warpol('.network-functions').removeClass('disabled');

//                   warpol.each(data,function(i, item)
//                   {
//                     warpol('#' + i).html(item);
//                   });

              }
            }
          );

        }
      }
    );

    if (service == 5)
    {
      warpol('.access-type-net').removeClass('btn-danger ');
      warpol('.access-type-net').addClass('btn-info');
      warpol('.access-type-net').html('Activate');
      warpol('.access-type-net').attr('type','6');
      warpol('#acces-network-id').html('signup');
    }
    else if ( service == 6 )
    {
      warpol('.access-type-net').removeClass('btn-info')
      warpol('.access-type-net').addClass('btn-danger')
      warpol('.access-type-net').html('Send to Signup');
      warpol('.access-type-net').attr('type','5');
      warpol('#acces-network-id').html('yes');
    }

  };

  //PENDING
  function servicesInfoUpdate (serviceID, serviceStatus, routeID)
  {
    var routes = ['updateCustomerServiceInfo'];

//   warpol('.network-functions').addClass('disabled');

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/" + routes[routeID],
        data:{'serviceid':serviceID, 'status':serviceStatus},
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          if (serviceStatus == 'active')
          {
            warpol('#serviceno-' + serviceID).addClass('disabled ital');
            warpol('#serviceinfo-status-' + serviceID).html('disabled');
            warpol('#xservice-btn-id-' + serviceID).attr('displaystatus','disabled');
            warpol('#xservice-btn-id-' + serviceID).addClass('btn-success fa-check');
            warpol('#xservice-btn-id-' + serviceID).removeClass('btn-dark');
            warpol('#xservice-btn-id-' + serviceID).removeClass('fa-times');
          }
          else
          {
            warpol('#serviceno-' + serviceID).removeClass('disabled ital');
            warpol('#serviceinfo-status-' + serviceID).html('active');
            warpol('#xservice-btn-id-' + serviceID).attr('displaystatus','active');
            warpol('#xservice-btn-id-' + serviceID).addClass('btn-dark fa-times');
            warpol('#xservice-btn-id-' + serviceID).removeClass('btn-success');
            warpol('#xservice-btn-id-' + serviceID).removeClass('fa-check');
          }

        }
      }
    );
  };


  $scope.showConfirm = function(ev)
  {
    var service       = warpol('#rport').attr('type');
    var portID        = warpol('#rport').attr('portid');
    var serviceID     = warpol('#rport').attr('serviceid');
    var serviceStatus = warpol('#rport').attr('displaystatus');
    var routeID       = warpol('#rport').attr('route');

    var confirm = $mdDialog.confirm()
      .title('Please Confirm Your Action!')
      .textContent('Once you click Yes, you need to wait the process to finish.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .clickOutsideToClose(true)
      .ok('YES')
      .cancel('NO');

      // YES/NO
    $mdDialog.show(confirm).then(function()
    {
      console.log('a' + confirm + ' ...b ' + service + 'PARAMS:  ' + serviceID + '...' + serviceStatus + '...' + routeID);
      $scope.status = 'You decided to get rid of your debt.';

      if (portID)
        networkServices(service);
      else if(serviceID)
        servicesInfoUpdate(serviceID, serviceStatus, routeID);

    }, function() {
      $scope.status = 'You decided to keep your debt.';
    });
  };

});
app.controller('customerNewTicketCtrl',             function ($scope, $http){
  $http.get("getTableData", {params:{'table':'reasons'}})
    .then(function (response) {
      $scope.newTicketData = response.data;
    });
});
app.controller('customerBuildingController',        function ($scope, $http){
  if($scope.customerData)
  {
    $http.get("buildings/" + $scope.customerData.address.id_buildings)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }
});
app.controller('submitController',                  function ($scope, $http) {
  $scope.submitForm = function (idForm)
  {
    console.log('warpol');

    var objects = warpol('#' + idForm).serializeArray();
    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    infoData['id'] = $scope.selectedTicket.id;


    $http.get("updateTicketDetails", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
  }
  $scope.submitFormUpdate = function (idForm)
  {
    var objects = warpol('#' + idForm).serializeArray();
    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    infoData['id'] = $scope.selectedTicket.id;

    $http.get("updateTicketHistory", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
    warpol('.thistory-form-2').val('');
  }
});
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

  $scope.addTR = function addTR(id)
  {
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
  function getNetworkResult(id)
  {
    var idString = 'nt-tmp-data-'+id;
    return ' <tr id="' + idString + '"><td colspan="11">info</td></tr>';
  };
});
app.controller('actionsController',                 function ($scope) {
  $scope.actionA = function ()
  {
    console.log('actionA');
  };
  $scope.actionB = function ()
  {
    console.log('actionB');
  };
  $scope.actionC = function ()
  {
    console.log('actionC');
    //status D
  };
  $scope.actionD = function ()
  {
    console.log('actionD');
  };

  $scope.validate = function (dato)
  {
    console.log(dato);
    console.log('clean data to send and update.');
    if (dato <= 0)
      console.log('error');

  };

});
app.controller('mainSearchController',              function ($scope, $http, $compile){
  $scope.closeSearch = function () {
    warpol('#globalSearch').fadeOut('fast');
    warpol('#lupa-global').fadeIn('slow');
    warpol('#tache-global').fadeOut('fast');
  };
  $scope.valLength = function () {
    if(!this.globalSearch)
      $scope.closeSearch;
  }
  $scope.search = function () {
    if(!this.globalSearch)
    {
      $scope.closeSearch;
      return;
    }

    warpol('#globalSearch').fadeIn('slow');
    warpol('#lupa-global').fadeOut('fast');
    warpol('#tache-global').fadeIn('slow');

    var string = this.globalSearch;

    $scope.loadingCl = true;
    $scope.loadingCu = true;
    $scope.loadingS = true;
    $scope.loadingB = true;
    $scope.loadingCP = true;
    getCustomerCodeSearch(string);
    getCustomersSearch(string);
    getTicketsSearch(string);
    getBuildingsSearch(string);
    getCustomerPoundSearch(string);
  };
  $scope.displayCustomerResume = function (id){
    $scope.stcid = id;
    $scope.stcFlag = false;
    callMidView('Customer');
  };
  $scope.displayTicketResume = function (id, idCustomer){
    $scope.midTicketId = id;
    $scope.stcid = idCustomer;
    $scope.stcFlag = false;
    callMidView('Resume');
  };
  $scope.displayBuildingResume = function (id){
    $scope.sbid = id;
    callMidView('Building');
  };
  function getCustomerCodeSearch(string) {
    $http.get("getCustomerCodeSearch", {params:{'string':string}})
      .then(function (response)  {
        $scope.globalCustomerCodeSearch = response.data;
        $scope.loadingCl = false;
//         console.log(response.data);
      });
  }
  function getCustomersSearch(string) {
    $http.get("getCustomersSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalCustomersSearch = false;
//         else
        $scope.globalCustomersSearch = response.data;
        $scope.loadingCu = false;
//         console.log(response.data);
      });
  }
  function getTicketsSearch(string) {
    $http.get("getTicketsSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalTicketsSearch = false;
//         else
        $scope.globalTicketsSearch = response.data;
        $scope.loadingS = false;
//         console.log(response.data);
      });
  }
  function getBuildingsSearch(string) {
    $http.get("getBuildingsSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalBuildingsSearch = false;
//         else
        $scope.globalBuildingsSearch = response.data;
        $scope.loadingB = false;
//         console.log(response.data);
      });
  }
  function getCustomerPoundSearch(string) {
    $http.get("getCustomerPoundSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalBuildingsSearch = false;
//         else
        $scope.globalCustomerPoundSearch = (response.data == 'ERROR')?false:response.data;
        $scope.loadingCP = false;
//         console.log(response.data);
      });
  }
  function callMidView (view) {
    $scope.globalViewON = view;
    var compiledeHTML = $compile("<div my-View-" + view + "></div>")($scope);
    warpol("#viewMidContent").html(compiledeHTML);
  };
});
app.controller('toolsController',                   function ($scope, $http) {
  $scope.letterLimit = 400;
  $scope.showFullComment = function(id) {
    warpol('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment = function(id) {
    warpol('#ticket-' + id).fadeOut('fast');
  }
  $scope.getReasons = function () {
    $http.get("getReasonsData")
      .then(function (response) {
        $scope.dataReasons = response.data;
      });
  }
  $scope.getUsers = function () {
    $http.get("admin")
      .then(function (response) {
        $scope.dataUsersAssigned = response.data;
      });
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
app.controller('directiveController',               function ($scope, $http, $compile, notify){
//   console.log('directiveController');
// Global TOOLS
  $scope.labelMonth = {1:'Jan', 2:'Feb', 3:'Mar', 4:'Apr', 5:'May', 6:'Jun',
    7:'Jul', 8:'Aug', 9:'Sep', 10:'Oct', 11:'Nov', 12:'Dec'};

  $scope.eventUpdateNotify = function (eventLabel) {
    notify({ message: eventLabel, templateUrl:'/views/notify.html'} );
  }
  $scope.showHideSide = function () {
    if($scope.closeLeftSide)
    {
      $scope.closeLeftSide = false;
      warpol('.left-colinout').css('width','280px');
    }
    else
    {
      $scope.closeLeftSide = true;
      warpol('.left-colinout').css('width', 0);
    }

  };
  $scope.validator = function (items){
    console.log(items);
  };


})
.directive('myViewFull',                            function() {
  return {
    templateUrl: '/views/supportFullList.html'
  };
})
.directive('myViewAll',                             function() {
  return {
    templateUrl: '/views/supportAllList.html'
  };
})
.directive('myViewResume',                          function() {
  return {
    templateUrl: '/views/supportTicketResume.html',
    controller: 'singleTicketInfo'
  };
})
.directive('myViewCustomer',                        function() {
  return {
    templateUrl: '/views/customer.html'
  };
})
.directive('myViewSupport',                         function() {
  return {
    templateUrl: '/views/supportDash.html'
  };
})
.directive('myViewBilling',                         function() {
  return {
    templateUrl: '/views/supportBillingList.html'
  };
})
.directive('viewNewTicket',                         function() {
  return {
    templateUrl: '/views/newticket.html'
  };
})
.directive('viewTicketHistory',                     function() {
  return {
    templateUrl: '/views/ticketshistory.html'
  };
})
.directive('viewBillingHistory',                    function() {
  return {
    templateUrl: '/views/billinghistory.html'
  };
})
.directive('viewNetwork',                           function() {
  return {
    templateUrl: '/views/network.html'
  };
})
.directive('viewProduct',                           function() {
  return {
    templateUrl: '/views/product.html'
  };
})
.directive('viewBuilding',                          function() {
  return {
    templateUrl: '/views/building/building.html'
  };
})
.directive('viewPaymentMethods',                    function() {
  return {
    templateUrl: '/views/paymentMethods.html'
  };
})
.directive('myViewUsers',                           function() {
  return {
    templateUrl: '/views/admin/user.html',
    controller:'admin'
  };
})
.directive('myViewProfiles',                        function() {
  return {
    templateUrl: '/views/admin/profile.html',
    controller: 'adminViewProfiles'
  };
})
.directive('myViewApps',                            function() {
  return {
    templateUrl: '/views/admin/app.html',
    controller: 'adminViewApps'
  };
})
.directive('myViewStatus',                          function() {
  return {
    templateUrl: '/views/admin/status.html',
    controller:'adminViewStatus'
  };
})
.directive('myViewElements',                        function() {
  return {
    templateUrl: '/views/admin/element.html',
    controller:'adminViewElements'
  };
})
.directive('myViewCustomers',                       function() {
  return {
    templateUrl: '/views/admin/customer.html',
    controller: 'adminViewCustomers'
  };
})
.directive('myViewTypes',                           function() {
  return {
    templateUrl: '/views/type.html',
    controller: 'adminViewTypes'
  };
})
.directive('myViewAddress',                         function() {
  return {
    templateUrl: '/views/address.html',
    controller: 'adminViewAddress'
  };
})
.directive('myViewContacts',                        function() {
  return {
    templateUrl: '/views/contact.html',
    controller: 'adminViewContacts'
  };
})
.directive('myViewPayments',                        function() {
  return {
    templateUrl: '/views/admin/contactInfo.html',
    controller: 'adminViewPayments'
  };
})
.directive('myViewNotes',                           function() {
  return {
    templateUrl: '/views/notes.html',
    controller: 'adminViewNotes'
  };
})
.directive('myViewAccessApps',                      function() {
  return {
    templateUrl: '/views/admin/access_app.html',
    controller: 'adminViewAccessApps'
  };
})
.directive('myViewAccessAppElements',               function() {
    return {
      templateUrl: '/views/admin/access_app_element.html',
      controller: 'adminViewAccessAppElements'
    };
  })
.directive('myViewBuilding',                        function() {
  return {
    templateUrl: '/views/building/building.html',
    controller:'buildingCtl'
  };
})
.directive('myViewSignup',                          function() {
  return {
    templateUrl: '/views/admin/signup.html',
    controller:'adminViewSignup'
  };
})
.directive('myBldView',                             function() {
    return {
      templateUrl: '/views/building/building.html',
//     controller:'admin'
    };
  });
//TABS
app.controller('AppCtrl', AppCtrl);
function AppCtrl ($scope, $log, $compile) {
  var tabs = [
        { title: 'New Ticket',      content:'New-Ticket'},
        { title: 'Tickets',         content:"Ticket-History"},
        { title: 'Billing',         content:"Billing-History"},
        { title: 'Network',         content:"Network"},
        { title: 'Building',        content:'Building'},
        { title: 'Services',        content:"Product"},
        { title: 'Payment Methods', content:"Payment-Methods"},
    ],
    selected = null,
    previous = null;
  $scope.tabs = tabs;
  $scope.selectedIndex = 0;
  $scope.$watch('selectedIndex', function(current, old)
  {
    previous = selected;
    selected = tabs[current];
    if ( old + 1 && (old != current)) $log.debug('Goodbye ' + previous.title + '!');
    if ( current + 1 )                $log.debug('Hello ' + selected.title + '!');
  });
  $scope.addTab = function (title, view)
  {
    view = view || title + " Content View";
    tabs.push({ title: title, content: view, disabled: false});
  };
  $scope.removeTab = function (tab)
  {
    var index = tabs.indexOf(tab);
    tabs.splice(index, 1);
  };
  $scope.changeView = function (view)
  {
    var compiledeHTML = $compile("<div view-"+view+"></div>")($scope);
    warpol("#tabsChange-"+view).html(compiledeHTML);
  }
}
//TableSorter's
app.controller('tableSorterTickets',                function ($scope, $filter, ngTableParams) {
  $scope.dataSort = $scope.supportData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 100
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});
app.controller('tableSorterAdminUsers',             function ($scope, $filter, ngTableParams) {
  $scope.letterLimit = 30;
  $scope.dataSort = $scope.userData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 15
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});
app.controller('tableSorterNetwork',                function ($scope, $filter, ngTableParams) {
  $scope.dataSort = $scope.networkData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 20
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});
//CHARTS
app.controller("PieCtrl",                           function ($scope) {
  $scope.labels = ["Download Sales", "In-Store Sales", "Mail-Order Sales"];
  $scope.data = [300, 500, 100];
  $scope.colours = ['#4FC5EA', '#6B79C4', '#FAD733'];
});
app.controller("DoughnutCtrl",                      function ($scope) {
  $scope.labels = ["Download Sales", "In-Store Sales", "Mail-Order Sales"];
  $scope.data = [300, 500, 100];
  $scope.colours = ['#27c24c', '#ff7a7a', '#D9EDF7'];
});
app.controller("PolarAreaCtrl",                     function ($scope) {
  $scope.labels = ["Download Sales", "In-Store Sales", "Mail-Order Sales", "Tele Sales", "Corporate Sales"];
  $scope.data = [300, 500, 100, 40, 120];
});
app.controller('getTicketsByMonthChart',            function ($scope){
  $scope.options = {
    chart: {
      type: 'discreteBarChart',
      height: 450,
      margin : {
        top: 20,
        right: 20,
        bottom: 50,
        left: 55
      },
      x: function(d){return $scope.labelMonth[d.label];},
      y: function(d){return d.value + (1e-10);},
      showValues: true,
      valueFormat: function(d){
        return d3.format(',.0f')(d);
      },
      duration: 500,
      xAxis: {
        axisLabel: 'X Axis'
      },
      yAxis: {
        axisLabel: 'Y Axis',
        axisLabelDistance: -10
      }
    }
  };


});
app.controller('getTicketsByMonth',                 function ($scope, $http) {
  $http.get("getTicketsByMonth")
    .then(function (response) {
      $scope.data = [response.data];
    });
});
app.controller("BarCtrl",                           function ($scope) {
  $scope.labels = ['2006', '2007', '2008', '2009', '2010', '2011', '2012'];
  $scope.series = ['Series A', 'Series B'];

  $scope.data = [
    [65, 59, 80, 81, 56, 55, 40],
    [28, 48, 40, 19, 86, 27, 90]
  ];
});
app.controller('warp',                              function ExampleCtrl(){
  this.xAxisTickFormatFunction = function(){
    return function(d){
      return d3.time.format('%b')(new Date(d));
    };
  };

  var colorCategory = d3.scale.category20b();
  this.colorFunction = function() {
    var colorsByDepartment = ['red', 'blue'];

    return function(d, i) {
      return colorsByDepartment[i];
    };
  }

  this.exampleData = [
    {
      "key": 1,
      "values": [ [ 1025409600000 , 5] , [ 1028088000000 , 6.3382185140371] , [ 1030766400000 , 5.9507873460847] , [ 1033358400000 , 11.569146943813] , [ 1036040400000 , 5.4767332317425] , [ 1038632400000 , 0.50794682203014] , [ 1041310800000 , 5.5310285460542] ]
    },
    {
      "key": 2,
      "values": [ [ 1025409600000 , 10] , [ 1028088000000 , 3] , [ 1030766400000 , 3] , [ 1033358400000 , 4] , [ 1036040400000 , 3] , [ 1038632400000 , 2] , [ 1041310800000 , 1] ]
    }
  ];
});
//[WORKING NOT IN USE
app.controller('getSignedUpCustomersByYear',        function($scope, $http) {
  $http.get("getSignedUpCustomersByYear")
    .then(function (response) {
      $scope.data = response.data;
    });
});
app.controller('getSignedUpCustomersByYearChart',   function($scope) {
//data to fill $scope.data;
});
//END WORKING NOT IN USE]


var popEventTool = '';
var objArray = '';
var objEdit = null;
var winSize  = warpol( document ).width();
function eventTool(event, id, toDo){
  event.stopPropagation();
  if (id == 9)
    return;

  if (id) {
    warpol(popEventTool).fadeOut();
    warpol('#' + id + '-tooltip').fadeIn();
    popEventTool = '#' + id + '-tooltip';
  }

  if (toDo == 1) {
    console.log('this Delete');
    objEdit = getIdEventClean (popEventTool);

    //THIS EDITS EVENT
    var request = gapi.client.calendar.events.delete({
      'calendarId': 'help@silverip.com',
      'eventId': objEdit.id
    });
    request.execute(function(event) {

      warpol("[idEvent=" + objEdit.id + "]").html('<div class="event-create anim" ></div>');
      warpol("[idEvent=" + objEdit.id + "]").attr('idEvent', '');
      warpol("[idEvent=" + objEdit.id + "]").attr('objindex', '');
      angular.element('#output').scope().ttt();
//       $scope.ttt();
    });
    angular.element('#output').scope().eventUpdateNotify('Event Delete');
//     $scope.eventUpdateNotify('Event Delete');

  }
  if (toDo == 2) {
    console.log('this edits');

    objEdit = getIdEventClean (popEventTool);

    var t1 = getTimeSelct(objEdit, 'start');
    var t2 = getTimeSelct(objEdit, 'end');
    angular.element('#output').scope().setSelectValue(t1,t2);
    angular.element('#output').scope().$apply()

    editWindow();
    var e = jQuery.Event("keydown");
    e.keyCode = 39;
    warpol('.input-titulo').val(objEdit.summary).focus();
    warpol('.input-where').val(objEdit.location).focus();
    warpol('.input-description').val(objEdit.description).focus().trigger(e);
    warpol('.select-calendar').focus();

  }
  if (toDo == 3)
    warpol(popEventTool).fadeOut();

}
function getTimeSelct(objEdit, when){

  var inicio    = new Date(objEdit[when]['dateTime']);

  var ini   = inicio.toLocaleTimeString();
  var xA = ini.split(':')[0];
  var xB = ini.split(':')[1];
  var xC = ini.split(':')[2];
  var xD = xC.split(' ')[1].toLowerCase();

  return xA + ':' + xB + xD;

}
function getIdEventClean(str) {
  var fStr = str.split('-box-tooltip')[0];
  var eventIndexEdit = warpol(fStr).attr('objindex');
  return objArray[eventIndexEdit];
}
function editWindow() {
  warpol('#transparent-bg').fadeIn();
  warpol('#new-event').fadeIn();
  warpol('.calendar-select').fadeIn();
  warpol('#new-event').css('left', '60px');
}
app.controller('calController', function ($scope){
  console.log('inside--calController');
  /*
  For new Tech:
  var who in ttt, setEventName
  Add html #div
*/

  $scope.eventsData = '';
  var objects = null;
  var tttFunction = null;
  var idEventToSet = '';
  var regExp = /\(([^)]+)\)/;
  var who = {'0':''
    ,'Izzy':'1'
    ,'Melvin':'2'
    ,'Abe':'3'
    ,'Eli':'4'
    ,'Brian':'5'
    ,'Charlie':'6'
    ,'Pablo':'7'
  };
  var size = 50;
  var maxDateTime = getMaxDateTime();
  var minDateTime = getMinDateTime();
  var timeLine = '<div class="time-line"></div>';

  $scope.timeSelect = ['8:00am','8:30am',
                       '9:00am', '9:30am',
                       '10:00am', '10:30am',
                       '11:00am', '11:30am',
                       '12:00pm', '12:30pm',
                       '1:00pm', '1:30pm',
                       '2:00pm', '2:30pm',
                       '3:00pm', '3:30pm',
                       '4:00pm', '4:30pm',
                       '5:00pm', '5:30pm',
                       '6:00pm', '6:30pm',
                       '7:00pm', '7:30pm',
                       '8:00pm', '8:30pm',
                       '9:00pm', '9:30pm',
                       '10:00pm', '10:30pm',
                       '11:00pm', '11:30pm'];
  $scope.idCalendars = {'Auth':'silverip.com_00bq2i57k1e83g8nuue29fenl8@group.calendar.google.com'
                       ,'Customer Canceled':'silverip.com_jgg5r8u7ohr8n8456nrt71m9lk@group.calendar.google.com'
                       ,'Onsite':'silverip.com_elc3ctcfdgle90b5jntqlpfnh8@group.calendar.google.com'
                       ,'Problem Ticket':'silverip.com_e5glc3dbassqckgva13d3qg7ic@group.calendar.google.com'
                       ,'Completed Ticket':'silverip.com_tpbi296lb5hldljngg6fcmjsac@group.calendar.google.com'
                       ,'Ticket':'help@silverip.com'};
  $scope.calendarsName = {'Auth':'Auth'
                         ,'Ticket':'Ticket'
                         ,'Customer Canceled':'Customer Canceled'
                         ,'Onsite':'Onsite'
                         ,'Problem Ticket':'Problem Ticket'
                         ,'Completed Ticket':'Completed Ticket'
                         };
  $scope.calendarColors = {'Auth':'9'//blue
                          ,'Customer Canceled':'3'//purple
                          ,'Onsite':'9'//blue
                          ,'Problem Ticket':'3'//purple
                          ,'Completed Ticket':'10'//green
                          ,'Ticket':'11'//red
                          };
  $scope.timeIni;
  $scope.timeFin;
  $scope.eventtitle;

  $scope.timeLine = function (){
    warpol('.time-line').remove();
    $scope.globalDate = new Date();
    $scope.gDateHr = $scope.globalDate.getHours();
    $scope.gDateMin = $scope.globalDate.getMinutes();

    warpol('#t-' + $scope.gDateHr).append(timeLine);
    warpol('.time-line').css('top', ($scope.gDateMin/60 * 50) + 'px' );
  };
  $scope.ttt = function () {

    clearInterval(tttFunction);

    warpol('.event-create').html('');
    var request = gapi.client.calendar.events.list({
//       'calendarId': 'primary',
      'calendarId': 'help@silverip.com',
      'timeMin': minDateTime,
//       'timeMin': (new Date()).toISOString(),
      'timeMax': maxDateTime,
      'showDeleted': false,
      'singleEvents': true,
//       'maxResults': 10,
      'orderBy': 'startTime'
    });
    request.execute(function(resp) {
        objects  = resp.items;
        objArray = resp.items;

        createEventsHtml(objects);
      });
    $scope.timeLine();

    tttFunction = setInterval(function(){$scope.ttt();$scope.timeLine ();}, 25000);
  }
  warpol( ".event-create" ).dblclick(function() {
    console.log('Handler for .dblclick() called.');
    idEventToSet = warpol(this.parentElement)[0].id;
    setEventName(idEventToSet);
    warpol('#transparent-bg').fadeIn();
    warpol('#new-event').fadeIn();
    warpol('.calendar-select').fadeOut();
    if(winSize <= 420){
      warpol('#new-event').css('left', '0px');
      warpol('#new-event').css('width', '80%');
    }
    else
      warpol('#new-event').css('left', '60px');
    warpol('.input-titulo').focus();

  });
  function getMaxDateTime (){
    var d = new Date();
    return d.getFullYear() + '-0' + (d.getMonth() + 1) + '-' + d.getDate() + 'T23:59:59-05:00';
  };
  function getMinDateTime (){
    var d = new Date();
    return d.getFullYear() + '-0' + (d.getMonth() + 1) + '-' + d.getDate() + 'T00:00:00-05:00';
  };
  function createEventsHtml(objects){
    for(var obj in objects ) {
      var geNameTmp = regExp.exec(objects[obj]['summary']);

      var inicio    = new Date(objects[obj]['start']['dateTime']);
      var fin       = new Date(objects[obj]['end']['dateTime']);

      var whoIs = geNameTmp?geNameTmp[1]:'';
      var ini   = inicio.toLocaleTimeString();
      var fn    = fin.toLocaleTimeString();

      if (ini == 'Invalid Date')
        continue;

      //inicio
      var xA = ini.split(':')[0];
      var xB = ini.split(':')[1];
      var xC = ini.split(':')[2];
      var xD = xC.split(' ')[1];

      //fin
      var xE = fn.split(':')[0];
      var xF = fn.split(':')[1];
      var xG = fn.split(':')[2];
      var xH = xG.split(' ')[1];
      var top = xB;
      var diffHours = Math.abs(fin - inicio) / 36e5;

      var tmpColor = 'default';

      if(objects[obj]['colorId'])
        tmpColor = objects[obj]['colorId'];


      var content =  '<div class="event-container color-' + tmpColor + ' eid' + objects[obj]['id'] + '" id="' + xA + '-' + xD + '-' + who[whoIs] + '-box" onclick="eventTool(event, this.id, 0);">';
            content +=  '<p class="ec-time cl'+ objects[obj]['id'] + '">' + (xA + (xB > 0 ? (':' + xB) : '')) + xD + ' - ' + (xE + (xF > 0 ? (':' + xF) : '')) + xH + '</p>';
            content +=  '<p class="ec-titulo">' + objects[obj]['summary'] + '</p>';
            content +=  '<div class="tooltip-event" id="' + xA + '-' + xD + '-' + who[whoIs] + '-box-tooltip" onclick="eventTool(event, 9,9)">';
              content +=  '<div>' + (xA + (xB > 0 ? (':' + xB) : '')) + xD + ' - ' + (xE + (xF > 0 ? (':' + xF) : '')) + xH + '</div>';
              content +=  '<div class="ect-desc">' + objects[obj]['description'] + '</div>';
              content +=  '<button class="dlt-btn-tool tool-btn" onclick="eventTool(event, null, 1)">Delete</button>';
              content +=  '<button class="edit-btn-tool tool-btn" onclick="eventTool(event, null, 2)">Edit event</button>';
              content +=  '<button class="close-btn-tool" onclick="eventTool(event, null, 3)">X</button>';
            content += '</div>';
          content += '</div>';




      warpol("[idEvent=" + objects[obj]['id'] + "]").html('<div class="event-create anim" ></div>');
      warpol("[idEvent=" + objects[obj]['id'] + "]").attr('idEvent', '');
      warpol("[idEvent=" + objects[obj]['id'] + "]").attr('objindex', '');
      warpol('#' + xA + '-' + xD + '-' + who[whoIs]).attr('idEvent', objects[obj]['id']);
      warpol('#' + xA + '-' + xD + '-' + who[whoIs]).attr('objindex', obj);

      if (warpol('#' + xA + '-' + xD + '-' + who[whoIs]))
        warpol('#' + xA + '-' + xD + '-' + who[whoIs]).append(content);
      else
        warpol('#' + xA + '-' + xD + '-' + who[whoIs]).html(content);

      warpol('.' + 'eid' + objects[obj]['id']).css('top', (xB/60 * size) + 'px' );
      warpol('.' + 'eid' + objects[obj]['id']).css('height', (size * diffHours))
      if((size * diffHours) == 25)
        warpol('.' + 'cl' + objects[obj]['id']).css('float', 'left');


    }
  }
  $scope.cancelNewEvent = function () {

    console.log('evento nuevo cancelado');
    warpol('#transparent-bg').fadeOut();
    warpol('#new-event').fadeOut();
    warpol('calendar-select').fadeOut();
    if(winSize <= 420)
      warpol('#new-event').css('width', '40%');
    else
      warpol('#new-event').css('left', '-50%');

    this.eventtitle = '';
    this.timeIni = '';
    this.timeFin = '';
    this.where = '';
    this.description = '';
    this.calendar = '';

    warpol('.input-titulo').val('');
    warpol('.input-where').val('');
    warpol('.input-description').html('');
    warpol('.input-description').val('');

    objEdit = null;

    $scope.ttt();

  }
  $scope.setNewEvent = function (){
    console.log('this will create the new event and the id =' + idEventToSet);

    var t  = this.eventtitle ? this.eventtitle : objEdit.summary;
    var w  = this.where ? this.where : objEdit.location;
    var d  = this.description ? this.description : objEdit.description;

    var ti = this.timeIni;
    var tf = this.timeFin;

    var calSelect = this.calendar;

    warpol('#select_3').focus();
    warpol('#select_5').focus();

    if(!ti || !tf)
      return;


    var t1 = getFormatTime("00:00", ti);
    var t2 = getFormatTime("00:00", tf);

    var tIso1 = createIsoDate(t1);
    var tIso2 = createIsoDate(t2);

    if (!t || !ti || !tf || !w)
      return;

    var event = {
      'summary': t,
      'location': w,
      'description': d,
      'colorId': 11,
      'start': {
        'dateTime': tIso1,
        'timeZone': 'America/Chicago'
      },
      'end': {
        'dateTime': tIso2,
        'timeZone': 'America/Chicago'
      }
    };

    //THIS EDITS EVENT IF
    if(objEdit) {
      if (calSelect)
      {
        event.colorId = $scope.calendarColors[calSelect];

        var request = gapi.client.calendar.events.insert({
          'calendarId': $scope.idCalendars[calSelect],
          'resource': event
        });
        request.execute(function(event) {
          console.log('Event created: ' + event.htmlLink);
          $scope.eventUpdateNotify('Event Created');
          $scope.ttt();
        });

        var request = gapi.client.calendar.events.update({
          'calendarId': 'help@silverip.com',
          'eventId': objEdit.id,
          'resource': event
        });
        request.execute(function(event) {
          console.log(event);
          $scope.ttt();
        });
        $scope.eventUpdateNotify('Event Updated');
      }
      else
      {
        var request = gapi.client.calendar.events.update({
          'calendarId': 'help@silverip.com',
          'eventId': objEdit.id,
          'resource': event
        });
        request.execute(function(event) {
          console.log(event);
          $scope.ttt();
        });
        $scope.eventUpdateNotify('Event Updated');
      }
    }
      //THIS IS COMMON INSERT
    else {
      var geNameTmp = regExp.exec(event.summary)[1];
      var tmpNameSplit = geNameTmp.split('/');
      var summary = event.summary.split('(')[1].split(')')[1];

      if (tmpNameSplit[1])
      {
        var event2 = event;

        event.summary = '(' + tmpNameSplit[0] + ')' + summary;

        var request1 = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event
        });
        request1.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });

        event2.summary = '(' + tmpNameSplit[1] + ')' + summary;

        var request2 = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event2
        });
        request2.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });

      }
      else
      {

        var request = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event
        });
        request.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });
      }
    }

    $scope.cancelNewEvent();

  }
  function getFormatTime(format, str) {
    var hours = Number(str.match(/^(\d+)/)[1]);
    var minutes = Number(str.match(/:(\d+)/)[1]);
    var AMPM = str.match(/\s?([AaPp][Mm]?)$/)[1];
    var pm = ['P', 'p', 'PM', 'pM', 'pm', 'Pm'];
    var am = ['A', 'a', 'AM', 'aM', 'am', 'Am'];
    if (pm.indexOf(AMPM) >= 0 && hours < 12) hours = hours + 12;
    if (am.indexOf(AMPM) >= 0 && hours == 12) hours = hours - 12;
    var sHours = hours.toString();
    var sMinutes = minutes.toString();
    if (hours < 10) sHours = "0" + sHours;
    if (minutes < 10) sMinutes = "0" + sMinutes;
    if (format == '0000') {
      return (sHours + sMinutes);
    } else if (format == '00:00') {
      return (sHours + ":" + sMinutes);
    } else {
      return false;
    }
  }
  function createIsoDate(time) {
    var fecha = new Date();
    var anio = fecha.getFullYear();
    var mes = fecha.getMonth()+1;
    var dia = fecha.getDate();

    return (anio + '-' + mes + '-' + dia + 'T' + time + ':00-05:00');
  }
  function setEventName(id){
    var who = {'0':''
              ,'1':'Izzy'
              ,'2':'Melvin'
              ,'3':'Abe'
              ,'4':'Eli'
              ,'5':'Brian'
              ,'6':'Charlie'
              ,'7':'Pablo'
    };
    var splitVal = id.split('-');
    var texto = '(' + who[splitVal[2]] + ')';
    $scope.eventtitle = texto;
    warpol('.input-titulo').val(texto).focus();
  }
  $scope.setSelectValue = function (t1,t2){
    $scope.timeIni = t1;
    $scope.timeFin = t2;
  };

});

























