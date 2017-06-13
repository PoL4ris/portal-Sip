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































