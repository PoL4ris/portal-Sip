app.controller('libController', function ($scope, $http) {

//  console.log('dashboardController');

  $http.get("getMainDashboard")
    .then(function (response) {
      $scope.dash1 = true;
      var data = response.data;
      new CountUp('t1', 0, data['commercial'], 0, 3).start();
      new CountUp('t2', 0, data['retail'],     0, 3).start();
      new CountUp('t3', 0, data['tickets'],    0, 3).start();
      new CountUp('t4', 0, data['avgHour'],    0, 3).start();
      new CountUp('t5', 0, data['avgDay'],     0, 3).start();
    });

  var today = new Date();
  var dateRequest = ((today.getMonth() + 1) + '/' + today.getDate() + '/' + today.getFullYear());

  $http.get("getCalendarDashboard", {params: {'date': dateRequest}})
    .then(function (response) {
      $scope.calendarData = true;
      var data = response.data;
      new CountUp('t6', 0, data['total_events'],  0, 3).start();
      new CountUp('t7', 0, data['complete'],      0, 3).start();
      new CountUp('t8', 0, data['pending'],       0, 3).start();
      new CountUp('t9', 0, data['onsite'],        0, 3).start();
  });


});

app.controller('dropZoneController', function($scope, $http, customerService, generalService){





  //This is the checkMobileDevice
  console.log($.browser.mobile);
  $scope.mobDevice  = $.browser.mobile;
  $scope.flagTab    = 'general';
  $scope.notesArray = [];

  //values to reset...
  $scope.resetValues = function (){

    $scope.viewResults    = false;
    $scope.verifyMsgView1 = false;
    $scope.flagTab        = 'general';
    $scope.notesArray     = [];

  }

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  /*DOROP ENGINE*/
  var ctrl  = this;
  ctrl.data = { upload:[] }
  $scope.filesControl = ctrl.data.upload;


  $scope.getDataControl = function(){

    var objetos = getFormValues('walkthrough-form');

    for(var obj in objetos )
    {
      $scope.filesControl[obj.split('image-')[1]].comment = objetos[obj];
    }

  }
  $scope.removeImage    = function (keyId){
    $scope.filesControl.splice(keyId, 1);
    ctrl.data.upload = $scope.filesControl;
  }

  $('.drop-zone-box').on('dragenter', function() {
    $(this)
      .css({'background-color' : 'rgba(255,255,255,0.4)'})
      .find("p").show();
  });
  $('.drop-zone-box').on('dragleave', function() {
    $(this)
      .css({'background-color' : ''})
      .find("p").hide();
  });













  /*mas Engine*/
  $scope.phaseFlagStyle = 0;

  $http.get("getProspectBuildings")
    .then(function (response) {
      $scope.prospectBuildings = response.data
    });
  $http.get("getNeighborhoodList")
    .then(function (response) {
      $scope.neighborhoodList = response.data
    });

  $scope.nextPhase = function(id, index){
    $('#' + id + index).css('left', '-110%');
    $('#' + id + (index+1)).css('left', '0');
  }
  $scope.backPhase = function(id, index){
    $('#' + id + index).css('left', '100%');
    $('#' + id + (index-1)).css('left', '0');
  }

  $scope.getWtLocation = function(id){

    $http.get("getWalkthroughLocation", {params: {'id':id}})
      .then(function (response) {

        $scope.newDataLoaded = response.data;

        $scope.nextPhase('mw-view-',0);
        $scope.nextPhase('mw-view-',1);

      });
  }

  $scope.verifyBldRecord = function (table){

    if(table  == 'building')
    {
      $http.get("buildingsSearch", {params:{'querySearch':this.verifyInfoBld, 'table':table}})
        .then(function (response) {

          $scope.nameVerifyData = response.data;

        });
    }
    else
    {
      $http.get("buildingsSearch", {params:{'querySearch':this.verifyInfoAdd, 'table':table}})
        .then(function (response) {

          $scope.addressVerifyData = response.data;

        });
    }

    $scope.mwView1 = true;
  };


  $scope.seeResults     = function (type){
    if(type == 'bld')
      $scope.viewResultsName = !$scope.viewResultsName;
    else
      $scope.viewResultsAddress = !$scope.viewResultsAddress;
  }
  $scope.insertProspectBuilding = function(){
    if($scope.addressVerifyData['count'] == 0 && $scope.nameVerifyData['count'] == 0)
    {

      $scope.nextPhase('mw-view-', 1);
      $scope.verifyMsgView1 = false;

      //insert temporal Location
      $http.get("insertWalkthroughLocation", {params:{'name':this.verifyInfoBld, 'address':this.verifyInfoAdd}})
        .then(function (response) {
          $scope.newDataLoaded = response.data;
        });
    }

    $scope.verifyMsgView1 = true;
    return;
  }

  $scope.setTabFlag     = function(tabName){
    $scope.flagTab = tabName;
  }
  $scope.updateinstance = function(){

    console.log('update instance with flag = ' + $scope.flagTab);

    switch ($scope.flagTab)
    {
      case  'general':

        var objects = getFormValues('general-tab-content');
        objects['id_buildings'] = $scope.newDataLoaded.building.id;
        objects['id_address']   = $scope.newDataLoaded.id;

        $http.get("updateWalkthroughLoc", {params : objects})
          .then(function (response) {
//            $scope.newDataLoaded = response.data;
            $scope.updatedGeneralValues();
          });

        break;
      case  'notes':

        var savedObjects = getFormValues('wt-saved-notes');
        var objects      = getFormValues('walkthrough-form-notes');

        $http.get("insertWtNotes", {params : {insert:objects, update:savedObjects, id_buildings:$scope.newDataLoaded.building.id}})
          .then(function (response) {
            $scope.newDataLoaded = response.data;
            $scope.notesArray = [];
            $scope.updatedNotesValues()
          });

        break;
      case  'images':

        $scope.getDataControl();
        var objects = getFormValues('walkthrough-form-images');
        objects['id_buildings'] = $scope.newDataLoaded.building.id;
        var tmpDataStance = $scope.filesControl;

        $http.get("updateMediaFiles", {params : objects})
          .then(function (response) {

            $scope.newDataLoaded = response.data;

            if($scope.filesControl.length > 0)
            {

              for (var x = 0 in tmpDataStance) {

                $http.post("insertMediaFiles", {data : tmpDataStance[x], id_buildings  : $scope.newDataLoaded.building.id})
                  .then(function (response) {
                    $scope.newDataLoaded = response.data;
                  });

                ctrl.data = { upload:[] }
                $scope.filesControl = ctrl.data.upload;
                $scope.updatedImgValues();

              }

            }
          });

        break;
    }




  }
  $scope.removeImgLocation = function(id){

    $http.get("removeImgLocation", {params : {'id':id, 'id_buildings':$scope.newDataLoaded.building.id}})
      .then(function (response) {

        $scope.newDataLoaded = response.data;

      });

  }
  $scope.addNoteFiled = function(){
    $scope.notesArray.push([]);
  }
  $scope.removeNoteField = function(index){
    $scope.notesArray.splice(index, 1);
  }
  $scope.removeNoteLocation = function(id){
    $http.get("removeNoteLocation", {params : {'id':id, 'id_buildings':$scope.newDataLoaded.building.id}})
      .then(function (response) {
        $scope.newDataLoaded = response.data;
      });
  }






  $scope.updatedGeneralValues  = function(){
    $('#update-code').fadeOut('slow');
    $('#update-type').fadeOut('slow');
    $('#update-units').fadeOut('slow');
    $('#update-floors').fadeOut('slow');
    $('#update-neighborhood').fadeOut('slow');
  }
  $scope.updatedNotesValues = function(){
    $('.saved-notes-icon').fadeOut('slow');
  }
  $scope.updatedImgValues = function(){
    $('.saved-img-icon').fadeOut('slow');
  }
  $scope.setToUpdate    = function(id){
    $('#'+id).fadeIn('slow');
  }
  $scope.setToUpdateS    = function(id){
    $('#saved-'+id).fadeIn('slow');
  }
  $scope.setToUpdateN    = function(id){
    $('#note-'+id).fadeIn('slow');
  }
  $scope.setToUpdateI    = function(id){
    $('#i-save-'+id).fadeIn('slow');
  }

})
.directive('dropZone',[
    function(){

      var config = {
        template: '<label class="drop-zone">' +
        '<input type="file" multiple accept="jpg" />' +
        '<div ng-transclude></div>' +       // <= transcluded stuff
        '</label>',
        transclude: true,
        replace: true,
        require: '?ngModel',
        link: function (scope, element, attributes, ngModel) {
          var upload = element[0].querySelector('input');
          upload.addEventListener('dragover', uploadDragOver, false);
          upload.addEventListener('drop', uploadFileSelect, false);
          upload.addEventListener('change', uploadFileSelect, false);
          config.scope = scope;
          config.model = ngModel;
        }
      }
      return config;

      // Helper functions
      function uploadDragOver(e) {
        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
      }

      function uploadFileSelect(e) {

        e.stopPropagation();
        e.preventDefault();
        var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;
        for (var i = 0, file; file = files[i]; ++i) {
          var reader = new FileReader();
          reader.onload = (function (file) {
            return function (e) {

              var data = {
                data: e.target.result,
                dataSize: e.target.result.length
              };
              for (var p in file) {
                data[p] = file[p]
              }

              config.scope.$apply(function () {
                config.model.$viewValue.push(data)
              })
            }
          })(file);
          reader.readAsDataURL(file);
        }
      }
    }
])


//New customer controller working
app.controller('newcustomerAppController', function($scope, $http, customerService, $state, generalService){
  console.log('this is the newcustomerAppController');

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  $scope.selectedBuilding = '/img/logoSmall.png';

  $http.get("getBuildingsList")
  .then(function (response) {
    $scope.buildingsData = response.data
  })

  $scope.setImageBuilding       = function (type){

    var optionVal = null;

    if(type == 0)
    {
      if(this.selectedOption == '')
      {
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

      $scope.selectedBuilding = '/img/buildings/' + this.selectedOption.img_building;

      $('#cn-filter').val('');
      optionVal = this.selectedOption.id;
    }
    else
    {
      if(this.filterBld == '')
      {
        $('#img-displayed').fadeOut();
        return;
      }

      $scope.selectedBuilding = '/img/buildings/' + this.filterBld.img_building;
      $('#cn-filter').val(this.filterBld.code + ' | ' + this.filterBld.name);
      $('#cn-select-address-opt').val('');
      optionVal = this.filterBld.id;
    }

    $scope.bldListResult = null;
    $('#cn-address-value').val(optionVal);
    $('#cn-address-value').attr('pass', true);
    $('#img-displayed').fadeIn();

    $scope.getAvailableServices(optionVal);
    $scope.getSwitchInfo(optionVal);

  }
  $scope.getAvailableServices   = function (idBuilding) {
    $http.get("getAvailableServices", {params: {'id': idBuilding}})
      .then(function (response) {
        $scope.availableServices = response.data;
      });
  }
  $scope.getSwitchInfo          = function (idBuilding) {
    $http.get("getBuildingSwitches", {params: {'id': idBuilding}})
      .then(function (response) {
        $scope.buildingSwitches = response.data;
      });
  }

  $scope.serviceSelected        = function (){
    console.log('this is the new ===> serviceSelected');
    var id = this.service.id;
    $scope.selectedServiceDisplay = this.service.product;

    $('.service-list').addClass('unfocus-service');
    $('.service-list').removeClass('selected-service');
    $('.service-list i').fadeOut();
    $('.select-service-' + id).removeClass('unfocus-service');
    $('.select-service-' + id).addClass('selected-service');
    $('.select-service-' + id + ' i').fadeIn();
  }

  $scope.getavailablePorts      = function (){

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

  $scope.portSelected           = function(){

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

  $scope.ableDisableSwitches    = function (id){
    $('.switches-list').addClass('unfocus-switch');
    $('.switches-list').removeClass('selected-switch');
    $('.select-switch-' + id).removeClass('unfocus-switch');
    $('.select-switch-' + id).addClass('selected-switch');
  }



  $scope.verifyCForm            = function (formData){

    var objects;

    if(!formData)
      objects = getFormValues('new-customer-form');
    else
      objects = formData;

    var mensajes = {
                    'customers.first_name':'First Name missing.',
                    'customers.last_name' :'Last Name missing',
                    'customers.email'     :'Email missing or invalid format',
                    'contacts.value'      :'Phone Number missing or invalid format',

                    'building.id'   :'Address missing',
                    'address.unit'  :'Unit Number missing or invalid format',

                    'product.id'  :'Product missing',
                    'switch.id'   :'Switch missing',
                    'port.id'     :'Port missing',
                    }

    var pIni        = '<p class="required-fields-p">';
    var pFin        = '</p>';
    var requiredDiv = '<div class="req-div">Required:</div>';

    var bloqueA     = ''; $scope.bA   = false;
    var bloqueB     = ''; $scope.bB   = false;
    var bloqueC     = ''; $scope.bC   = false;

    for(var field in objects)
    {
      if(objects[field])
        continue;

      if(field.split('.')[0] == 'customers' || field.split('.')[0] == 'contacts'){
        bloqueA += pIni +  mensajes[field]  + pFin;
        $scope.bA = true;
      }
      if(field.split('.')[0] == 'building' || field.split('.')[0] == 'address')
      {
        bloqueB += pIni +  mensajes[field]  + pFin;
        $scope.bB = true;
      }
      if(field.split('.')[0] == 'product' || field.split('.')[0] == 'switch' || field.split('.')[0] == 'port' )
      {
        bloqueC += pIni +  mensajes[field]  + pFin;
        $scope.bC = true;
      }
    }

    $scope.bloqueA = $scope.bA ? (requiredDiv + bloqueA) : false;
    $scope.bloqueB = $scope.bB ? (requiredDiv + bloqueB) : false;
    $scope.bloqueC = $scope.bC ? (requiredDiv + bloqueC) : false;

    if($scope.bloqueA || $scope.bloqueB || $scope.bloqueC)
    {
      console.log('ERROR ');
      $scope.loadingResponse = true;

      $scope.sendNewCustomer(objects);//to test, comment to work correct.
      return false;
    }
    else
    {
      $scope.sendNewCustomer(objects);
      console.log('INFO LISTA');
    }

  }
  $scope.filterBldList          = function () {
    $http.get("getFilterBld", {params: {'query': this.filterBldListModel}})
      .then(function (response) {
        $scope.bldListResult = response.data;
      });
  }
  $scope.filterName             = function(name) {

    var result;
    var case1 = name.split('GigabitEthernet');
    var case2 = name.split('FastEthernet');

    if(case1[1])
      result = 'Gi' + case1[1];
    else if(case2[1])
      result = 'Fa' + case2[1];
    else
      result = name;

      return result;

  }


  $scope.sendNewCustomer        = function(objects){

    if($scope.errorResponse)
    {
      $scope.triggerErrorMsg();
      return;
    }

    $scope.loadingResponse = true;
    var wapol = {'error':'This is the error msg'};

    if(wapol.error)
    {
      $scope.errorResponse = wapol.error;
      $scope.triggerErrorMsg();

      return;
    }




    $http.get("insertNewCustomer", {params:objects})
      .then(function (response) {
        $scope.loadingResponse = true;

//        $scope.buildingsData = response.data
      })
  }
  $scope.triggerErrorMsg = function(){
    $('.error-message p').css('background', 'rgba(220, 20, 60, 0.42)');

    setTimeout( function(){
      $('.error-message p').css('background', 'white');
      $('.error-message p').css('border', '2px solid rgba(220, 20, 60, 0.42)');
      $('.error-message p').css('border-radius', '3px');
    }  , 2500 );

    $scope.loadingResponse = false;
  }

  $scope.resetFullForm          = function(){
    $state.reload(
      customerService.exist = true,
      customerService.sideBarFlag = true,
      customerService.rightView = false
    );
//    $route.reload();

//    $('#new-customer-form').trigger("reset");
//    $("input[type='hidden']").each(function(){
//      $(this).reset();
//    });
//    $("input[type='text']").each(function(){
//      $(this).reset();
//    });
//    $("select").each(function(){
//      $(this).reset();
//    });
  }




});

//Tabs
app.controller('dummyAppController', function ($scope, $http, customerService, generalService) {

  $scope.addToTabArray = function (id) {

    if (customerService.customerArray[id])
      return;

    customerService.customerArray[id] = id;
    customerService.lastRequestedId   = id;
    $scope.tabsArray                  = customerService.customerArray;
    generalService.rightView          = true;
    $scope.customerServiceData        = customerService.tabs;

  };

  $scope.removeFromTabArray = function (id) {

    delete customerService.customerArray[id];
    $scope.tabsArray = customerService.customerArray;
    delete customerService.tabs[id];
  }


  $scope.createTab = function ($event) {
    $scope.addToTabArray(this.customerData.id);
    $event.stopPropagation();
  }


});




































