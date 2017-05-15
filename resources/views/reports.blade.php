
<?php

$monthsArray    = array_keys($retail_stats);
$months         = '"' . implode('","', array_keys($retail_stats)). '"';
$latestMonth    = $monthsArray[count($monthsArray)-1];
$data_pointsArr = array();
foreach($retail_stats as $statObj)
{
    $detailsJson      = $statObj->revenue_data;
    $detailsArr       = json_decode($detailsJson,true);
    $data_pointsArr[] = $detailsArr['amount'];
}

$data_points = implode(', ', $data_pointsArr);
?>
<!-- this is what the user will see -->
<h5 class="text-primary text-left padding-bottom-3">Past <strong>12 Months</strong></h5>
<canvas id="revenueChart"></canvas>


<script type="text/javascript">
    /*
     * LOAD AJAX PAGES
     */
    function loadURLGet(url, container, data, loaderMessage) {
        loadAjaxURL(url,container, 'GET', data, loaderMessage);
    }

    function loadURLPost(url, container, data, loaderMessage) {
        loadAjaxURL(url,container, 'POST', data, loaderMessage);
    }

    function loadURL(url, container) {
        loadAjaxURL(url,container);
    }

    function loadAjaxURL(url, container, type, data, loaderMessage) {
        console.log(url +"|: URL :|"+  container +"|: CONTAINER :|"+  type +"|: TYPE :|"+  data +"|: DATA :|"+  loaderMessage +"|: LOADERMESSAGE :|");
        console.log(url);
        console.log(container);
        console.log(type);
        console.log(data);
        console.log(loaderMessage);
//    return;
        // debugState
        if (debugState){
            root.root.console.log("Loading URL: %c" + url, debugStyle);
            root.root.console.log("Loading URL data: %c" + data, debugStyle);
            root.root.console.log("Loading URL loaderMessage: %c" + loaderMessage, debugStyle);
        }


        $.ajax({
            type : type,
            url : url,
            data: data,
            dataType : 'html',
            cache : true, // (warning: setting it to false will cause a timestamp and will call the request twice)
            beforeSend : function() {

                //IE11 bug fix for googlemaps (delete all google map instances)
                //check if the page is ajax = true, has google map class and the container is #content
                if ($.navAsAjax && $(".google_maps")[0] && (container[0] == $("#content")[0]) ) {

                    // target gmaps if any on page
                    var collection = $(".google_maps"),
                        i = 0;
                    // run for each	map
                    collection.each(function() {
                        i ++;
                        // get map id from class elements
                        var divDealerMap = document.getElementById(this.id);

                        if(i == collection.length + 1) {
                            // "callback"
                        } else {
                            // destroy every map found
                            if (divDealerMap) divDealerMap.parentNode.removeChild(divDealerMap);

                            // debugState
                            if (debugState){
                                root.console.log("Destroying maps.........%c" + this.id, debugStyle_warning);
                            }
                        }
                    });

                    // debugState
                    if (debugState){
                        root.console.log("✔ Google map instances nuked!!!");
                    }

                } //end fix

                // destroy all datatable instances
                if ( $.navAsAjax && $('.dataTables_wrapper')[0] && (container[0] == $("#content")[0]) ) {

                    var tables = $.fn.dataTable.fnTables(true);
                    $(tables).each(function () {

                        if($(this).find('.details-control').length != 0) {
                            $(this).find('*').addBack().off().remove();
                            $(this).dataTable().fnDestroy();
                        } else {
                            $(this).dataTable().fnDestroy();
                        }

                    });

                    // debugState
                    if (debugState){
                        root.console.log("✔ Datatable instances nuked!!!");
                    }
                }
                // end destroy

                // pop intervals (destroys jarviswidget related intervals)
                if ( $.navAsAjax && $.intervalArr.length > 0 && (container[0] == $("#content")[0]) && enableJarvisWidgets ) {

                    while($.intervalArr.length > 0)
                        clearInterval($.intervalArr.pop());
                    // debugState
                    if (debugState){
                        root.console.log("✔ All JarvisWidget intervals cleared");
                    }

                }
                // end pop intervals

                // destroy all widget instances
                if ( $.navAsAjax && (container[0] == $("#content")[0]) && enableJarvisWidgets && $("#widget-grid")[0] ) {

                    $("#widget-grid").jarvisWidgets('destroy');
                    // debugState
                    if (debugState){
                        root.console.log("✔ JarvisWidgets destroyed");
                    }

                }
                // end destroy all widgets

                // cluster destroy: destroy other instances that could be on the page
                // this runs a script in the current loaded page before fetching the new page
                if ( $.navAsAjax && (container[0] == $("#content")[0]) ) {



                    // this function is below the pagefunction for all pages that has instances

                    if (typeof pagedestroy == 'function') {

                        try {
                            pagedestroy();

                            if (debugState){
                                root.console.log("✔ Pagedestroy()");
                            }
                        }
                        catch(err) {
                            pagedestroy = undefined;

                            if (debugState){
                                root.console.log("! Pagedestroy() Catch Error");
                            }
                        }

                    }

                    // destroy all inline charts

                    if ( $.fn.sparkline && $("#content .sparkline")[0] ) {
                        $("#content .sparkline").sparkline( 'destroy' );

                        if (debugState){
                            root.console.log("✔ Sparkline Charts destroyed!");
                        }
                    }

                    if ( $.fn.easyPieChart && $("#content .easy-pie-chart")[0] ) {
                        $("#content .easy-pie-chart").easyPieChart( 'destroy' );

                        if (debugState){
                            root.console.log("✔ EasyPieChart Charts destroyed!");
                        }
                    }



                    // end destory all inline charts

                    // destroy form controls: Datepicker, select2, autocomplete, mask, bootstrap slider

                    if ( $.fn.select2 && $("#content select.select2")[0] ) {
                        $("#content select.select2").select2('destroy');

                        if (debugState){
                            root.console.log("✔ Select2 destroyed!");
                        }
                    }

                    if ( $.fn.mask && $('#content [data-mask]')[0] ) {
                        $('#content [data-mask]').unmask();

                        if (debugState){
                            root.console.log("✔ Input Mask destroyed!");
                        }
                    }

                    if ( $.fn.datepicker && $('#content .datepicker')[0] ) {
                        $('#content .datepicker').off();
                        $('#content .datepicker').remove();

                        if (debugState){
                            root.console.log("✔ Datepicker destroyed!");
                        }
                    }

                    if ( $.fn.slider && $('#content .slider')[0] ) {
                        $('#content .slider').off();
                        $('#content .slider').remove();

                        if (debugState){
                            root.console.log("✔ Bootstrap Slider destroyed!");
                        }
                    }

                    // end destroy form controls


                }
                // end cluster destroy

                // empty container and var to start garbage collection (frees memory)
                pagefunction = null;
                container.removeData().html("");

                if(loaderMessage == null){
                    // place cog
                    container.html('<h1 class="ajax-loading-animation"><i class="fa fa-cog fa-spin"></i> Loading...</h1>');
                } else {
                    container.html(loaderMessage);
                }


                // Only draw breadcrumb if it is main content material
                if (container[0] == $("#content")[0]) {

                    // clear everything else except these key DOM elements
                    // we do this because sometime plugins will leave dynamic elements behind
                    $('body').find('> *').filter(':not(' + ignore_key_elms + ')').empty().remove();

                    // draw breadcrumb
                    drawBreadCrumb();

                    // scroll up
                    $("html").animate({
                        scrollTop : 0
                    }, "fast");
                }
                // end if
            },
            success : function(data) {

                // dump data to container
                container.css({
                    opacity : '0.0'
                }).html(data).delay(50).animate({
                    opacity : '1.0'
                }, 300);

                // clear data var
                data = null;
                container = null;
            },
            error : function(xhr, status, thrownError, error) {
                container.html('<h4 class="ajax-loading-error"><i class="fa fa-warning txt-color-orangeDark"></i> Error requesting <span class="txt-color-red">' + url + '</span>: ' + xhr.status + ' <span style="text-transform: capitalize;">'  + thrownError + '</span></h4>');
            },
            async : true
        });

    }
</script>


<script type="text/javascript">



    $(document).ready(function () {

        var date        = "{{ $latestMonth }}";
        var shortname   = "{{ $shortname }}";
        var root_url    = "{{ Request::root() }}/"


        var ajax_url2 = root_url + "reports/display-location-stats"; // put this in php file
        var containerName2 = '#location-details';
        loadURLGet(ajax_url2, $(containerName2), { "shortname": shortname }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');

        var ajax_url = root_url + "reports/display-retail-revenue-details"; // put this in php file
        var containerName = '#monthly-summary';
        loadURLGet(ajax_url, $(containerName), { "shortname": shortname , "date": date }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');



        // BAR CHART
        var barOptions = {
            //Boolean - Whether the scale should start at zero, or an order of magnitude down from the lowest value
            scaleBeginAtZero : true,
            //Boolean - Whether grid lines are shown across the chart
            scaleShowGridLines : true,
            //String - Colour of the grid lines
            scaleGridLineColor : "rgba(0,0,0,.05)",
            //Number - Width of the grid lines
            scaleGridLineWidth : 1,
            //Boolean - If there is a stroke on each bar
            barShowStroke : true,
            //Number - Pixel width of the bar stroke
            barStrokeWidth : 1,
            //Number - Spacing between each of the X value sets
            barValueSpacing : 5,
            //Number - Spacing between data sets within X values
            barDatasetSpacing : 1,
            //Boolean - Re-draw chart on page resize
            responsive: true,
            // Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
            maintainAspectRatio: false,
            //String - A legend template

            {{--legendTemplate : "<ul class='<%=name.toLowerCase()%>-legend'><% for (var i=0; i<datasets.length; i++){%><li><span style=\"background-color:<%=datasets[i].lineColor%>\"></span><%if(datasets[i].label){%><%=datasets[i].label%><%}%></li><%}%></ul>",--}}
            scaleLabel: function(label){
                return  '$' + label.value.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            }
        }

        var barData = {
            labels: [<?php echo $months; ?>],
            datasets: [
                {
                    label: "My First dataset",
                    fillColor: "rgba(220,220,220,0.5)",
                    strokeColor: "rgba(220,220,220,0.8)",
                    highlightFill: "rgba(220,220,220,0.75)",
                    highlightStroke: "rgba(220,220,220,1)",
                    data: [{{ $data_points }}]
                }
            ]
        };

        // render chart
        var ctx = document.getElementById("revenueChart").getContext("2d");
        myNewChart_1 = new Chart(ctx).Bar(barData, barOptions);
        // END BAR CHART


        $("#revenueChart").click(function(evt){
            // => activePoints is an array of points on the canvas that are at the same position as the click event.
            var activeBars = myNewChart_1.getBarsAtEvent(evt);
            var date = activeBars[0].label;
            var shortname = "{{ $shortname }}";
            var root_url = "{{ Request::root() }}/"
            var ajax_url = root_url + "reports/display-retail-revenue-details"; // put this in php file
            var containerName = '#monthly-summary';
            loadURLGet(ajax_url, $(containerName), { "shortname": shortname , "date": date }, '<h4 class="ajax-loading-animation"><i class="fa  fa-spinner fa-spin"></i></h4>');


        });


        // custom toolbar
        //                $("div.toolbar").html('<div class="text-right"><img src="img/logo.png" alt="SmartAdmin" style="width: 111px; margin-top: 3px; margin-right: 10px;"></div>');

        // Apply the filter
        $("#voip-notice-table thead th input[type=text]").on( 'keyup change', function () {
            sptOtable
                .column( $(this).parent().index()+':visible' )
                .search( this.value )
                .draw();

        } );

        $('#voip-notice-table tbody').on( 'click', 'tr', function () {
            var salesid = $(this).attr('salesid');
            var ajax_url = "sales/property"; // put this in php file
            var containerName  = '#content';
            loadURLGet(ajax_url, $(containerName), { "salesid": salesid }); //, containerName, "Loading Page");

            //                    $('#content').load('/sales/property', { "salesid": salesid });
            //                    if ( $(this).hasClass('selected') ) {
            //                        $(this).removeClass('selected');
            //                    }
            //                    else {
            //                        table.$('tr.selected').removeClass('selected');
            //                        $(this).addClass('selected');
            //                    }
        } );
    });

</script>


