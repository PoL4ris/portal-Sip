
//User Profile Controllers
app.controller('userProfileController',             function ($scope, $http){

  $scope.checkboxModel = false;

  $http.get("getProfileInfo")
    .then(function (response){
      $scope.profileData = response.data;
    });

  $scope.customerEditMode   = function (){
    if ( $scope.checkboxModel == true)
    {
      $('.editable-text').fadeIn('slow');
      $('.no-editable-text').css('display', 'none');
    }
    else
    {
      $('.no-editable-text').fadeIn('slow');
      $('.editable-text').css('display', 'none');
    }
  };
  $scope.updatePassword     = function() {
    var psw1 = this.psw1;
    var psw2 = this.psw2;

    if(psw1 == psw2)
    {
      console.log('passwords match update data');
      $http.get("updateProfileInfo", {params:{'password':psw1}})
        .then(function (response){
          if (response.data == 'OK')
          {

            $.smallBox({
              title: "Password Updated!",
              content: "<i class='fa fa-clock-o'></i> <i>3 seconds ago...</i>",
              color: "#739E73",
              iconSmall: "fa fa-thumbs-up bounce animated",
              timeout: 6000
            });

            $('#uno').val('');
            $('#dos').val('');
            $scope.checkboxModel = true;
            $scope.customerEditMode();
          }

          //           console.log( response.data);
        });
    }
    else
      alert('Passwords do not match.');

  };
  $scope.lengthpsw          = function () {
    var psw1Length = this.psw1?this.psw1.length:0;
    var psw2Length = this.psw2?this.psw2.length:0;

    if (psw1Length >= 5 && psw2Length >= 5 )
      $('#pswbton').attr('disabled', false);
    else
      $('#pswbton').attr('disabled', true);
  }

});