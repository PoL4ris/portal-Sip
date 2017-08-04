app.controller('techschedulercontroller', function ($scope, $http, customerService) {

    if (customerService.sideBarFlag) {
        $scope.sipTool(2);
        customerService.sideBarFlag = false;
    }

    $scope.today = new Date();
    $scope.techscheduledate = $('.datepicker').val() != '' ? $('.datepicker').val() : (($scope.today.getMonth() + 1) + '/' + $scope.today.getDate() + '/' + $scope.today.getFullYear());
    $scope.loadinganimation = true;
    $scope.renewtable = function (date) {
        $scope.loadinganimation = true;
        console.log('table is renewing');
        $(".datepicker").val(date);  //repopulates date field

        $http.get("/tech-schedule/generatetable", {
            params: {date: date}
        }).then(function (response) {

            $scope.populatetable(response.data);
        });
    };

    $scope.dateselected = function (date) {
        angular.element('#techscheduledate').scope().techscheduledate = date;
        angular.element('#techscheduledate').scope().loadinganimation = true;  //loadinganimation start
        angular.element('#techscheduledate').scope().renewtable($scope.techscheduledate);

    };

    $(".datepicker").datepicker({
        onSelect: $scope.dateselected
    });


    $scope.renewtable($scope.techscheduledate);
    $scope.selectaddress = '';


    $scope.regioncolor = [];
    $scope.regioncolor['0'] = 'black';
    $scope.regioncolor['1'] = 'purple';
    $scope.regioncolor['2'] = 'green';
    $scope.regioncolor['3'] = 'darkblue';
    $scope.regioncolor['4'] = 'lightblue';
    $scope.regioncolor['5'] = 'yellow';
    $scope.regioncolor['6'] = 'red';
    $scope.regioncolor['7'] = 'pink';
    $scope.regioncolor['8'] = 'brown';

    $scope.selectcallback = function (elem, ui) {
        console.log(ui);
        document.getElementById("buildingcode").style.backgroundColor = $scope.regioncolor[ui.item.region];
        $scope.selectaddress = ui.item.address;

    };


    $scope.updatebuildingsearch = function () {
        $scope.bcodesearchvariable = $('#buildingcode').val();
        $http.get('/tech-schedule/bcodesearch', {search: $scope.bcodesearchvariable})
            .then(function (response) {
                $scope.availableTags = [];
                for (var i = 0; i < response.data.length; i++) {

                    // $scope.availableTags.push(response.data[i]['code'] + ' - ' + response.data[i]['address']);
                    $scope.availableTags.push({
                        label: response.data[i]['code'] + ' - ' + response.data[i]['address'],
                        value: response.data[i]['code'],
                        address: response.data[i]['address'],
                        region: response.data[i]['region']
                    });
                }
                if (!$('#buildingcode').val().length) {
                    $scope.selectaddress = '';
                    document.getElementById("buildingcode").style.backgroundColor = 'white';
                }

                $("#buildingcode").autocomplete({
                    source: $scope.availableTags,
                    select: $scope.selectcallback,
                    autoFocus: true
                });


            });

    };


    $scope.dragAppointmentEnd = function (event, ui) {
        //and the info from the target cell
        $scope.loadinganimation = true;
        //console.log('start of drag end')
        var origininput = ui.draggable.find('input').val();
        //console.log('origininput');
        // console.log(origininput);
        // console.log('destination');
        //  console.log( $(this).find('input').val() );
//make an ajax call with both of these value and then figure it out on the back end.

        $.ajax({
            type: "GET",
            url: '/tech-schedule/moveappointment',
            data: {origin: origininput, destination: $(this).find('input').val()},
            success: $scope.movedappointment,
        });

        //  console.log('drag end');

    };

    $scope.parkAppointment = function (event, ui) {

        $('.parking').html(ui.draggable);
        console.log('parking');
    };


    $scope.populatetable = function (data) {
// console.log(data);
        $scope.loadinganimation = true;
        $('.scheduletable').html("");
        $('.scheduletable').append('<tbody>');
        var row = '';  //building the table rows one line at a time.
        var offset = data['offset'];
        var hour;  //hour of appointment.  schedule starts at offset
        var filler;  //the data then ends up in the table cell
        var appointmentcolor = {};  //the color of the appointment.  defined below.
        appointmentcolor['completed'] = 'green';
        appointmentcolor['pending'] = 'red';
        appointmentcolor['onsite'] = 'powerderblue';
        appointmentcolor['problem'] = 'purple';

        row += '<thead><tr><th></th>';  //header columns. blank followed by the name of each tech scheduled.
        for (var hit = 0; hit < data['colums']; hit++) {
            row += '<th>' + data['header'][hit] + '</th>';
        }
        row += '</tr></thead>';
        $('.scheduletable').prepend(row);  //connect it, had to prepend it due to append placing the header at the wrong spot.
        row = '';  //clear the text in the row and start building the next row.

        for (var it1 = 0; it1 < data['rows']; it1++) {

            if (offset < 12) {
                hour = offset + ' a.m.';
            } else if (offset != 12) {
                hour = (offset - 12) + ' p.m.';
            } else {
                hour = '12 p.m.';
            }

            row += '<tr><td>' + hour + '</td>';
            for (var it2 = 0; it2 < data['colums']; it2++) {  //for each row in table
//  console.log(data[it1][it2]['appointment']);
                switch (data[it1][it2]['type']) {
                    case 'closed':
                        filler = 'closed';
                        break;
                    case 'free':
                        filler = '<div class="freeappointment"><input type="checkbox" class="techtimeselect" name="selected[]" value=' + JSON.stringify(data[it1][it2]) + ' ng-model="selected"' + '></div>';
                        break;
                    case 'completed':
                        filler = '<a target="_blank" style="color:' + appointmentcolor['completed'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a>';
                        break;
                    case 'pending':
                        var eventvalue = {
                            'eventid': data[it1][it2]['eventid'],  //google cal event id provided on back end
                            'tech': data[it1][it2]['tech']  //this pending appointment belongs to.
                        };
                        filler = '<div class="dragdiv"><input type="hidden" disabled="disabled" value=' + JSON.stringify(eventvalue) + '></input><a target="_blank" style="color:' + appointmentcolor['pending'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a></div>';
                        //+
                        //'<!--div class="btn-group-xs"><a href="" style="border: solid green 1px; border-top-left-radius: 3px; border-bottom-left-radius: 3px; " class="">O</a><a href="" style="border: solid purple 1px; class="">P</a><a href="" style="border: solid brown 1px;" class="">C</a><a href="" style="border: solid brown 1px;" class="">R</a></div-->';
                        break;
                    case 'onsite':
                        filler = '<a target="_blank" style="color:' + appointmentcolor['onsite'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a>';
                        break;
                    case 'problem':
                        filler = '<a target="_blank" style="color:' + appointmentcolor['problem'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a>';
                        break;
                    default:
                        filler = '';
                        break;
                }
                // console.log(data[it1][it2]['region']);  //here is where we can set the border color around the entry to indicate region.
                if (data[it1][it2]['region']) {
                    row += "<td>" + filler + " <div style='background-color: " + $scope.regioncolor[data[it1][it2]['region']] + "; width: 10px; height: 10px; position: relative; float: right; display: inline-block;'></div>" + '</td>';
                } else {
                    row += "<td>" + filler + '</td>';
                }
            }
            row += '</tr>';
            $('.scheduletable').find('tbody:last').append(row);
            row = '';
            offset++;
        }
//since the table has been redrawn we'll do some cleanups

//drag and droppable appointments
        $(function () {

            $(".dragdiv").draggable({
                snap: ".freeappointment",
                snapMode: "inner",
                revert: true,
                helper: "clone",
                containment: ".schedulecontainer",
                cursor: "move",
                refreshPositionsType: true,
            });

            $('.freeappointment').droppable({
                drop: $scope.dragAppointmentEnd,
                hoverClass: "hovercss",
            });


            $('.parking').droppable({
                drop: $scope.parkAppointment,
                hoverClass: "hovercss",
            });
            $scope.loadinganimation = false;
        });
        //clear the loading animation.
//end drag and drop
    };

    $scope.movedappointment = function (a) {
        var date = $('.datepicker').val();

        $.ajax({
            type: "GET",
            url: '/tech-schedule/generatetable',
            data: {date: date},
            success: $scope.populatetable,
        });
        $('.parking').html("to move an appointment to another day, first park it here.");
    };


    $scope.prepareforappointmentsubmission = function (event) {
        // this is needed to get only the building code from the buildingcode input search otherwise gives bcode + address when scheduling.
        $scope.loadinganimation = true;
        event.preventDefault();

        var fullstring = $("#buildingcode").val();
        var splitstring = fullstring.split(" ");
        $("#buildingcode").val(splitstring[0]);
        var selected = [];
        var submission = getFormValues('scheduleform');
        submission['selected[]'] = [];
        $('input[name="selected[]"]:checked').each(function () {
            submission['selected[]'].push($(this).val());
        });

        $http.get("/tech-schedule/setappointment", {
            params: submission,
        }).then(function (response) {

            if (!response.data['htmlLink']) {
                $scope.errors = response.data;
                return false;
            } else {
                $scope.errors = '';
                document.getElementById("scheduleform").reset();
                document.getElementById("buildingcode").style.backgroundColor = 'white';
                $scope.selectaddress = '';

                $scope.renewtable($scope.techscheduledate);
                $scope.messages = response.data['htmlLink'];
                console.log(response.data);
            }
        });


        return true;
    };


});


app.controller('tech-appointments', function ($scope, $http, customerService) {

    if (customerService.sideBarFlag) {
        $scope.sipTool(2);
        customerService.sideBarFlag = false;
    }

    $scope.userDataAuth = JSON.parse($('#auth-user').val());
    $scope.techalias = $scope.userDataAuth.alias;

    $scope.refresh = function () {
        $scope.loadinganimation = true;
        $http.get('/tech-schedule/myappointments', {params: {tech: $scope.techalias}}).then(function (response) {
                $scope.appointments = response.data;
                // console.log($scope.appointments);
                $scope.loadinganimation = false;
            }
        );
    };

    $scope.refresh();


    $scope.arguments = [];

    $scope.submit = function (event) {
        $scope.loadinganimation = true;
        for (x = 0; x < event.target.length; x++) {
            if (event.target[x].name) {
                $scope.arguments[event.target[x].name] = event.target[x].value;
            }
        }

        console.log($scope.arguments);
        $http.get('/tech-schedule/changestatus',
            {
                params: $scope.arguments
            }).then(function (response) {
                console.log(response);
                $scope.refresh();
            }
        );


    };


});