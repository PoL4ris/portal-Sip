
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
  $scope.getAppAccess         = function(profileId, appId) {
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
  //Apps
  $scope.getAdminApps         = function(){
    $http.post("getAdminApps", {params:{'token':adminService.existeToken}})
      .then(function (response) {
        $scope.adminApps = response.data;
      });
  }
  $scope.addNewApp            = function(){
    $scope.getAdminProfiles();
    $scope.editAppData = null;
    $scope.newApp = true;
  };
  $scope.appCancel            = function(){
    $scope.newApp = false;
    $scope.editAppData = false;
  };
  $scope.setIconApp           = function(){
    $scope.selectedIcon = this.selectedIcon;
  };
  $scope.editApp              = function(){
    $scope.getAdminProfiles();
    $scope.newApp = null;
    $scope.editAppData = this.data;
  };
  $scope.submitNewApp         = function(){
    var objects = getFormValues('admin-app-form');

    if(objects.app_name.length === 0 || objects.icon.length === 0 || objects.url.length === 0)
      return;

    $http.post("insertNewApp", {params:{'objects' : objects}})
      .then(function (response) {
        $scope.adminApps = response.data;
        $scope.appCancel();
      });
  };
  $scope.updateAdminApp       = function(){

    var objects = getFormValues('admin-app-form');

    if(objects.app_name.length === 0 || objects.url.length === 0)
      return;

    objects.id_apps = $scope.editAppData.id

    $http.post("updateAdminApp", {params:{'objects' : objects}})
      .then(function (response) {

        $scope.adminApps = response.data;
        $scope.appCancel();
      });
  }



  $scope.setView              = function(id){
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
  $scope.getAdminApps();
  $scope.fontAwesomeArray = fontAwesomeArray;

});
app.controller('adminPAppACont',                    function ($scope, $http){

  $http.post("getAppAccess", {params : {'id_profiles' : $scope.editProfileData.id, 'id_apps' : $scope.data.id}})
    .then(function (response) {
      $scope.dataCheck = response.data;
    });
});
app.controller('adminAProfileACont',                function ($scope, $http){
  $http.post("getAppAccess", {params : {'id_profiles' : $scope.data.id, 'id_apps' : $scope.editAppData.id}})
    .then(function (response) {
      $scope.dataCheck = response.data;
    });
});


app.controller('warpolController',                  function($scope, $http){


  $scope.warpolString = 'warpolController';

  var options = {
                  useEasing : true,
                  useGrouping : true,
                  separator : ',',
                  decimal : '.',
                  prefix : '',
                  suffix : ''
                };

//   var demo = new CountUp("testCount", //Target
//                           0,          //Start Val
//                           3562,       //End Val
//                           0,          //Decimals -> optional
//                           2.5,        //Duration -> optional
//                           options     //Options  -> optional
//                          );
//   demo.start();



  $http.get("dummyRouteController")
    .then(function (response) {
      console.log(response.data);
      var data = response.data;
      new CountUp('testCountdos', 0, data.open_tickets, 0, 3).start();

    });


});


