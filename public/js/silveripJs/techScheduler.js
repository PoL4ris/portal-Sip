app.controller('techschedulercontroller', function ($scope, $http, customerService) {

//    if (customerService.sideBarFlag) {
//        $scope.sipTool(2);
//        customerService.sideBarFlag = false;
//    }

    $scope.today = new Date();
    $scope.techscheduledate = $('.datepicker').val() != '' ? $('.datepicker').val() : (($scope.today.getMonth() + 1) + '/' + $scope.today.getDate() + '/' + $scope.today.getFullYear());

    $scope.renewtable = function (date) {
        console.log('table is renewing');
        $('.datepicker').val(date);  //repopulates date field
        $scope.loadinganimation = true;
        $http.get("/tech-schedule/generatetable", {
            params: {date: date}
        }).then(function (response) {

            $scope.populatetable(response.data);
        });
    };


    $('.datepicker').datepicker({
        onSelect: function (date) {
            $scope.techscheduledate = date;
            console.log($scope.techscheduledate);
            $scope.renewtable($scope.techscheduledate);

        }
    });

    $scope.renewtable($scope.techscheduledate);

    $scope.updatebuildingsearch = function () {
        $scope.bcodesearchvariable = $('#buildingcode').val();
        $http.get('/tech-schedule/bcodesearch', {search: $scope.bcodesearchvariable})
            .then(function (response) {
                $scope.availableTags = [];
                for (var i = 0; i < response.data.length; i++) {

                    $scope.availableTags.push(response.data[i]['code'] + ' - ' + response.data[i]['address']);
                }

                $("#buildingcode").autocomplete({
                    source: $scope.availableTags
                });


            });

    };


    $scope.dragAppointmentEnd = function (event, ui) {
        //and the info from the target cell
        var origininput = ui.draggable.find('input').val();

//so make an ajax call with both of these value and then figure it out on the back end.
        $.ajax({
            type: "GET",
            url: '/tech-schedule/moveappointment',
            data: {origin: origininput, destination: $(this).find('input').val()},
            success: $scope.movedappointment,
        });
        $scope.loadinganimation = true;

    };


    $scope.populatetable = function (data) {
// console.log(data);

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
                        filler = '<a style="color:' + appointmentcolor['completed'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a>';
                        break;
                    case 'pending':
                        var eventvalue = {
                            'eventid': data[it1][it2]['eventid'],  //google cal event id provided on back end
                            'tech': data[it1][it2]['tech']  //this pending appointment belongs to.
                        };
                        filler = '<div class="dragdiv"><input type="hidden" disabled="disabled" value=' + JSON.stringify(eventvalue) + '></input><a style="color:' + appointmentcolor['pending'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a></div>';
                        break;
                    case 'onsite':
                        filler = '<a style="color:' + appointmentcolor['onsite'] + ';" href=' + data[it1][it2]['appointment']['htmlLink'] + '>' + data[it1][it2]['appointment']['summary'] + '</a>';
                        break;
                    default:
                        filler = '';
                        break;
                }

                row += '<td>' + filler + '</td>';

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
                containment: ".scheduletable",
                cursor: "move",
                refreshPositionsType: true,
            });

            $('.freeappointment').droppable({
                drop: $scope.dragAppointmentEnd,
                hoverClass: "hovercss",
            });
            $scope.loadinganimation = false;  //clear the loading animation.
        });

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
        }).then(function () {

            $scope.renewtable($scope.techscheduledate);
        });

        $scope.loadinganimation = false;
        return true;
    };

    return;

});


app.controller('tech-appointments', function ($scope, $http, customerService) {

    if (customerService.sideBarFlag) {
        $scope.sipTool(2);
        customerService.sideBarFlag = false;
    }

    $scope.userDataAuth = JSON.parse($('#auth-user').val());
    $scope.techalias = $scope.userDataAuth.alias;

    $scope.refresh = function () {
        $http.get('/tech-schedule/myappointments', {params: {tech: $scope.techalias}}).then(function (response) {
                $scope.appointments = response.data;
                console.log($scope.appointments);
                $scope.loadinganimation = false;
            }
        );
    };

    $scope.refresh();
    $scope.loadinganimation = false;


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
                $scope.refresh();
            }
        );


    };

    return;
});