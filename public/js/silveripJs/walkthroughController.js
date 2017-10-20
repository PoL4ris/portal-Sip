app.controller('walkthroughController', function ($scope, $http, customerService, generalService) {





  //This is the checkMobileDevice
  console.log($.browser.mobile);
  $scope.mobDevice = $.browser.mobile;
  $scope.flagTab = 'general';
  $scope.notesArray = [];

  //values to reset...
  $scope.resetValues = function () {

    $scope.viewResults = false;
    $scope.verifyMsgView1 = false;
    $scope.flagTab = 'general';
    $scope.notesArray = [];

  }

  if (generalService.sideBarFlag) {
    $scope.sipTool(2);
    generalService.sideBarFlag = false;
  }

  /*DOROP ENGINE*/
  var ctrl = this;
  ctrl.data = {upload: []}
  $scope.filesControl = ctrl.data.upload;


  $scope.getDataControl = function () {

    var objetos = getFormValues('walkthrough-form');

    for (var obj in objetos) {
      $scope.filesControl[obj.split('image-')[1]].comment = objetos[obj];
    }

  }
  $scope.removeImage = function (keyId) {

    if (generalService.stateRoute == 'newBuilding')
      generalService.nBuildingImage = false;


    $scope.filesControl.splice(keyId, 1);
    ctrl.data.upload = $scope.filesControl;

  }

  $('.drop-zone-box').on('dragenter', function () {
    $(this)
      .css({'background-color': 'rgba(255,255,255,0.4)'})
      .find("p").show();
  });
  $('.drop-zone-box').on('dragleave', function () {
    $(this)
      .css({'background-color': ''})
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

  $scope.nextPhase = function (id, index) {
    $('#' + id + index).css('left', '-110%');
    $('#' + id + (index + 1)).css('left', '0');
  }
  $scope.backPhase = function (id, index) {
    $('#' + id + index).css('left', '100%');
    $('#' + id + (index - 1)).css('left', '0');
  }

  $scope.getWtLocation = function (id) {

    $http.get("getWalkthroughLocation", {params: {'id': id}})
      .then(function (response) {

        $scope.newDataLoaded = response.data;

        $scope.nextPhase('mw-view-', 0);
        $scope.nextPhase('mw-view-', 1);

      });
  }

  $scope.verifyBldRecord = function (table) {

    if (table == 'building') {
      $http.get("buildingsSearch", {params: {'querySearch': this.verifyInfoBld, 'table': table}})
        .then(function (response) {

          $scope.nameVerifyData = response.data;

        });
    }
    else {
      $http.get("buildingsSearch", {params: {'querySearch': this.verifyInfoAdd, 'table': table}})
        .then(function (response) {

          $scope.addressVerifyData = response.data;

        });
    }

    $scope.mwView1 = true;
  };


  $scope.seeResults = function (type) {
    if (type == 'bld')
      $scope.viewResultsName = !$scope.viewResultsName;
    else
      $scope.viewResultsAddress = !$scope.viewResultsAddress;
  }
  $scope.insertProspectBuilding = function () {
    if ($scope.addressVerifyData['count'] == 0 && $scope.nameVerifyData['count'] == 0) {

      $scope.nextPhase('mw-view-', 1);
      $scope.verifyMsgView1 = false;

      //insert temporal Location
      $http.get("insertWalkthroughLocation", {
        params: {
          'name': this.verifyInfoBld,
          'address': this.verifyInfoAdd
        }
      })
        .then(function (response) {
          $scope.newDataLoaded = response.data;
        });
    }

    $scope.verifyMsgView1 = true;
    return;
  }

  $scope.setTabFlag = function (tabName) {
    $scope.flagTab = tabName;
  }
  $scope.updateinstance = function () {

    console.log('update instance with flag = ' + $scope.flagTab);

    switch ($scope.flagTab) {
      case  'general':

        var objects = getFormValues('general-tab-content');
        objects['id_buildings'] = $scope.newDataLoaded.building.id;
        objects['id_address'] = $scope.newDataLoaded.id;

        $http.get("updateWalkthroughLoc", {params: objects})
          .then(function (response) {
//            $scope.newDataLoaded = response.data;
            $scope.updatedGeneralValues();
          });

        break;
      case  'notes':

        var savedObjects = getFormValues('wt-saved-notes');
        var objects = getFormValues('walkthrough-form-notes');

        $http.get("insertWtNotes", {
          params: {
            insert: objects,
            update: savedObjects,
            id_buildings: $scope.newDataLoaded.building.id
          }
        })
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

        $http.get("updateMediaFiles", {params: objects})
          .then(function (response) {

            $scope.newDataLoaded = response.data;

            if ($scope.filesControl.length > 0) {

              for (var x = 0 in tmpDataStance) {

                $http.post("insertMediaFiles", {
                  data: tmpDataStance[x],
                  id_buildings: $scope.newDataLoaded.building.id
                })
                  .then(function (response) {
                    $scope.newDataLoaded = response.data;
                  });

                ctrl.data = {upload: []}
                $scope.filesControl = ctrl.data.upload;
                $scope.updatedImgValues();

              }

            }
          });

        break;
    }


  }
  $scope.removeImgLocation = function (id) {

    $http.get("removeImgLocation", {params: {'id': id, 'id_buildings': $scope.newDataLoaded.building.id}})
      .then(function (response) {

        $scope.newDataLoaded = response.data;

      });

  }
  $scope.addNoteFiled = function () {
    $scope.notesArray.push([]);
  }
  $scope.removeNoteField = function (index) {
    $scope.notesArray.splice(index, 1);
  }
  $scope.removeNoteLocation = function (id) {
    $http.get("removeNoteLocation", {params: {'id': id, 'id_buildings': $scope.newDataLoaded.building.id}})
      .then(function (response) {
        $scope.newDataLoaded = response.data;
      });
  }


  $scope.updatedGeneralValues = function () {
    $('#update-code').fadeOut('slow');
    $('#update-type').fadeOut('slow');
    $('#update-units').fadeOut('slow');
    $('#update-floors').fadeOut('slow');
    $('#update-neighborhood').fadeOut('slow');
  }
  $scope.updatedNotesValues = function () {
    $('.saved-notes-icon').fadeOut('slow');
  }
  $scope.updatedImgValues = function () {
    $('.saved-img-icon').fadeOut('slow');
  }
  $scope.setToUpdate = function (id) {
    $('#' + id).fadeIn('slow');
  }
  $scope.setToUpdateS = function (id) {
    $('#saved-' + id).fadeIn('slow');
  }
  $scope.setToUpdateN = function (id) {
    $('#note-' + id).fadeIn('slow');
  }
  $scope.setToUpdateI = function (id) {
    $('#i-save-' + id).fadeIn('slow');
  }

})
  .directive('dropZone', ['generalService',
    function (generalService) {

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


//        console.log('entramos al draganddrop');
//        console.log(generalService);


        e.stopPropagation();
        e.preventDefault();
        e.dataTransfer.dropEffect = 'copy';
      }

      function uploadFileSelect(e) {

        e.stopPropagation();
        e.preventDefault();
        var files = e.dataTransfer ? e.dataTransfer.files : e.target.files;

        if (generalService.nBuildingImage)
          return;


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

          if (generalService.stateRoute == 'newBuilding') {
            generalService.nBuildingImage = true;
            return;
          }

        }
      }

    }
  ])
































