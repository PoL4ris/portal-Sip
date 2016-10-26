console.log('LIB->JS');
app.controller('menuController',    function($scope, $http){
  $http.get('/menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
});

app.controller('buildingCtl',       function($scope, $http) {

  if (!$scope.sbid) {
    $scope.SiteMenu = [];
    $http.get('buildings').then(function (data){
      $scope.bldData = data.data;
      $scope.bld = $scope.bldData.building;
    }), function (error){
      alert('Error');
    }
  }
  else {
    $http.get("buildings/" + $scope.sbid)
      .then(function (response) {
        $scope.bld = response.data;
      });
  }
  $scope.displayBldData = function (idBld) {
    $http.get("buildings/" + idBld)
      .then(function (response) {
        $scope.bld = response.data.building;
      });

  }
  $scope.displayBldForm = function () {
    if ($scope.show == false) {
      $scope.show = true;
      $('#bld-content-form').fadeIn('slow');
      $('#add-bld-btn').fadeOut('fast');
      $('#cancel-bld-btn').fadeIn('fast');
    }
    else {
      $scope.show = false;
      $('#bld-content-form').fadeOut('slow');
      $('#add-bld-btn').fadeIn('fast');
      $('#cancel-bld-btn').fadeOut('fast');
    }
  }
  $scope.buscador = function(searchType, side) {
    var query = {};

    if (side == 'left')
      query = {'querySearch' : this.searchLeft};
    else
      query = {'querySearch' : this.searchRight};

    $http.get("buildingsSearch", {params:query})
      .then(function (response) {
        if (side == 'left')
          $scope.bldSearchResultLeft = response.data;
        else
          $scope.bldSearchResultRight = response.data;
      });

    return;

  }
  $scope.clearSearch = function (){
    this.searchRight = '';
    $scope.buscador();
  }
  $scope.editFormByType = function (id) {

    tempTicketID = id;

    if ($('#' + id).attr('stand') == '1')
    {
      $('.' + id + '-label').css('display','table-cell');
      $('.' + id + '-edit').css('display','none');
      $('#save-' + id).fadeOut( "slow" );
      $('#' + id).html('Edit');
      $('#' + id).switchClass('btn-danger', 'btn-info');
      $('#' + id).attr('stand', '2');
      if(path == '/supportdash')
      {
        $('.resultadosComplex').html('');
        $('.dis-input').val('');
      }

    }
    else
    {
      $('.' + id + '-label').css('display','none');
      $('.' + id + '-edit').fadeIn( "slow" );
      $('#save-' + id).fadeIn( "slow" );
      $('#' + id).html('Cancel');
      $('#' + id).switchClass('btn-success', 'btn-danger');
      $('#' + id).attr('stand', '1');
    }

  }
  $scope.submitForm = function () {
    console.log('buildingCtl');
    var objects = $('#building-update-form').serializeArray();
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
  $scope.getBuildingPropertyValues = function(){
    $http.get("getBuildingProperties")
      .then(function (response) {
        $scope.propValuesList = response.data;
      });
  }
  $scope.insertBuildingProperty = function (){
    var objects = $('#new-bpv-form').serializeArray();
    var infoData = {};
    for(var obj in objects ) {
      infoData[objects[obj]['name']] = objects[obj]['value'];
    }
    infoData['id_buildings'] = $scope.bld.id;

    $http.get("insertBuildingProperties", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-property-cancel').scope().fadeViews('bpv-container', 'new-form-function', 0, 'enable', 'add-property', 'add-property-cancel')
    $('#new-bpv-form').trigger("reset");
  }
})
.directive('getBuildingPropValues', function (){
  return function (scope){
    scope.getBuildingPropertyValues();
  }

})




/* Global Tools */
app.controller('globalToolsCtl', function ($scope, $http, $compile, $sce){
  $scope.leftColumnOpenClose = function (){
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
  $scope.singleUpdateXedit   = function(id, value, field, table) {

    var data = {};
    data['id']    = id;
    data['value'] = value;
    data['field'] = field;
    data['table'] = table;
//     data['id_customers'] = $scope.customerData.id;
    $http.get("update" + table + "Table", {params:data})
      .then(function (response) {
        console.log('OK');
      });
  }
  $scope.fadeViews           = function (view1, view2, action,bt1,bt2,bt3){
  /*
    view1 = view to hide
    view2 = view to show
    action = [0 = cancel
              1 = addNew
              2 = ]
    bt1 = actionButtons
    bt2 = actionButtons
    bt3 = cancelButton
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
});

function gToolsxEdit(value, field, id, idContainer, table){
  angular.element('#' + idContainer + '-gTools').scope().singleUpdateXedit(id, value, field, table);
}
