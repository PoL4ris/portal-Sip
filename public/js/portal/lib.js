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
    var typeoff = $(this).attr('typeoff');
    if(typeoff == 'open')
    {
      $("#bgblack-" + $(this).attr('ticket-id')).fadeIn("slow");
      $("#fancy-" + $(this).attr('ticket-id')).fadeIn("slow");
    }
    else
    {
      $("#bgblack-" + $(this).attr('ticket-id')).fadeOut("slow");
      $("#fancy-" + $(this).attr('ticket-id')).fadeOut("slow");
    }
  }
};
//get building list OFFSET LIMIT
var buildingsList = function (position)
{
  return function(data, textStatus, jqXHR)
  {
    //Get Data from current Info
    var idDivResult    = $('#bldlist-result').attr('id');
    var offset         = parseInt($('.' + idDivResult + '-limits-' + position).attr('offset'));
    var limit          = parseInt($('.' + idDivResult + '-limits-' + position).attr('limit'));
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
    $.ajax(
      {
        type: "GET",
        url: "buildingsList",
        data: query,
        success: function (data) {
          if (data.length === 0 || !data.trim())
            return;
          //Result JsonParser to use data
          var resultData = jQuery.parseJSON(data);

          $('#' + idDivResult).html('');
          $.each(resultData, function (i, item) {
            $('#' + idDivResult).append('<p>' + item.id + item.name + '</p>');
          });
          //Rewrite LIMIT OFFSET fields for new calcRequest
          $('.' + idDivResult + '-limits-' + 0).attr('offset',offset);
          $('.' + idDivResult + '-limits-' + 0).attr('limit',limit);
          $('.' + idDivResult + '-limits-' + 1).attr('offset',offset);
          $('.' + idDivResult + '-limits-' + 1).attr('limit',limit);
          $('#' + idDivResult).scrollTop(0);
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
    idDivResult = idDivResult?idDivResult:$(this).attr('id');

    if (!idDivResult)
      return;

    //Clean search fields
    $('#' + idDivResult + '-result').html('');
    $('.resultadosComplex').html('');

    //checamos si es simple o complex
    if(document.getElementById('complexSearch'))
      searchType = 'Complex';

    if (searchType == 'Simple')
    {
      $('.ntas-tmp').css('display', 'none');
      query = {"querySearch" : $(this).val()};
    }
    else // if searchType == 'COMPLEX'
    {
      index = $(this).attr('index');
      complexType[0] = 'complex';
      complexType[index] = $(this).val();
      query = {"querySearch" : complexType };
    }

    path = window.location.pathname;

    //Split ID for Dynamic UrlRequest
    urlTmp = idDivResult.split('id-');
    urlTmp = urlTmp[1].split('-search');

    //AJAX request
    $.ajax(
      {type:"GET",
        url:"/"+ urlTmp[0] + "Search",
        data:query,
        success: function(data)
        {
          //Validate info existing or die
          if (data == 'null')
          {
            $('.ntas-tmp').fadeIn("slow");
            return;
          }
          
          //Result JsonParser tu use data
          var resultData = jQuery.parseJSON(data);
          $('#' + idDivResult + '-result').append('<p>Results...( '+ resultData.length +' )</p>');
          $('.resultadosComplex').append('<p>Results...( '+ resultData.length +' )</p>');
          $.each(resultData,function(i, item)
          {
            //Rewrite results
            if (urlTmp[0] == 'customers')
              var nombre = '<label>' + item.Firstname + ' ' + item.Lastname + ' </label><label> <b> CODE: </b> ' + item.ShortName + ' </label><label> <b> UNIT: </b> ' + item.Unit + ' </label><label> <b> Address: </b> </label><label>' + item.Address  + '</label>';
            else
              var nombre = item.Name;

              if (path == '/supportdash')
              {
                $('.resultadosComplex').append('<p id="name-CID-' + item.CID + '" onclick="refeshDisabledInput(' + item.CID + ');">' + item.Firstname+ ' ' + item.Lastname + '</p>');
              }
              else
              {
                $('#' + idDivResult + '-result').append('<p><a href="/'+ urlTmp[0] +'/'+ item.LocID +'"> ' + nombre + '</a></p>');
                $('.resultadosComplex').append('<p><a href="/'+ urlTmp[0] +'/'+ item.CID +'"> ' + nombre + '</a></p>');
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
        $('.prvw-img-form').attr('src', e.target.result);
      }

      reader.readAsDataURL(input.files[0]);
    }
  }

  $(".inp-img-form").change(function()
  {
    readURL(this);
  });
};
var validator = {

  startValidations : function(){

    $('.validation-form').submit(function(e)
    {

      e.preventDefault();

      var validated = true;

      $(this).find('input').each(function()
      {
        try{
          if(!validator.validateInput($(this).attr('class'),$(this).val(),$(this)))
          {
            validated = false;
          }
        }
        catch(err)
        {
          console.log(err);
        }
      });

      if(validated)
      {
        $(this).unbind().submit();
      }
      else
      {
        $("<div class='message-validation'>Valida todos los datos requeridos</div>").dialog({"modal" : true});
      }


    });

  },

  validateInput : function(type, val, selector){

    type = type.split(" ");
    var valid = true;
    for(var i = 0; i < type.length; i++)
    {
      var tmp = type[i];
      tmp = tmp.split('validation-');

      if(tmp.length > 1)
      {

        if(!validator.validator(tmp[1],val, selector)){
          valid = false;

        }
      }
    }

    return valid;

  },

  validator : function(type, val, selector){

    var validated = true;

    switch(type){

      case "tel":
        var patt = new RegExp("^[1-9]{10}$");
        var telefono = val;
        if (!patt.test(telefono))
        {
          validated = false;
        }
        break;

      default:
        if(typeof val === "undefined" || val == "" )
        {
          validated = false;
        }

        break;
    }

    if(!validated)
    {
      validator.changeColor(selector,"error");
    }
    else
    {
      validator.changeColor(selector,"regular");
    }

    return validated;

  },

  changeColor : function(selector, type){


    switch(type){

      case "error":
        $(selector).css({"color":"#cc0000"});
        $(selector).parent().parent().find('.descripcion').css({"color":"#cc0000"});
        break;
      case "regular":
        $(selector).css({"color":"inherit"});
        $(selector).parent().parent().find('.descripcion').css({"color":"inherit"});

        break;

    }


  }

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
      $(selector).append(table);


      $("#rand").DataTable();
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
    var id = $(this).attr('id');
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
  };
};
function refeshDisabledInput(id)
{
  if(!id)
    return;

  var name = $('#name-CID-' + id).html();

  $('.' + tempTicketID + '-input').val(name.replace(/&nbsp;/g, ''));
  globalName = $('.' + tempTicketID + '-input').val();
  $('.' + tempTicketID + '-hidden').val(id);
}
var bgWindowClick = function ()
{
  return function(data, textStatus, jqXHR)
  {
    vistas.bgWindowCheck();
    var idshown = $('#bg-black-window').attr('idshown');
    switch (idshown)
    {
      case 'updateservicecontentbox':
        $('.type-'+ $('#bg-black-window').attr('tipo')).css('display', 'none');
        $('#updateServiceId-'+ $('#bg-black-window').attr('usid')).css('display', 'none');
        $('#updateservicecontentbox').fadeOut('slow');
        $('.btn-display-service').fadeOut('slow');
        $('#bg-black-window').attr('idshown', '');
        $('#bg-black-window').attr('tipo', '');
        $('#bg-black-window').attr('usid', '');
        break;
      case 'addservicecontentbox':
        $('#addservicecontentbox').fadeOut('slow');
        $('#bg-black-window').attr('idshown', '');
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
      $('#addservicecontentbox').fadeIn('slow');
      $('#bg-black-window').attr('idshown', 'addservicecontentbox');
    }
    else
    {
      $('#addservicecontentbox').fadeOut('slow');
      $('#bg-black-window').attr('idshown', '');
    }

  }
};
var displayServiceInfo = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var existIdNow = $('#addservicecontentbox').attr('currentId');
    if(existIdNow)
    {
      $('#addServiceId-' + existIdNow).css('display', 'none');
//       $('.addServiceBoton').css('display', 'none');
    }

    var idToSHow  = $(this).attr('value');
    if (idToSHow != '#')
    {
      $('#addServiceId-' + idToSHow).fadeIn('slow');
//       $('.addServiceBoton').fadeIn('slow');
      $('#addservicecontentbox').attr('currentId', idToSHow);
    }
  };
};
var modifServiceBtn = function ()
{
  return function(data, textStatus, jqXHR)
  {
    displayServiceInfo();
    var resultView = vistas.bgWindowCheck();
    var idToShow = $(this).attr('tipoid');
    var kind = $(this).attr('kind');
    var tipo = $(this).attr('tipo');
    var serviceid = $(this).attr('serviceid');

    if(resultView == 'open')
    {
      $('#updateservicecontentbox').fadeIn('slow');
      $('.btn-display-service-' + serviceid).fadeIn('slow');
      $('#bg-black-window').attr('idshown', 'updateservicecontentbox');
      $('#bg-black-window').attr('tipo', tipo);
      $('#bg-black-window').attr('usid', idToShow);

      if (kind == 'update')
      {
        $('.type-'+ tipo).css('display', 'block');
        $('#updateServiceId-'+ idToShow).css('display', 'block');
      }
      else
      {

      }
    }
    else
    {
      $('#updateservicecontentbox').fadeOut('slow');
      $('.btn-display-service-' + serviceid).fadeOut('slow');
      $('#bg-black-window').attr('idshown', '');
      $('.type-'+ tipo).css('display', 'none');
      $('#updateServiceId-'+ idToShow).css('display', 'none');
    }

  };
};
var confirmDialog = function ()
{
  return function(data, textStatus, jqXHR)
  {
    var service = $(this).attr('type');
    var portID = $(this).attr('portid');
    var serviceID = $(this).attr('serviceid');
    var serviceStatus = $(this).attr('displaystatus');
    var routeID = $(this).attr('route');

    $('<div class="confirmBtn"></div>').appendTo('body')
      .html('<div ><h6>Confirm this Action!</h6></div>')
      .dialog({
        modal: true, title: 'Please confirm', zIndex: 10000, autoOpen: true,
        width: 'auto', resizable: false,
        buttons: {
          Yes: function () {
            // $(obj).removeAttr('onclick');
            // $(obj).parents('.Parent').remove();

            $(this).dialog("close");
            if (portID)
              networkServices(service, portID);
            else if(serviceID)
              servicesInfoUpdate(serviceID, serviceStatus, routeID);

          },
          No: function () {
            $(this).dialog("close");
          }
        },
        close: function (event, ui) {
          $(this).remove();
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

    var idType = $(this).attr('idType');
    var bloque = $(this).attr('bloque');
    var id = $(this).attr(idType);
    var objects = $('#'+ bloque +'-form-' + id).serializeArray();
    var table = $('#'+ bloque +'-form-' + id).attr('dbtable');
    var route = $('#'+ bloque +'-form-' + id).attr('action');
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
    $.ajax(
      {type:"POST",
        url:"/" + route,
        data:infoData,
        success: function(data)
        {
          switch(infoData['table'])
          {
            case 'supportTickets':
              $('#block-' + id).click();
              $.each(data[0], function(i, item)
              {
                if(i == '_token' || i == 'id')
                  return true;
                else
                {
                  $('#' + i + '-' + id).html(item);
                }
              });
              break;
            case 'supportTicketHistory':
              var tbodyData = $('#'+ bloque + '-tbody-' + id).html();
              var htmlContent = "<tr class='even' role='row'>";
              htmlContent += "<td class='sorting_1'>" + data[0]['TimeStamp'] +"</td>";
              htmlContent += "<td class='special-td'>" + data[0]['Comment'] +"</td>";
              htmlContent += "<td>" + data[0]['Status'] +"</td>";
              htmlContent += "<td>" + data[0]['Name'] +"</td>";
              htmlContent += '</tr> ';

              $('#'+ bloque + '-tbody-' + id).html(htmlContent + tbodyData);
              $('#'+ bloque +'-comment-' + id).val('');

              break;
            case 'customers':
              $('#block-' + bloque).click();

              for(var objResp in objects )
              {
                if(objects[objResp]['name'] == '_token')
                  continue;
                  if(bloque == 'b')
                    if(objects[objResp]['name'] == 'CCscode')
                      continue;
                    else if(objects[objResp]['name'] == 'CCnumber')
                      $('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value'].substr(12, 4));
                    else
                      $('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value']);
                  else
                    $('#' + bloque + '-' + objects[objResp]['name']).html(objects[objResp]['value']);
              }
              break;
            case 'supportTicketsID':

              $('.block-' + id + '-' + bloque).click();
//               var newName = $('.block-' + id + '-getName-' + bloque ).val();
//               console.log('.bloque-' + id + '-CID-' + bloque);
               $('#bloque-' + id + '-CID-' + bloque).html(globalName);


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

    var objects = $('#newticketform').serializeArray();
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
    $.ajax(
      {type:"POST",
        url:"/insertCustomerData",
        data:infoData,
        success: function(data)
        {
          
          $('#create-customer-ticket').notify('Ticket created.');
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

    $('.network-functions').addClass('disabled');
    
    var service = service;
    var portID = portID;

    //AJAX request
    $.ajax(
      {type:"GET",
        url:"/" + routes[service],
        data:{'portid':portID},
        success: function(data)
        {
          if (data == 'ERROR')
            alert(data);

          $.each(data,function(i, item)
          {
            $('#' + i).html(item);
          });
          $('#basic-info-net').notify('OK');

          service = 1;
          $.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:{'portid':portID},
              success: function(data)
              {
                $.each(data,function(i, item)
                {
                  $('#' + i).html(item);
                });
              }
            }
          );

          service = 2;
          $.ajax(
            {type:"GET",
              url:"/" + routes[service],
              data:{'portid':portID},
              success: function(data)
              {

                $('#IPs').notify('IPs Array.');
                $('.network-functions').removeClass('disabled');

//                   $.each(data,function(i, item)
//                   {
//                     $('#' + i).html(item);
//                   });

              }
            }
          );

        }
      }
      );

      if (service == 5)
      {
        $('.access-type-net').removeClass('btn-danger ');
        $('.access-type-net').addClass('btn-info');
        $('.access-type-net').html('Activate');
        $('.access-type-net').attr('type','6');
        $('#acces-network-id').html('signup');
      }
      else if ( service == 6 )
      {
        $('.access-type-net').removeClass('btn-info')
        $('.access-type-net').addClass('btn-danger')
        $('.access-type-net').html('Send to Signup');
        $('.access-type-net').attr('type','5');
        $('#acces-network-id').html('yes');
      }

};
var changeSeccionView = function ()
{
  return function(data, textStatus, jqXHR)
  {
    if (customerSeccion)
      $(customerSeccion).css('display', 'none');

    var window = $(this).attr('window');
    customerSeccion = '.' + window;
    $(customerSeccion).fadeIn("slow");
  };
};
var servicesInfoUpdate = function (serviceID, serviceStatus, routeID)
{

  var routes = ['updateCustomerServiceInfo'];

//   $('.network-functions').addClass('disabled');

  //AJAX request
  $.ajax(
    {type:"GET",
      url:"/" + routes[routeID],
      data:{'serviceid':serviceID, 'status':serviceStatus},
      success: function(data)
      {
        if (data == 'ERROR')
          alert(data);

        if (serviceStatus == 'active')
        {
          $('#serviceno-' + serviceID).addClass('disabled ital');
          $('#serviceinfo-status-' + serviceID).html('disabled');
          $('#xservice-btn-id-' + serviceID).attr('displaystatus','disabled');
          $('#xservice-btn-id-' + serviceID).addClass('btn-success fa-check');
          $('#xservice-btn-id-' + serviceID).removeClass('btn-dark');
          $('#xservice-btn-id-' + serviceID).removeClass('fa-times');
        }
        else
        {
          $('#serviceno-' + serviceID).removeClass('disabled ital');
          $('#serviceinfo-status-' + serviceID).html('active');
          $('#xservice-btn-id-' + serviceID).attr('displaystatus','active');
          $('#xservice-btn-id-' + serviceID).addClass('btn-dark fa-times');
          $('#xservice-btn-id-' + serviceID).removeClass('btn-success');
          $('#xservice-btn-id-' + serviceID).removeClass('fa-check');
        }

      }
    }
  );
};
function updateActiveServiceInfo (CSID, ProdIDc)
{

  var ProdID = $('#select-csiu').val();

  console.log(CSID);
  console.log(ProdID);
  console.log(ProdIDc);

  //AJAX request
  $.ajax(
    {type:"GET",
      url:"/updateCustomerActiveServiceInfo",
      data:{'CSID':CSID, 'ProdID':ProdID},
      success: function(data)
      {
        if (data == 'ERROR')
          alert(data);

        $.each(data[0],function(i, item)
        {
          console.log(i + '-'+ProdID+'::' + item);
          $('#' + i + '-' + ProdIDc).html(item);
        });
        
        $('.btn-display-service-'+CSID).notify('Service Updated');


      }
    }
  );
}

























