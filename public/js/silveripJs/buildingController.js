//Building Controllers
app.controller('buildingCtl',             function ($scope, $http, $stateParams, customerService, buildingService, DTOptionsBuilder, generalService) {
  console.log('bldContrl');

  //TMP HIDE AND SHOW SIDEBAR
  $scope.fedeINbtn  = function () {

    if($scope.mobDevice)
    {
      $('#left-bld').animate({width: '100%'});
      $('#right-bld').animate({width: '0'});
    }
    else{
      $('#left-bld').animate({width: '200px'});
      $('#right-bld').css('width', 'calc(100% - 200px)');
    }
//    $('#left-bld').animate({width: '200px'});
//    $('#right-bld').css('width', 'calc(100% - 200px)');
  }
  $scope.fedeOUTbtn = function () {
    $('#left-bld').animate({width: '0'});
    setTimeout(function(){ $('#right-bld').css('width', '100%'); }, 500);
  }

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  if(generalService.stateRoute == 'buildings' && $scope.mobDevice){
    $('#left-bld').css({width: '0'});
    $('#right-bld').animate({'width': '100%'});
  }


  $scope.idBuilding = null;

  if ($stateParams.id)
    $scope.idBuilding = $stateParams.id;

  if(generalService.stateRoute != 'newBuilding')
  {
    $http.get("buildingData", {params: {'id': $scope.idBuilding}})
      .then(function (response) {
        buildingService.building = response.data;
        $scope.buildingData      = response.data;
      });

    $http.get("getBuildingsList")
      .then(function (response) {
        $scope.bldListResult = response.data;
      });

  }

  $http.get("getBuildingProperties")
    .then(function (response) {
      $scope.propValuesList = response.data;
    });


  $scope.dtOptions = DTOptionsBuilder.newOptions().withDisplayLength(5).withOption('order', [4, 'desc']);
  $scope.dtOptionsResult = DTOptionsBuilder.newOptions().withOption('order', [4, 'desc']);
  var checkoutSelectedProducts = {};
  $scope.pArrayNewBld = {};


  $scope.displayBldData             = function (idBld) {
    $http.get("buildings/" + idBld)
      .then(function (response) {
        $scope.buildingData = response.data;
      });
  }
  $scope.filterBldList              = function () {
    $http.get("getFilterBld", {params: {'query': this.filterBldListModel}})
      .then(function (response) {
        $scope.bldListResult = response.data;
      });
  }
  $scope.displayBldForm             = function () {
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
  $scope.buildingsList              = function (position) {
    //Math var operations.
    var offset = parseInt($scope.bldData.offset);
    var limit = parseInt($scope.bldData.limit);
    var a = parseInt(offset);
    var b = parseInt(limit);
    //Back Arrow empty
    if (position == 0 && offset <= 0 || ($scope.limitoffset == 'dif' && position == 1))
      return;
    //Solve correct LIMIT OFFSET info to request
    if (position == 1) {
      offset = b;
      limit = b + (b - a);
    }
    else {
      limit = a;
      offset = a - (b - a);
    }
    //Case result is wrong
    if (offset < 0 || limit <= 0)
      return;
    //Main info to do request
    var query = {"offset": offset, "limit": limit, "position": position};


    $http.get("buildingsList", {params: query})
      .then(function (response) {
        if (response.data.length == 0)
          $scope.limitoffset = 'dif';
        else {
          $scope.bldData['buildingList'] = response.data;
          $scope.limitoffset = '';
        }
      });

    if ($scope.limitoffset == 'dif') {
      limit = offset;
      offset = (limit - 20);
    }

    $scope.bldData.offset = offset;
    $scope.bldData.limit = limit;

    //     $scope.offsetLimitFunction(offset, limit);
  }
  $scope.buscador                   = function (searchType, side) {
    var query = {};

    if (side == 'left')
      query = {'querySearch': this.searchLeft};
    else
      query = {'querySearch': this.searchRight};

    $http.get("buildingsSearch", {params: query})
      .then(function (response) {
        if (side == 'left')
          $scope.bldSearchResultLeft = response.data;
        else
          $scope.bldSearchResultRight = response.data;
      });

    return;

  }
  $scope.clearSearch                = function () {
    this.searchRight = '';
    $scope.buscador();
  }
  $scope.editFormByType             = function (id) {

    tempTicketID = id;

    if ($('#' + id).attr('stand') == '1') {
      $('.' + id + '-label').css('display', 'table-cell');
      $('.' + id + '-edit').css('display', 'none');
      $('#save-' + id).fadeOut("slow");
      $('#' + id).html('Edit');
      $('#' + id).switchClass('btn-danger', 'btn-info');
      $('#' + id).attr('stand', '2');
      if (path == '/supportdash') {
        $('.resultadosComplex').html('');
        $('.dis-input').val('');
      }

    }
    else {
      $('.' + id + '-label').css('display', 'none');
      $('.' + id + '-edit').fadeIn("slow");
      $('#save-' + id).fadeIn("slow");
      $('#' + id).html('Cancel');
      $('#' + id).switchClass('btn-success', 'btn-danger');
      $('#' + id).attr('stand', '1');
    }

  }
  $scope.submitForm                 = function () {
    console.log('buildingCtl');
    var objects = $('#building-update-form').serializeArray();
    var infoData = {};
    for (var obj in objects) {
      if (objects[obj]['value'] == 'err' || objects[obj]['value'] == '') {
        var tmp = objects[obj]['name'].split('id_');
        alert('Verify ' + (tmp[1] ? tmp[1] : objects[obj]['name']) + ' Field');
        return;
      }
      infoData[objects[obj]['name']] = objects[obj]['value'];
    }

    $http.get("updateBuilding", {params: infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    $scope.editFormByType('block-a');
  }
  $scope.getBuildingPropertyValues  = function () {
    $http.get("getBuildingProperties")
      .then(function (response) {
        $scope.propValuesList = response.data;
      });
  }
  $scope.insertBuildingProperty     = function () {

    var infoData = getFormValues('new-bpv-form');
    infoData['id_buildings'] = $scope.buildingData.id;

    $http.get("insertBuildingProperties", {params: infoData})
      .then(function (response) {
        $scope.buildingData.properties = response.data.properties;
      });

    angular.element('#add-property-cancel').scope().fadeViews('bpv-container', 'new-form-function', 0, 'enable', 'add-property', 'add-property-cancel');
    $('#new-bpv-form').trigger("reset");
  }
  $scope.insertBuildingContact      = function () {
    var infoData = getFormValues('new-bc-form');
    if (!infoData['first_name'] && !infoData['last_name'] && !infoData['contact'])
      return;

    infoData['id_buildings'] = buildingService.building.id;

    $http.get("insertBuildingContacts", {params: infoData})
      .then(function (response) {
        buildingService.building = response.data;
        $scope.buildingData      = response.data;
      });

    angular.element('#add-contact-cancel').scope().fadeViews('bc-container', 'new-contact-form', 0, 'enable-contact', 'add-contact', 'add-contact-cancel')
    $('#new-bc-form').trigger("reset");

  };
  $scope.filterBuildingList         = function () {
    var type = this.buildingFilter;

    if (type == 0)
      type = null

    $http.get("getBuildingsList", {params: {'type': type}})
      .then(function (response) {
        $scope.bldListResult = response.data;
      });
  }



  $scope.jsonPropertiesFix          = function (json) {


    var jsonParse          = JSON.parse(json);
    var jsonKey            = Object.keys(jsonParse)[0];
    var multProp           = Object.keys(jsonParse).length;

    if(multProp > 1)
      multProp = true;
    else
      multProp = false;

    if(!jsonKey)
    {
      $scope.unitsResultData = false;
      $scope.checkAllUnits();
      return;
    }

    var jsonLength         = jsonParse[jsonKey].length;
    var jsonValues         = jsonParse[jsonKey];
    $scope.unitsResultData = {'arrIndex'   : jsonKey,
                              'arrLength'  : jsonLength,
                              'arrValues'  : jsonValues,
                              'rawData'    : jsonParse,
                              'multProp'   : multProp,
                              'recordId'   : this.properData.idBpv ? this.properData.idBpv : this.properData.id
                             };

    return $scope.unitsResultData;
  };
  $scope.exportToCsv                = function () {

    var data = [$scope.unitsResultData.arrValues];

    var csv = buildingService.building.code + ' Units\n';
    data.forEach(function (row) {
      csv += row.join('\n');
      csv += "\n";
    });

    var hiddenElement = document.createElement('a');
    hiddenElement.href = 'data:text/csv;charset=utf-8,' + encodeURI(csv);
    hiddenElement.target = '_blank';
    hiddenElement.download = buildingService.building.name + ' ' + buildingService.building.code + ' Units.csv';
    hiddenElement.click();

  };

  $scope.checkAllUnits              = function () {
    $('.units-checks').prop('checked', $('#check-uncheck').is(':checked'));
  };
  $scope.removePropUnits            = function () {

    var obj = getFormValues('units-form-container');
    var arr = Object.keys(obj).map(function (key) { return obj[key]; });

    if(arr.length > 0 )
      if (arr.length == $scope.unitsResultData.arrLength)
      {
        $http.get("deleteAllPropUnits", {params: {'id': $scope.unitsResultData.recordId,'jsonIndex' : $scope.unitsResultData.arrIndex}})
          .then(function (response) {
            $scope.buildingData = response.data;
            $('#units-form-container').trigger("reset");
          });
      }
      else
      {
        var arreglo = {'arr'  :arr,
                       'id'   : $scope.unitsResultData.recordId,
                       'jsonIndex'    : $scope.unitsResultData.arrIndex,
                       'arrValues'    : $scope.unitsResultData.arrValues,
                       'id_buildings' : buildingService.building.id
                       };

        $http.get("deleteUnitsByArray", {params: {'content' : arreglo }})
          .then(function (response) {
            $scope.buildingData = response.data;
            $('#units-form-container').trigger("reset");
          });
      }

  };
  $scope.addPropUnits               = function () {

    var units = getFormValues('add-units-comma-separated');

    if(units.unitsComaArray.length <= 0 )
      return;

    var arreglo = {'units'       : units.unitsComaArray,
                   'id'           : $scope.unitsResultData.recordId,
                   'jsonIndex'    : $scope.unitsResultData.arrIndex,
                   'arrValues'    : $scope.unitsResultData.arrValues,
                   'id_buildings' : buildingService.building.id
                  };

    $http.get("addUnitsByArray", {params: {'content' : arreglo }})
      .then(function (response) {
        console.log('DONE');
        $scope.buildingData = response.data;
        $('#add-units-comma-separated').trigger("reset");
      });

  };




  $scope.productSearch              = function () {
    $scope.productLoading = false;
    if(!this.productSearchModel || this.productSearchModel.length === 0)
    {
      $scope.productResultSearch = true;
      $scope.productLoading = $scope.productResult = false;
      return;
    }

    $http.get("productsSearch", {params: {'string':this.productSearchModel}})
      .then(function (response) {
        $scope.productResultSearch = response.data.data;
        $scope.productResult  = response.data;
        $scope.productLoading = true;

      });
  };
  $scope.prodIdsArray               = function () {
  if(checkoutSelectedProducts[this.resultSearch.id])
  {
    delete checkoutSelectedProducts[this.resultSearch.id];
    $('.service-id-' + this.resultSearch.id ).removeClass('selected-product-active');
  }
  else
  {
    checkoutSelectedProducts[this.resultSearch.id] = this.resultSearch;
    $('.service-id-' + this.resultSearch.id ).addClass('selected-product-active');
  }
  $scope.checkoutSelectedProducts = checkoutSelectedProducts;
  $scope.productLargo = Object.keys(checkoutSelectedProducts).length;


  };
  $scope.nextPage                   = function () {

    $scope.productLoading = false;

    $http.get("productsSearch"+$scope.productResult.next_page_url, {params: {'string':this.productSearchModel}})
      .then(function (response) {
        $scope.productResultSearch = response.data.data;
        $scope.productResult  = response.data;
        $scope.productLoading = true;

      });
  };
  $scope.prevPage                   = function () {

    $scope.productLoading = false;

    $http.get("productsSearch"+$scope.productResult.prev_page_url, {params: {'string':this.productSearchModel}})
      .then(function (response) {
        $scope.productResultSearch = response.data.data;
        $scope.productResult  = response.data;
        $scope.productLoading = true;
      });
  };
  $scope.submitNewProducts          = function () {

    $scope.checkoutSelectedProducts.id_buildings = $scope.buildingData.id;

    $.SmartMessageBox({
      title: "Please Confirm",
      content: 'Add this Products to this Building?',
      buttons: '[No][Yes]'
    }, function (ButtonPressed) {
      if (ButtonPressed === "Yes") {
        $http.get("insertBuildingProducts", {params: $scope.checkoutSelectedProducts})
          .then(function (response) {

            $scope.buildingData.active_building_products = response.data.active_building_products;
            //ResetValues
            checkoutSelectedProducts = {};
            $scope.checkoutSelectedProducts = checkoutSelectedProducts;
            $('#input-product-name').val('');
            $scope.productResult = $scope.productResultSearch = $scope.productLargo = $scope.productLoading = false;

            //Notiff
            $.smallBox({
              title: "Products added",
              content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
              color: "#739E73",
              iconSmall: "fa fa-thumbs-up bounce animated",
              timeout: 6000
            });


          });
      }
    });

  }



//newBuildingController actions

$scope.propertyValue = function(){

  if(!this.propertyIndex)
    return;

  if(this.propertyIndex[this.prop.id] == '')
  {
    delete $scope.pArrayNewBld[this.prop.id];
    return;
  }

  $scope.pArrayNewBld[this.prop.id] = {'value'    : this.propertyIndex[this.prop.id], 'property' : this.prop.name};

}

$scope.validaFormaCompleta = function(){
  console.log('this is the bush button red');
  var tmpData = $('#new-building-form').find(":invalid");


  tmpData.each(function(index, node){
    console.log(node);
    $(node).addClass('required');
    $(node).focus();
  });
  return;
}




/*
* RESET VALUES
*
* generalService.nBuildingImage = false;
*
* */

})
.directive('getBuildingPropValues',     function () {
    return function (scope) {
      scope.getBuildingPropertyValues();
    }

  })
.directive('buildingContactForm',       function () {
    return {
      restrict: 'E',
      replace: true,
      link: function (scope, form) {
        form.bootstrapValidator({
          container: '#messages',
          feedbackIcons: {
            valid:      'glyphicon glyphicon-ok',
            invalid:    'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
          },
          fields: {
            first_name: {
              validators: {
                notEmpty: {
                  message: 'The First Name is required and cannot be empty'
                }
              }
            },
            last_name:  {
              validators: {
                notEmpty: {
                  message: 'The Last Name is required and cannot be empty'
                }
              }
            },
            contact:    {
              validators: {
                notEmpty: {
                  message: 'The Contact field is required and cannot be empty'
                }
              }
            },
            fax:        {
              validators: {
                stringLength: {
                  max: 100,
                  message: 'The Fax must be less than 100 characters long'
                }
              }
            },
            company:    {
              validators: {
                stringLength: {
                  max: 200,
                  message: 'The Company must be less than 200 characters long'
                }
              }
            },
            comments:   {
              validators: {
                stringLength: {
                  max: 500,
                  message: 'The Comment must be less than 500 characters long'
                }
              }
            },
          }
        });
      }
    }
  })
.directive('newBuildingForm', function () {

  return {
    restrict: 'E',
    replace: true,
    link: function (scope, form, attrs) {
//    console.log(attrs);
//    console.log(attrs.msgid);
      form.bootstrapValidator({
        container: '#' + attrs.msgid,
        feedbackIcons: {
          valid:      'glyphicon glyphicon-ok',
          invalid:    'glyphicon glyphicon-remove',
          validating: 'glyphicon glyphicon-refresh'
        },
        fields: {
          //Building Main Info
          'building[name]': {
            validators: {
              stringLength: {
                min: 2,
                message: 'Error: Invalid Name'
              },
              notEmpty: {
                message: 'Error: This cant be empty'
              },
              callback:{
                
              }
            }
          },
          'building[code]': {
            validators: {
              regexp: {
                regexp: /^[a-z0-9]+$/,
                message: 'Error: No signs allowed !@#$...',
              }
            }
          },
          'building[floors]': {
            validators: {
              numeric: {
                message: 'Error: The value needs to be a number',
              }
            }
          },
          'building[units]': {
            validators: {
              numeric: {
                message: 'Error: The value needs to be a number',
              }
            }
          },
          //END Building Main Info.

          //Address info
          'address[address]': {
            validators: {
              stringLength: {
                min: 2,
                message: 'Error: Invalid Name'
              },
              notEmpty: {
                message: 'Error: This cant be empty'
              },
            }
          },
          'address[zip]': {
            validators: {
              regexp: {
                regexp: /^\d{5}$/,
                message: 'The US zipcode must contain 5 digits'
              }
            }
          },
          //END Address info


          floor: {
            validators: {

              regexp: {
                regexp: /^[0-9]+$/,
                message: 'ERROR: SOLO NUMEROS AQUI'
              },
              stringLength: {
                min: 2,
                max: 10,
                message: 'ERROR: STRINGLENGTH'
              },
              notEmpty: {
                message: 'ERROR: ESTA VACIO'
              },
              empty: {
                message:'ERROR RUDO'
              }

            }
          },


          comments: {
            validators: {
              stringLength: {
                max: 500,
                message: 'The Comment must be less than 500 characters long'
              }
            }
          },


        }
      });
    }
  }
});
app.controller('getBuildingPropertyCtl',  function ($scope, $http){
    $http.get("getBuildingProperty", {params: {'id': $scope.properData.id_building_properties}})
      .then(function (response) {
        $scope.getBuildingProperty = response.data.name;
      });
});


app.controller('getBuildingByAddressId',  function ($scope, $http){

  console.log($scope.dataContained);

  $http.get("getBuildingByAddressId", {params: {'id': $scope.dataContained}})
    .then(function (response) {
      $scope.addressInfoContained = response.data;
    });


});
//Building Side Bar
app.controller('sideBldController',       function ($scope, $http, buildingService, $stateParams, customerService) {

  console.log('this is someting');


  $http.get("getBuildingsList")
    .then(function (response) {
      $scope.bldListResult = response.data;
    });


  $scope.displayBldDataS = function (idBld) {
    console.log(idBld);
    $scope.displayBldData(idBld);
  }
})