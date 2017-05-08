

app.controller('tech-scheduler', function ($scope) {
    $scope.date = new Date() | $('.datepicker').val();
    $scope.inittable = $http({
        method: "GET",
        data: {date: $scope.date},
        url: '/tech-schedule/generatetable',
        }).then(populatetable(response));
    $scope.renewtable = renewtable($scope.date);

});


function renewtable(date) {
    $('.datepicker').val(date);  //repopulates date field
    $.ajax({
        type: "GET",
        url: '/tech-schedule/generatetable',
        data: {date: date},
        success: populatetable,
    });
}

function dragAppointmentEnd(event, ui) {

//and the info from the target cell
    var origininput = ui.draggable.find('input').val();

//so make an ajax call with both of these value and then figure it out on the back end.
    $.ajax({
        type: "GET",
        url: '/tech-schedule/moveappointment',
        data: {origin: origininput, destination: $(this).find('input').val()},
        success: movedappointment,
    });

}

function movedappointment(a) {

    var date = $('.datepicker').val();

    $.ajax({
        type: "GET",
        url: '/tech-schedule/generatetable',
        data: {date: date},
        success: populatetable,
    });
}



function updatesearch() {
//ajax get available codes
    var search = $('#bcode').val();

    $.ajax({
        type: "GET",
        url: '/tech-schedule/bcodesearch',
        data: {search: search},
        success: updatesearchsuccess
    });
}


function updatesearchsuccess(data) {

    var availableTags = [];
//console.log(data);
    for (var i = 0; i < data.length; i++) {
        availableTags.push(data[i]['code'] + ' - ' + data[i]['address']);

    }

    $("#bcode").autocomplete({
        source: availableTags,
        select: function (event, ui) {
            console.log(ui);
        }
    });
}

function populatetable(data) {
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
                    filler = '<div class="freeappointment"><input type="checkbox" class="techtimeselect" name="selected[]" value=' + JSON.stringify(data[it1][it2]) + '></div>';
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
            drop: dragAppointmentEnd,
            hoverClass: "hovercss",
        });

    });

//end drag and drop

} //end of populate table


function preparesubmit() {
    // this is needed to get only the bcode from the bcode input search otherwise gives bcode + address when scheduling.
    var fullstring = $("#bcode").val();
    var splitstring = fullstring.split(" ");
    $("#bcode").val(splitstring[0]);
    return true;
}


//document init
$(document).ready(function () {


    $('.datepicker').datepicker({  //init the datepicker
        autoclose: true,
        todayHighlight: true,
        clearBtn: false,
        format: 'mm-dd-yyyy',
        onSelect: renewtable,
        todayBtn: "linked",
        startView: 0, maxViewMode: 0, minViewMode: 0

    }).on('changeDate', renewtable);


    //force proper date display
    var date;
    //olddate is defined in the main file due to use of laravel old.  repopulate date input with proper date if old date exists.
    if (olddate) {

        date = new Date(olddate);
    } else {
        date = new Date();
    }

    $('.datepicker').val((date.getMonth() + 1) + '/' + date.getDate() + '/' + date.getFullYear());

    $.ajax({  //init schedule table
        type: "GET",
        url: '/tech-schedule/generatetable',
        data: {date: date},
        success: populatetable
    });

    $("#service").selectmenu();  //nice looking select menu
    $("#action").selectmenu();

});



