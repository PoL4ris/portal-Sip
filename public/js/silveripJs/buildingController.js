
//Building Controllers
app.controller('buildingCtl',                       function($scope, $http, $stateParams, customerService) {


  console.log('bldContrl');
  if(customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }

  $scope.idBuilding = null;

  if($stateParams.id)
    $scope.idBuilding = $stateParams.id;

  $http.get("buildingData", {params:{'id' : $scope.idBuilding}})
    .then(function (response) {
      $scope.buildingData = response.data;
    });

  $http.get("getBuildingsList")
    .then(function (response) {
      $scope.bldListResult = response.data;
    });

  $http.get("getBuildingProperties")
    .then(function (response) {
      $scope.propValuesList = response.data;
    });










//verifyUse
//     if (!$scope.sbid || !$stateParams.id) {
//         $scope.SiteMenu = [];
//         $http.get('buildings')
//           .then(function (data){
//             $scope.bldData = data.data;
//             $scope.bld = $scope.bldData.building;
//             //       $scope.offsetLimitFunction($scope.bldData.offset, $scope.bldData.limit);
//         }), function (error){
//             alert('Error');
//         }
//     }
//     else {
//       console.log($stateParams.id);
//         $http.get("buildings", params + $scope.sbid ? $scope.sbid : $stateParams.id)
//             .then(function (response) {
//             $scope.bld = response.data;
//         });
//     }





  $scope.displayBldData = function (idBld) {
    $http.get("buildings/" + idBld)
      .then(function (response) {
        $scope.buildingData = response.data;
      });
  }
  $scope.filterBldList = function(){
    $http.get("getFilterBld", {params: {'query' : this.filterBldListModel}})
      .then(function (response) {
        $scope.bldListResult = response.data;
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
  $scope.buildingsList = function (position) {
    //Math var operations.
    var offset              = parseInt($scope.bldData.offset);
    var limit               = parseInt($scope.bldData.limit);
    var a                   = parseInt(offset);
    var b                   = parseInt(limit);
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

    //     $scope.offsetLimitFunction(offset, limit);
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

    var infoData = getFormValues('new-bpv-form');
    infoData['id_buildings'] = $scope.bld.id;

    $http.get("insertBuildingProperties", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-property-cancel').scope().fadeViews('bpv-container', 'new-form-function', 0, 'enable', 'add-property', 'add-property-cancel')
    $('#new-bpv-form').trigger("reset");
  }
  $scope.insertBuildingContact = function  (){
    var infoData = getFormValues('new-bc-form');
    if (!infoData['first_name'] && !infoData['last_name'] && !infoData['contact'])
      return;

    infoData['id_buildings'] = $scope.bld.id;

    $http.get("insertBuildingContacts", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    angular.element('#add-contact-cancel').scope().fadeViews('bc-container', 'new-contact-form', 0, 'enable-contact', 'add-contact', 'add-contact-cancel')
    $('#new-bc-form').trigger("reset");

  };
})
  .directive('getBuildingPropValues',             function (){
    return function (scope){
      scope.getBuildingPropertyValues();
    }

  })
  .directive('buildingContactForm',               function(){


    return {
      restrict: 'E',
      replace: true,
      link: function(scope, form){
        form.bootstrapValidator({
          container : '#messages',
          feedbackIcons : {
            valid : 'glyphicon glyphicon-ok',
            invalid : 'glyphicon glyphicon-remove',
            validating : 'glyphicon glyphicon-refresh'
          },
          fields : {
            first_name : {
              validators : {
                notEmpty : {
                  message : 'The First Name is required and cannot be empty'
                }
              }
            },
            last_name : {
              validators : {
                notEmpty : {
                  message : 'The Last Name is required and cannot be empty'
                }
              }
            },
            contact : {
              validators : {
                notEmpty : {
                  message : 'The Contact field is required and cannot be empty'
                }
              }
            },
            fax:{
              validators : {
                stringLength : {
                  max : 100,
                  message : 'The Fax must be less than 100 characters long'
                }
              }
            },
            company:{
              validators : {
                stringLength : {
                  max : 200,
                  message : 'The Company must be less than 200 characters long'
                }
              }
            },
            comments : {
              validators : {
                stringLength : {
                  max : 500,
                  message : 'The Comment must be less than 500 characters long'
                }
              }
            }
          }
        });
      }
    }
  })
//Building Side Bar
app.controller('sideBldController',                 function($scope, $http, buildingService, $stateParams, customerService){




  $http.get("getBuildingsList")
    .then(function (response) {
      $scope.bldListResult = response.data;
    });


  $scope.displayBldDataS = function (idBld) {
    console.log(idBld);
    $scope.displayBldData(idBld);
  }
})