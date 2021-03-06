
//Network Controllers
app.controller('networkController',                 function ($scope, $http, customerService, DTOptionsBuilder, DTColumnDefBuilder, generalService){


  if(generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

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


  };//????PENDING

  $scope.getCoreData = function (){
//     console.log(this.dataNet.core);
//     return;
    $scope.pLoad     = true;
    $scope.pRecord   = this.dataNet;
    $http.get("getSwitchStats", {params:{'ip':this.dataNet.core}})
      .then(function (response) {
        $scope.pLoad    = false;
        $scope.pStatus  = response.data[0]
        $scope.pInfo    = response.data[1]
      });
  }
  $scope.addTR = function addTR(id) {

    var stance   = $('#net-btn-' + id);
    var mas      = 'details-control';
    var menos    = 'dc-show-menos';
    var idString = 'nt-tmp-data-' + id;

    if (stance.attr('stance') == '1')
    {
//      console.log('Stance = 2');
      stance.attr('stance', 2);
      stance.removeClass(mas);
      stance.addClass(menos);
      $(' <tr id="' + idString + '"><td colspan="11">info</td></tr>').insertAfter('#det-net-' + id).hide().slideDown('slow');
    }
    else
    {
//      console.log('Stance = 1');
      stance.attr('stance', 1);
      stance.removeClass(menos);
      stance.addClass(mas);
      $('#nt-tmp-data-' + id).remove();
    }
  };
  $scope.cleanHrefField        = function (valor){
    var spaceClean = valor.split(' ')[0];
    var httpClean = spaceClean.match('https*');

    return httpClean ? httpClean['input'] : 'http://' + spaceClean;
  }
  $scope.cleanNetField         = function (valor){
    var httpClean = valor.match('https*');
    return httpClean ? httpClean['input'].split('https://')[1] : valor;
  }

  $scope.pStInOptions = DTOptionsBuilder.newOptions().withDisplayLength(50);
});
app.controller('networkControllerTSort',            function (DTOptionsBuilder, DTColumnDefBuilder, $scope ){

  var vm = this;
  vm.persons = [];
  vm.dtOptions = DTOptionsBuilder.newOptions().withPaginationType('full_numbers').withDisplayLength(25).withOption('order', [1, 'asc']);
  //   vm.dtColumnDefs = [
  //     DTColumnDefBuilder.newColumnDef(0),
  //     DTColumnDefBuilder.newColumnDef(1).withClass('WWWWWWW'),
  //     DTColumnDefBuilder.newColumnDef(2).notSortable()
  //   ];

  vm.persons = $scope.networkData;
});
