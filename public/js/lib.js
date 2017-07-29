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

app.controller('dropZoneController', function($scope, $http, customerService){

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }

  var ctrl = this;
  ctrl.data = { upload:[] }
  $scope.filesControl = ctrl.data.upload;

  $scope.getDataControl = function(){
    console.log($scope.filesControl);
  }
  $scope.removeImage = function (keyId){
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

app.controller('dummyAppController', function ($scope, $http,customerService){
  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }




//  $http.get('renderViewUno')
//    .then(function(response){
//      console.log(response.data);
//      $scope.htmlLoco = response.data;
//      $scope.trustedInputHtml = $sce.trustAsHtml(response.data);
//
//    });





});







app.controller('newcustomerAppController', function($scope, $http, customerService){
  console.log('this is the newcustomerAppController');

  if (customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
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

      if($scope.buildingsData[this.selectedOption])
        $scope.selectedBuilding = '/img/buildings/' + $scope.buildingsData[this.selectedOption].img_building;

      $('#cn-filter').val('');
      optionVal = this.selectedOption;
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
    $('.select-service-' + id).removeClass('unfocus-service');
    $('.select-service-' + id).addClass('selected-service');
  }

  $scope.getavailablePorts      = function (){
    $scope.loadingPorts = false;
    var id = this.switch.id;
    $scope.ableDisableSwitches(id);
    $http.get("getAvailableSwitchPorts", {params: {'ip': this.switch.ip_address}})
      .then(function (response) {
        $scope.switchAvailablePorts = response.data;
        $scope.loadingPorts = true;
      });
  }

  $scope.portSelected = function(){
    console.log(this.index);
    var initIndexId = this.index;

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



  console.log(formData);
  return;

    var fname = $('#cn-fname').attr('pass');
    var lname = $('#cn-lname').attr('pass');
    var email = $('#cn-email').attr('pass');
    var tel   = $('#cn-tel').attr('pass');
    var bld   = $('#cn-address-value').attr('pass');

    if(fname && lname && email && tel && bld)
    {
      console.log(fname + ' | ' + lname + ' | ' + email + ' | ' + tel);
    }
    else
      console.log('error');
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
      result = 'G.E : ' + case1[1];
    else if(case2[1])
      result = 'F.E : ' + case2[1];
    else
      result = name;

      return result;

  }




});



















