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
var createFancyBox = function ()//INVALID
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
var buildingsList = function (position)//INVALID
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
var buscador = function(searchType)//INVALID
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
var confirmDialog = function ()//INVALID
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
var networkServices = function (service, portID)//INVALID
{
console.log(portID);
return;
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
var servicesInfoUpdate = function (serviceID, serviceStatus, routeID)//INVALID
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
  $http.get('/menumaker').then(function (data){
    $scope.SiteMenu = data.data;
  }), function (error){
    alert('Error');
  }
}]);
app.controller('userController',      function($scope, $http){
  $http.get("/getUserData")
    .then(function (response) {
      $scope.usr = response.data;
    });
});
// app.controller('Ctrl', function($scope) {
//   $scope.xedit = {
//     name: 'awesome user',
//     test: 'test coso'
//   };
// });
app.controller('adminusers',          function($scope, $http) {
  $http.get("adminusers")
    .then(function (response) {
      $scope.users = response.data;
    });
});
app.controller('admin',               function($scope, $http, $compile, $sce, notify){
  $http.get("/admin")
    .then(function (response) {
      $scope.userData = response.data;
    });

  $scope.callAdminView = function (view) {
    callAdminView(view);
  };
  $scope.addNewForm = function (table)
  {
    $http.get("getAdminForm", {params:{'table':table}})
      .then(function (response) {

        var compiledeFormHTML = $compile(response.data)($scope);
//       console.log(compiledeFormHTML[0]);
        $scope.insertForm = $sce.trustAsHtml(response.data);

      });
  };
  $scope.cancelForm = function ()
  {
    cancelForm();
  };
  $scope.submitForm = function ($scope)
  {

    var objects = warpol('#admin-insert-form').serializeArray();
    var infoData = {};

    for(var obj in objects )
    {
//       if(objects[obj]['value'] == 'err' || objects[obj]['value'] == '')
//       {
//         alert('Verify ' + objects[obj]['name'] + ' Field');
//         return;
//       }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }
//     validator.startValidations;

    $http.get("insertAdminForm", {params:infoData})
      .then(function (response) {
        cancelForm();
      });

    callAdminView(infoData['table']);
    notify({ message: 'Data inserted!', templateUrl:'/views/notify.html'} );

  }
  function cancelForm()
  {
    $scope.insertForm = null;
  };
  function callAdminView (view)
  {
    var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
    warpol("#viewContents").html(compiledeHTML);
    $scope.insertForm = '';
  }
})
.directive('myViewUsers',             function() {
  return {
    templateUrl: '/views/admin/user.html',
    controller:'admin'
  };
})
.directive('myViewProfiles',          function() {
  return {
    templateUrl: '/views/admin/profile.html',
    controller: 'adminViewProfiles'
  };
})
.directive('myViewApps',              function() {
  return {
    templateUrl: '/views/admin/app.html',
    controller: 'adminViewApps'
  };
})
.directive('myViewStatus',            function() {
  return {
    templateUrl: '/views/admin/status.html',
    controller:'adminViewStatus'
  };
})
.directive('myViewElements',          function() {
  return {
    templateUrl: '/views/admin/element.html',
    controller:'adminViewElements'
  };
})
.directive('myViewCustomers',         function() {
  return {
    templateUrl: '/views/admin/customer.html',
    controller: 'adminViewCustomers'
  };
})
.directive('myViewTypes',             function() {
  return {
    templateUrl: '/views/type.html',
    controller: 'adminViewTypes'
  };
})
.directive('myViewAddress',           function() {
  return {
    templateUrl: '/views/address.html',
    controller: 'adminViewAddress'
  };
})
.directive('myViewContacts',          function() {
  return {
    templateUrl: '/views/contact.html',
    controller: 'adminViewContacts'
  };
})
.directive('myViewPayments',          function() {
  return {
    templateUrl: '/views/admin/payment.html',
    controller: 'adminViewPayments'
  };
})
.directive('myViewNotes',             function() {
  return {
    templateUrl: '/views/notes.html',
    controller: 'adminViewNotes'
  };
})
.directive('myViewAccessApps',        function() {
  return {
    templateUrl: '/views/admin/access_app.html',
    controller: 'adminViewAccessApps'
  };
})
.directive('myViewAccessAppElements', function() {
  return {
    templateUrl: '/views/admin/access_app_element.html',
    controller: 'adminViewAccessAppElements'
  };
});

app.controller('adminViewStatus',             function($scope, $http){
  $http.get("adminStatus")
    .then(function (response) {
      $scope.adminStatus = response.data;
    });
});
app.controller('adminViewElements',           function($scope, $http){
  $http.get("adminElements")
    .then(function (response) {
      $scope.adminElements = response.data;
    });
});
app.controller('adminViewApps',               function($scope, $http){
  $http.get("adminApps")
    .then(function (response) {
      $scope.adminApps = response.data;
    });
});
app.controller('adminViewProfiles',           function($scope, $http){
  $http.get("adminProfiles")
    .then(function (response) {
      $scope.adminProfiles = response.data;
    });
});
app.controller('adminViewTypes',              function($scope, $http){
  $http.get("adminTypes")
    .then(function (response) {
      $scope.adminTypes = response.data;
    });
});
app.controller('adminViewCustomers',          function($scope, $http){
  $http.get("adminCustomers")
    .then(function (response) {
      $scope.adminCustomers = response.data;
    });
});
app.controller('adminViewAddress',            function($scope, $http){
  $http.get("adminAddress")
    .then(function (response) {
      $scope.adminAddress = response.data;
    });
});
app.controller('adminViewContacts',           function($scope, $http){
  $http.get("adminContacts")
    .then(function (response) {
      $scope.adminContacts = response.data;
    });
});
app.controller('adminViewPayments',           function($scope, $http){
  $http.get("adminPayments")
    .then(function (response) {
      $scope.adminPayments = response.data;
    });
});
app.controller('adminViewNotes',              function($scope, $http){
  $http.get("adminNotes")
    .then(function (response) {
      $scope.adminNotes = response.data;
    });
});
app.controller('adminViewAccessApps',         function($scope, $http){
  $http.get("adminAccessApps")
    .then(function (response) {
      $scope.adminAccessApps = response.data;
    });
});
app.controller('adminViewAccessAppElements',  function($scope, $http){
  $http.get("adminAccessAppElements")
    .then(function (response) {
      $scope.adminAccessAppElements = response.data;
    });
});

app.controller('buildingCtl', ['$scope','$route','$http', function($scope, $route, $http)
{

  $scope.SiteMenu = [];
  $http.get('buildings').then(function (data){
    $scope.bldData = data.data;
    $scope.offsetLimitFunction($scope.bldData.offset, $scope.bldData.limit);
  }), function (error){
    alert('Error');
  }


  $scope.displayBldData = function (idBld) {
//     console.log(idBld);

    $http.get("buildings/" + idBld)
      .then(function (response) {
        console.log(response.data);
        $scope.bld = response.data;
      });

  }
  $scope.displayBldForm = function () {
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
  $scope.offsetLimitFunction = function (offset, limit) {
    warpol('#ol-left-btn').attr('offset', offset);
    warpol('#ol-left-btn').attr('limit', limit);
    warpol('#ol-right-btn').attr('offset', offset);
    warpol('#ol-right-btn').attr('limit', limit);
  }
  $scope.buildingsList = function (position) {
    //Math var operations.
    var offset              = parseInt($scope.bldData.offset);
    var limit              = parseInt($scope.bldData.limit);
    var a              = parseInt(offset);
    var b              = parseInt(limit);
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

    $scope.offsetLimitFunction(offset, limit);
  }
  $scope.buscador = function(searchType, side) {
    console.log('entramos');
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
  $scope.editFormByType = function (id) {

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

  }
  $scope.submitForm = function () {
    console.log('entramos');
    var objects = warpol('#building-update-form').serializeArray();
    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    $http.get("updateBuilding", {params:infoData})
      .then(function (response) {
        $scope.bld = response.data;
      });

    $scope.editFormByType('block-a');

  }


}])
.directive('myBldView', function() {
  return {
    templateUrl: '/views/building/building.html',
//     controller:'admin'
  };
});
app.controller('newbuildingform', ['$scope', '$http', function($scope, $http)
{
  $http.get("newbuildingform")
    .then(function (response) {
      $scope.newbldform = response.data;
      warpol('#bld-form-html').html($scope.newbldform);
    });
}]);

// app.service('validator', function ($scope){
//   $scope.startValidations = function()
//   {
//     console.log('Tada');
//   }
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
//
//
// });

app.controller('getCustomerDataTicket',    function ($scope, $http){
  $http.get("getCustomerDataTicket", {params:{'id':$scope.results.id_customers}})
    .then(function (response) {
      $scope.ticketCustomerData = response.data;
    });
});
app.controller('supportControllerTools',   function ($scope, $http) {
  $scope.buscador = function(side) {
    var query = {};
    if (side == 'center')
      query = {'code': this.searchCenterCode?this.searchCenterCode:false,
        'unit': this.searchCenterUnit?this.searchCenterUnit:false};

    if (query['code'] == false && query['unit'] == false)
    {
      $scope.customerCodeUnitList = '';
      return;
    }

    $http.get("getTicketCustomerList", {params: query})
      .then(function (response) {
        $scope.customerCodeUnitList = response.data;
      });
  }
  $scope.selectCustomerUpdate = function (name, id) {
    warpol('.preview-name').val(name);
    warpol('#save-block-b').attr('idCustomerUpdate', id);
  };
  $scope.updateCustomerTicketName = function () {
    var customerID =  warpol('#save-block-b').attr('idCustomerUpdate')
    var ticketID = $scope.selectedTicket.id;

    $http.get("updateTicketCustomerName", {params:{'id':ticketID, 'id_customers':customerID}})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });

    $scope.customerCodeUnitList = '';
    if ($scope.globalViewON != 'Resume')
      $scope.cancel();
    else
      $scope.displayTicketResume(ticketID);

  }
  $scope.editFormByType = function (id) {

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

    if (id == 'block-a')
    {
      $scope.getReasons();
      $scope.getUsers();
    }

  }
});



app.controller('supportController', function ($scope, $http, notify, $compile, $sce){
//   notify({ message: 'Support Controller Active', templateUrl:'/views/notify.html'} );
  $http.get("supportTickets")
    .then(function (response) {
      $scope.supportData = response.data;
    });

  function callMidView (view) {
    $scope.globalViewON = view;
      var compiledeHTML = $compile("<div my-View-"+view+"></div>")($scope);
      warpol("#mid-content-tickets").html(compiledeHTML);
    };
  $scope.fullTickets = function (){
    $http.get("supportTickets")
      .then(function (response) {
        $scope.supportData = response.data;
      });
      $scope.viewTicketsDirective = 'Full';
    callMidView('Full');
  };
  $scope.billingTickets = function (){
    $http.get("supportTicketsBilling")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'Billing';
    callMidView('Billing');
  };
  $scope.allTickets = function (){
    $http.get("supportTicketsAll")
      .then(function (response) {
        $scope.supportData = response.data;
      });
    $scope.viewTicketsDirective = 'All';
    callMidView('All');
  };
  $scope.displayTicketResume = function (id, idCustomer){

    console.log(id);
    console.log(idCustomer);

    $scope.midTicketId = id;
    $scope.stcid = idCustomer;
    $scope.stcFlag = true;
    callMidView('Resume');
  };
})
.directive('myViewFull',            function() {
  return {
    templateUrl: '/views/supportFullList.html'
  };
})
.directive('myViewAll',             function() {
  return {
    templateUrl: '/views/supportAllList.html'
  };
})
.directive('myViewResume',             function() {
  return {
    templateUrl: '/views/supportTicketResume.html',
    controller: 'singleTicketInfo'
  };
})
.directive('myViewCustomer',             function() {
  return {
    templateUrl: '/views/customer.html'
  };
})
.directive('myViewBilling',         function() {
  return {
    templateUrl: '/views/supportBillingList.html'
  };
});


app.controller('singleTicketInfo', function ($scope, $http){

  $http.get("getTicketInfo", {params:{'ticketId':$scope.midTicketId}})
    .then(function (response) {
      $scope.selectedTicket = response.data;
    });
})



app.controller('customerControllerList', function ($scope, $http){
  $http.get("getCustomerList")
    .then(function (response) {
      $scope.supportDataCustomer = response.data;
    });
});
app.controller('customerController',    function ($scope, $http, $routeParams, notify){

  var idCustomer = $routeParams.id;

  if ($scope.stcid)
    idCustomer = $scope.stcid;

  $http.get("customersData", {params:{'id':idCustomer}})
    .then(function (response) {
      console.log(response.data);
      $scope.customerData = response.data;
    });

  $scope.checkboxModel = true;


  $scope.submitForm = function (table) {

    var objects = warpol('#'+table+'-insert-form').serializeArray();
    var infoData = {};


    for(var obj in objects )
    {
      if(objects[obj]['value'] == 'err' /*|| objects[obj]['value'] == ''*/)
      {
        alert('Verify ' + objects[obj]['name'] + ' Field');
        return;
      }

      infoData[objects[obj]['name']] = objects[obj]['value'];
    }
    infoData['id_customers'] = $scope.customerData.id;

//     validator.startValidations;

    $http.get("insertCustomerTicket", {params:infoData})
      .then(function (response) {
      console.log(response);
//         cancelForm();
        if(response.data == 'OK')
        {
          document.getElementById(table+"-insert-form").reset();
          notify({ message: 'New Ticket Created!', templateUrl:'/views/notify.html'} );

        }
      });

//     callAdminView(infoData['table']);
//     notify({ message: 'Data inserted!', templateUrl:'/views/notify.html'} );

  }

  $scope.validate = function(value, table, field) {
    var data = {};
    data[field] = value;
    data['id_customers'] = $scope.customerData.id;

    $http.get("update"+table+"Table", {params:data})
      .then(function (response) {
        console.log('OK');
      });
  }


  $scope.customerEditMode = function (){
    if ( $scope.checkboxModel == false)
    {
       warpol('.editable-text').fadeIn('slow');
       warpol('.no-editable-text').css('display', 'none');
    }
    else
    {
      warpol('.no-editable-text').fadeIn('slow');
      warpol('.editable-text').css('display', 'none');
    }
  };
  $scope.customerEditMode();

});
app.controller('supportTicketHistory',  function ($scope, $http){
  $http.get("supportTicketHistory", {params:{'id':$scope.history.id}})
    .then(function (response) {
      $scope.historyData = response.data;
    });
});
app.controller('ModalController',       function ($scope, $uibModal, $log) {

  $scope.animationsEnabled = false;

  $scope.open = function (ticketId)
  {

    $scope.ticketId = ticketId;

    var modalInstance = $uibModal.open(
    {
      animation: $scope.animationsEnabled,
      templateUrl: 'myModalContent.html',
      controller: 'ModalInstanceCtrl',
      size: 'lg',
      resolve: {
        ticketId: function () {
          return $scope.ticketId;
        }
      }
    });

    modalInstance.result.then(function () {}, function ()
    {
      $log.info('Modal dismissed at: ' + new Date());
    });
  };

  $scope.toggleAnimation = function () {
    $scope.animationsEnabled = !$scope.animationsEnabled;
  };

});
app.controller('ModalInstanceCtrl',     function ($scope, $http, $uibModalInstance, ticketId){
  console.log(ticketId);

  $http.get("getTicketInfo", {params:{'ticketId':ticketId}})
    .then(function (response) {
      $scope.selectedTicket = response.data;
    });

  $scope.ok = function () {
    $uibModalInstance.close($scope.selected.item);
  };

  $scope.cancel = function () {
    $uibModalInstance.dismiss('cancel');
  };
});




app.controller('customerPaymentController',         function ($scope, $http){
  $http.get("getCustomerPayment", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.paymentData = response.data;
    });
});
app.controller('customerTicketHistoryController',   function ($scope, $http){
  $http.get("getTicketHistory", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.ticketHistory = response.data;
    });
});
app.controller('customerTicketHistoryData',         function ($scope, $http){
  $http.get("getTicketHistoryNotes", {params:{'id':$scope.ticket.id_ticket_notes}})
    .then(function (response) {
      $scope.ticketNotes = response.data;
      $scope.letterLimit = 20;
    });
  $http.get("getTicketHistoryReason", {params:{'id':$scope.ticket.id_reasons}})
    .then(function (response) {
      $scope.ticketReason = response.data;
    });

  $scope.showFullComment = function(id)
  {
    warpol('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment = function(id)
    {
      warpol('#ticket-' + id).fadeOut('fast');
    }
});
app.controller('customerBillingHistoryController',  function ($scope, $http){
  $http.get("getBillingHistory", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.billingHistory = response.data;
    });
});
app.controller('customerServicesController',        function ($scope, $http){
  $http.get("getCustomerServices", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.customerServices = response.data;
    });
});
app.controller('serviceProductController',          function ($scope, $http){
  $http.get("getCustomerProduct", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProduct = response.data;
    });
  $http.get("getCustomerProductType", {params:{'id':$scope.service.id}})
    .then(function (response) {
      $scope.customerProductStatus = response.data;
    });
});
app.controller('customerNetworkController',         function ($scope, $http, $mdDialog, $mdMedia){
  $scope.status = '  ';
  $scope.customFullscreen = $mdMedia('xs') || $mdMedia('sm');

  $http.get("getCustomerNetwork", {params:{'id':$scope.customerData.id}})
    .then(function (response) {
      $scope.customerNetwork = response.data;
    });

  $scope.networkServices = function (service)
  {
    networkServices(service);
  }
  function networkServices (service)
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
    var portID = $scope.customerNetwork.portId;

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

  //PENDING
  function servicesInfoUpdate (serviceID, serviceStatus, routeID)
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


  $scope.showConfirm = function(ev)
  {
    var service = warpol('#rport').attr('type');
    var portID = warpol('#rport').attr('portid');
    var serviceID = warpol('#rport').attr('serviceid');
    var serviceStatus = warpol('#rport').attr('displaystatus');
    var routeID = warpol('#rport').attr('route');

    var confirm = $mdDialog.confirm()
      .title('Please Confirm Your Action!')
      .textContent('Once you click Yes, you need to wait the process to finish.')
      .ariaLabel('Lucky day')
      .targetEvent(ev)
      .clickOutsideToClose(true)
      .ok('YES')
      .cancel('NO');

      // YES/NO
    $mdDialog.show(confirm).then(function()
    {
      $scope.status = 'You decided to get rid of your debt.';

      if (portID)
        networkServices(service);
      else if(serviceID)
        servicesInfoUpdate(serviceID, serviceStatus, routeID);

    }, function() {
      $scope.status = 'You decided to keep your debt.';
    });
  };

});
app.controller('customerNewTicketCtrl',             function ($scope, $http){
  $http.get("getTableData", {params:{'table':'reasons'}})
    .then(function (response) {
      $scope.newTicketData = response.data;
    });
});
app.controller('customerBuildingController',        function ($scope, $http){
  if($scope.customerData)
  {
    $http.get("buildings/" + $scope.customerData.address.id_buildings)
      .then(function (response) {
        console.log(response.data);
        $scope.bld = response.data;
      });
  }
});
app.controller('submitController',                  function ($scope, $http) {
  $scope.submitForm = function (idForm)
  {

    var objects = warpol('#' + idForm).serializeArray();
    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    infoData['id'] = $scope.selectedTicket.id;


    $http.get("updateTicketDetails", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
  }
  $scope.submitFormUpdate = function (idForm)
  {
    var objects = warpol('#' + idForm).serializeArray();
    var infoData = {};
    for(var obj in objects )
      infoData[objects[obj]['name']] = objects[obj]['value'];

    infoData['id'] = $scope.selectedTicket.id;
    console.log(infoData);

    $http.get("updateTicketHistory", {params:infoData})
      .then(function (response) {
        $scope.selectedTicket = response.data;
      });
    warpol('.thistory-form-2').val('');
  }
});


app.controller('networkController',                 function ($scope, $http){
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


  };

  $scope.addTR = function addTR(id)
  {
    var stance = warpol('#net-btn-' + id).attr('stance');
    var iconoA = '<i class="fa fa-plus-circle txt-green sign-network"></i>';
    var iconoB = '<i class="fa fa-minus-circle txt-red sign-network"></i>';

    console.log(stance);
    if (stance == '1')
    {
      warpol('#net-btn-' + id).attr('stance', '2');
      warpol('#net-btn-' + id).html(iconoB);
      warpol(getNetworkResult(id)).insertAfter('#det-net-' + id).hide().slideDown('slow');
    }
    else
    {
      warpol('#net-btn-' + id).attr('stance', '1');
      warpol('#net-btn-' + id).html(iconoA);
      warpol('#nt-tmp-data-' + id).remove();
    }
//     getNetworkResult();

  };
  function getNetworkResult(id)
  {
    var idString = 'nt-tmp-data-'+id;
    return ' <tr id="' + idString + '"><td colspan="11">info</td></tr>';
  };
});











//TOOLS
app.controller('mainSearchController', function ($scope, $http){
  console.log('mainSearchController');
  $scope.closeSearch = function ()
  {
    warpol('#globalSearch').fadeOut('fast');
    warpol('#lupa-global').fadeIn('slow');
    warpol('#tache-global').fadeOut('fast');
  };
  $scope.valLength = function ()
  {
    if(!this.globalSearch)
      $scope.closeSearch;
  }

  $scope.search = function ()
  {
    if(!this.globalSearch)
    {
      $scope.closeSearch;
      return;
    }

    warpol('#globalSearch').fadeIn('slow');
    warpol('#lupa-global').fadeOut('fast');
    warpol('#tache-global').fadeIn('slow');

    var string = this.globalSearch;

    $scope.loadingCl = true;
    $scope.loadingCu = true;
    $scope.loadingS = true;
    $scope.loadingB = true;
    getClientsSearch(string);
    getCustomersSearch(string);
    getTicketsSearch(string);
    getBuildingsSearch(string);
  };

  function getClientsSearch(string)
  {
    $http.get("getClientsSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//           $scope.globalClientSearch = false;
//         else
          $scope.globalClientSearch = response.data;
        $scope.loadingCl = false;
        console.log(response.data);
      });
  }
  function getCustomersSearch(string)
  {
    $http.get("getCustomersSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalCustomersSearch = false;
//         else
        $scope.globalCustomersSearch = response.data;
        $scope.loadingCu = false;
        console.log(response.data);
      });
  }
  function getTicketsSearch(string)
  {
    $http.get("getTicketsSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalTicketsSearch = false;
//         else
        $scope.globalTicketsSearch = response.data;
        $scope.loadingS = false;
        console.log(response.data);
      });
  }
  function getBuildingsSearch(string)
  {
    $http.get("getBuildingsSearch", {params:{'string':string}})
      .then(function (response)  {
//         if(response.data.length === 0 )
//         $scope.globalBuildingsSearch = false;
//         else
        $scope.globalBuildingsSearch = response.data;
        $scope.loadingB = false;
        console.log(response.data);
      });
  }

















  
});
app.controller('toolsController', function ($scope, $http) {
  $scope.letterLimit = 400;
  $scope.showFullComment = function(id) {
    warpol('#ticket-' + id).fadeIn('slow');
  }
  $scope.hideFullComment = function(id) {
    warpol('#ticket-' + id).fadeOut('fast');
  }
  $scope.getReasons = function () {
    $http.get("getReasonsData")
      .then(function (response) {
        $scope.dataReasons = response.data;
      });
  }
  $scope.getUsers = function () {
    $http.get("admin")
      .then(function (response) {
        $scope.dataUsersAssigned = response.data;
      });
  }
  $scope.editFormByType = function (id) {

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

    if (id == 'block-a')
    {
      $scope.getReasons();
      $scope.getUsers();
    }

  }

});


//TABS
app.controller('AppCtrl', AppCtrl)
.directive('viewNewTicket',            function() {
  return {
    templateUrl: '/views/newticket.html'
  };
})
.directive('viewTicketHistory',            function() {
  return {
    templateUrl: '/views/ticketshistory.html'
  };
})
.directive('viewBillingHistory',            function() {
  return {
    templateUrl: '/views/billinghistory.html'
  };
})
.directive('viewNetwork',            function() {
  return {
    templateUrl: '/views/network.html'
  };
})
.directive('viewProduct',            function() {
  return {
    templateUrl: '/views/product.html'
  };
})
.directive('viewBuilding',            function() {
  return {
    templateUrl: '/views/building/building.html'
  };
});
function AppCtrl ($scope, $log, $compile) {
  var tabs = [
        { title: 'New Ticket',    content:'New-Ticket'},
        { title: 'Tickets',       content:"Ticket-History"},
        { title: 'Billing',       content:"Billing-History"},
        { title: 'Network',       content:"Network"},
        { title: 'Services Info', content:"Product"},
        { title: 'Building',      content:'Building'},
    ],
    selected = null,
    previous = null;
  $scope.tabs = tabs;
  $scope.selectedIndex = 0;
  $scope.$watch('selectedIndex', function(current, old)
  {
    previous = selected;
    selected = tabs[current];
    if ( old + 1 && (old != current)) $log.debug('Goodbye ' + previous.title + '!');
    if ( current + 1 )                $log.debug('Hello ' + selected.title + '!');
  });
  $scope.addTab = function (title, view)
  {
    view = view || title + " Content View";
    tabs.push({ title: title, content: view, disabled: false});
  };
  $scope.removeTab = function (tab)
  {
    var index = tabs.indexOf(tab);
    tabs.splice(index, 1);
  };
  $scope.changeView = function (view)
  {
    var compiledeHTML = $compile("<div view-"+view+"></div>")($scope);
    warpol("#tabsChange-"+view).html(compiledeHTML);
  }
}
//END TABS


//TableSorter's
app.controller('tableSorterTickets', function ($scope, $filter, ngTableParams) {
  $scope.dataSort = $scope.supportData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 100
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});
app.controller('tableSorterAdminUsers', function ($scope, $filter, ngTableParams) {
  $scope.letterLimit = 30;
  $scope.dataSort = $scope.userData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 15
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});
app.controller('tableSorterNetwork', function ($scope, $filter, ngTableParams) {
  $scope.dataSort = $scope.networkData;
  $scope.usersTable = new ngTableParams({
    page: 1,
    count: 20
  }, {
    total: $scope.dataSort.length,
    getData: function ($defer, params) {
      $scope.dataResult = $scope.dataSort.slice((params.page() - 1) * params.count(), params.page() * params.count());
      $defer.resolve($scope.dataResult);
    }
  });

});