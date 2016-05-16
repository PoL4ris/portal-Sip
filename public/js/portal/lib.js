//Global var.
var idDivResult = '';
var path = '';
var complexType = [];
var query = null;
var index = null;
var urlTmp = null;
var tempTicketID = null;
var globalName = null;
var customerSeccion = null;


//getTicketData
var createFancyBox = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var typeoff = warpol(this).attr('typeoff');
    if(typeoff == 'open')
    {
      warpol("#bgblack-" + warpol(this).attr('ticket-id')).fadeIn("slow");
      warpol("#fancy-" + warpol(this).attr('ticket-id')).fadeIn("slow");
    }
    else
    {
      warpol("#bgblack-" + warpol(this).attr('ticket-id')).fadeOut("slow");
      warpol("#fancy-" + warpol(this).attr('ticket-id')).fadeOut("slow");
    }
  }
};
//get building list OFFSET LIMIT
//--->angular
var buildingsList = function (position)
{
  return function(data, textStatus, jqXHR)
  {
    //Get Data from current Info
    var idDivResult    = warpol('#bldlist-result').attr('id');
    var offset         = parseInt(warpol('.' + idDivResult + '-limits-' + position).attr('offset'));
    var limit          = parseInt(warpol('.' + idDivResult + '-limits-' + position).attr('limit'));
    //Math var operations.
    var a              = parseInt(offset);
    var b              = parseInt(limit);
    //Back Arrow empty
    if (position == 0 && offset <= 0)
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
    //AJAX request
    warpol.ajax(
      {
        type: "GET",
        url: "buildingsList",
        data: query,
        success: function (data) {
          if (data.length === 0 || !data.trim())
            return;
          //Result JsonParser to use data
          var resultData = jQuery.parseJSON(data);

          warpol('#' + idDivResult).html('');
          warpol.each(resultData, function (i, item) {
            warpol('#' + idDivResult).append('<p>' + item.id + item.name + '</p>');
          });
          //Rewrite LIMIT OFFSET fields for new calcRequest
          warpol('.' + idDivResult + '-limits-' + 0).attr('offset',offset);
          warpol('.' + idDivResult + '-limits-' + 0).attr('limit',limit);
          warpol('.' + idDivResult + '-limits-' + 1).attr('offset',offset);
          warpol('.' + idDivResult + '-limits-' + 1).attr('limit',limit);
          warpol('#' + idDivResult).scrollTop(0);
        }
      }
    );
  };
};
//Search by type
var buscador = function(searchType)
{
  return function(data, textStatus, jqXHR)
  {

    //id de quien solicita
    idDivResult = idDivResult?idDivResult:warpol(this).attr('id');

    if (!idDivResult)
      return;

    //Clean search fields
    warpol('#' + idDivResult + '-result').html('');
    warpol('.resultadosComplex').html('');

    //checamos si es simple o complex
    if(document.getElementById('complexSearch'))
      searchType = 'Complex';

    if (searchType == 'Simple')
    {
      warpol('.ntas-tmp').css('display', 'none');
      query = {"querySearch" : warpol(this).val()};
    }
    else // if searchType == 'COMPLEX'
    {
      index = warpol(this).attr('index');
      complexType[0] = 'complex';
      complexType[index] = warpol(this).val();
      query = {"querySearch" : complexType };
    }

    path = window.location.pathname;

    //Split ID for Dynamic UrlRequest
    urlTmp = idDivResult.split('id-');
    urlTmp = urlTmp[1].split('-search');

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/"+ urlTmp[0] + "Search",
        data:query,
        success: function(data)
        {
          //Validate info existing or die
          if (data == 'null')
          {
            warpol('.ntas-tmp').fadeIn("slow");
            return;
          }

          //Result JsonParser tu use data
          var resultData = jQuery.parseJSON(data);
          warpol('#' + idDivResult + '-result').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol('.resultadosComplex').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol.each(resultData,function(i, item)
          {
            //Rewrite results
            if (urlTmp[0] == 'customers')
              var nombre = '<label>' + item.Firstname + ' ' + item.Lastname + ' </label><label> <b> CODE: </b> ' + item.ShortName + ' </label><label> <b> UNIT: </b> ' + item.Unit + ' </label><label> <b> Address: </b> </label><label>' + item.Address  + '</label>';
            else
              var nombre = item.Name;

              if (path == '/supportdash')
              {
                warpol('.resultadosComplex').append('<p id="name-CID-' + item.CID + '" onclick="refeshDisabledInput(' + item.CID + ');">' + item.Firstname+ ' ' + item.Lastname + '</p>');
              }
              else
              {
                warpol('#' + idDivResult + '-result').append('<p><a href="/'+ urlTmp[0] +'/'+ item.LocID +'"> ' + nombre + '</a></p>');
                warpol('.resultadosComplex').append('<p><a href="/'+ urlTmp[0] +'/'+ item.CID +'"> ' + nombre + '</a></p>');
              }
          });
        }
      }
    );
  };
};
//Preview Images Uploaded
var imgPreview = function()
{
  function readURL(input) {
    if (input.files && input.files[0])
    {
      var reader = new FileReader();
      reader.onload = function (e)
      {
        warpol('.prvw-img-form').attr('src', e.target.result);
      }

      reader.readAsDataURL(input.files[0]);
    }
  }

  warpol(".inp-img-form").change(function()
  {
    readURL(this);
  });
};
var utils = {

  getParameterByName: function(name){

    name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
    var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
      results = regex.exec(location.search);
    return results == null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));

  },

  createTable: function(selector, objects, data, column){

    console.log(data.length);

    try{
      var table = "<table id='rand'>";

      var headFoot = "";

      for(var i = 0; i < column.length; i++){

        headFoot+="<th>" + column[i] + "</th>";

      }

      table+= "<thead><tr>" + headFoot + "</tr></thead>";
      table+= "<tfoot><tr>" + headFoot + "</tr></tfoot>";

      var body = "";

      for(var i = 0; i < data.length; i++){

        body+= "<tr>";

        for(var j = 0; j < objects.length; j++){


          body+= "<td>" + data[i][j][objects[j]] + "</td>";

        }

        body+= "</tr>";
      }

      table += body;
      table += "</table>";
      warpol(selector).append(table);


      warpol("#rand").DataTable();
      return true;

    }catch(err){

      console.log(err);

    }



  }

};
var editFormByType = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var id = warpol(this).attr('id');
    tempTicketID = id;

    if (warpol('#' + id).attr('stand') == '1')
    {
      warpol('.' + id + '-label').css('display','table-cell');
      warpol('.' + id + '-edit').css('display','none');
      warpol('#save-' + id).fadeOut( "slow" );
      warpol('#' + id).html('Edit');
      warpol('#' + id).switchClass('btn-danger', 'btn-info');
      warpol('#' + id).attr('stand', '2');
      if(path == '/supportdash')
      {
        warpol('.resultadosComplex').html('');
        warpol('.dis-input').val('');
      }

    }
    else
    {
      warpol('.' + id + '-label').css('display','none');
      warpol('.' + id + '-edit').fadeIn( "slow" );
      warpol('#save-' + id).fadeIn( "slow" );
      warpol('#' + id).html('Cancel');
      warpol('#' + id).switchClass('btn-success', 'btn-danger');
      warpol('#' + id).attr('stand', '1');
    }
  };
};
function refeshDisabledInput(id)
{
  if(!id)
    return;

  var name = warpol('#name-CID-' + id).html();

  warpol('.' + tempTicketID + '-input').val(name.replace(/&nbsp;/g, ''));
  globalName = warpol('.' + tempTicketID + '-input').val();
  warpol('.' + tempTicketID + '-hidden').val(id);
}
var bgWindowClick = function ()
{
  return function(data, textStatus, jqXHR)
  {
    vistas.bgWindowCheck();
    var idshown = warpol('#bg-black-window').attr('idshown');
    switch (idshown)
    {
      case 'updateservicecontentbox':
        warpol('.type-'+ warpol('#bg-black-window').attr('tipo')).css('display', 'none');
        warpol('#updateServiceId-'+ warpol('#bg-black-window').attr('usid')).css('display', 'none');
        warpol('#updateservicecontentbox').fadeOut('slow');
        warpol('.btn-display-service').fadeOut('slow');
        warpol('#bg-black-window').attr('idshown', '');
        warpol('#bg-black-window').attr('tipo', '');
        warpol('#bg-black-window').attr('usid', '');
        break;
      case 'addservicecontentbox':
        warpol('#addservicecontentbox').fadeOut('slow');
        warpol('#bg-black-window').attr('idshown', '');
        break;

    }


  }
};
var addServiceBtn = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var resultView = vistas.bgWindowCheck();
    if(resultView == 'open')
    {
      warpol('#addservicecontentbox').fadeIn('slow');
      warpol('#bg-black-window').attr('idshown', 'addservicecontentbox');
    }
    else
    {
      warpol('#addservicecontentbox').fadeOut('slow');
      warpol('#bg-black-window').attr('idshown', '');
    }

  }
};
var displayServiceInfo = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var existIdNow = warpol('#addservicecontentbox').attr('currentId');
    if(existIdNow)
    {
      warpol('#addServiceId-' + existIdNow).css('display', 'none');
//       warpol('.addServiceBoton').css('display', 'none');
    }

    var idToSHow  = warpol(this).attr('value');
    if (idToSHow != '#')
    {
      warpol('#addServiceId-' + idToSHow).fadeIn('slow');
//       warpol('.addServiceBoton').fadeIn('slow');
      warpol('#addservicecontentbox').attr('currentId', idToSHow);
    }
  };
};
var modifServiceBtn = function ()
{
  return function(data, textStatus, jqXHR)
  {
    displayServiceInfo();
    var resultView = vistas.bgWindowCheck();
    var idToShow = warpol(this).attr('tipoid');
    var kind = warpol(this).attr('kind');
    var tipo = warpol(this).attr('tipo');
    var serviceid = warpol(this).attr('serviceid');

    if(resultView == 'open')
    {
      warpol('#updateservicecontentbox').fadeIn('slow');
      warpol('.btn-display-service-' + serviceid).fadeIn('slow');
      warpol('#bg-black-window').attr('idshown', 'updateservicecontentbox');
      warpol('#bg-black-window').attr('tipo', tipo);
      warpol('#bg-black-window').attr('usid', idToShow);

      if (kind == 'update')
      {
        warpol('.type-'+ tipo).css('display', 'block');
        warpol('#updateServiceId-'+ idToShow).css('display', 'block');
      }
      else
      {

      }
    }
    else
    {
      warpol('#updateservicecontentbox').fadeOut('slow');
      warpol('.btn-display-service-' + serviceid).fadeOut('slow');
      warpol('#bg-black-window').attr('idshown', '');
      warpol('.type-'+ tipo).css('display', 'none');
      warpol('#updateServiceId-'+ idToShow).css('display', 'none');
    }

  };
};
var confirmDialog = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var service = warpol(this).attr('type');
    var portID = warpol(this).attr('portid');
    var serviceID = warpol(this).attr('serviceid');
    var serviceStatus = warpol(this).attr('displaystatus');
    var routeID = warpol(this).attr('route');

    warpol('<div class="confirmBtn"></div>').appendTo('body')
      .html('<div ><h6>Confirm this Action!</h6></div>')
      .dialog({
        modal: true, title: 'Please confirm', zIndex: 10000, autoOpen: true,
        width: 'auto', resizable: false,
        buttons: {
          Yes: function () {
            // warpol(obj).removeAttr('onclick');
            // warpol(obj).parents('.Parent').remove();

            warpol(this).dialog("close");
            if (portID)
              networkServices(service, portID);
            else if(serviceID)
              servicesInfoUpdate(serviceID, serviceStatus, routeID);

          },
          No: function () {
            warpol(this).dialog("close");
          }
        },
        close: function (event, ui) {
          warpol(this).remove();
        }
      });
  };
};
//idType = type of id on table[CID,TID,ID...]
//table = attr that has table Target.
//route, attr that knows the target action.
var updateBtn = function ()
{
  return function(data, textStatus, jqXHR)
  {

    var idType = warpol(this).attr('idType');
    var bloque = warpol(this).attr('bloque');
    var id = warpol(this).attr(idType);
    var objects = warpol('#'+ bloque +'-form-' + id).serializeArray();
    var table = warpol('#'+ bloque +'-form-' + id).attr('dbtable');
    var route = warpol('#'+ bloque +'-form-' + id).attr('action');
    var infoData = {};

//     console.log(objects);
//     return;

    for(var obj in objects )
    {
      if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
      {
        alert('Verify ' + objects[obj]['name'] + ' Field');
        return;
      }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }

    infoData['id'] = id;
    infoData['table'] = table;
    infoData['bloque'] = bloque;

    //AJAX request
    warpol.ajax(
      {type:"POST",
        url:"/" + route,
        data:infoData,
        success: function(data)
        {
          switch(infoData['table'])
          {
            case 'supportTickets':
              warpol('#block-' + id).click();
              warpol.each(data[0], function(i, item)
              {
                if(i == '_token' || i == 'id')
                  return true;
                else
                {
                  warpol('#' + i + '-' + id).html(item);
                }
              });
              break;
            case 'supportTicketHistory':
              var tbodyData = warpol('#'+ bloque + '-tbody-' + id).html();
              var htmlContent = "<tr class='even' role='row'>";
              htmlContent += "<td class='sorting_1'>" + data[0]['TimeStamp'] +"</td>";
              htmlContent += "<td class='special-td'>" + data[0]['Comment'] +"</td>";
              htmlContent += "<td>" + data[0]['Status'] +"</td>";
              htmlContent += "<td>" + data[0]['Name'] +"</td>";
              htmlContent += '</tr> ';

              warpol('#'+ bloque + '-tbody-' + id).html(htmlContent + tbodyData);
              warpol('#'+ bloque +'-comment-' + id).val('');

              break;
            case 'customers':
              warpol('#block-' + bloque).click();

              for(var objResp in objects )
              {
                if(objects[objResp]['name'] == '_token')
                  continue;
                  if(bloque == 'b')
                    if(objects[objResp]['name'] == 'CCscode')
                      continue;
                    else if(objects[objResp]['name'] == 'CCnumber')
                      warpol('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value'].substr(12, 4));
                    else
                      warpol('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value']);
                  else
                    warpol('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value']);
              }
              break;
            case 'supportTicketsID':

              warpol('.block-' + id + '-' + bloque).click();
//               var newName = warpol('.block-' + id + '-getName-' + bloque ).val();
//               console.log('.bloque-' + id + '-CID-' + bloque);
               warpol('#bloque-' + id + '-CID-' + bloque).html(globalName);


              break;

//             default:
//             default code block
          }
        }
      }
    );
  };
};
var insertCustomerTicket = function ()
{
  return function(data, textStatus, jqXHR)
  {

    var objects = warpol('#newticketform').serializeArray();
    var infoData = {};


    for(var obj in objects )
    {
      if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
      {
        alert('Verify ' + objects[obj]['name'] + ' Field');
        return;
      }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }

    //AJAX request
    warpol.ajax(
      {type:"POST",
        url:"/insertCustomerData",
        data:infoData,
        success: function(data)
        {

          warpol('#create-customer-ticket').notify('Ticket created.');
          document.getElementById("newticketform").reset();


        }
      }
      );
  };
};
var networkServices = function (service, portID)
{
    var routes = ['networkCheckStatus',
                  'netwokAdvancedInfo',
                  'networkAdvanceIPs',
                  'networkRecyclePort',
                  '4',
                  'networkSignUp',
                  'networkActivate'];

    warpol('.network-functions').addClass('disabled');

    var service = service;
    var portID = portID;

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/" + routes[service],
        data:{'portid':portID},
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          warpol.each(data,function(i, item)
          {
            warpol('#' + i).html(item);
          });
          warpol('#basic-info-net').notify('OK');

          service = 1;
          warpol.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:{'portid':portID},
              success: function(data)
              {
                warpol.each(data,function(i, item)
                {
                  warpol('#' + i).html(item);
                });
              }
            }
          );

          service = 2;
          warpol.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:{'portid':portID},
              success: function(data)
              {

                warpol('#IPs').notify('IPs Array.');
                warpol('.network-functions').removeClass('disabled');

//                   warpol.each(data,function(i, item)
//                   {
//                     warpol('#' + i).html(item);
//                   });

              }
            }
          );

        }
      }
      );

      if (service == 5)
      {
        warpol('.access-type-net').removeClass('btn-danger ');
        warpol('.access-type-net').addClass('btn-info');
        warpol('.access-type-net').html('Activate');
        warpol('.access-type-net').attr('type','6');
        warpol('#acces-network-id').html('signup');
      }
      else if ( service == 6 )
      {
        warpol('.access-type-net').removeClass('btn-info')
        warpol('.access-type-net').addClass('btn-danger')
        warpol('.access-type-net').html('Send to Signup');
        warpol('.access-type-net').attr('type','5');
        warpol('#acces-network-id').html('yes');
      }

};
var changeSeccionView = function ()
{
  return function(data, textStatus, jqXHR)
  {
    if (customerSeccion)
      warpol(customerSeccion).css('display', 'none');

    var window = warpol(this).attr('window');
    customerSeccion = '.' + window;
    warpol(customerSeccion).fadeIn("slow");
  };
};
var servicesInfoUpdate = function (serviceID, serviceStatus, routeID)
{

  var routes = ['updateCustomerServiceInfo'];

//   warpol('.network-functions').addClass('disabled');

  //AJAX request
  warpol.ajax(
    {type:"GET",
      url:"/" + routes[routeID],
      data:{'serviceid':serviceID, 'status':serviceStatus},
      success: function(data)
      {
        if (data == 'ERROR')
          alert(data);

        if (serviceStatus == 'active')
        {
          warpol('#serviceno-' + serviceID).addClass('disabled ital');
          warpol('#serviceinfo-status-' + serviceID).html('disabled');
          warpol('#xservice-btn-id-' + serviceID).attr('displaystatus','disabled');
          warpol('#xservice-btn-id-' + serviceID).addClass('btn-success fa-check');
          warpol('#xservice-btn-id-' + serviceID).removeClass('btn-dark');
          warpol('#xservice-btn-id-' + serviceID).removeClass('fa-times');
        }
        else
        {
          warpol('#serviceno-' + serviceID).removeClass('disabled ital');
          warpol('#serviceinfo-status-' + serviceID).html('active');
          warpol('#xservice-btn-id-' + serviceID).attr('displaystatus','active');
          warpol('#xservice-btn-id-' + serviceID).addClass('btn-dark fa-times');
          warpol('#xservice-btn-id-' + serviceID).removeClass('btn-success');
          warpol('#xservice-btn-id-' + serviceID).removeClass('fa-check');
        }

      }
    }
  );
};
function updateActiveServiceInfo (CSID, ProdIDc)
{

  var ProdID = warpol('#select-csiu').val();

  console.log(CSID);
  console.log(ProdID);
  console.log(ProdIDc);

  //AJAX request
  warpol.ajax(
    {type:"GET",
      url:"/updateCustomerActiveServiceInfo",
      data:{'CSID':CSID, 'ProdID':ProdID},
      success: function(data)
      {
        if (data == 'ERROR')
          alert(data);

        warpol.each(data[0],function(i, item)
        {
          console.log(i + '-'+ProdID+'::' + item);
          warpol('#' + i + '-' + ProdIDc).html(item);
        });

        warpol('.btn-display-service-'+CSID).notify('Service Updated');


      }
    }
  );
}



/* MENU */
app.controller('menuController', ['$scope', '$http', function($scope, $http){
  $scope.SiteMenu = [];
  $http.get('menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
}]);

app.controller('Ctrl', function($scope) {
  $scope.xedit = {
    name: 'awesome user',
    test: 'test coso'
  };
});


app.controller('adminusers', function($scope, $http) {
  $http.get("adminusers")
    .then(function (response) {
      $scope.users = response.data;
    });
});





app.controller('admin', function($scope, $http, $compile, $sce)
{

  $http.get("admin")
    .then(function (response) {
//       console.log(response.data);
      $scope.userData = response.data;
    });

  $scope.callAdminView = function (view)
  {
    var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
    warpol("#viewContents").html(compiledeHTML);
    $scope.insertForm = '';
  };

  $scope.addNewForm = function (table)
  {
//     console.log(table);
    $http.get("insertAdminForm", {params:{'table':table}})
      .then(function (response) {

        var compiledeFormHTML = $compile(response.data)($scope);
//       console.log(compiledeFormHTML[0]);
        $scope.insertForm = $sce.trustAsHtml(response.data);

      });
  };

  $scope.cancelForm = function ()
  {
    $scope.insertForm = null;
  };

  $scope.submitForm = function ($scope)
  {
    console.log('TADA');
    var objects = warpol('#admin-insert-form').serializeArray();
    console.log(objects);
//     validator.startValidations;

  }


})
.directive('myViewUser', function() {
  return {
    templateUrl: '/views/admin/user.html'
  };
})
.directive('myViewProfile', function() {
  return {
    templateUrl: '/views/admin/profile.html',
    controller: 'adminProfileController'
  };
})
.directive('myViewApp', function() {
  return {
    templateUrl: '/views/admin/app.html'
  };
})
.directive('myViewStatus', function() {
  return {
    templateUrl: '/views/admin/status.html'
  };
})
.directive('myViewElement', function() {
  return {
    templateUrl: '/views/admin/element.html'
  };
});
app.controller('adminProfileController', function ($scope, $http){
  $http.get("adminProfile")
    .then(function (response) {
//       console.log(response.data);
      $scope.profilesData = response.data;
    });
})
app.controller('buildingCtl', ['$scope','$route','$http', function($scope, $route, $http)
{

  $scope.displayBldData = function (idBld)
  {
//     console.log(idBld);

    $http.get("buildings/" + idBld)
      .then(function (response) {
//         console.log(response.data);
        $scope.bld = response.data;
      });

  }




  $scope.displayBldForm = function ()
  {
    if ($scope.show == false)
    {
      $scope.show = true;
      warpol('#bld-content-form').fadeIn('slow');
      warpol('#add-bld-btn').fadeOut('fast');
      warpol('#cancel-bld-btn').fadeIn('fast');
    }
    else
    {
      $scope.show = false;
      warpol('#bld-content-form').fadeOut('slow');
      warpol('#add-bld-btn').fadeIn('fast');
      warpol('#cancel-bld-btn').fadeOut('fast');
    }
  }







  $scope.offsetLimitFunction = function (offset, limit)
  {
    warpol('#ol-left-btn').attr('offset', offset);
    warpol('#ol-left-btn').attr('limit', limit);
    warpol('#ol-right-btn').attr('offset', offset);
    warpol('#ol-right-btn').attr('limit', limit);
  }




  $scope.buildingsList = function (position)
  {
    //Math var operations.
    var offset              = parseInt($scope.bldData.offset);
    var limit              = parseInt($scope.bldData.limit);
    var a              = parseInt(offset);
    var b              = parseInt(limit);
    //Back Arrow empty
    if (position == 0 && offset <= 0)
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

    console.log(query);

    $http.get("buildingsList", {params:query})
      .then(function (response) {
//         console.log(response.data);
        $scope.bldData['buildingList'] = response.data;
      });

    $scope.bldData.offset = offset;
    $scope.bldData.limit = limit;
    $scope.offsetLimitFunction(offset, limit);
  }







  $scope.buscador = function(searchType, side)
  {

    var query = {};

    if (side == 'left')
      query = {'querySearch' : this.searchLeft};
    else
      query = {'querySearch' : this.searchRight};

    $http.get("buildingsSearch", {params:query})
      .then(function (response) {
//         console.log(response.data);
        if (side == 'left')
          $scope.bldSearchResultLeft = response.data;
        else
          $scope.bldSearchResultRight = response.data;
      });

return;


//END SEARCH TOOL


    //id de quien solicita
    idDivResult = idDivResult?idDivResult:warpol(this).attr('id');



    if (!idDivResult)
      return;

    //Clean search fields
    warpol('#' + idDivResult + '-result').html('');
    warpol('.resultadosComplex').html('');

    //checamos si es simple o complex
    if(document.getElementById('complexSearch'))
      searchType = 'Complex';

    if (searchType == 'Simple')
    {
      warpol('.ntas-tmp').css('display', 'none');
      query = {"querySearch" : warpol(this).val()};
    }
    else // if searchType == 'COMPLEX'
    {
      index = warpol(this).attr('index');
      complexType[0] = 'complex';
      complexType[index] = warpol(this).val();
      query = {"querySearch" : complexType };
    }

    path = window.location.pathname;

    //Split ID for Dynamic UrlRequest
    urlTmp = idDivResult.split('id-');
    urlTmp = urlTmp[1].split('-search');

    //AJAX request
    warpol.ajax(
      {type:"GET",
        url:"/"+ urlTmp[0] + "Search",
        data:query,
        success: function(data)
        {
          //Validate info existing or die
          if (data == 'null')
          {
            warpol('.ntas-tmp').fadeIn("slow");
            return;
          }

          //Result JsonParser tu use data
          var resultData = jQuery.parseJSON(data);
          warpol('#' + idDivResult + '-result').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol('.resultadosComplex').append('<p>Results...( '+ resultData.length +' )</p>');
          warpol.each(resultData,function(i, item)
          {
            //Rewrite results
            if (urlTmp[0] == 'customers')
              var nombre = '<label>' + item.Firstname + ' ' + item.Lastname + ' </label><label> <b> CODE: </b> ' + item.ShortName + ' </label><label> <b> UNIT: </b> ' + item.Unit + ' </label><label> <b> Address: </b> </label><label>' + item.Address  + '</label>';
            else
              var nombre = item.Name;

            if (path == '/supportdash')
            {
              warpol('.resultadosComplex').append('<p id="name-CID-' + item.CID + '" onclick="refeshDisabledInput(' + item.CID + ');">' + item.Firstname+ ' ' + item.Lastname + '</p>');
            }
            else
            {
              warpol('#' + idDivResult + '-result').append('<p><a href="/'+ urlTmp[0] +'/'+ item.LocID +'"> ' + nombre + '</a></p>');
              warpol('.resultadosComplex').append('<p><a href="/'+ urlTmp[0] +'/'+ item.CID +'"> ' + nombre + '</a></p>');
            }
          });
        }
      }
    );








  }



  $scope.SiteMenu = [];
  $http.get('buildings').then(function (data){
    $scope.bldData = data.data;
    $scope.offsetLimitFunction($scope.bldData.offset, $scope.bldData.limit);
//     $scope.parentmethod('gral');
  }), function (error){
    alert('Error');
  }



}]);
app.controller('newbuildingform', ['$scope', '$http', function($scope, $http)
{
  $http.get("newbuildingform")
    .then(function (response) {
      $scope.newbldform = response.data;
      warpol('#bld-form-html').html($scope.newbldform);
    });
}]);


function test ($scope)
{
  $scope.$emit.valueloco = 'a';
}




app.service('validator', function ($scope)
{
  $scope.startValidations = function()
  {
    console.log('Tada');
  }
//
//   $scope.startValidations = function()
//   {
//     warpol('.validation-form').submit(function(e)
//     {
//       e.preventDefault();
//       var validated = true;
//       warpol(this).find('input').each(function()
//       {
//         try{
//           if(!validator.validateInput(warpol(this).attr('class'),warpol(this).val(),warpol(this)))
//           {
//             validated = false;
//           }
//         }
//         catch(err)
//         {
//           console.log(err);
//         }
//       });
//
//       if(validated)
//         warpol(this).unbind().submit();
//       else
//         warpol("<div class='message-validation'>Valida todos los datos requeridos</div>").dialog({"modal" : true});
//     });
//   };
//
//   $scope.validateInput = function(type, val, selector, validator)
//   {
//     type = type.split(" ");
//     var valid = true;
//     for(var i = 0; i < type.length; i++)
//     {
//       var tmp = type[i];
//       tmp = tmp.split('validation-');
//       if(tmp.length > 1)
//       {
//         if(!validator.validator(tmp[1],val, selector)){
//           valid = false;
//         }
//       }
//     }
//     return valid;
//   };
//
//   $scope.validator = function(type, val, selector, validator)
//   {
//     var validated = true;
//     switch(type)
//     {
//       case "tel":
//         var patt = new RegExp("^[1-9]{10}$");
//         var telefono = val;
//         if (!patt.test(telefono))
//         {
//           validated = false;
//         }
//         break;
//       default:
//         if(typeof val === "undefined" || val == "" )
//         {
//           validated = false;
//         }
//         break;
//     }
//     if(!validated)
//     {
//       validator.changeColor(selector,"error");
//     }
//     else
//     {
//       validator.changeColor(selector,"regular");
//     }
//     return validated;
//   };
//
//   $scope.changeColor = function(selector, type)
//   {
//     switch(type)
//     {
//       case "error":
//         warpol(selector).css({"color":"#cc0000"});
//         warpol(selector).parent().parent().find('.descripcion').css({"color":"#cc0000"});
//         break;
//       case "regular":
//         warpol(selector).css({"color":"inherit"});
//         warpol(selector).parent().parent().find('.descripcion').css({"color":"inherit"});
//         break;
//     }
//   }


});







