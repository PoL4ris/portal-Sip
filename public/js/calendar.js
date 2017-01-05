//CALENDAR ENGINE

var popEventTool = '';
var objArray = '';
var objEdit = null;
var winSize  = $( document ).width();
function eventTool(event, id, toDo){
  event.stopPropagation();
  if (id == 9)
    return;

  if (id) {
    $(popEventTool).fadeOut();
    $('#' + id + '-tooltip').fadeIn();
    popEventTool = '#' + id + '-tooltip';
  }

  if (toDo == 1) {
    console.log('this Delete');
    objEdit = getIdEventClean (popEventTool);

    //THIS EDITS EVENT
    var request = gapi.client.calendar.events.delete({
      'calendarId': 'help@silverip.com',
      'eventId': objEdit.id
    });
    request.execute(function(event) {

      $("[idEvent=" + objEdit.id + "]").html('<div class="event-create anim" ></div>');
      $("[idEvent=" + objEdit.id + "]").attr('idEvent', '');
      $("[idEvent=" + objEdit.id + "]").attr('objindex', '');
      angular.element('#output').scope().ttt();
//       $scope.ttt();
    });
    angular.element('#output').scope().eventUpdateNotify('Event Delete');
//     $scope.eventUpdateNotify('Event Delete');

  }
  if (toDo == 2) {
    console.log('this edits');

    objEdit = getIdEventClean (popEventTool);

    var t1 = getTimeSelct(objEdit, 'start');
    var t2 = getTimeSelct(objEdit, 'end');
    angular.element('#output').scope().setSelectValue(t1,t2);
    angular.element('#output').scope().$apply()

//     editWindow();
    $("#newEventModal").modal();
    var e = jQuery.Event("keydown");
    e.keyCode = 39;
    $('.calendar-select').fadeIn();
    $('.input-titulo').val(objEdit.summary).focus();
    $('.input-where').val(objEdit.location).focus();
    $('.input-description').val(objEdit.description).focus().trigger(e);
    $('#select-calendar').focus();//no funciona.

  }
  if (toDo == 3)
    $(popEventTool).fadeOut(); //This close!

}
function getTimeSelct(objEdit, when){

  var inicio    = new Date(objEdit[when]['dateTime']);

  var ini   = inicio.toLocaleTimeString();
  var xA = ini.split(':')[0];
  var xB = ini.split(':')[1];
  var xC = ini.split(':')[2];
  var xD = xC.split(' ')[1].toLowerCase();

  return xA + ':' + xB + xD;

}
function getIdEventClean(str) {
  var fStr = str.split('-box-tooltip')[0];
  var eventIndexEdit = $(fStr).attr('objindex');
  return objArray[eventIndexEdit];
}
function editWindow() {
  $('#transparent-bg').fadeIn();
  $('#new-event').fadeIn();
  $('.calendar-select').fadeIn();
  $('#new-event').css('left', '60px');
}
app.controller('calController', function ($scope){
  console.log('inside--calController');
  /*
   For new Tech:
   var who in ttt, setEventName
   Add html #div
   */

  $scope.eventsData = '';
  var objects = null;
  var tttFunction = null;
  var idEventToSet = '';
  var regExp = /\(([^)]+)\)/;
  var who = {'0':''
    ,'Izzy':'1'
    ,'Melvin':'2'
    ,'Abe':'3'
    ,'Eli':'4'
    ,'Brian':'5'
    ,'Charlie':'6'
    ,'Pablo':'7'
  };
  var size = 50;
  var maxDateTime = getMaxDateTime();
  var minDateTime = getMinDateTime();
  var timeLine = '<div class="time-line"></div>';

  $scope.timeSelect = ['8:00am','8:30am',
    '9:00am', '9:30am',
    '10:00am', '10:30am',
    '11:00am', '11:30am',
    '12:00pm', '12:30pm',
    '1:00pm', '1:30pm',
    '2:00pm', '2:30pm',
    '3:00pm', '3:30pm',
    '4:00pm', '4:30pm',
    '5:00pm', '5:30pm',
    '6:00pm', '6:30pm',
    '7:00pm', '7:30pm',
    '8:00pm', '8:30pm',
    '9:00pm', '9:30pm',
    '10:00pm', '10:30pm',
    '11:00pm', '11:30pm'];
  $scope.idCalendars = {'Auth':'silverip.com_00bq2i57k1e83g8nuue29fenl8@group.calendar.google.com'
    ,'Customer Canceled':'silverip.com_jgg5r8u7ohr8n8456nrt71m9lk@group.calendar.google.com'
    ,'Onsite':'silverip.com_elc3ctcfdgle90b5jntqlpfnh8@group.calendar.google.com'
    ,'Problem Ticket':'silverip.com_e5glc3dbassqckgva13d3qg7ic@group.calendar.google.com'
    ,'Completed Ticket':'silverip.com_tpbi296lb5hldljngg6fcmjsac@group.calendar.google.com'
    ,'Ticket':'help@silverip.com'};
  $scope.calendarsName = {'Auth':'Auth'
    ,'Ticket':'Ticket'
    ,'Customer Canceled':'Customer Canceled'
    ,'Onsite':'Onsite'
    ,'Problem Ticket':'Problem Ticket'
    ,'Completed Ticket':'Completed Ticket'
  };
  $scope.calendarColors = {'Auth':'9'//blue
    ,'Customer Canceled':'3'//purple
    ,'Onsite':'9'//blue
    ,'Problem Ticket':'3'//purple
    ,'Completed Ticket':'10'//green
    ,'Ticket':'11'//red
  };
  $scope.timeIni;
  $scope.timeFin;
  $scope.eventtitle;

  $scope.timeLine = function (){
    $('.time-line').remove();
    $scope.globalDate = new Date();
    $scope.gDateHr = $scope.globalDate.getHours();
    $scope.gDateMin = $scope.globalDate.getMinutes();

    $('#t-' + $scope.gDateHr).append(timeLine);
    $('.time-line').css('top', ($scope.gDateMin/60 * 50) + 'px' );
  };
  $scope.ttt = function () {

    clearInterval(tttFunction);

    $('.event-create').html('');

    var request = gapi.client.calendar.events.list({
      'calendarId'    : 'help@silverip.com',
      'timeMin'       : minDateTime,
      'timeMax'       : maxDateTime,
      'showDeleted'   : false,
      'singleEvents'  : true,
      'orderBy'       : 'startTime'
    });


    request.execute(function(resp) {
      objects  = resp.items;
      objArray = resp.items;
      createEventsHtml(objects);

    });

    $scope.timeLine();

//     tttFunction = setInterval(function(){$scope.ttt();$scope.timeLine ();}, 25000);
  }
  $(".event-create").dblclick(function() {

    $("#newEventModal").modal()

    idEventToSet = $(this.parentElement)[0].id;

    setEventName(idEventToSet);

//     $('#transparent-bg').fadeIn();
//     $('#new-event').fadeIn();
//     $('.calendar-select').fadeOut();
//
//     if(winSize <= 420){
//       $('#new-event').css('left', '0px');
//       $('#new-event').css('width', '80%');
//     }
//     else
//       $('#new-event').css('left', '0px');

    $('.input-titulo').focus();

  });
  function getMaxDateTime (){
    var d = new Date();
    return d.getFullYear() + '-' + ((d.getMonth() + 1) <= 9 ? ('0' + (d.getMonth() + 1)) : (d.getMonth() + 1)) + '-' + d.getDate() + 'T23:59:59-05:00';
  };
  function getMinDateTime (){
    var d = new Date();
    return d.getFullYear() + '-' + ((d.getMonth() + 1) <= 9 ? ('0' + (d.getMonth() + 1)) : (d.getMonth() + 1)) + '-' + d.getDate() + 'T00:00:00-05:00';
  };
  function createEventsHtml(objects){
    for(var obj in objects ) {
      var geNameTmp = regExp.exec(objects[obj]['summary']);

      var inicio    = new Date(objects[obj]['start']['dateTime']);
      var fin       = new Date(objects[obj]['end']['dateTime']);

      var whoIs = geNameTmp?geNameTmp[1]:'';
      var ini   = inicio.toLocaleTimeString();
      var fn    = fin.toLocaleTimeString();

      if (ini == 'Invalid Date')
        continue;

      //inicio
      var xA = ini.split(':')[0];
      var xB = ini.split(':')[1];
      var xC = ini.split(':')[2];
      var xD = xC.split(' ')[1];

      //fin
      var xE = fn.split(':')[0];
      var xF = fn.split(':')[1];
      var xG = fn.split(':')[2];
      var xH = xG.split(' ')[1];
      var top = xB;
      var diffHours = Math.abs(fin - inicio) / 36e5;

      var tmpColor = 'default';

      if(objects[obj]['colorId'])
        tmpColor = objects[obj]['colorId'];


      var content =  '<div class="event-container color-' + tmpColor + ' eid' + objects[obj]['id'] + '" id="' + xA + '-' + xD + '-' + who[whoIs] + '-box" onclick="eventTool(event, this.id, 0);">';
      content +=  '<p class="ec-time cl'+ objects[obj]['id'] + '">' + (xA + (xB > 0 ? (':' + xB) : '')) + xD + ' - ' + (xE + (xF > 0 ? (':' + xF) : '')) + xH + '</p>';
      content +=  '<p class="ec-titulo">' + objects[obj]['summary'] + '</p>';
      content +=  '<div class="tooltip-event" id="' + xA + '-' + xD + '-' + who[whoIs] + '-box-tooltip" onclick="eventTool(event, 9,9)">';
      content +=  '<div>' + (xA + (xB > 0 ? (':' + xB) : '')) + xD + ' - ' + (xE + (xF > 0 ? (':' + xF) : '')) + xH + '</div>';
      content +=  '<div class="ect-desc">' + objects[obj]['description'] + '</div>';
      content +=  '<button class="dlt-btn-tool tool-btn" onclick="eventTool(event, null, 1)">Delete</button>';
      content +=  '<button class="edit-btn-tool tool-btn" onclick="eventTool(event, null, 2)">Edit event</button>';
      content +=  '<button class="close-btn-tool" onclick="eventTool(event, null, 3)">X</button>';
      content += '</div>';
      content += '</div>';




      $("[idEvent=" + objects[obj]['id'] + "]").html('<div class="event-create anim" ></div>');
      $("[idEvent=" + objects[obj]['id'] + "]").attr('idEvent', '');
      $("[idEvent=" + objects[obj]['id'] + "]").attr('objindex', '');
      $('#' + xA + '-' + xD + '-' + who[whoIs]).attr('idEvent', objects[obj]['id']);
      $('#' + xA + '-' + xD + '-' + who[whoIs]).attr('objindex', obj);

      if ($('#' + xA + '-' + xD + '-' + who[whoIs]))
        $('#' + xA + '-' + xD + '-' + who[whoIs]).append(content);
      else
        $('#' + xA + '-' + xD + '-' + who[whoIs]).html(content);

      $('.' + 'eid' + objects[obj]['id']).css('top', (xB/60 * size) + 'px' );
      $('.' + 'eid' + objects[obj]['id']).css('height', (size * diffHours))
      if((size * diffHours) == 25)
        $('.' + 'cl' + objects[obj]['id']).css('float', 'left');


    }
  }
  $scope.cancelNewEvent = function () {

    console.log('evento nuevo cancelado');
//     $('#transparent-bg').fadeOut();
//     $('#new-event').fadeOut();
//     $('calendar-select').fadeOut();
//     if(winSize <= 420)
//       $('#new-event').css('width', '40%');
//     else
//       $('#new-event').css('left', '-50%');

    this.eventtitle = '';
    this.timeIni = '';
    this.timeFin = '';
    this.where = '';
    this.description = '';
    this.calendar = '';

    $('.input-titulo').val('');
    $('.input-where').val('');
    $('.input-description').html('');
    $('.input-description').val('');

    objEdit = null;

    $scope.ttt();

    $("#newEventModal").modal("hide");

  }
  $scope.setNewEvent = function (){
    console.log('this will create the new event and the id =' + idEventToSet);

    var t  = this.eventtitle ? this.eventtitle : objEdit.summary;
    var w  = this.where ? this.where : objEdit.location;
    var d  = this.description ? this.description : objEdit.description;

    var ti = this.timeIni;
    var tf = this.timeFin;

    var calSelect = this.calendar;

    $('#select_3').focus();
    $('#select_5').focus();

    if(!ti || !tf)
      return;


    var t1 = getFormatTime("00:00", ti);
    var t2 = getFormatTime("00:00", tf);

    var tIso1 = createIsoDate(t1);
    var tIso2 = createIsoDate(t2);

    if (!t || !ti || !tf || !w)
      return;

    var event = {
      'summary': t,
      'location': w,
      'description': d,
      'colorId': 11,
      'start': {
        'dateTime': tIso1,
        'timeZone': 'America/Chicago'
      },
      'end': {
        'dateTime': tIso2,
        'timeZone': 'America/Chicago'
      }
    };

    //THIS EDITS EVENT IF
    if(objEdit) {
      if (calSelect)
      {
        event.colorId = $scope.calendarColors[calSelect];

        var request = gapi.client.calendar.events.insert({
          'calendarId': $scope.idCalendars[calSelect],
          'resource': event
        });
        request.execute(function(event) {
          console.log('Event created: ' + event.htmlLink);
          $scope.notifySmallBoxAlert('Event Created');
          $scope.ttt();
        });

        var request = gapi.client.calendar.events.update({
          'calendarId': 'help@silverip.com',
          'eventId': objEdit.id,
          'resource': event
        });
        request.execute(function(event) {
          console.log(event);
          $scope.ttt();
        });
        $scope.notifySmallBoxAlert('Event Updated');
      }
      else
      {
        var request = gapi.client.calendar.events.update({
          'calendarId': 'help@silverip.com',
          'eventId': objEdit.id,
          'resource': event
        });
        request.execute(function(event) {
          console.log(event);
          $scope.ttt();
        });
        $scope.notifySmallBoxAlert('Event Updated');
      }
    }
    //THIS IS COMMON INSERT
    else {
      var geNameTmp = regExp.exec(event.summary)[1];
      var tmpNameSplit = geNameTmp.split('/');
      var summary = event.summary.split('(')[1].split(')')[1];

      if (tmpNameSplit[1])
      {
        var event2 = event;

        event.summary = '(' + tmpNameSplit[0] + ')' + summary;

        var request1 = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event
        });
        request1.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });

        event2.summary = '(' + tmpNameSplit[1] + ')' + summary;

        var request2 = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event2
        });
        request2.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });

      }
      else
      {

        var request = gapi.client.calendar.events.insert({
          'calendarId': 'help@silverip.com',
          'resource': event
        });
        request.execute(function(event) {
          console.log('Event created: ' + event.summary);
          $scope.ttt();
        });
      }
    }

    $scope.cancelNewEvent();

  }
  function getFormatTime(format, str) {
    var hours = Number(str.match(/^(\d+)/)[1]);
    var minutes = Number(str.match(/:(\d+)/)[1]);
    var AMPM = str.match(/\s?([AaPp][Mm]?)$/)[1];
    var pm = ['P', 'p', 'PM', 'pM', 'pm', 'Pm'];
    var am = ['A', 'a', 'AM', 'aM', 'am', 'Am'];
    if (pm.indexOf(AMPM) >= 0 && hours < 12) hours = hours + 12;
    if (am.indexOf(AMPM) >= 0 && hours == 12) hours = hours - 12;
    var sHours = hours.toString();
    var sMinutes = minutes.toString();
    if (hours < 10) sHours = "0" + sHours;
    if (minutes < 10) sMinutes = "0" + sMinutes;
    if (format == '0000') {
      return (sHours + sMinutes);
    } else if (format == '00:00') {
      return (sHours + ":" + sMinutes);
    } else {
      return false;
    }
  }
  function createIsoDate(time) {
    var fecha = new Date();
    var anio = fecha.getFullYear();
    var mes = fecha.getMonth()+1;
    var dia = fecha.getDate();

    return (anio + '-' + mes + '-' + dia + 'T' + time + ':00-05:00');
  }
  function setEventName(id){
    var who = {'0':''
      ,'1':'Izzy'
      ,'2':'Melvin'
      ,'3':'Abe'
      ,'4':'Eli'
      ,'5':'Brian'
      ,'6':'Charlie'
      ,'7':'Pablo'
    };
    var splitVal = id.split('-');
    var texto = '(' + who[splitVal[2]] + ')';
    $scope.eventtitle = texto;
    $('.input-titulo').val(texto).focus();
  }
  $scope.setSelectValue = function (t1,t2){
    $scope.timeIni = t1;
    $scope.timeFin = t2;
  };
  $scope.notifySmallBoxAlert = function (algo)
  {
    $.smallBox({
      title: "NOTIFY ALERT" + algo,
      content: "<i class='fa fa-clock-o'></i> <i>2 seconds ago...</i>",
      color: "#739E73",
      iconSmall: "fa fa-thumbs-up bounce animated",
      timeout: 6000
    });
  }
});
