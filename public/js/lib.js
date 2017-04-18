
//ADMIN
app.controller('adminController',                   function($scope, $http, customerService, adminService){

  if(customerService.sideBarFlag) {
    $scope.sipTool(2);
    customerService.sideBarFlag = false;
  }

  $scope.displayView = 'users';

  $http.post("getAdminUsers", {params:{'token':adminService.existeToken}})
    .then(function (response) {
      $scope.adminUsers = response.data;
    });



  $scope.editUser             = function(){
    $scope.adminEditingUser = this.data;
  }
  $scope.generatePsw          = function(){
    $scope.createdPsw = Math.random().toString(36).slice(-8);
  }
  $scope.resetPsw             = function(){
    $scope.createdPsw = null;
  }
  $scope.updateUser           = function(){

    var objetos = getFormValues('admin-edit-form');
    var flag = true;

    if(objetos['password'].length === 0)
      objetos['password'] = null;
    else{
      flag = false;
      var tmp1 = objetos['password'].split(' ').length;
      var tmp2 = objetos['password'].length;

      if(tmp1 == 1 && tmp2 == 8)
        flag = true;
    }

    if(flag)
    {
      objetos['id'] = $scope.adminEditingUser.id;
//       console.log(objetos);
      $http.post("updateAdminUser", {params:{'token':adminService.existeToken, 'objetos': objetos}})
        .then(function (response) {
          $scope.adminUsers = response.data;
          $('#adminModal').modal('toggle');
        });
    }
    else
    {
      $('#pswRegen').css('border-color', 'crimson');
      $('#psw-alert').css('display', 'block');
      return;
    }

  }
  $scope.addNewUser           = function(){
    $scope.newUserRequired = true;
    $scope.adminEditingUser = null;
    $scope.createdPsw = null;

  }
  $scope.insertNewUser        = function(){
    var objects = getFormValues('admin-edit-form');

    for(var obj in objects ) {
      if(!objects[obj]){
        $('#psw-alert').css('display', 'block').css('color', 'crimson');
        return;
      }
    }

    $http.post("insertAdminUser", {params:{'token':adminService.existeToken, 'objetos': objects}})
      .then(function (response) {
        $scope.adminUsers = response.data;
        $('#adminModal').modal('toggle');
        $('#admin-edit-form').trigger("reset");
        $scope.createdPsw = null;
      });

  }
  //Profile
  $scope.getAdminProfiles     = function (){
    $http.post("getAdminProfiles", {params:{'token':adminService.existeToken}})
      .then(function (response) {
        $scope.adminProfiles = response.data;
      });
  }
  $scope.getAdminApps         = function (){
    $http.post("getAdminApps", {params:{'token':adminService.existeToken}})
      .then(function (response) {
        $scope.adminApps = response.data;
      });
  }
  $scope.addNewProfile        = function(){
    $scope.getAdminApps();
    $scope.editProfileData = null;
    $scope.newProfile = true;
  };
  $scope.profileCancel        = function(){
    $scope.newProfile = false;
    $scope.editProfileData = false;
  };
  $scope.editProfile          = function(){
    $scope.getAdminApps();
    $scope.newProfile = null;
    $scope.editProfileData = this.data;
  };
  $scope.submitNewProfile     = function(){
    var objects = getFormValues('admin-profile-form');

    if(objects.profile_name.length === 0)
      return;

    $http.post("insertNewProfile", {params:{'objects' : objects}})
      .then(function (response) {
        $scope.adminProfiles = response.data;
        $scope.profileCancel();
      });
  }
  $scope.getAppAccess         = function (profileId, appId) {
    $http.post("getAppAccess", {params : {'id_profiles' : profileId, 'id_apps' : appId}})
      .then(function (response) {
        $scope.dataCheck = response.data;
      });
  }
  $scope.updateAdminProfile   = function(){

    var objects = getFormValues('admin-profile-form');

    if(objects.profile_name.length === 0)
      return;

    objects.id_profiles = $scope.editProfileData.id

    $http.post("updateAdminProfile", {params:{'objects' : objects}})
      .then(function (response) {

        $scope.adminProfiles = response.data;
        $scope.profileCancel();
      });
  }



  $scope.setView          = function(id){
  //Views:
  //0 = Users
  //1 = Profiles
  //2 = Apps
  //3 = Access Apps
  var views = {'0' : 'users',
               '1' : 'profiles',
               '2' : 'apps',
               '3' : 'acces_apps',
              }
  $scope.displayView = views[id];
  };
  $scope.getAdminProfiles();
});
app.controller('adminPAppACont', function ($scope, $http){

  $http.post("getAppAccess", {params : {'id_profiles' : $scope.editProfileData.id, 'id_apps' : $scope.data.id}})
    .then(function (response) {
      $scope.dataCheck = response.data;
    });

});


app.controller('warpolController',                  function($scope, $http){

  console.log('WarpolController con la  Santa Muerte');

  $scope.getGenericSearch = function (){

    console.log(this.genericSearch);

    var query = {'querySearch' : this.genericSearch};

    $http.get("getTicketsSearch", {params:query})
      .then(function (response) {
        $scope.genericSearchResult = response.data;
      });

    return;
  }


});


